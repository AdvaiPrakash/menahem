<?php

namespace App\Services;

use App\Models\CustomerLead;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class CustomerLeadService
{
    /**
     * Record or update unified Customer Lead upon an order placement.
     */
    public function recordLeadFromOrder(Order $order): CustomerLead
    {
        $email = strtolower(trim($order->customer_email));
        $shipping = $order->shipping_address ?? [];

        $addressText = implode(', ', array_filter([
            $shipping['address_line1'] ?? '',
            $shipping['address_line2'] ?? '',
            $shipping['city'] ?? '',
            $shipping['state'] ?? '',
            $shipping['pin_code'] ?? '',
        ]));

        $lead = CustomerLead::where('email', $email)->first();

        if (! $lead) {
            $lead = new CustomerLead([
                'email' => $email,
                'customer_type' => $order->customer_type ?: 'guest',
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'delivery_address' => $addressText,
                'city' => $shipping['city'] ?? null,
                'state' => $shipping['state'] ?? null,
                'pin_code' => $shipping['pin_code'] ?? null,
                'customer_gstin' => $order->customer_gstin,
                'user_id' => $order->user_id,
                'first_purchase_date' => Carbon::now(),
            ]);
        }

        // Update aggregated order statistics
        $lead->user_id = $order->user_id ?: $lead->user_id;
        $lead->name = $order->customer_name ?: $lead->name;
        $lead->phone = $order->customer_phone ?: $lead->phone;
        if (! empty($addressText)) {
            $lead->delivery_address = $addressText;
            $lead->city = $shipping['city'] ?? $lead->city;
            $lead->state = $shipping['state'] ?? $lead->state;
            $lead->pin_code = $shipping['pin_code'] ?? $lead->pin_code;
        }

        // If registered or Google, upgrade customer type
        if ($order->customer_type && in_array($order->customer_type, ['registered', 'google'], true)) {
            $lead->customer_type = $order->customer_type;
        }

        $lead->last_purchase_date = Carbon::now();
        $lead->total_orders = Order::where('customer_email', $email)->where('payment_status', 'paid')->count();
        $lead->total_purchase_amount = (float) Order::where('customer_email', $email)->where('payment_status', 'paid')->sum('grand_total');

        $lead->save();

        return $lead;
    }

    /**
     * Record or link a unified Customer Lead when a customer submits a website enquiry.
     */
    public function recordLeadFromInquiry(Inquiry $inquiry): ?CustomerLead
    {
        $email = strtolower(trim((string) ($inquiry->email ?? '')));
        $phone = trim((string) ($inquiry->phone ?? ''));

        if ($email === '' && $phone === '') {
            return null;
        }

        $lead = null;
        if ($email !== '') {
            $lead = CustomerLead::where('email', $email)->first();
        } elseif ($phone !== '') {
            $lead = CustomerLead::where('phone', $phone)->first();
        }

        if (! $lead) {
            $fallbackEmail = $email !== ''
                ? $email
                : 'phone+'.preg_replace('/[^0-9]/', '', $phone).'@enquiry.local';

            $lead = new CustomerLead([
                'email' => $fallbackEmail,
                'customer_type' => 'guest',
                'name' => $inquiry->name ?: 'Website Visitor',
                'phone' => $phone !== '' ? $phone : null,
                'total_orders' => 0,
                'total_purchase_amount' => 0,
            ]);
        } else {
            if (empty($lead->name) && ! empty($inquiry->name)) {
                $lead->name = $inquiry->name;
            }
            if (empty($lead->phone) && $phone !== '') {
                $lead->phone = $phone;
            }
        }

        $lead->save();

        return $lead;
    }

    /**
     * Record lead from user registration or Google login.
     */
    public function recordLeadFromUser(User $user, string $type = 'registered'): CustomerLead
    {
        $email = strtolower(trim($user->email));

        $lead = CustomerLead::firstOrNew(['email' => $email]);

        $lead->user_id = $user->id;
        $lead->name = $user->name;
        $lead->customer_type = $type;
        $lead->phone = $user->phone ?: $lead->phone;
        $lead->registration_date = $lead->registration_date ?: Carbon::now();

        $lead->save();

        return $lead;
    }
}

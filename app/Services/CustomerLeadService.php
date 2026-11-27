<?php

namespace App\Services;

use App\Models\CustomerLead;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CustomerLeadService
{
    /**
     * Record or update unified Customer Lead upon an order placement or manual bill.
     */
    public function recordLeadFromOrder(Order $order): CustomerLead
    {
        $rawEmail = strtolower(trim((string) $order->customer_email));
        $isSyntheticEmail = $rawEmail === ''
            || str_ends_with($rawEmail, '@menahem.local')
            || str_ends_with($rawEmail, '@enquiry.local');
        $phone = trim((string) $order->customer_phone);
        $name = trim((string) $order->customer_name);
        $shipping = $order->shipping_address ?? [];

        $addressText = implode(', ', array_filter([
            $shipping['address_line1'] ?? '',
            $shipping['address_line2'] ?? '',
            $shipping['city'] ?? '',
            $shipping['state'] ?? '',
            $shipping['pin_code'] ?? '',
        ]));

        $lead = null;

        if (! $isSyntheticEmail) {
            $lead = CustomerLead::where('email', $rawEmail)->first();
        } elseif ($phone !== '' && $name !== '') {
            // Match walk-in customer only when BOTH phone and name match so different people sharing a phone are never merged
            $lead = CustomerLead::where('phone', $phone)
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                ->first();
        }

        if (! $lead) {
            $leadEmail = ! $isSyntheticEmail
                ? $rawEmail
                : 'walkin+'.preg_replace('/[^0-9]/', '', $phone).'.'.Str::lower(Str::random(6)).'@menahem.local';

            $lead = new CustomerLead([
                'email' => $leadEmail,
                'customer_type' => $order->customer_type ?: 'guest',
                'name' => $name !== '' ? $name : 'Walk-in Customer',
                'phone' => $phone !== '' ? $phone : null,
                'delivery_address' => $addressText,
                'city' => $shipping['city'] ?? null,
                'state' => $shipping['state'] ?? null,
                'pin_code' => $shipping['pin_code'] ?? null,
                'customer_gstin' => $order->customer_gstin,
                'user_id' => $order->user_id,
                'first_purchase_date' => Carbon::now(),
            ]);
        } else {
            // Preserve existing customer name; only fill if currently blank/generic
            if (trim((string) $lead->name) === '' && $name !== '') {
                $lead->name = $name;
            }
            $lead->user_id = $order->user_id ?: $lead->user_id;
            $lead->phone = $phone !== '' ? $phone : $lead->phone;
            if (! empty($addressText) && $addressText !== 'Manual Billing / Store Counter') {
                $lead->delivery_address = $addressText;
                $lead->city = $shipping['city'] ?? $lead->city;
                $lead->state = $shipping['state'] ?? $lead->state;
                $lead->pin_code = $shipping['pin_code'] ?? $lead->pin_code;
            }
        }

        // If registered or Google, upgrade customer type
        if ($order->customer_type && in_array($order->customer_type, ['registered', 'google'], true)) {
            $lead->customer_type = $order->customer_type;
        }

        $lead->last_purchase_date = Carbon::now();

        $matchingOrdersQuery = Order::query()
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->where(function ($q) use ($lead, $isSyntheticEmail, $rawEmail): void {
                if (! $isSyntheticEmail) {
                    $q->where('customer_email', $rawEmail);
                } else {
                    $q->where('customer_email', $lead->email);
                    if (! empty($lead->phone) && ! empty($lead->name)) {
                        $q->orWhere(function ($sub) use ($lead): void {
                            $sub->where('customer_phone', $lead->phone)
                                ->whereRaw('LOWER(TRIM(customer_name)) = ?', [strtolower(trim((string) $lead->name))]);
                        });
                    }
                }
            });

        $lead->total_orders = (clone $matchingOrdersQuery)->count();
        $lead->total_purchase_amount = (float) (clone $matchingOrdersQuery)->sum('grand_total');

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

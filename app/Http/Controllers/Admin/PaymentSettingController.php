<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    /**
     * Display Payment Gateway & Business GST settings form.
     */
    public function index(): View
    {
        $settings = [
            'razorpay_key_id' => Setting::get('razorpay_key_id', ''),
            'razorpay_key_secret' => Setting::get('razorpay_key_secret', ''),
            'razorpay_mode' => Setting::get('razorpay_mode', 'test'),
            'razorpay_status' => Setting::get('razorpay_status', 'enabled'),
            'gateway_fee_mode' => Setting::get('gateway_fee_mode', 'absorbed'),
            'gateway_fee_percent' => Setting::get('gateway_fee_percent', '0'),
            'seller_name' => Setting::get('seller_name', 'MENAHEM'),
            'seller_gstin' => Setting::get('seller_gstin', '32DDLPP5032G1ZN'),
            'seller_address' => Setting::get('seller_address', "Karunya,\nNo. 52/1307, St. James Road,\nThykoodam, Kochi,\nErnakulam, Kerala.\nKochi 682019, Kerala, India."),
            'seller_state' => Setting::get('seller_state', 'Kerala'),
            'seller_state_code' => Setting::get('seller_state_code', '32'),
            'seller_phone' => Setting::get('seller_phone', '+91 8129 4620 45'),
            'seller_email' => Setting::get('seller_email', 'info@menahem.co.in'),
            'order_notification_emails' => Setting::get('order_notification_emails', 'info@menahem.co'),
        ];

        return view('admin.settings.payment', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update payment and seller settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'razorpay_key_id' => ['nullable', 'string', 'max:255'],
            'razorpay_key_secret' => ['nullable', 'string', 'max:255'],
            'razorpay_mode' => ['required', 'in:test,live'],
            'razorpay_status' => ['required', 'in:enabled,disabled'],
            'gateway_fee_mode' => ['required', 'in:absorbed,customer_charged'],
            'gateway_fee_percent' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'seller_name' => ['required', 'string', 'max:255'],
            'seller_gstin' => ['required', 'string', 'max:20'],
            'seller_address' => ['required', 'string'],
            'seller_state' => ['required', 'string', 'max:100'],
            'seller_state_code' => ['required', 'string', 'max:5'],
            'seller_phone' => ['required', 'string', 'max:30'],
            'seller_email' => ['required', 'email', 'max:100'],
            'order_notification_emails' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '', 'payment');
        }

        return redirect()->route('admin.settings.payment')
            ->with('success', 'Payment gateway and GST invoice settings saved successfully.');
    }
}

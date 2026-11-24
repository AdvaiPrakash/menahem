<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\PincodeService;
use App\Services\ShippingCalculationService;
use App\Services\TaxCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingSettingController extends Controller
{
    public function __construct(
        protected PincodeService $pincodeService,
        protected ShippingCalculationService $shippingService
    ) {}

    /**
     * Display Shipping & Delivery Distance Configuration form.
     */
    public function index(): View
    {
        $settings = $this->shippingService->getSettings();

        return view('admin.settings.shipping', [
            'settings' => $settings,
            'states' => TaxCalculationService::STATES,
        ]);
    }

    /**
     * Update business pickup location, free delivery distance, and per-KM shipping charge.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_origin_pincode' => ['required', 'string', 'regex:/^[1-9]\d{5}$/'],
            'shipping_origin_address' => ['required', 'string', 'max:500'],
            'shipping_origin_city' => ['required', 'string', 'max:100'],
            'shipping_origin_state' => ['required', 'string', 'max:100'],
            'shipping_free_distance_km' => ['required', 'numeric', 'min:0', 'max:5000'],
            'shipping_charge_per_km' => ['required', 'numeric', 'min:0', 'max:5000'],
        ], [
            'shipping_origin_pincode.regex' => 'Please enter a valid 6-digit Indian PIN code for the business pickup location.',
        ]);

        $pinLookup = $this->pincodeService->lookup($validated['shipping_origin_pincode']);
        if (! $pinLookup['valid']) {
            return back()
                ->withInput()
                ->withErrors(['shipping_origin_pincode' => $pinLookup['message']]);
        }

        $normalizedState = $this->pincodeService->normalizeStateName($validated['shipping_origin_state']);

        $resolvedCoords = $this->shippingService->resolveCoordinatesForLocation(
            $pinLookup['pincode'],
            $validated['shipping_origin_address'],
            null,
            $validated['shipping_origin_city'],
            $normalizedState,
            $pinLookup
        );

        Setting::setMany([
            'shipping_origin_pincode' => $pinLookup['pincode'],
            'shipping_origin_address' => trim($validated['shipping_origin_address']),
            'shipping_origin_city' => trim($validated['shipping_origin_city']),
            'shipping_origin_state' => $normalizedState,
            'shipping_origin_lat' => $resolvedCoords ? (string) $resolvedCoords['lat'] : '',
            'shipping_origin_lng' => $resolvedCoords ? (string) $resolvedCoords['lng'] : '',
            'shipping_free_distance_km' => (string) round((float) $validated['shipping_free_distance_km'], 2),
            'shipping_charge_per_km' => (string) round((float) $validated['shipping_charge_per_km'], 2),
        ], 'shipping');

        return redirect()->route('admin.settings.shipping')
            ->with('success', 'Shipping configuration and pickup location saved successfully.');
    }
}

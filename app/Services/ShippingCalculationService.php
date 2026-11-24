<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ShippingCalculationService
{
    public function __construct(protected PincodeService $pincodeService) {}

    /**
     * Retrieve current shipping configuration from the settings table with safe defaults.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $originPincode = trim((string) Setting::get('shipping_origin_pincode', '400002'));
        $originAddress = trim((string) Setting::get('shipping_origin_address', '42, Textile Market Road, Kalbadevi'));
        $originCity = trim((string) Setting::get('shipping_origin_city', 'Mumbai'));
        $originState = trim((string) Setting::get('shipping_origin_state', Setting::get('seller_state', 'Maharashtra')));

        $originLat = Setting::get('shipping_origin_lat');
        $originLng = Setting::get('shipping_origin_lng');

        $freeDistanceKm = (float) Setting::get('shipping_free_distance_km', 5);
        $chargePerKm = (float) Setting::get('shipping_charge_per_km', 10);

        return [
            'origin_pincode' => $originPincode,
            'origin_address' => $originAddress,
            'origin_city' => $originCity,
            'origin_state' => $originState,
            'origin_lat' => is_numeric($originLat) ? (float) $originLat : null,
            'origin_lng' => is_numeric($originLng) ? (float) $originLng : null,
            'free_distance_km' => max(0.0, $freeDistanceKm),
            'charge_per_km' => max(0.0, $chargePerKm),
        ];
    }

    /**
     * Resolve latitude/longitude for the configured business origin location.
     *
     * @return array{lat: float, lng: float, source: string}|null
     */
    public function resolveOriginCoordinates(?array $overrideOrigin = null): ?array
    {
        $settings = $overrideOrigin ?? $this->getSettings();

        if (
            isset($settings['origin_lat'], $settings['origin_lng'])
            && is_numeric($settings['origin_lat'])
            && is_numeric($settings['origin_lng'])
        ) {
            return [
                'lat' => (float) $settings['origin_lat'],
                'lng' => (float) $settings['origin_lng'],
                'source' => 'saved_origin_coordinates',
            ];
        }

        $pincode = (string) ($settings['origin_pincode'] ?? '');
        $address = (string) ($settings['origin_address'] ?? '');
        $city = (string) ($settings['origin_city'] ?? '');
        $state = (string) ($settings['origin_state'] ?? '');

        return $this->resolveCoordinatesForLocation($pincode, $address, null, $city, $state);
    }

    /**
     * Calculate delivery distance and shipping charge for a customer delivery address.
     *
     * @param  array{
     *     pincode?: ?string,
     *     address_line1?: ?string,
     *     address_line2?: ?string,
     *     city?: ?string,
     *     state?: ?string
     * }  $address
     * @return array<string, mixed>
     */
    public function calculateForAddress(array $address): array
    {
        $settings = $this->getSettings();
        $rawPincode = (string) ($address['pincode'] ?? '');
        $pinLookup = $this->pincodeService->lookup($rawPincode);

        if (! $pinLookup['valid']) {
            return [
                'determined' => false,
                'shipping_fee' => null,
                'distance_km' => null,
                'free_distance_km' => $settings['free_distance_km'],
                'charge_per_km' => $settings['charge_per_km'],
                'billable_distance_km' => null,
                'is_free' => false,
                'distance_source' => null,
                'location' => null,
                'message' => $pinLookup['message'],
            ];
        }

        $pincode = $pinLookup['pincode'];
        $line1 = trim((string) ($address['address_line1'] ?? ''));
        $line2 = trim((string) ($address['address_line2'] ?? ''));
        $city = trim((string) ($address['city'] ?? ($pinLookup['city'] ?? '')));
        $state = trim((string) ($address['state'] ?? ($pinLookup['state'] ?? '')));

        $originCoords = $this->resolveOriginCoordinates($settings);
        if ($originCoords === null) {
            return [
                'determined' => false,
                'shipping_fee' => null,
                'distance_km' => null,
                'free_distance_km' => $settings['free_distance_km'],
                'charge_per_km' => $settings['charge_per_km'],
                'billable_distance_km' => null,
                'is_free' => false,
                'distance_source' => null,
                'location' => $pinLookup,
                'message' => 'Store pickup location coordinates are unavailable. Please contact support.',
            ];
        }

        // 1. Prefer Google Maps Distance Matrix API if a server-side API key is configured
        $distanceKm = null;
        $distanceSource = null;

        if (! empty(config('services.google_maps.api_key'))) {
            $matrixDistance = $this->fetchGoogleDistanceMatrixKm(
                $this->formatAddressQuery(
                    $settings['origin_address'],
                    null,
                    $settings['origin_city'],
                    $settings['origin_state'],
                    $settings['origin_pincode']
                ),
                $this->formatAddressQuery($line1, $line2, $city, $state, $pincode)
            );

            if ($matrixDistance !== null) {
                $distanceKm = $matrixDistance;
                $distanceSource = 'google_distance_matrix';
            }
        }

        // 2. Fall back to address-level geocoding + PIN centroid with Haversine distance
        if ($distanceKm === null) {
            $customerCoords = $this->resolveCoordinatesForLocation(
                $pincode,
                $line1,
                $line2,
                $city,
                $state,
                $pinLookup
            );

            if ($customerCoords === null) {
                return [
                    'determined' => false,
                    'shipping_fee' => null,
                    'distance_km' => null,
                    'free_distance_km' => $settings['free_distance_km'],
                    'charge_per_km' => $settings['charge_per_km'],
                    'billable_distance_km' => null,
                    'is_free' => false,
                    'distance_source' => null,
                    'location' => $pinLookup,
                    'message' => "Unable to determine delivery distance for PIN code {$pincode}. Please verify your address or contact support.",
                ];
            }

            $distanceKm = $this->haversineKm(
                $originCoords['lat'],
                $originCoords['lng'],
                $customerCoords['lat'],
                $customerCoords['lng']
            );
            $distanceSource = $customerCoords['source'];
        }

        $distanceKm = round($distanceKm, 2);
        $freeDistanceKm = $settings['free_distance_km'];
        $chargePerKm = $settings['charge_per_km'];
        $billableDistanceKm = $distanceKm <= $freeDistanceKm
            ? 0.0
            : round($distanceKm - $freeDistanceKm, 2);
        $shippingFee = $billableDistanceKm <= 0.0
            ? 0.0
            : round($billableDistanceKm * $chargePerKm, 2);
        $isFree = $shippingFee <= 0.0;

        $message = $isFree
            ? "Free delivery ({$distanceKm} km, within {$freeDistanceKm} km free limit)"
            : "{$distanceKm} km ({$billableDistanceKm} km beyond {$freeDistanceKm} km free limit @ ₹{$chargePerKm}/km)";

        return [
            'determined' => true,
            'shipping_fee' => $shippingFee,
            'distance_km' => $distanceKm,
            'free_distance_km' => $freeDistanceKm,
            'charge_per_km' => $chargePerKm,
            'billable_distance_km' => $billableDistanceKm,
            'is_free' => $isFree,
            'distance_source' => $distanceSource,
            'location' => $pinLookup,
            'message' => $message,
        ];
    }

    /**
     * Resolve geographic coordinates using street-level geocoding when available, falling back to PIN code coordinates.
     *
     * @param  array<string, mixed>|null  $pinLookup
     * @return array{lat: float, lng: float, source: string}|null
     */
    public function resolveCoordinatesForLocation(
        string $pincode,
        ?string $line1 = null,
        ?string $line2 = null,
        ?string $city = null,
        ?string $state = null,
        ?array $pinLookup = null
    ): ?array {
        $pinData = $pinLookup ?? $this->pincodeService->lookup($pincode);
        $pinLat = isset($pinData['latitude']) && is_numeric($pinData['latitude']) ? (float) $pinData['latitude'] : null;
        $pinLng = isset($pinData['longitude']) && is_numeric($pinData['longitude']) ? (float) $pinData['longitude'] : null;

        $streetQuery = trim(implode(', ', array_filter([$line1, $line2])));
        $hasStreetAddress = $streetQuery !== '';

        // Try address-level geocoding when a street address is available
        if ($hasStreetAddress) {
            $fullQuery = $this->formatAddressQuery($line1, $line2, $city ?: ($pinData['city'] ?? ''), $state ?: ($pinData['state'] ?? ''), $pincode);
            $geocoded = $this->geocodeAddressString($fullQuery, $pinLat, $pinLng);
            if ($geocoded !== null) {
                return $geocoded;
            }
        }

        if ($pinLat !== null && $pinLng !== null) {
            return [
                'lat' => $pinLat,
                'lng' => $pinLng,
                'source' => 'pincode_centroid',
            ];
        }

        // Last resort: geocode by city/state/pincode if local dataset lacked coordinates
        $fallbackQuery = $this->formatAddressQuery(null, null, $city ?: ($pinData['city'] ?? ''), $state ?: ($pinData['state'] ?? ''), $pincode);

        return $this->geocodeAddressString($fallbackQuery);
    }

    /**
     * Calculate great-circle distance between two coordinates in kilometers (Haversine formula).
     */
    public function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * (sin($dLng / 2) ** 2);

        $c = 2 * atan2(sqrt($a), sqrt(max(0.0, 1 - $a)));

        return $earthRadiusKm * $c;
    }

    /**
     * Format a clean comma-separated Indian address string for geocoding or distance APIs.
     */
    protected function formatAddressQuery(
        ?string $line1,
        ?string $line2,
        ?string $city,
        ?string $state,
        string $pincode
    ): string {
        $parts = array_filter([
            trim((string) $line1),
            trim((string) $line2),
            trim((string) $city),
            trim((string) $state),
            trim($pincode),
            'India',
        ], static fn (string $part): bool => $part !== '');

        return implode(', ', $parts);
    }

    /**
     * Fetch driving distance in KM from Google Maps Distance Matrix API (server-side, cached).
     */
    protected function fetchGoogleDistanceMatrixKm(string $originQuery, string $destinationQuery): ?float
    {
        $apiKey = (string) config('services.google_maps.api_key');
        if ($apiKey === '') {
            return null;
        }

        $cacheKey = 'shipping.gmaps_distance.'.sha1(strtolower($originQuery.'|'.$destinationQuery));

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($apiKey, $originQuery, $destinationQuery): ?float {
            try {
                $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                    'origins' => $originQuery,
                    'destinations' => $destinationQuery,
                    'units' => 'metric',
                    'region' => 'in',
                    'key' => $apiKey,
                ]);

                if (! $response->successful()) {
                    return null;
                }

                $element = $response->json('rows.0.elements.0');
                if (! is_array($element) || ($element['status'] ?? '') !== 'OK') {
                    return null;
                }

                $meters = $element['distance']['value'] ?? null;

                return is_numeric($meters) ? round(((float) $meters) / 1000.0, 2) : null;
            } catch (\Throwable) {
                return null;
            }
        });
    }

    /**
     * Geocode an address string using Google Maps Geocoding API or OpenStreetMap Nominatim (cached, server-side).
     *
     * @return array{lat: float, lng: float, source: string}|null
     */
    protected function geocodeAddressString(string $query, ?float $pinLat = null, ?float $pinLng = null): ?array
    {
        $cacheKey = 'shipping.geocode.'.sha1(strtolower($query));

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['lat'], $cached['lng'])) {
            return $cached;
        }

        $googleKey = (string) config('services.google_maps.api_key');
        if ($googleKey !== '') {
            try {
                $response = Http::timeout(4)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $query,
                    'region' => 'in',
                    'key' => $googleKey,
                ]);

                if ($response->successful() && $response->json('status') === 'OK') {
                    $loc = $response->json('results.0.geometry.location');
                    if (isset($loc['lat'], $loc['lng']) && $this->isWithinPinSanityRadius((float) $loc['lat'], (float) $loc['lng'], $pinLat, $pinLng)) {
                        $result = [
                            'lat' => round((float) $loc['lat'], 6),
                            'lng' => round((float) $loc['lng'], 6),
                            'source' => 'google_geocoding',
                        ];
                        Cache::put($cacheKey, $result, now()->addDays(7));

                        return $result;
                    }
                }
            } catch (\Throwable) {
                // Fall through to Nominatim / PIN centroid
            }
        }

        if (! config('services.geocoding.nominatim_enabled', true)) {
            return null;
        }

        // Skip external Nominatim calls during unit/feature tests unless explicitly mocked via Http::fake()
        if (app()->runningUnitTests() && ! $this->hasFakedHttpHandlers()) {
            return null;
        }

        try {
            $response = Http::timeout(3)
                ->withHeaders([
                    'User-Agent' => (string) config('services.geocoding.user_agent', 'MenahemStoreCheckout/1.0'),
                    'Accept' => 'application/json',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'countrycodes' => 'in',
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if ($response->successful()) {
                $first = $response->json('0');
                if (is_array($first) && isset($first['lat'], $first['lon'])) {
                    $lat = (float) $first['lat'];
                    $lng = (float) $first['lon'];

                    if ($this->isWithinPinSanityRadius($lat, $lng, $pinLat, $pinLng)) {
                        $result = [
                            'lat' => round($lat, 6),
                            'lng' => round($lng, 6),
                            'source' => 'address_geocoded',
                        ];
                        Cache::put($cacheKey, $result, now()->addDays(7));

                        return $result;
                    }
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * Ensure an address-level geocode falls within a sane 60 km radius of its PIN code centroid.
     */
    protected function isWithinPinSanityRadius(float $lat, float $lng, ?float $pinLat, ?float $pinLng): bool
    {
        if ($lat < 6.0 || $lat > 38.0 || $lng < 67.0 || $lng > 98.0) {
            return false;
        }

        if ($pinLat === null || $pinLng === null) {
            return true;
        }

        return $this->haversineKm($lat, $lng, $pinLat, $pinLng) <= 60.0;
    }

    /**
     * Check whether Laravel's HTTP client currently has faked handlers registered in tests.
     */
    protected function hasFakedHttpHandlers(): bool
    {
        try {
            $factory = Http::getFacadeRoot();

            return is_object($factory) && method_exists($factory, 'recorded') && property_exists($factory, 'stubCallbacks')
                ? (static function () use ($factory): bool {
                    $ref = new \ReflectionProperty($factory, 'stubCallbacks');

                    return $ref->getValue($factory)->isNotEmpty();
                })()
                : false;
        } catch (\Throwable) {
            return false;
        }
    }
}

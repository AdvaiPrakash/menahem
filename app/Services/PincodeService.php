<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PincodeService
{
    /**
     * In-memory cache of loaded shard files during a single request lifecycle.
     *
     * @var array<string, array<string, array<string, mixed>>>
     */
    protected array $loadedShards = [];

    /**
     * Check whether a string is a valid 6-digit Indian PIN code format.
     */
    public function isValidFormat(string $pincode): bool
    {
        return (bool) preg_match('/^[1-9]\d{5}$/', trim($pincode));
    }

    /**
     * Look up an Indian PIN code from the local sharded dataset with cached API fallback.
     *
     * @return array{
     *     valid: bool,
     *     pincode: string,
     *     city: ?string,
     *     district: ?string,
     *     post_office: ?string,
     *     state: ?string,
     *     latitude: ?float,
     *     longitude: ?float,
     *     source: ?string,
     *     message: string
     * }
     */
    public function lookup(string $rawPincode): array
    {
        $pincode = preg_replace('/\D+/', '', trim($rawPincode));

        if (! $this->isValidFormat($pincode)) {
            return [
                'valid' => false,
                'pincode' => $pincode,
                'city' => null,
                'district' => null,
                'post_office' => null,
                'state' => null,
                'latitude' => null,
                'longitude' => null,
                'source' => null,
                'message' => 'Please enter a valid 6-digit Indian PIN code (cannot start with 0).',
            ];
        }

        // 1. Check local all-India dataset shard (19,300+ PIN codes)
        $localRecord = $this->lookupLocalShard($pincode);
        if ($localRecord !== null) {
            return [
                'valid' => true,
                'pincode' => $pincode,
                'city' => $localRecord['c'] ?? $localRecord['d'] ?? null,
                'district' => $localRecord['d'] ?? null,
                'post_office' => $localRecord['o'] ?? null,
                'state' => $this->normalizeStateName((string) ($localRecord['s'] ?? '')),
                'latitude' => isset($localRecord['lat']) ? (float) $localRecord['lat'] : null,
                'longitude' => isset($localRecord['lng']) ? (float) $localRecord['lng'] : null,
                'source' => 'local_dataset',
                'message' => 'PIN code verified.',
            ];
        }

        // 2. Fallback to cached India Post API lookup for newly created PIN codes
        $apiRecord = $this->lookupIndiaPostApi($pincode);
        if ($apiRecord !== null) {
            return $apiRecord;
        }

        return [
            'valid' => false,
            'pincode' => $pincode,
            'city' => null,
            'district' => null,
            'post_office' => null,
            'state' => null,
            'latitude' => null,
            'longitude' => null,
            'source' => null,
            'message' => "PIN code {$pincode} was not found. Please check and enter a valid Indian PIN code.",
        ];
    }

    /**
     * Normalize raw state / UT names from postal datasets or geocoders to TaxCalculationService::STATES keys.
     */
    public function normalizeStateName(string $rawState): string
    {
        $trimmed = trim($rawState);
        if ($trimmed === '') {
            return '';
        }

        foreach (array_keys(TaxCalculationService::STATES) as $canonical) {
            if (strcasecmp($canonical, $trimmed) === 0) {
                return $canonical;
            }
        }

        $normalizedKey = strtoupper(preg_replace('/\s+/', ' ', str_replace('&', 'AND', $trimmed)));

        $aliases = [
            'ANDAMAN AND NICOBAR ISLANDS' => 'Andaman & Nicobar Islands',
            'ANDAMAN AND NICOBAR' => 'Andaman & Nicobar Islands',
            'CHATTISGARH' => 'Chhattisgarh',
            'DADRA AND NAGAR HAVELI' => 'Dadra & Nagar Haveli and Daman & Diu',
            'DAMAN AND DIU' => 'Dadra & Nagar Haveli and Daman & Diu',
            'DADRA AND NAGAR HAVELI AND DAMAN AND DIU' => 'Dadra & Nagar Haveli and Daman & Diu',
            'THE DADRA AND NAGAR HAVELI AND DAMAN AND DIU' => 'Dadra & Nagar Haveli and Daman & Diu',
            'NCT OF DELHI' => 'Delhi',
            'NEW DELHI' => 'Delhi',
            'JAMMU AND KASHMIR' => 'Jammu & Kashmir',
            'ORISSA' => 'Odisha',
            'PONDICHERRY' => 'Puducherry',
            'UTTARANCHAL' => 'Uttarakhand',
        ];

        return $aliases[$normalizedKey] ?? ucwords(strtolower($trimmed));
    }

    /**
     * Read PIN record from the sharded local JSON directory.
     *
     * @return array<string, mixed>|null
     */
    protected function lookupLocalShard(string $pincode): ?array
    {
        $prefix = substr($pincode, 0, 2);

        if (! isset($this->loadedShards[$prefix])) {
            $path = database_path("data/pincodes/{$prefix}.json");
            if (! is_file($path)) {
                $this->loadedShards[$prefix] = [];
            } else {
                $decoded = json_decode((string) file_get_contents($path), true);
                $this->loadedShards[$prefix] = is_array($decoded) ? $decoded : [];
            }
        }

        return $this->loadedShards[$prefix][$pincode] ?? null;
    }

    /**
     * Query India Post public API as a cached fallback for PIN codes missing from the local snapshot.
     *
     * @return array<string, mixed>|null
     */
    protected function lookupIndiaPostApi(string $pincode): ?array
    {
        if (app()->runningUnitTests()) {
            return null;
        }

        return Cache::remember("pincode_api_lookup.{$pincode}", now()->addDays(30), function () use ($pincode): ?array {
            try {
                $response = Http::timeout(4)
                    ->acceptJson()
                    ->get("https://api.postalpincode.in/pincode/{$pincode}");

                if (! $response->successful()) {
                    return null;
                }

                $json = $response->json();
                $entry = is_array($json) ? ($json[0] ?? null) : null;
                if (! is_array($entry) || ($entry['Status'] ?? '') !== 'Success' || empty($entry['PostOffice'])) {
                    return null;
                }

                $postOffice = $entry['PostOffice'][0];
                $rawOffice = trim((string) ($postOffice['Name'] ?? ''));
                $district = trim((string) ($postOffice['District'] ?? ''));
                $state = $this->normalizeStateName((string) ($postOffice['State'] ?? ''));

                return [
                    'valid' => true,
                    'pincode' => $pincode,
                    'city' => $district ?: $rawOffice,
                    'district' => $district ?: null,
                    'post_office' => $rawOffice ?: null,
                    'state' => $state ?: null,
                    'latitude' => null,
                    'longitude' => null,
                    'source' => 'india_post_api',
                    'message' => 'PIN code verified via India Post.',
                ];
            } catch (\Throwable) {
                return null;
            }
        });
    }
}

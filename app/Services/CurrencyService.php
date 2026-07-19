<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\CurrencyRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    private const API_URL = 'https://api.exchangerate-api.com/v4/latest/';

    public function getRate(string $from, string $to): float
    {
        if ($from === $to) {
            return 1.0;
        }

        $rate = CurrencyRate::forPair($from, $to)->first();

        if ($rate && ! $rate->isStale()) {
            return (float) $rate->rate;
        }

        $fetchedRate = $this->fetchRate($from, $to);

        if ($fetchedRate !== null) {
            CurrencyRate::updateOrCreate(
                ['from_currency' => $from, 'to_currency' => $to],
                ['rate' => $fetchedRate, 'fetched_at' => now()]
            );

            return $fetchedRate;
        }

        if ($rate) {
            return (float) $rate->rate;
        }

        return 1.0;
    }

    private function fetchRate(string $from, ?string $to = null): float|array|null
    {
        try {
            $response = Http::timeout(5)->get(self::API_URL.$from);

            if ($response->successful()) {
                $data = $response->json();
                $rates = $data['rates'] ?? [];

                if ($to !== null) {
                    return $rates[$to] ?? null;
                }

                return $rates;
            }
        } catch (\Exception $e) {
            Log::warning("Failed to fetch exchange rate: {$e->getMessage()}");
        }

        return $to !== null ? null : [];
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $rate = $this->getRate($from, $to);

        return round($amount * $rate, 2);
    }

    public function getAllRates(string $base = 'USD'): array
    {
        $currencies = Currency::pluck('code')->toArray();
        $rates = [];

        // Fetch rates for all currencies in a single API call
        $response = $this->fetchRate($base, null);
        if ($response !== null) {
            foreach ($currencies as $code) {
                $rates[$code] = $response[$code] ?? 1.0;
            }
        } else {
            // Fallback: fetch each rate individually from cache/DB
            foreach ($currencies as $code) {
                $rates[$code] = $this->getRate($base, $code);
            }
        }

        return $rates;
    }

    public function refreshAllRates(): void
    {
        $base = Currency::where('is_default', true)->first()?->code ?? 'USD';
        $currencies = Currency::where('code', '!=', $base)->pluck('code')->toArray();

        // Fetch all rates in a single API call
        $allRates = $this->fetchRate($base, null);
        if (is_array($allRates) && !empty($allRates)) {
            foreach ($currencies as $code) {
                $rate = $allRates[$code] ?? null;
                if ($rate !== null) {
                    CurrencyRate::updateOrCreate(
                        ['from_currency' => $base, 'to_currency' => $code],
                        ['rate' => $rate, 'fetched_at' => now()]
                    );
                }
            }
        } else {
            // Fallback: fetch each rate individually
            foreach ($currencies as $code) {
                $rate = $this->fetchRate($base, $code);
                if ($rate !== null) {
                    CurrencyRate::updateOrCreate(
                        ['from_currency' => $base, 'to_currency' => $code],
                        ['rate' => $rate, 'fetched_at' => now()]
                    );
                }
            }
        }
    }
}

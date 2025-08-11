<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExchangeRate;
use App\Models\Currency;
use Carbon\Carbon;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = Currency::active()->pluck('code')->toArray();
        $baseCurrency = 'USD';

        // Generate sample exchange rates for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);

            foreach ($currencies as $targetCurrency) {
                if ($targetCurrency === $baseCurrency) {
                    continue;
                }

                // Generate realistic mock rates
                $rate = $this->generateMockRate($baseCurrency, $targetCurrency, $date);

                ExchangeRate::updateOrCreate(
                    [
                        'base_currency' => $baseCurrency,
                        'target_currency' => $targetCurrency,
                        'rate_date' => $date->toDateString()
                    ],
                    [
                        'rate' => $rate,
                        'fetched_at' => $date,
                        'source' => 'seeder'
                    ]
                );

                // Also create reverse rates
                $reverseRate = $rate > 0 ? 1 / $rate : 0;

                ExchangeRate::updateOrCreate(
                    [
                        'base_currency' => $targetCurrency,
                        'target_currency' => $baseCurrency,
                        'rate_date' => $date->toDateString()
                    ],
                    [
                        'rate' => $reverseRate,
                        'fetched_at' => $date,
                        'source' => 'seeder'
                    ]
                );
            }
        }
    }

    /**
     * Generate mock exchange rate (similar to ForexService)
     */
    private function generateMockRate($from, $to, $date)
    {
        // Base rates (mock data similar to real forex rates)
        $baseRates = [
            'USD_EUR' => 0.85,
            'USD_GBP' => 0.73,
            'USD_JPY' => 110.0,
            'USD_MYR' => 4.15,
            'USD_SGD' => 1.35,
            'USD_PHP' => 50.0,
            'USD_CNY' => 6.45,
            'USD_AUD' => 1.35,
            'USD_CAD' => 1.25,
            'USD_HKD' => 7.8,
            'USD_KRW' => 1200.0,
            'USD_HUF' => 300.0,
            'USD_MXN' => 20.0,
            'USD_NZD' => 1.42,
            'USD_CHF' => 0.92,
            'USD_SEK' => 10.5,
            'USD_NOK' => 10.8,
            'USD_THB' => 35.0,
            'USD_INR' => 75.0
        ];

        $pairKey = "{$from}_{$to}";
        $reversePairKey = "{$to}_{$from}";

        if (isset($baseRates[$pairKey])) {
            $baseRate = $baseRates[$pairKey];
        } elseif (isset($baseRates[$reversePairKey])) {
            $baseRate = 1 / $baseRates[$reversePairKey];
        } else {
            $baseRate = 1.0; // Default rate
        }

        // Add some volatility based on date
        $volatility = 0.02; // 2% volatility
        $seed = $date->timestamp;
        mt_srand($seed);
        $randomFactor = 1 + (mt_rand() / mt_getrandmax() - 0.5) * $volatility;

        return round($baseRate * $randomFactor, 6);
    }
}

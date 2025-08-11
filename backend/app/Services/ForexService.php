<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\CurrencyConversion;

class ForexService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.forex.api_key');
        $this->baseUrl = config('services.forex.base_url', 'https://api.exchangerate-api.com/v4');
    }

    /**
     * Get current exchange rates
     */
    public function getCurrentRates($baseCurrency = 'USD')
    {
        $cacheKey = "forex_rates_{$baseCurrency}";

        return Cache::remember($cacheKey, 300, function () use ($baseCurrency) {
            // For demo purposes, return mock data
            // In production, you would call a real forex API
            $currencies = $this->getAvailableCurrencies();
            $rates = [];

            foreach ($currencies as $code => $name) {
                if ($code !== $baseCurrency) {
                    // Generate mock rates based on real-world patterns
                    $rates[$code] = $this->generateMockRate($baseCurrency, $code);
                }
            }

            return [
                'base' => $baseCurrency,
                'rates' => $rates,
                'last_updated' => now()->toISOString()
            ];
        });
    }

    /**
     * Get exchange rates by date
     */
    public function getRatesByDate(Carbon $date)
    {
        $cacheKey = "forex_rates_" . $date->toDateString();

        return Cache::remember($cacheKey, 3600, function () use ($date) {
            // Mock historical data
            $currencies = $this->getAvailableCurrencies();
            $rates = [];

            foreach ($currencies as $code => $name) {
                if ($code !== 'USD') {
                    $rates[$code] = $this->generateMockRate('USD', $code, $date);
                }
            }

            return [
                'base' => 'USD',
                'date' => $date->toDateString(),
                'rates' => $rates
            ];
        });
    }

    /**
     * Convert currency
     */
    public function convertCurrency($from, $to, $amount, $userId = null, $ipAddress = null, $userAgent = null)
    {
        if ($from === $to) {
            return [
                'converted_amount' => $amount,
                'rate' => 1.0
            ];
        }

        // Try to get rate from database first
        $exchangeRate = ExchangeRate::forPair($from, $to)
            ->forDate(Carbon::today())
            ->first();

        if ($exchangeRate) {
            $rate = $exchangeRate->rate;
        } else {
            // Fallback to mock data
            $rates = $this->getCurrentRates($from);
            if (!isset($rates['rates'][$to])) {
                throw new \Exception("Currency pair {$from}/{$to} not found");
            }
            $rate = $rates['rates'][$to];
        }

        $convertedAmount = round($amount * $rate, 4);

        // Log the conversion
        if ($userId || $ipAddress) {
            CurrencyConversion::create([
                'user_id' => $userId,
                'from_currency' => $from,
                'to_currency' => $to,
                'from_amount' => $amount,
                'to_amount' => $convertedAmount,
                'exchange_rate' => $rate,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        }

        return [
            'converted_amount' => $convertedAmount,
            'rate' => $rate
        ];
    }

    /**
     * Get available currencies
     */
    public function getAvailableCurrencies()
    {
        // Try to get from database first
        $currencies = Currency::active()->get();

        if ($currencies->isNotEmpty()) {
            $result = [];
            foreach ($currencies as $currency) {
                $result[$currency->code] = $currency->name;
            }
            return $result;
        }

        // Fallback to hardcoded data if database is empty
        return [
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'JPY' => 'Japanese Yen',
            'AUD' => 'Australian Dollar',
            'CAD' => 'Canadian Dollar',
            'CHF' => 'Swiss Franc',
            'CNY' => 'Chinese Yuan',
            'SEK' => 'Swedish Krona',
            'NZD' => 'New Zealand Dollar',
            'MXN' => 'Mexican Peso',
            'SGD' => 'Singapore Dollar',
            'HKD' => 'Hong Kong Dollar',
            'NOK' => 'Norwegian Krone',
            'MYR' => 'Malaysian Ringgit',
            'PHP' => 'Philippine Peso',
            'THB' => 'Thai Baht',
            'INR' => 'Indian Rupee',
            'KRW' => 'South Korean Won',
            'HUF' => 'Hungarian Forint'
        ];
    }

    /**
     * Get historical data for currency pair
     */
    public function getHistoricalData($from, $to, $days = 30)
    {
        $data = [];
        $endDate = now();

        for ($i = 0; $i < $days; $i++) {
            $date = $endDate->copy()->subDays($i);
            $rate = $this->generateMockRate($from, $to, $date);

            $data[] = [
                'date' => $date->toDateString(),
                'rate' => $rate,
                'timestamp' => $date->timestamp
            ];
        }

        return array_reverse($data);
    }

    /**
     * Generate mock exchange rate (for demo purposes)
     */
    private function generateMockRate($from, $to, $date = null)
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
            'USD_NZD' => 1.42
        ];

        $pairKey = "{$from}_{$to}";
        $reversePairKey = "{$to}_{$from}";

        if (isset($baseRates[$pairKey])) {
            $baseRate = $baseRates[$pairKey];
        } elseif (isset($baseRates[$reversePairKey])) {
            $baseRate = 1 / $baseRates[$reversePairKey];
        } else {
            $baseRate = 1.2296; // Default rate from your image
        }

        // Add some volatility
        $volatility = 0.02; // 2% volatility
        $seed = $date ? $date->timestamp : time();
        mt_srand($seed);
        $randomFactor = 1 + (mt_rand() / mt_getrandmax() - 0.5) * $volatility;

        return round($baseRate * $randomFactor, 4);
    }
}

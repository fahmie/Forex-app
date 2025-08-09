<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ForexService;
use App\Http\Requests\ConvertCurrencyRequest;
use Carbon\Carbon;

class ForexController extends Controller
{
    protected $forexService;

    public function __construct(ForexService $forexService)
    {
        $this->forexService = $forexService;
    }

    /**
     * Get current exchange rates
     */
    public function getRates(Request $request)
    {
        try {
            $baseCurrency = $request->get('base', 'USD');
            $rates = $this->forexService->getCurrentRates($baseCurrency);

            return response()->json([
                'success' => true,
                'data' => $rates,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exchange rates by specific date
     */
    public function getRatesByDate($date)
    {
        try {
            $carbonDate = Carbon::parse($date);
            $rates = $this->forexService->getRatesByDate($carbonDate);

            return response()->json([
                'success' => true,
                'data' => $rates,
                'date' => $carbonDate->toDateString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch historical rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert currency
     */
    public function convertCurrency(ConvertCurrencyRequest $request)
    {
        try {
            $from = $request->from;
            $to = $request->to;
            $amount = $request->amount;

            $result = $this->forexService->convertCurrency($from, $to, $amount);

            return response()->json([
                'success' => true,
                'data' => [
                    'from' => $from,
                    'to' => $to,
                    'amount' => $amount,
                    'converted_amount' => $result['converted_amount'],
                    'exchange_rate' => $result['rate'],
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Conversion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available currencies
     */
    public function getCurrencies()
    {
        try {
            $currencies = $this->forexService->getAvailableCurrencies();

            return response()->json([
                'success' => true,
                'data' => $currencies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch currencies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get historical data for currency pair
     */
    public function getHistory($from, $to, Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $history = $this->forexService->getHistoricalData($from, $to, $days);

            return response()->json([
                'success' => true,
                'data' => $history,
                'pair' => "$from/$to"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch historical data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

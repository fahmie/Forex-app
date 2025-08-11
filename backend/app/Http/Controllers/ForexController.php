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

        // Add CORS headers to all responses
        $this->addCorsHeaders();
    }

    /**
     * Add CORS headers to response
     */
    private function addCorsHeaders()
    {
        if (request()->isMethod('OPTIONS')) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
            header('Access-Control-Allow-Credentials: true');
            exit(0);
        }
    }

    /**
     * Create JSON response with CORS headers
     */
    private function jsonResponse($data, $status = 200)
    {
        $response = response()->json($data, $status);

        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
        $response->header('Access-Control-Allow-Credentials', 'true');

        return $response;
    }

    /**
     * Get current exchange rates
     */
    public function getRates(Request $request)
    {
        try {
            $baseCurrency = $request->get('base', 'USD');
            $rates = $this->forexService->getCurrentRates($baseCurrency);

            return $this->jsonResponse([
                'success' => true,
                'data' => $rates,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
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

            return $this->jsonResponse([
                'success' => true,
                'data' => $rates,
                'date' => $carbonDate->toDateString()
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
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

            // Get user ID if authenticated (for now, set to null)
            $userId = null; // TODO: Implement authentication
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            $result = $this->forexService->convertCurrency($from, $to, $amount, $userId, $ipAddress, $userAgent);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'from' => $from,
                    'to' => $to,
                    'amount' => $amount,
                    'converted_amount' => $result['converted_amount'],
                    'rate' => $result['rate'],
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
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

            return $this->jsonResponse([
                'success' => true,
                'data' => $currencies
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
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

            return $this->jsonResponse([
                'success' => true,
                'data' => $history,
                'pair' => "$from/$to"
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch historical data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

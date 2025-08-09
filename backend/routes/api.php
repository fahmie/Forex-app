
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForexController;

Route::middleware('api')->group(function () {
    Route::prefix('forex')->group(function () {
        Route::get('/rates', [ForexController::class, 'getRates']);
        Route::get('/rates/{date}', [ForexController::class, 'getRatesByDate']);
        Route::post('/convert', [ForexController::class, 'convertCurrency']);
        Route::get('/currencies', [ForexController::class, 'getCurrencies']);
        Route::get('/history/{from}/{to}', [ForexController::class, 'getHistory']);
    });
});

// Enable CORS for frontend
Route::middleware(['cors'])->group(function () {
    // All API routes will be wrapped with CORS
});


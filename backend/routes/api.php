
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForexController;

// Add CORS headers to all API routes
Route::middleware([\App\Http\Middleware\CorsMiddleware::class])->group(function () {
    Route::prefix('forex')->group(function () {
        Route::get('/rates', [ForexController::class, 'getRates']);
        Route::get('/rates/{date}', [ForexController::class, 'getRatesByDate']);
        Route::post('/convert', [ForexController::class, 'convertCurrency']);
        Route::get('/currencies', [ForexController::class, 'getCurrencies']);
        Route::get('/history/{from}/{to}', [ForexController::class, 'getHistory']);
    });
});

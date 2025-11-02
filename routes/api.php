<?php

declare(strict_types=1);

use App\Http\Controllers\Api\FinanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // Portfolio endpoints
    Route::get('/portfolios', [FinanceController::class, 'getPortfolios']);
    Route::get('/portfolios/{id}', [FinanceController::class, 'getPortfolio']);
    Route::post('/portfolios', [FinanceController::class, 'createPortfolio']);
    Route::put('/portfolios/{id}', [FinanceController::class, 'updatePortfolio']);
    Route::delete('/portfolios/{id}', [FinanceController::class, 'deletePortfolio']);

    // Position endpoints
    Route::get('/positions', [FinanceController::class, 'getPositions']);
    Route::get('/positions/{id}', [FinanceController::class, 'getPosition']);
    Route::post('/positions', [FinanceController::class, 'createPosition']);
    Route::put('/positions/{id}', [FinanceController::class, 'updatePosition']);
    Route::delete('/positions/{id}', [FinanceController::class, 'deletePosition']);

    // Price endpoints
    Route::get('/prices/latest', [FinanceController::class, 'getLatestPrices']);
    Route::get('/prices/{ticker}', [FinanceController::class, 'getPriceHistory']);

    // Summary/dashboard endpoints
    Route::get('/summary', [FinanceController::class, 'getSummary']);
    Route::get('/summary/value', [FinanceController::class, 'getTotalValue']);
    Route::get('/summary/last-update', [FinanceController::class, 'getLastPriceUpdate']);
});

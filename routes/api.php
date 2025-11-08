<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Portfolios;
use App\Http\Controllers\Api\Positions;
use App\Http\Controllers\Api\Prices;
use App\Http\Controllers\Api\Summary;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // Portfolios
    Route::prefix('portfolios')->group(fn () => [
        Route::get('/', Portfolios\Index::class),
        Route::post('/', Portfolios\Store::class),
        Route::get('/{portfolio}', Portfolios\Show::class),
        Route::put('/{portfolio}', Portfolios\Update::class),
        Route::delete('/{portfolio}', Portfolios\Destroy::class),
    ]);

    // Positions
    Route::prefix('positions')->group(fn () => [
        Route::get('/', Positions\Index::class),
        Route::post('/', Positions\Store::class),
        Route::get('/{position}', Positions\Show::class),
        Route::put('/{position}', Positions\Update::class),
        Route::delete('/{position}', Positions\Destroy::class),
    ]);

    // Prices
    Route::prefix('prices')->group(fn () => [
        Route::get('/latest', Prices\Index::class),
        Route::get('/{ticker}', Prices\Show::class),
    ]);

    // Summary
    Route::prefix('summary')->group(fn () => [
        Route::get('/', Summary\Index::class),
        Route::get('/value', Summary\ShowValue::class),
        Route::get('/last-update', Summary\ShowLastUpdate::class),
    ]);
});

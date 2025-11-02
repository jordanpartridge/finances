<?php

use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('portfolios.index');
});

// Portfolio routes
Route::resource('portfolios', PortfolioController::class);

// Position routes
Route::resource('positions', PositionController::class);

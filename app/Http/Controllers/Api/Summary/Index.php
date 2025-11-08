<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Summary;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Price;
use Illuminate\Http\JsonResponse;

class Index
{
    /**
     * Get complete portfolio summary
     */
    public function __invoke(): JsonResponse
    {
        $totalValue = Portfolio::all()->sum(fn ($p) => $p->calculateValue());
        $portfolioCount = Portfolio::count();
        $positionCount = Position::count();
        $lastUpdate = Price::latest('quoted_at')->first()?->quoted_at;

        return response()->json([
            'success' => true,
            'data' => [
                'total_value' => $totalValue,
                'portfolio_count' => $portfolioCount,
                'position_count' => $positionCount,
                'last_price_update' => $lastUpdate,
                'prices_are_fresh' => $lastUpdate ? $lastUpdate->diffInHours(now()) < 24 : false,
            ],
        ]);
    }
}

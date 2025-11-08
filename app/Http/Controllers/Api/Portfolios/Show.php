<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Portfolios;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Price;
use Illuminate\Http\JsonResponse;

class Show
{
    /**
     * Get single portfolio with positions
     */
    public function __invoke(Portfolio $portfolio): JsonResponse
    {

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $portfolio->id,
                'name' => $portfolio->name,
                'description' => $portfolio->description,
                'type' => $portfolio->type,
                'current_value' => $portfolio->calculateValue(),
                'positions' => $portfolio->positions->map(function (Position $position): array {
                    $price = Price::where('ticker', $position->ticker)
                        ->latest('quoted_at')
                        ->first();
                    $currentPrice = $price ? (float) $price->midpoint() : null;

                    return [
                        'id' => $position->id,
                        'ticker' => $position->ticker,
                        'shares' => (float) $position->shares,
                        'current_price' => $currentPrice,
                        'position_value' => $currentPrice !== null ? (float) $position->shares * $currentPrice : null,
                    ];
                }),
                'created_at' => $portfolio->created_at,
                'updated_at' => $portfolio->updated_at,
            ],
        ]);
    }
}

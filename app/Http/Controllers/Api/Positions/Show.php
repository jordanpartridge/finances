<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Positions;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Price;
use Illuminate\Http\JsonResponse;

class Show
{
    /**
     * Get single position
     */
    public function __invoke(Position $position): JsonResponse
    {
        $position->load('portfolio');
        $price = Price::where('ticker', $position->ticker)
            ->latest('quoted_at')
            ->first();
        $currentPrice = $price ? (float) $price->midpoint() : null;

        /** @var Portfolio|null $portfolio */
        $portfolio = $position->portfolio;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $position->id,
                'portfolio_id' => $position->portfolio_id,
                'portfolio_name' => $portfolio?->name,
                'ticker' => $position->ticker,
                'shares' => (float) $position->shares,
                'current_price' => $currentPrice,
                'position_value' => $currentPrice !== null ? (float) $position->shares * $currentPrice : null,
                'created_at' => $position->created_at,
                'updated_at' => $position->updated_at,
            ],
        ]);
    }
}

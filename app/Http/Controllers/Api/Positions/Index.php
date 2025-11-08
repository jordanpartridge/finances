<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Positions;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Price;
use Illuminate\Http\JsonResponse;

class Index
{
    /**
     * Get all positions
     */
    public function __invoke(): JsonResponse
    {
        $positions = Position::with('portfolio')->get()->map(function (Position $position): array {
            $price = Price::where('ticker', $position->ticker)
                ->latest('quoted_at')
                ->first();
            $currentPrice = $price ? (float) $price->midpoint() : null;

            /** @var Portfolio|null $portfolio */
            $portfolio = $position->portfolio;

            return [
                'id' => $position->id,
                'portfolio_id' => $position->portfolio_id,
                'portfolio_name' => $portfolio?->name,
                'ticker' => $position->ticker,
                'shares' => (float) $position->shares,
                'current_price' => $currentPrice,
                'position_value' => $currentPrice !== null ? (float) $position->shares * $currentPrice : null,
                'created_at' => $position->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $positions,
            'count' => $positions->count(),
        ]);
    }
}

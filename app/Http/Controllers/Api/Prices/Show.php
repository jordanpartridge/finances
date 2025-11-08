<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Prices;

use App\Models\Price;
use Illuminate\Http\JsonResponse;

class Show
{
    /**
     * Get price history for specific ticker
     */
    public function __invoke(string $ticker): JsonResponse
    {
        $prices = Price::where('ticker', strtoupper($ticker))
            ->orderByDesc('quoted_at')
            ->limit(50)
            ->get()
            ->map(function ($price) {
                return [
                    'bid' => (float) $price->bid,
                    'ask' => (float) $price->ask,
                    'midpoint' => $price->midpoint(),
                    'quoted_at' => $price->quoted_at,
                ];
            });

        return response()->json([
            'success' => true,
            'ticker' => strtoupper($ticker),
            'data' => $prices,
            'count' => $prices->count(),
        ]);
    }
}

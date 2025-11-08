<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Prices;

use App\Models\Price;
use Illuminate\Http\JsonResponse;

class Index
{
    /**
     * Get latest prices for all tickers
     */
    public function __invoke(): JsonResponse
    {
        $prices = Price::selectRaw('ticker, MAX(id) as id')
            ->groupBy('ticker')
            ->pluck('id')
            ->toArray();

        $latestPrices = Price::whereIn('id', $prices)->get()->map(function ($price) {
            return [
                'ticker' => $price->ticker,
                'bid' => (float) $price->bid,
                'ask' => (float) $price->ask,
                'midpoint' => $price->midpoint(),
                'quoted_at' => $price->quoted_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $latestPrices,
            'count' => $latestPrices->count(),
        ]);
    }
}

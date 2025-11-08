<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Summary;

use App\Models\Price;
use Illuminate\Http\JsonResponse;

class ShowLastUpdate
{
    /**
     * Get last price update timestamp
     */
    public function __invoke(): JsonResponse
    {
        $lastUpdate = Price::latest('quoted_at')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'timestamp' => $lastUpdate?->quoted_at,
                'human_readable' => $lastUpdate?->quoted_at->diffForHumans(),
                'is_fresh' => $lastUpdate ? $lastUpdate->quoted_at->diffInHours(now()) < 24 : false,
            ],
        ]);
    }
}

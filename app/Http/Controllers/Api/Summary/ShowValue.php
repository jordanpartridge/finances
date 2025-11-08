<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Summary;

use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;

class ShowValue
{
    /**
     * Get total portfolio value
     */
    public function __invoke(): JsonResponse
    {
        $totalValue = Portfolio::all()->sum(fn ($p) => $p->calculateValue());

        return response()->json([
            'success' => true,
            'data' => [
                'total_value' => $totalValue,
                'formatted' => '$'.number_format($totalValue, 2),
            ],
        ]);
    }
}

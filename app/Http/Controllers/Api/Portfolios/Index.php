<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Portfolios;

use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;

class Index
{
    /**
     * Get all portfolios with their calculated values
     */
    public function __invoke(): JsonResponse
    {
        /** @var \Illuminate\Support\Collection<int, array{id: int, name: string, description: string|null, type: string, current_value: float, position_count: int, created_at: \Illuminate\Support\Carbon, updated_at: \Illuminate\Support\Carbon}> $portfolios */
        $portfolios = Portfolio::all()->map(function (Portfolio $portfolio) {
            return [
                'id' => $portfolio->id,
                'name' => $portfolio->name,
                'description' => $portfolio->description,
                'type' => $portfolio->type,
                'current_value' => $portfolio->calculateValue(),
                'position_count' => $portfolio->positions()->count(),
                'created_at' => $portfolio->created_at,
                'updated_at' => $portfolio->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $portfolios,
            'count' => $portfolios->count(),
        ]);
    }
}

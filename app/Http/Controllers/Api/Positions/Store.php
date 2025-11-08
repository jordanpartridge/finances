<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Positions;

use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Store
{
    /**
     * Create new position
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'portfolio_id' => 'required|exists:portfolios,id',
            'ticker' => 'required|string|uppercase|max:10',
            'shares' => 'required|numeric|min:0.01',
        ]);

        $position = Position::create($validated);

        return response()->json([
            'success' => true,
            'data' => $position,
        ], 201);
    }
}

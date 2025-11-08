<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Portfolios;

use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Store
{
    /**
     * Create new portfolio
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:stock_portfolio,crypto_portfolio,bond_portfolio,mixed_portfolio',
        ]);

        $portfolio = Portfolio::create($validated);

        return response()->json([
            'success' => true,
            'data' => $portfolio,
        ], 201);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Portfolios;

use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Update
{
    /**
     * Update portfolio
     */
    public function __invoke(Request $request, Portfolio $portfolio): JsonResponse
    {

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|string|in:stock_portfolio,crypto_portfolio,bond_portfolio,mixed_portfolio',
        ]);

        $portfolio->update($validated);

        return response()->json([
            'success' => true,
            'data' => $portfolio,
        ]);
    }
}

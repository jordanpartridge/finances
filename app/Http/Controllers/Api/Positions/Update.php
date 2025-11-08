<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Positions;

use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Update
{
    /**
     * Update position
     */
    public function __invoke(Request $request, Position $position): JsonResponse
    {

        $validated = $request->validate([
            'ticker' => 'sometimes|string|uppercase|max:10',
            'shares' => 'sometimes|numeric|min:0.01',
        ]);

        $position->update($validated);

        return response()->json([
            'success' => true,
            'data' => $position,
        ]);
    }
}

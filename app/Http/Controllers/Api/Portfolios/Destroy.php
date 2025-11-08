<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Portfolios;

use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;

class Destroy
{
    /**
     * Delete portfolio
     */
    public function __invoke(Portfolio $portfolio): JsonResponse
    {
        $portfolio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio deleted',
        ]);
    }
}

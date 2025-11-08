<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Positions;

use App\Models\Position;
use Illuminate\Http\JsonResponse;

class Destroy
{
    /**
     * Delete position
     */
    public function __invoke(Position $position): JsonResponse
    {
        $position->delete();

        return response()->json([
            'success' => true,
            'message' => 'Position deleted',
        ]);
    }
}

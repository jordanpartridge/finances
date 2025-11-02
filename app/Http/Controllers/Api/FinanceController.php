<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FinanceController extends Controller
{
    /**
     * Get all portfolios with their calculated values
     */
    public function getPortfolios(): JsonResponse
    {
        $portfolios = Portfolio::all()->map(function ($portfolio) {
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

    /**
     * Get single portfolio with positions
     */
    public function getPortfolio(string $id): JsonResponse
    {
        $portfolio = Portfolio::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $portfolio->id,
                'name' => $portfolio->name,
                'description' => $portfolio->description,
                'type' => $portfolio->type,
                'current_value' => $portfolio->calculateValue(),
                'positions' => $portfolio->positions->map(function ($position) {
                    $price = Price::where('ticker', $position->ticker)
                        ->latest('quoted_at')
                        ->first();
                    $current_price = $price ? $price->midpoint() : null;

                    return [
                        'id' => $position->id,
                        'ticker' => $position->ticker,
                        'shares' => (float) $position->shares,
                        'current_price' => $current_price,
                        'position_value' => $current_price ? (float) $position->shares * $current_price : null,
                    ];
                }),
                'created_at' => $portfolio->created_at,
                'updated_at' => $portfolio->updated_at,
            ],
        ]);
    }

    /**
     * Create new portfolio
     */
    public function createPortfolio(Request $request): JsonResponse
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

    /**
     * Update portfolio
     */
    public function updatePortfolio(Request $request, string $id): JsonResponse
    {
        $portfolio = Portfolio::findOrFail($id);

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

    /**
     * Delete portfolio
     */
    public function deletePortfolio(string $id): JsonResponse
    {
        $portfolio = Portfolio::findOrFail($id);
        $portfolio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio deleted',
        ]);
    }

    /**
     * Get all positions
     */
    public function getPositions(): JsonResponse
    {
        $positions = Position::with('portfolio')->get()->map(function ($position) {
            $price = Price::where('ticker', $position->ticker)
                ->latest('quoted_at')
                ->first();
            $current_price = $price ? $price->midpoint() : null;

            return [
                'id' => $position->id,
                'portfolio_id' => $position->portfolio_id,
                'portfolio_name' => $position->portfolio?->name,
                'ticker' => $position->ticker,
                'shares' => (float) $position->shares,
                'current_price' => $current_price,
                'position_value' => $current_price ? (float) $position->shares * $current_price : null,
                'created_at' => $position->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $positions,
            'count' => $positions->count(),
        ]);
    }

    /**
     * Get single position
     */
    public function getPosition(string $id): JsonResponse
    {
        $position = Position::with('portfolio')->findOrFail($id);
        $price = Price::where('ticker', $position->ticker)
            ->latest('quoted_at')
            ->first();
        $current_price = $price ? $price->midpoint() : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $position->id,
                'portfolio_id' => $position->portfolio_id,
                'portfolio_name' => $position->portfolio?->name,
                'ticker' => $position->ticker,
                'shares' => (float) $position->shares,
                'current_price' => $current_price,
                'position_value' => $current_price ? (float) $position->shares * $current_price : null,
                'created_at' => $position->created_at,
                'updated_at' => $position->updated_at,
            ],
        ]);
    }

    /**
     * Create new position
     */
    public function createPosition(Request $request): JsonResponse
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

    /**
     * Update position
     */
    public function updatePosition(Request $request, string $id): JsonResponse
    {
        $position = Position::findOrFail($id);

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

    /**
     * Delete position
     */
    public function deletePosition(string $id): JsonResponse
    {
        $position = Position::findOrFail($id);
        $position->delete();

        return response()->json([
            'success' => true,
            'message' => 'Position deleted',
        ]);
    }

    /**
     * Get latest prices for all tickers
     */
    public function getLatestPrices(): JsonResponse
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

    /**
     * Get price history for specific ticker
     */
    public function getPriceHistory(string $ticker): JsonResponse
    {
        $prices = Price::where('ticker', strtoupper($ticker))
            ->orderByDesc('quoted_at')
            ->limit(50)
            ->get()
            ->map(function ($price) {
                return [
                    'bid' => (float) $price->bid,
                    'ask' => (float) $price->ask,
                    'midpoint' => $price->midpoint(),
                    'quoted_at' => $price->quoted_at,
                ];
            });

        return response()->json([
            'success' => true,
            'ticker' => strtoupper($ticker),
            'data' => $prices,
            'count' => $prices->count(),
        ]);
    }

    /**
     * Get complete portfolio summary
     */
    public function getSummary(): JsonResponse
    {
        $totalValue = Portfolio::all()->sum(fn ($p) => $p->calculateValue());
        $portfolioCount = Portfolio::count();
        $positionCount = Position::count();
        $lastUpdate = Price::latest('quoted_at')->first()?->quoted_at;

        return response()->json([
            'success' => true,
            'data' => [
                'total_value' => $totalValue,
                'portfolio_count' => $portfolioCount,
                'position_count' => $positionCount,
                'last_price_update' => $lastUpdate,
                'prices_are_fresh' => $lastUpdate ? $lastUpdate->diffInHours(now()) < 24 : false,
            ],
        ]);
    }

    /**
     * Get total portfolio value
     */
    public function getTotalValue(): JsonResponse
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

    /**
     * Get last price update timestamp
     */
    public function getLastPriceUpdate(): JsonResponse
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

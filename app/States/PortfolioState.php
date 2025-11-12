<?php

declare(strict_types=1);

namespace App\States;

use Illuminate\Support\Collection;
use Thunk\Verbs\State;

/**
 * Portfolio aggregate root - represents the complete state of a portfolio
 * State is rebuilt by replaying all events for this portfolio
 */
class PortfolioState extends State
{
    public string $account_number;

    public string $name;

    public string $type = 'taxable'; // taxable, ira, roth_ira, etc

    public ?string $description = null;

    /** @var array<string, array{shares: float, cost_basis: float|null}> */
    public array $positions = [];

    public float $cash = 0.0;

    /**
     * Calculate current portfolio value from positions and latest prices
     * This is DERIVED state - calculated on demand, not stored
     */
    public function value(): float
    {
        $positionValue = collect($this->positions)->reduce(function (float $total, array $position, string $ticker) {
            // Get latest price for this ticker
            $latestPrice = \App\Models\Price::where('ticker', $ticker)
                ->latest('quoted_at')
                ->first();

            if (!$latestPrice) {
                return $total;
            }

            return $total + ($position['shares'] * (float) $latestPrice->midpoint());
        }, 0.0);

        return $positionValue + $this->cash;
    }

    /**
     * Get detailed breakdown of portfolio value calculation
     * Useful for debugging and displaying to users
     */
    public function valueBreakdown(): array
    {
        $breakdown = [];

        foreach ($this->positions as $ticker => $position) {
            $latestPrice = \App\Models\Price::where('ticker', $ticker)
                ->latest('quoted_at')
                ->first();

            $breakdown[$ticker] = [
                'shares' => $position['shares'],
                'price' => $latestPrice?->midpoint() ?? 0,
                'value' => $position['shares'] * ($latestPrice?->midpoint() ?? 0),
                'cost_basis' => $position['cost_basis'],
                'gain_loss' => $latestPrice
                    ? ($position['shares'] * $latestPrice->midpoint()) - ($position['shares'] * ($position['cost_basis'] ?? 0))
                    : 0,
            ];
        }

        return [
            'positions' => $breakdown,
            'cash' => $this->cash,
            'total' => $this->value(),
        ];
    }

    /**
     * Check if portfolio has a position in this ticker
     */
    public function hasPosition(string $ticker): bool
    {
        return isset($this->positions[$ticker]);
    }

    /**
     * Get shares held for a ticker
     */
    public function sharesOf(string $ticker): float
    {
        return $this->positions[$ticker]['shares'] ?? 0.0;
    }
}

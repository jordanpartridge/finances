<?php

declare(strict_types=1);

namespace App\Events;

use App\States\PortfolioState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

/**
 * Fired when shares are added to an existing position
 * Additional purchase of a ticker already owned
 */
class PositionIncreased extends Event
{
    #[StateId(PortfolioState::class)]
    public int $portfolio_id;

    public string $ticker;

    public float $shares;

    public ?float $cost_basis = null;

    public function apply(PortfolioState $state): void
    {
        $currentShares = $state->positions[$this->ticker]['shares'];
        $currentCostBasis = $state->positions[$this->ticker]['cost_basis'];

        // Update shares
        $newShares = $currentShares + $this->shares;

        // Calculate weighted average cost basis if both are set
        if ($currentCostBasis !== null && $this->cost_basis !== null) {
            $totalCost = ($currentShares * $currentCostBasis) + ($this->shares * $this->cost_basis);
            $newCostBasis = $totalCost / $newShares;
        } else {
            $newCostBasis = $currentCostBasis ?? $this->cost_basis;
        }

        $state->positions[$this->ticker] = [
            'shares' => $newShares,
            'cost_basis' => $newCostBasis,
        ];
    }

    public function validate(PortfolioState $state): void
    {
        $this->assert(
            $state->hasPosition($this->ticker),
            "Position {$this->ticker} does not exist in portfolio"
        );

        $this->assert(
            $this->shares > 0,
            'Shares must be greater than 0'
        );
    }
}

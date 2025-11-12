<?php

declare(strict_types=1);

namespace App\Events;

use App\States\PortfolioState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

/**
 * Fired when shares are removed from a position (partial sale)
 * Reduces share count but doesn't close the position
 */
class PositionDecreased extends Event
{
    #[StateId(PortfolioState::class)]
    public int $portfolio_id;

    public string $ticker;

    public float $shares;

    public function apply(PortfolioState $state): void
    {
        $currentShares = $state->positions[$this->ticker]['shares'];
        $newShares = $currentShares - $this->shares;

        // Safety check: ensure we never end up with invalid share counts
        assert($newShares > 0, 'Resulting shares must be positive after decrease');

        $state->positions[$this->ticker]['shares'] = $newShares;
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

        $currentShares = $state->positions[$this->ticker]['shares'] ?? 0;

        $this->assert(
            $this->shares < $currentShares,
            'Cannot decrease by more shares than currently held'
        );
    }
}

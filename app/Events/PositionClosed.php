<?php

declare(strict_types=1);

namespace App\Events;

use App\States\PortfolioState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

/**
 * Fired when a position is completely sold/closed
 * Removes the position from the portfolio
 */
class PositionClosed extends Event
{
    #[StateId(PortfolioState::class)]
    public int $portfolio_id;

    public string $ticker;

    public function apply(PortfolioState $state): void
    {
        unset($state->positions[$this->ticker]);
    }

    public function validate(PortfolioState $state): void
    {
        $this->assert(
            $state->hasPosition($this->ticker),
            "Position {$this->ticker} does not exist in portfolio"
        );
    }
}

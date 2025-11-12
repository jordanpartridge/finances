<?php

declare(strict_types=1);

namespace App\Events;

use App\States\PortfolioState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

/**
 * Fired when a new position is opened in a portfolio
 * First purchase of a ticker
 */
class PositionOpened extends Event
{
    #[StateId(PortfolioState::class)]
    public int $portfolio_id;

    public string $ticker;

    public float $shares;

    public ?float $cost_basis = null;

    public function apply(PortfolioState $state): void
    {
        $state->positions[$this->ticker] = [
            'shares' => $this->shares,
            'cost_basis' => $this->cost_basis,
        ];
    }

    public function validate(PortfolioState $state): void
    {
        $this->assert(
            ! $state->hasPosition($this->ticker),
            "Position {$this->ticker} already exists in portfolio"
        );

        $this->assert(
            $this->shares > 0,
            'Shares must be greater than 0'
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Events;

use App\States\PortfolioState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

/**
 * Fired when a new portfolio is discovered from a statement or trade confirmation
 * This is the first event for a portfolio - it creates the initial state
 */
class PortfolioDiscovered extends Event
{
    #[StateId(PortfolioState::class)]
    public int $portfolio_id;

    public string $account_number;

    public string $name;

    public string $type;

    public ?string $description = null;

    public function apply(PortfolioState $state): void
    {
        $state->account_number = $this->account_number;
        $state->name = $this->name;
        $state->type = $this->type;
        $state->description = $this->description;
    }
}

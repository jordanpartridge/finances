<?php

declare(strict_types=1);

namespace App\Events;

use Thunk\Verbs\Event;

/**
 * Fired when a stock price is updated
 * This doesn't mutate portfolio state directly, but is used for time-travel queries
 * Allows us to calculate portfolio value at any point in history
 */
class StockPriceChanged extends Event
{
    public string $ticker;

    public float $price;

    public float $bid;

    public float $ask;

    public string $quoted_at;

    // No apply() method - this event is for historical calculations only
    // Portfolio value is derived from these price events + position events
}

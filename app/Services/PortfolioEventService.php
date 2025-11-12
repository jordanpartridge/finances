<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\PortfolioDiscovered;
use App\Events\PositionClosed;
use App\Events\PositionDecreased;
use App\Events\PositionIncreased;
use App\Events\PositionOpened;
use App\States\PortfolioState;

/**
 * Service for firing portfolio events
 * This is how we interact with the event sourcing system
 */
class PortfolioEventService
{
    /**
     * Discover a new portfolio from a statement
     */
    public function discoverPortfolio(
        string $accountNumber,
        string $name,
        string $type = 'taxable',
        ?string $description = null
    ): int {
        $event = PortfolioDiscovered::fire(
            account_number: $accountNumber,
            name: $name,
            type: $type,
            description: $description
        );

        return $event->portfolio_id;
    }

    /**
     * Open a new position (first purchase of a ticker)
     */
    public function openPosition(
        int $portfolioId,
        string $ticker,
        float $shares,
        ?float $costBasis = null
    ): void {
        PositionOpened::fire(
            portfolio_id: $portfolioId,
            ticker: $ticker,
            shares: $shares,
            cost_basis: $costBasis
        );
    }

    /**
     * Increase an existing position (buy more shares)
     */
    public function increasePosition(
        int $portfolioId,
        string $ticker,
        float $shares,
        ?float $costBasis = null
    ): void {
        PositionIncreased::fire(
            portfolio_id: $portfolioId,
            ticker: $ticker,
            shares: $shares,
            cost_basis: $costBasis
        );
    }

    /**
     * Decrease a position (sell some shares)
     */
    public function decreasePosition(
        int $portfolioId,
        string $ticker,
        float $shares
    ): void {
        PositionDecreased::fire(
            portfolio_id: $portfolioId,
            ticker: $ticker,
            shares: $shares
        );
    }

    /**
     * Close a position completely (sell all shares)
     */
    public function closePosition(
        int $portfolioId,
        string $ticker
    ): void {
        PositionClosed::fire(
            portfolio_id: $portfolioId,
            ticker: $ticker
        );
    }

    /**
     * Get current state of a portfolio
     */
    public function getPortfolioState(int $portfolioId): PortfolioState
    {
        return PortfolioState::load($portfolioId);
    }

    /**
     * Get portfolio value at current time
     */
    public function getPortfolioValue(int $portfolioId): float
    {
        $state = $this->getPortfolioState($portfolioId);

        return $state->value();
    }

    /**
     * Get portfolio value at a specific point in time
     * This is TIME-TRAVEL - replay events up to that date
     */
    public function getPortfolioValueAt(int $portfolioId, string $date): float
    {
        // TODO: Implement time-travel replay
        // This will replay all events up to $date and calculate value
        throw new \Exception('Time-travel not yet implemented');
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Integrations\Alpaca\AlpacaConnector;
use App\Integrations\Alpaca\Requests\GetLatestQuote;

class PriceService
{
    /**
     * Get the latest price for a stock symbol.
     *
     * @param string $symbol The stock ticker symbol (e.g., 'TSLA', 'AAPL')
     * @return float The current price (midpoint between bid and ask)
     */
    public function getLatestPrice(string $symbol): float
    {
        $connector = new AlpacaConnector();
        $request = new GetLatestQuote($symbol);

        $response = $connector->send($request);

        if (! $response->successful()) {
            throw new \Exception("Failed to fetch price for {$symbol}: {$response->status()}");
        }

        $data = $response->json();

        // Calculate midpoint between bid and ask
        $bid = $data['quote']['bp'] ?? 0;
        $ask = $data['quote']['ap'] ?? 0;

        return ($bid + $ask) / 2;
    }

    /**
     * Get the latest prices for multiple symbols.
     *
     * @param array<string> $symbols Array of stock ticker symbols
     * @return array<string, float> Array of symbol => price pairs
     */
    public function getLatestPrices(array $symbols): array
    {
        $prices = [];

        foreach ($symbols as $symbol) {
            try {
                $prices[$symbol] = $this->getLatestPrice($symbol);
            } catch (\Exception $e) {
                // Log error but continue with other symbols
                \Log::warning("Failed to fetch price for {$symbol}", ['error' => $e->getMessage()]);
                $prices[$symbol] = null;
            }
        }

        return $prices;
    }
}

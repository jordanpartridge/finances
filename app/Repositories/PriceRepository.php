<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Price;
use Illuminate\Support\Collection;

class PriceRepository
{
    /**
     * Store a price record from API data.
     *
     * @param array<string, mixed> $data
     * @return Price
     */
    public function storeFromApiData(array $data): Price
    {
        return Price::updateOrCreate(
            [
                'ticker' => $data['ticker'],
                'quoted_at' => $data['quoted_at'],
            ],
            [
                'bid' => $data['bid'],
                'ask' => $data['ask'],
                'last' => $data['last'],
            ]
        );
    }

    /**
     * Get the latest price for a ticker.
     */
    public function getLatest(string $ticker): ?Price
    {
        return Price::where('ticker', $ticker)
            ->orderByDesc('quoted_at')
            ->first();
    }

    /**
     * Get all prices for a ticker, ordered by date descending.
     *
     * @return Collection<int, Price>
     */
    public function getHistory(string $ticker): Collection
    {
        return Price::where('ticker', $ticker)
            ->orderByDesc('quoted_at')
            ->get();
    }

    /**
     * Get prices for multiple tickers at the latest timestamp.
     *
     * @param array<int, string> $tickers
     * @return Collection<int, Price>
     */
    public function getLatestForMultiple(array $tickers): Collection
    {
        $latest = Price::whereIn('ticker', $tickers)
            ->selectRaw('MAX(quoted_at) as max_quoted_at')
            ->groupBy('ticker')
            ->get()
            ->pluck('max_quoted_at');

        return Price::whereIn('ticker', $tickers)
            ->whereIn('quoted_at', $latest)
            ->get();
    }
}

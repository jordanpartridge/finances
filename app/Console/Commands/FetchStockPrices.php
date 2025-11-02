<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Position;
use App\Repositories\PriceRepository;
use App\Services\PriceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FetchStockPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:fetch {--force : Skip trading hours check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch live stock prices from Alpaca and store in database. Runs only during US market trading hours (9:30 AM - 4:00 PM ET).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if we're in trading hours (unless --force flag is used)
        if (!$this->option('force') && !$this->isMarketOpen()) {
            $this->info('Market is closed. Skipping price fetch. Use --force to override.');
            return 0;
        }

        $this->info('Fetching live stock prices from Alpaca...');

        // Get all unique tickers from positions
        $tickers = Position::distinct()->pluck('ticker')->toArray();

        if (empty($tickers)) {
            $this->warn('No positions found. Nothing to fetch.');
            return 0;
        }

        $this->info(sprintf('Fetching prices for %d ticker(s)...', count($tickers)));

        $priceService = new PriceService();
        $priceRepository = new PriceRepository();
        $successCount = 0;
        $failureCount = 0;

        foreach ($tickers as $ticker) {
            try {
                $price = $priceService->getLatestPrice($ticker);

                $priceRepository->storeFromApiData([
                    'ticker' => $ticker,
                    'bid' => $price,
                    'ask' => $price,
                    'last' => $price,
                    'quoted_at' => now(),
                ]);

                $this->line("  ✓ {$ticker}: ${price}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("  ✗ {$ticker}: {$e->getMessage()}");
                $failureCount++;
            }
        }

        $this->newLine();
        $this->info("Fetch complete: {$successCount} successful, {$failureCount} failed.");

        return $successCount > 0 ? 0 : 1;
    }

    /**
     * Check if the US stock market is currently open.
     * Trading hours: 9:30 AM - 4:00 PM ET, Monday-Friday
     * Does not account for holidays.
     */
    private function isMarketOpen(): bool
    {
        $now = Carbon::now('America/New_York');

        // Market is only open Monday-Friday
        if ($now->isWeekend()) {
            return false;
        }

        // Market hours: 9:30 AM to 4:00 PM ET
        $marketOpen = $now->copy()->setTime(9, 30, 0);
        $marketClose = $now->copy()->setTime(16, 0, 0);

        return $now->isBetween($marketOpen, $marketClose);
    }
}

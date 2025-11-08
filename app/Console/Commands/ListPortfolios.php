<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Portfolio;
use Illuminate\Console\Command;

use function Laravel\Prompts\table;

class ListPortfolios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portfolio:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays a rich laravel prompt display of active portfolios and overview.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $portfolios = Portfolio::all();

        if ($portfolios->isEmpty()) {
            $this->info('No portfolios found.');

            return self::SUCCESS;
        }

        /** @var array<int, array<int, string>> $rows */
        $rows = $portfolios
            ->map(
                fn (Portfolio $portfolio): array => [
                    (string) $portfolio->id,
                    $portfolio->name,
                    $portfolio->description ?? 'N/A',
                    '$'.number_format($portfolio->calculateValue(), 2),
                ],
            )
            ->toArray();

        table(['ID', 'Name', 'Description', 'Value'], $rows);

        $totalValue = $portfolios->sum(fn (Portfolio $portfolio) => $portfolio->calculateValue());
        $this->info('Total Portfolio Value: $'.number_format($totalValue, 2));

        return self::SUCCESS;
    }
}

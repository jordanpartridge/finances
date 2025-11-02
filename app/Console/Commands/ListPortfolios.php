<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Portfolio;

use function Laravel\Prompts\table;

class ListPortfolios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "portfolio:list";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Displays a rich laravel prompt display of active portfolios and overview.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $portfolios = Portfolio::all();

        if ($portfolios->isEmpty()) {
            $this->info("No portfolios found.");
            return;
        }

        table(
            ["ID", "Name", "Description", "Value"],
            $portfolios
                ->map(
                    fn($portfolio) => [
                        $portfolio->id,
                        $portfolio->name,
                        $portfolio->description ?? "N/A",
                        '$' . number_format($portfolio->calculateValue(), 2),
                    ],
                )
                ->toArray(),
        );

        $totalValue = $portfolios->sum(fn($portfolio) => $portfolio->calculateValue());
        $this->info("Total Portfolio Value: $" . number_format($totalValue, 2));
    }
}

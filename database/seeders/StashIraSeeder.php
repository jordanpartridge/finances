<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Price;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StashIraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Stash IRA Portfolio
        $portfolio = Portfolio::create([
            'name' => 'Stash APEX C/F Traditional IRA',
            'description' => 'Account 6SQ-92997-18 - Traditional IRA via Stash/Apex Clearing',
            'type' => 'stock_portfolio',
        ]);

        // Holdings from statement (Sept 30, 2025)
        $holdings = [
            ['ticker' => 'APLD', 'shares' => 2.68268, 'price' => 22.94],
            ['ticker' => 'AAPL', 'shares' => 3.50397, 'price' => 254.63],
            ['ticker' => 'BRKB', 'shares' => 0.17972, 'price' => 502.74],
            ['ticker' => 'TFLO', 'shares' => 1.19735, 'price' => 50.59],
            ['ticker' => 'AOA', 'shares' => 17.14869, 'price' => 88.14],
            ['ticker' => 'NVDA', 'shares' => 4.48989, 'price' => 186.58],
            ['ticker' => 'SCHD', 'shares' => 30.48584, 'price' => 27.30],
            ['ticker' => 'TSLA', 'shares' => 2.21224, 'price' => 444.72],
        ];

        // Create positions and transactions from PDF data
        // September 5 buys
        $this->createPositionWithTransactions($portfolio, 'APLD', 2.68268, [
            ['date' => '2025-09-05', 'type' => 'buy', 'quantity' => 0.70093, 'price' => 14.2667],
        ]);

        $this->createPositionWithTransactions($portfolio, 'AAPL', 3.50397, [
            ['date' => '2025-09-05', 'type' => 'buy', 'quantity' => 0.21034, 'price' => 237.71],
        ]);

        $this->createPositionWithTransactions($portfolio, 'TFLO', 1.19735, [
            ['date' => '2025-09-05', 'type' => 'buy', 'quantity' => 0.09909, 'price' => 50.4599],
            ['date' => '2025-09-05', 'type' => 'dividend_reinvestment', 'quantity' => 0.00396, 'price' => 50.48],
        ]);

        $this->createPositionWithTransactions($portfolio, 'AOA', 17.14869, [
            ['date' => '2025-09-05', 'type' => 'buy', 'quantity' => 0.23337, 'price' => 85.70],
        ]);

        $this->createPositionWithTransactions($portfolio, 'NVDA', 4.48989, [
            ['date' => '2025-09-05', 'type' => 'buy', 'quantity' => 0.32213, 'price' => 170.7359],
        ]);

        $this->createPositionWithTransactions($portfolio, 'SCHD', 30.48584, [
            ['date' => '2025-09-05', 'type' => 'buy', 'quantity' => 0.5422, 'price' => 27.665],
            ['date' => '2025-09-29', 'type' => 'dividend_reinvestment', 'quantity' => 0.29014, 'price' => 27.0899],
        ]);

        // TSLA buys (multiple dates)
        $this->createPositionWithTransactions($portfolio, 'TSLA', 2.21224, [
            ['date' => '2025-09-05', 'type' => 'buy', 'quantity' => 0.05967, 'price' => 335.1899],
            ['date' => '2025-09-16', 'type' => 'buy', 'quantity' => 0.71805, 'price' => 417.7962],
            ['date' => '2025-09-22', 'type' => 'buy', 'quantity' => 0.35147, 'price' => 426.805],
        ]);

        $this->createPositionWithTransactions($portfolio, 'BRKB', 0.17972, [
            ['date' => '2025-09-05', 'type' => 'buy', 'quantity' => 0.17972, 'price' => 502.74],
        ]);

        // Seed current prices from the statement (September 30, 2025)
        foreach ($holdings as $holding) {
            // Create a price record using the statement date and price
            Price::create([
                'ticker' => $holding['ticker'],
                'bid' => $holding['price'] - 0.10,
                'ask' => $holding['price'] + 0.10,
                'last' => $holding['price'],
                'quoted_at' => '2025-09-30 23:59:59',
            ]);
        }
    }

    /**
     * Helper to create a position with its transactions.
     */
    private function createPositionWithTransactions(Portfolio $portfolio, string $ticker, float $shares, array $transactions): void
    {
        $position = Position::create([
            'portfolio_id' => $portfolio->id,
            'ticker' => $ticker,
            'shares' => $shares,
        ]);

        foreach ($transactions as $txn) {
            Transaction::create([
                'position_id' => $position->id,
                'transaction_type' => $txn['type'],
                'quantity' => $txn['quantity'],
                'price_per_share' => $txn['price'],
                'transaction_date' => $txn['date'],
                'settlement_date' => $txn['date'],
            ]);
        }
    }
}

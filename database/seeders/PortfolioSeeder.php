<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Portfolio::create([
            'name' => 'Tech Stocks',
            'description' => 'Portfolio focused on technology companies',
            'value' => 150000.00,
        ]);

        \App\Models\Portfolio::create([
            'name' => 'Real Estate',
            'description' => 'Investment in property assets',
            'value' => 250000.00,
        ]);

        \App\Models\Portfolio::create([
            'name' => 'Bonds Fund',
            'description' => 'Low-risk bond investments',
            'value' => 100000.00,
        ]);
    }
}

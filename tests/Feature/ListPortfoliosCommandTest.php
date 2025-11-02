<?php

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Price;
use App\Models\Transaction;

it('executes successfully', function () {
    $this->artisan('portfolio:list')
        ->assertSuccessful();
});

it('displays message when no portfolios exist', function () {
    $this->artisan('portfolio:list')
        ->expectsOutput('No portfolios found.')
        ->assertSuccessful();
});

it('displays all portfolios in a table', function () {
    $portfolio = Portfolio::factory()->create([
        'name' => 'Tech Stocks',
        'description' => 'Technology investments',
    ]);
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id, 'shares' => 100, 'ticker' => 'AAPL']);
    Transaction::factory()->create(['position_id' => $position->id, 'price_per_share' => 500]);
    Price::factory()->create(['ticker' => 'AAPL', 'bid' => 499.95, 'ask' => 500.05, 'last' => 500]);

    $this->artisan('portfolio:list')
        ->expectsOutputToContain('Tech Stocks')
        ->expectsOutputToContain('$50,000.00')
        ->assertSuccessful();
});

it('displays N/A for portfolios without description', function () {
    $portfolio = Portfolio::factory()->create([
        'name' => 'Index Fund',
        'description' => null,
    ]);
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id, 'shares' => 100]);
    Transaction::factory()->create(['position_id' => $position->id, 'price_per_share' => 250]);

    $this->artisan('portfolio:list')
        ->expectsOutputToContain('Index Fund')
        ->assertSuccessful();
});

it('displays total portfolio value', function () {
    $portfolio1 = Portfolio::factory()->create();
    $position1 = Position::factory()->create(['portfolio_id' => $portfolio1->id, 'shares' => 100, 'ticker' => 'TSLA']);
    Transaction::factory()->create(['position_id' => $position1->id, 'price_per_share' => 100]);
    Price::factory()->create(['ticker' => 'TSLA', 'bid' => 99.95, 'ask' => 100.05, 'last' => 100]);

    $portfolio2 = Portfolio::factory()->create();
    $position2 = Position::factory()->create(['portfolio_id' => $portfolio2->id, 'shares' => 100, 'ticker' => 'NVDA']);
    Transaction::factory()->create(['position_id' => $position2->id, 'price_per_share' => 200]);
    Price::factory()->create(['ticker' => 'NVDA', 'bid' => 199.95, 'ask' => 200.05, 'last' => 200]);

    $this->artisan('portfolio:list')
        ->expectsOutputToContain('Total Portfolio Value: $30,000.00')
        ->assertSuccessful();
});

it('formats currency values correctly', function () {
    $portfolio = Portfolio::factory()->create(['name' => 'Bonds']);
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id, 'shares' => 100, 'ticker' => 'SCHD']);
    Transaction::factory()->create(['position_id' => $position->id, 'price_per_share' => 123.46]);
    Price::factory()->create(['ticker' => 'SCHD', 'bid' => 123.41, 'ask' => 123.51, 'last' => 123.46]);

    $this->artisan('portfolio:list')
        ->expectsOutputToContain('$12,346.00')
        ->assertSuccessful();
});

it('displays multiple portfolios', function () {
    Portfolio::factory()->count(3)->create();

    $this->artisan('portfolio:list')
        ->assertSuccessful();
});

it('displays portfolio data in output', function () {
    Portfolio::factory()->create(['name' => 'My Portfolio']);

    $this->artisan('portfolio:list')
        ->expectsOutputToContain('My Portfolio')
        ->assertSuccessful();
});

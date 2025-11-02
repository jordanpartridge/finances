<?php

declare(strict_types=1);

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Price;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('requires authentication to access API', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/portfolios');

    $response->assertUnauthorized();
});

test('can get all portfolios with calculated values', function () {
    $portfolio = Portfolio::factory()->create([
        'name' => 'Test Portfolio',
        'type' => 'stock_portfolio',
    ]);

    $position = Position::factory()->create([
        'portfolio_id' => $portfolio->id,
        'ticker' => 'TEST',
        'shares' => 10,
    ]);

    Price::factory()->create([
        'ticker' => 'TEST',
        'bid' => 100.00,
        'ask' => 100.20,
        'quoted_at' => now(),
    ]);

    $response = $this->getJson('/api/portfolios');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'type',
                    'current_value',
                    'position_count',
                    'created_at',
                    'updated_at',
                ],
            ],
            'count',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                [
                    'name' => 'Test Portfolio',
                    'position_count' => 1,
                ],
            ],
        ]);
});

test('can get single portfolio with positions', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create([
        'portfolio_id' => $portfolio->id,
        'ticker' => 'AAPL',
        'shares' => 5,
    ]);

    Price::factory()->create([
        'ticker' => 'AAPL',
        'bid' => 150.00,
        'ask' => 150.20,
        'quoted_at' => now(),
    ]);

    $response = $this->getJson("/api/portfolios/{$portfolio->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'description',
                'type',
                'current_value',
                'positions' => [
                    '*' => [
                        'id',
                        'ticker',
                        'shares',
                        'current_price',
                        'position_value',
                    ],
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $portfolio->id,
            ],
        ]);
});

test('can create portfolio', function () {
    $data = [
        'name' => 'New Portfolio',
        'description' => 'Test description',
        'type' => 'stock_portfolio',
    ];

    $response = $this->postJson('/api/portfolios', $data);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'New Portfolio',
                'type' => 'stock_portfolio',
            ],
        ]);

    $this->assertDatabaseHas('portfolios', [
        'name' => 'New Portfolio',
        'type' => 'stock_portfolio',
    ]);
});

test('portfolio creation validates required fields', function () {
    $response = $this->postJson('/api/portfolios', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'type']);
});

test('can update portfolio', function () {
    $portfolio = Portfolio::factory()->create([
        'name' => 'Original Name',
    ]);

    $response = $this->putJson("/api/portfolios/{$portfolio->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Updated Name',
            ],
        ]);

    $this->assertDatabaseHas('portfolios', [
        'id' => $portfolio->id,
        'name' => 'Updated Name',
    ]);
});

test('can delete portfolio', function () {
    $portfolio = Portfolio::factory()->create();

    $response = $this->deleteJson("/api/portfolios/{$portfolio->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Portfolio deleted',
        ]);

    $this->assertDatabaseMissing('portfolios', [
        'id' => $portfolio->id,
    ]);
});

test('can get all positions with current prices', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create([
        'portfolio_id' => $portfolio->id,
        'ticker' => 'NVDA',
        'shares' => 2.5,
    ]);

    Price::factory()->create([
        'ticker' => 'NVDA',
        'bid' => 180.00,
        'ask' => 180.20,
        'quoted_at' => now(),
    ]);

    $response = $this->getJson('/api/positions');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'portfolio_id',
                    'portfolio_name',
                    'ticker',
                    'shares',
                    'current_price',
                    'position_value',
                    'created_at',
                ],
            ],
            'count',
        ])
        ->assertJson([
            'success' => true,
        ]);
});

test('can get single position', function () {
    $portfolio = Portfolio::factory()->create(['name' => 'My Portfolio']);
    $position = Position::factory()->create([
        'portfolio_id' => $portfolio->id,
        'ticker' => 'TSLA',
        'shares' => 1.5,
    ]);

    Price::factory()->create([
        'ticker' => 'TSLA',
        'bid' => 400.00,
        'ask' => 400.20,
        'quoted_at' => now(),
    ]);

    $response = $this->getJson("/api/positions/{$position->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $position->id,
                'ticker' => 'TSLA',
                'shares' => 1.5,
                'portfolio_name' => 'My Portfolio',
            ],
        ]);
});

test('can create position', function () {
    $portfolio = Portfolio::factory()->create();

    $data = [
        'portfolio_id' => $portfolio->id,
        'ticker' => 'AAPL',
        'shares' => 10.5,
    ];

    $response = $this->postJson('/api/positions', $data);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'ticker' => 'AAPL',
                'shares' => '10.5',
            ],
        ]);

    $this->assertDatabaseHas('positions', [
        'portfolio_id' => $portfolio->id,
        'ticker' => 'AAPL',
    ]);
});

test('position creation validates portfolio exists', function () {
    $response = $this->postJson('/api/positions', [
        'portfolio_id' => 999,
        'ticker' => 'AAPL',
        'shares' => 10,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['portfolio_id']);
});

test('can update position', function () {
    $position = Position::factory()->create([
        'shares' => 5,
    ]);

    $response = $this->putJson("/api/positions/{$position->id}", [
        'shares' => 10,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'shares' => '10',
            ],
        ]);

    $this->assertDatabaseHas('positions', [
        'id' => $position->id,
        'shares' => 10,
    ]);
});

test('can delete position', function () {
    $position = Position::factory()->create();

    $response = $this->deleteJson("/api/positions/{$position->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Position deleted',
        ]);

    $this->assertDatabaseMissing('positions', [
        'id' => $position->id,
    ]);
});

test('can get latest prices for all tickers', function () {
    Price::factory()->create([
        'ticker' => 'AAPL',
        'bid' => 150.00,
        'ask' => 150.20,
        'quoted_at' => now()->subHour(),
    ]);

    Price::factory()->create([
        'ticker' => 'AAPL',
        'bid' => 151.00,
        'ask' => 151.20,
        'quoted_at' => now(),
    ]);

    Price::factory()->create([
        'ticker' => 'TSLA',
        'bid' => 400.00,
        'ask' => 400.20,
        'quoted_at' => now(),
    ]);

    $response = $this->getJson('/api/prices/latest');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'ticker',
                    'bid',
                    'ask',
                    'midpoint',
                    'quoted_at',
                ],
            ],
            'count',
        ])
        ->assertJson([
            'success' => true,
            'count' => 2,
        ]);
});

test('can get price history for ticker', function () {
    Price::factory()->count(3)->create([
        'ticker' => 'NVDA',
        'bid' => 180.00,
        'ask' => 180.20,
    ]);

    $response = $this->getJson('/api/prices/NVDA');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'ticker',
            'data' => [
                '*' => [
                    'bid',
                    'ask',
                    'midpoint',
                    'quoted_at',
                ],
            ],
            'count',
        ])
        ->assertJson([
            'success' => true,
            'ticker' => 'NVDA',
            'count' => 3,
        ]);
});

test('can get complete summary', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create([
        'portfolio_id' => $portfolio->id,
        'ticker' => 'AAPL',
        'shares' => 10,
    ]);

    Price::factory()->create([
        'ticker' => 'AAPL',
        'bid' => 100.00,
        'ask' => 100.20,
        'quoted_at' => now(),
    ]);

    $response = $this->getJson('/api/summary');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'total_value',
                'portfolio_count',
                'position_count',
                'last_price_update',
                'prices_are_fresh',
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'portfolio_count' => 1,
                'position_count' => 1,
                'prices_are_fresh' => true,
            ],
        ]);
});

test('can get total value', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create([
        'portfolio_id' => $portfolio->id,
        'ticker' => 'AAPL',
        'shares' => 10,
    ]);

    Price::factory()->create([
        'ticker' => 'AAPL',
        'bid' => 100.00,
        'ask' => 100.00,
        'quoted_at' => now(),
    ]);

    $response = $this->getJson('/api/summary/value');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'total_value',
                'formatted',
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'total_value' => 1000.0,
                'formatted' => '$1,000.00',
            ],
        ]);
});

test('can get last price update', function () {
    $quoted = now()->subHours(2);

    Price::factory()->create([
        'ticker' => 'AAPL',
        'quoted_at' => $quoted,
    ]);

    $response = $this->getJson('/api/summary/last-update');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'timestamp',
                'human_readable',
                'is_fresh',
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'is_fresh' => true,
            ],
        ]);
});

test('returns 404 for non-existent portfolio', function () {
    $response = $this->getJson('/api/portfolios/999');

    $response->assertNotFound();
});

test('returns 404 for non-existent position', function () {
    $response = $this->getJson('/api/positions/999');

    $response->assertNotFound();
});

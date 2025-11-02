<?php

use App\Models\Price;
use App\Repositories\PriceRepository;

it('can store price from api data', function () {
    $repo = new PriceRepository();
    $data = [
        'ticker' => 'AAPL',
        'bid' => 150.50,
        'ask' => 150.60,
        'last' => 150.55,
        'quoted_at' => now(),
    ];

    $price = $repo->storeFromApiData($data);

    expect($price->ticker)->toBe('AAPL');
    expect($price->bid)->toBe('150.50');
    expect($price->ask)->toBe('150.60');
    expect($price->last)->toBe('150.55');
});

it('can get latest price for a ticker', function () {
    $oldTime = now()->subHours(2);
    $newTime = now();

    Price::factory()->create(['ticker' => 'TSLA', 'quoted_at' => $oldTime]);
    Price::factory()->create(['ticker' => 'TSLA', 'quoted_at' => $newTime]);

    $repo = new PriceRepository();
    $latest = $repo->getLatest('TSLA');

    expect($latest)->not->toBeNull();
    expect($latest->ticker)->toBe('TSLA');
    expect($latest->quoted_at->format('Y-m-d H'))->toBe($newTime->format('Y-m-d H'));
});

it('returns null when ticker has no price', function () {
    $repo = new PriceRepository();
    $latest = $repo->getLatest('NONEXISTENT');

    expect($latest)->toBeNull();
});

it('can get price history for a ticker', function () {
    $time1 = now()->subHours(2);
    $time2 = now()->subHours(1);
    $time3 = now();

    Price::factory()->create(['ticker' => 'NVDA', 'quoted_at' => $time1]);
    Price::factory()->create(['ticker' => 'NVDA', 'quoted_at' => $time2]);
    Price::factory()->create(['ticker' => 'NVDA', 'quoted_at' => $time3]);

    $repo = new PriceRepository();
    $history = $repo->getHistory('NVDA');

    expect($history)->toHaveCount(3);
    expect($history->first()->quoted_at->format('Y-m-d H'))->toBe($time3->format('Y-m-d H'));
});

it('can get latest prices for multiple tickers', function () {
    Price::factory()->create(['ticker' => 'AAPL', 'quoted_at' => now()]);
    Price::factory()->create(['ticker' => 'TSLA', 'quoted_at' => now()]);
    Price::factory()->create(['ticker' => 'NVDA', 'quoted_at' => now()]);

    $repo = new PriceRepository();
    $prices = $repo->getLatestForMultiple(['AAPL', 'TSLA', 'NVDA']);

    expect($prices)->toHaveCount(3);
    expect($prices->pluck('ticker')->sort()->values()->toArray())->toEqual(['AAPL', 'NVDA', 'TSLA']);
});

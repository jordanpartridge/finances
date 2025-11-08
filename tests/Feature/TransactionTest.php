<?php

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Transaction;

it('can be created', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    $transaction = Transaction::create([
        'position_id' => $position->id,
        'transaction_type' => 'buy',
        'quantity' => 10.5,
        'price_per_share' => 150.25,
        'transaction_date' => now()->toDateString(),
    ]);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->position_id)->toBe($position->id);
});

it('belongs to a position', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    $transaction = Transaction::create([
        'position_id' => $position->id,
        'transaction_type' => 'buy',
        'quantity' => 5,
        'price_per_share' => 100,
        'transaction_date' => now()->toDateString(),
    ]);

    expect($transaction->position)->toBeInstanceOf(Position::class);
    expect($transaction->position->id)->toBe($position->id);
});

it('casts dates correctly', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    $transactionDate = now()->subDays(5);
    $transaction = Transaction::create([
        'position_id' => $position->id,
        'transaction_type' => 'buy',
        'quantity' => 5,
        'price_per_share' => 100,
        'transaction_date' => $transactionDate,
        'settlement_date' => $transactionDate,
    ]);

    expect($transaction->transaction_date)->toBeInstanceOf(\Carbon\CarbonInterface::class);
    expect($transaction->settlement_date)->toBeInstanceOf(\Carbon\CarbonInterface::class);
});

it('casts numeric types correctly', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    $transaction = Transaction::create([
        'position_id' => $position->id,
        'transaction_type' => 'buy',
        'quantity' => 10.12345678,
        'price_per_share' => 150.99,
        'transaction_date' => now()->toDateString(),
    ]);

    expect((string) $transaction->quantity)->toContain('10.12');
    expect((string) $transaction->price_per_share)->toContain('150.99');
});

it('handles all transaction types', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    $types = ['buy', 'sell', 'dividend_reinvestment', 'contribution', 'dividend'];

    foreach ($types as $type) {
        $transaction = Transaction::create([
            'position_id' => $position->id,
            'transaction_type' => $type,
            'quantity' => 1,
            'price_per_share' => 100,
            'transaction_date' => now()->toDateString(),
        ]);

        expect($transaction->transaction_type)->toBe($type);
    }
});

it('can store optional notes', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    $transaction = Transaction::create([
        'position_id' => $position->id,
        'transaction_type' => 'buy',
        'quantity' => 5,
        'price_per_share' => 100,
        'transaction_date' => now()->toDateString(),
        'notes' => 'Bought as part of IRA contribution',
    ]);

    expect($transaction->notes)->toBe('Bought as part of IRA contribution');
});

it('can be deleted', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    $transaction = Transaction::create([
        'position_id' => $position->id,
        'transaction_type' => 'buy',
        'quantity' => 5,
        'price_per_share' => 100,
        'transaction_date' => now()->toDateString(),
    ]);

    $transactionId = $transaction->id;
    $transaction->delete();

    expect(Transaction::find($transactionId))->toBeNull();
});

it('retrieves all transactions for a position', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    Transaction::create([
        'position_id' => $position->id,
        'transaction_type' => 'buy',
        'quantity' => 5,
        'price_per_share' => 100,
        'transaction_date' => now()->toDateString(),
    ]);

    Transaction::create([
        'position_id' => $position->id,
        'transaction_type' => 'buy',
        'quantity' => 3,
        'price_per_share' => 110,
        'transaction_date' => now()->toDateString(),
    ]);

    expect($position->transactions)->toHaveCount(2);
});

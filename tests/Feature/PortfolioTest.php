<?php

use App\Models\Portfolio;

it('can be created', function () {
    $portfolio = Portfolio::factory()->create();

    expect($portfolio)->toBeInstanceOf(Portfolio::class);
});

it('can be updated', function () {
    $portfolio = Portfolio::factory()->create();

    $portfolio->update(['name' => 'Updated Portfolio']);

    expect($portfolio->name)->toBe('Updated Portfolio');
});

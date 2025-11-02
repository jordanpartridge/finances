<?php

use App\Models\Portfolio;
use App\Models\Position;

it('portfolio has many positions', function () {
    $portfolio = Portfolio::factory()->create();
    $position1 = Position::factory()->create(['portfolio_id' => $portfolio->id]);
    $position2 = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    expect($portfolio->positions)->toHaveCount(2);
    expect($portfolio->positions->first()->id)->toBe($position1->id);
    expect($portfolio->positions->last()->id)->toBe($position2->id);
});

it('position belongs to a portfolio', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);

    expect($position->portfolio)->toBeInstanceOf(Portfolio::class);
    expect($position->portfolio->id)->toBe($portfolio->id);
});

it('can retrieve all positions for a portfolio', function () {
    $portfolio = Portfolio::factory()->create();
    Position::factory()->count(5)->create(['portfolio_id' => $portfolio->id]);
    $otherPortfolio = Portfolio::factory()->create();
    Position::factory()->create(['portfolio_id' => $otherPortfolio->id]);

    expect($portfolio->positions->count())->toBe(5);
    expect($otherPortfolio->positions->count())->toBe(1);
});

it('deletes positions when portfolio is deleted', function () {
    $portfolio = Portfolio::factory()->create();
    $position = Position::factory()->create(['portfolio_id' => $portfolio->id]);
    $positionId = $position->id;

    $portfolio->delete();

    expect(Position::find($positionId))->toBeNull();
});

it('can create position through portfolio relationship', function () {
    $portfolio = Portfolio::factory()->create();

    $portfolio->positions()->create([
        'ticker' => 'AAPL',
        'shares' => 100,
    ]);

    expect($portfolio->positions)->toHaveCount(1);
    expect($portfolio->positions->first()->ticker)->toBe('AAPL');
});

it('returns empty collection when portfolio has no positions', function () {
    $portfolio = Portfolio::factory()->create();

    expect($portfolio->positions)->toHaveCount(0);
    expect($portfolio->positions)->toBeIterable();
});

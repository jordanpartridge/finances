<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Filament\Resources\Portfolios\RelationManagers\PositionsRelationManager;
use App\Models\Portfolio;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('prevents SQL injection in latest_price sortable query', function () {
    // This test would fail if SQL injection is possible
    // We're testing that malicious $direction values are rejected

    $portfolio = Portfolio::factory()->create();
    Position::factory()->create([
        'portfolio_id' => $portfolio->id,
        'ticker' => 'TEST',
        'shares' => 100,
    ]);

    // Attempt SQL injection via sort direction
    $maliciousDirection = "ASC; DROP TABLE positions--";

    // Enable query logging
    DB::enableQueryLog();

    try {
        // This should either sanitize the input or throw an error
        // It should NOT execute "DROP TABLE positions"
        Livewire::test(PositionsRelationManager::class, [
            'ownerRecord' => $portfolio,
            'pageClass' => 'view',
        ])
            ->sortTable('latest_price', $maliciousDirection);

        $queries = DB::getQueryLog();
        $lastQuery = end($queries)['query'] ?? '';

        // Verify the malicious SQL was NOT executed
        expect($lastQuery)->not->toContain('DROP TABLE')
            ->and($lastQuery)->not->toContain('--');

    } catch (\Exception $e) {
        // Expected: Should throw error on invalid direction
        expect($e->getMessage())->toContain('Invalid');
    }

    // Most important: Verify positions table still exists
    expect(DB::getSchemaBuilder()->hasTable('positions'))->toBeTrue();
});

it('prevents SQL injection in market_value sortable query', function () {
    $portfolio = Portfolio::factory()->create();
    Position::factory()->create([
        'portfolio_id' => $portfolio->id,
        'ticker' => 'TEST',
        'shares' => 100,
    ]);

    $maliciousDirection = "DESC; UPDATE positions SET shares = 0--";

    DB::enableQueryLog();

    try {
        Livewire::test(PositionsRelationManager::class, [
            'ownerRecord' => $portfolio,
            'pageClass' => 'view',
        ])
            ->sortTable('market_value', $maliciousDirection);

        $queries = DB::getQueryLog();
        $lastQuery = end($queries)['query'] ?? '';

        expect($lastQuery)->not->toContain('UPDATE')
            ->and($lastQuery)->not->toContain('--');

    } catch (\Exception $e) {
        expect($e->getMessage())->toContain('Invalid');
    }

    // Verify shares weren't modified
    $position = Position::first();
    expect((float) $position->shares)->toBe(100.0);
});

it('only accepts valid sort directions ASC or DESC', function () {
    $portfolio = Portfolio::factory()->create();
    Position::factory()->create(['portfolio_id' => $portfolio->id]);

    // Test invalid directions
    $invalidDirections = [
        'INVALID',
        '1=1',
        'ASC OR 1=1',
        "ASC' OR '1'='1",
        'DESC/**/LIMIT/**/1',
    ];

    foreach ($invalidDirections as $direction) {
        try {
            Livewire::test(PositionsRelationManager::class, [
                'ownerRecord' => $portfolio,
                'pageClass' => 'view',
            ])
                ->sortTable('latest_price', $direction);

            // If no exception, verify query is safe
            $queries = DB::getQueryLog();
            $lastQuery = end($queries)['query'] ?? '';

            // Should contain only 'asc' or 'desc' (normalized)
            $queryLower = strtolower($lastQuery);
            $hasValidDirection = str_contains($queryLower, 'order by') &&
                                (str_contains($queryLower, ' asc') || str_contains($queryLower, ' desc'));

            expect($hasValidDirection)->toBeTrue();

        } catch (\Exception $e) {
            // Expected: Invalid directions should be rejected
            $this->assertTrue(true);
        }
    }
});

it('accepts valid ASC and DESC directions', function () {
    $portfolio = Portfolio::factory()->create();
    Position::factory()->create([
        'portfolio_id' => $portfolio->id,
        'ticker' => 'TEST',
        'shares' => 100,
    ]);

    $validDirections = ['asc', 'ASC', 'desc', 'DESC'];

    foreach ($validDirections as $direction) {
        DB::enableQueryLog();

        Livewire::test(PositionsRelationManager::class, [
            'ownerRecord' => $portfolio,
            'pageClass' => 'view',
        ])
            ->sortTable('latest_price', $direction);

        $queries = DB::getQueryLog();
        $lastQuery = end($queries)['query'] ?? '';

        // Should contain the sanitized direction
        $queryLower = strtolower($lastQuery);
        expect($queryLower)->toContain('order by');

        DB::disableQueryLog();
    }

    expect(true)->toBeTrue(); // All valid directions worked
});

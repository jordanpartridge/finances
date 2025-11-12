<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Portfolio;
use Illuminate\Support\Collection;

/**
 * Aggregates information from multiple statements to discover/match portfolios
 *
 * NOT event sourcing - just smart parsing and grouping
 */
class StatementAggregator
{
    /**
     * Parse multiple statements and group by account
     *
     * @param array $statementPaths Array of file paths to statements
     * @return Collection Collection of discovered account profiles
     */
    public function aggregateStatements(array $statementPaths): Collection
    {
        $profiles = collect();

        foreach ($statementPaths as $path) {
            $data = $this->parseStatement($path);

            if ($data) {
                $accountNumber = $data['account_number'];

                if (!$profiles->has($accountNumber)) {
                    $profiles[$accountNumber] = [
                        'account_number' => $accountNumber,
                        'names' => collect(),
                        'types' => collect(),
                        'statements_count' => 0,
                        'first_seen' => $data['statement_date'],
                        'last_seen' => $data['statement_date'],
                        'positions' => collect(),
                    ];
                }

                // Aggregate data
                $profile = $profiles[$accountNumber];
                $profile['names']->push($data['account_name'] ?? null);
                $profile['types']->push($data['account_type'] ?? null);
                $profile['statements_count']++;
                $profile['positions'] = $profile['positions']->merge($data['positions'] ?? []);

                // Update date range
                if ($data['statement_date'] < $profile['first_seen']) {
                    $profile['first_seen'] = $data['statement_date'];
                }
                if ($data['statement_date'] > $profile['last_seen']) {
                    $profile['last_seen'] = $data['statement_date'];
                }

                $profiles[$accountNumber] = $profile;
            }
        }

        return $profiles->map(function ($profile) {
            return [
                'account_number' => $profile['account_number'],
                'suggested_name' => $this->getMostCommonValue($profile['names']),
                'suggested_type' => $this->getMostCommonValue($profile['types']),
                'confidence' => $profile['statements_count'], // More statements = higher confidence
                'statements_count' => $profile['statements_count'],
                'date_range' => [
                    'from' => $profile['first_seen'],
                    'to' => $profile['last_seen'],
                ],
                'unique_positions' => $profile['positions']->unique()->count(),
                'existing_portfolio' => $this->findExistingPortfolio($profile['account_number']),
            ];
        });
    }

    /**
     * Match or create portfolios from aggregated profiles
     */
    public function matchOrCreatePortfolios(Collection $profiles): array
    {
        $results = [
            'matched' => [],
            'created' => [],
            'needs_review' => [],
        ];

        foreach ($profiles as $profile) {
            if ($profile['existing_portfolio']) {
                // Portfolio exists - just link statements
                $results['matched'][] = $profile['existing_portfolio'];
            } elseif ($profile['confidence'] >= 3) {
                // High confidence (3+ statements) - auto-create
                $portfolio = Portfolio::create([
                    'account_number' => $profile['account_number'],
                    'name' => $profile['suggested_name'] ?? 'Portfolio '.$profile['account_number'],
                    'type' => $profile['suggested_type'] ?? 'taxable',
                    'description' => "Auto-created from {$profile['statements_count']} statements",
                ]);
                $results['created'][] = $portfolio;
            } else {
                // Low confidence - needs manual review
                $results['needs_review'][] = $profile;
            }
        }

        return $results;
    }

    /**
     * Parse a single statement (stub - implement based on your PDF format)
     */
    private function parseStatement(string $path): ?array
    {
        // TODO: Implement actual PDF parsing
        // Extract: account_number, account_name, account_type, positions, statement_date
        return null;
    }

    private function getMostCommonValue(Collection $values): ?string
    {
        return $values->filter()->countBy()->sortDesc()->keys()->first();
    }

    private function findExistingPortfolio(string $accountNumber): ?Portfolio
    {
        return Portfolio::where('account_number', $accountNumber)->first();
    }
}

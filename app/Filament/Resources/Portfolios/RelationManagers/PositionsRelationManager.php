<?php

namespace App\Filament\Resources\Portfolios\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PositionsRelationManager extends RelationManager
{
    protected static string $relationship = 'positions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ticker')
                    ->required()
                    ->maxLength(255)
                    ->uppercase()
                    ->placeholder('e.g., AAPL, BRK.B'),

                TextInput::make('shares')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->placeholder('0.00'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ticker')
            ->modifyQueryUsing(function ($query) {
                // Subquery to get latest price ID for each ticker
                $latestPriceIds = \App\Models\Price::query()
                    ->selectRaw('MAX(id) as id')
                    ->groupBy('ticker');

                // Join with prices table to get latest price data
                return $query->leftJoinSub(
                    \App\Models\Price::query()
                        ->whereIn('id', $latestPriceIds)
                        ->select('ticker', 'bid', 'ask', 'last', 'quoted_at'),
                    'latest_prices',
                    'positions.ticker',
                    '=',
                    'latest_prices.ticker'
                )->selectRaw('positions.*, latest_prices.bid, latest_prices.ask, latest_prices.last, latest_prices.quoted_at');
            })
            ->columns([
                TextColumn::make('ticker')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shares')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('latest_price')
                    ->label('Current Price')
                    ->money('USD')
                    ->getStateUsing(function ($record) {
                        if (!isset($record->bid) || !isset($record->ask)) {
                            return null;
                        }

                        // Calculate midpoint
                        return ((float) $record->bid + (float) $record->ask) / 2;
                    })
                    ->placeholder('No price data')
                    ->sortable(query: function ($query, string $direction) {
                        $safeDirection = $this->validateSortDirection($direction);
                        return $query->orderByRaw("(latest_prices.bid + latest_prices.ask) / 2 {$safeDirection}");
                    }),

                TextColumn::make('market_value')
                    ->label('Market Value')
                    ->money('USD')
                    ->getStateUsing(function ($record) {
                        if (!isset($record->bid) || !isset($record->ask)) {
                            return null;
                        }

                        $midpoint = ((float) $record->bid + (float) $record->ask) / 2;
                        return (float) $record->shares * $midpoint;
                    })
                    ->placeholder('—')
                    ->sortable(query: function ($query, string $direction) {
                        $safeDirection = $this->validateSortDirection($direction);
                        return $query->orderByRaw("positions.shares * ((latest_prices.bid + latest_prices.ask) / 2) {$safeDirection}");
                    }),

                TextColumn::make('quoted_at')
                    ->label('Price Updated')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Validates and normalizes SQL sort direction.
     *
     * Defense-in-depth security: Explicitly validates sort direction even though
     * Filament/Livewire provides framework-level protection. This ensures the
     * application fails securely and doesn't rely on implicit framework behavior.
     *
     * @param string $direction The sort direction to validate
     * @return string Normalized direction ('ASC' or 'DESC')
     * @throws \InvalidArgumentException If direction is not 'ASC' or 'DESC'
     */
    protected function validateSortDirection(string $direction): string
    {
        $normalized = strtoupper(trim($direction));

        if (!in_array($normalized, ['ASC', 'DESC'], true)) {
            \Log::warning('Invalid sort direction attempted', [
                'direction' => $direction,
                'normalized' => $normalized,
                'component' => self::class,
            ]);

            throw new \InvalidArgumentException(
                "Invalid sort direction: {$direction}. Must be 'ASC' or 'DESC'."
            );
        }

        return $normalized;
    }
}

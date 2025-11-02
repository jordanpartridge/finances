<?php

namespace App\Filament\Resources\Portfolios\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PortfolioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('type')
                    ->required()
                    ->default('stock_portfolio'),
            ]);
    }
}

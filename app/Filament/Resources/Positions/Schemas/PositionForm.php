<?php

namespace App\Filament\Resources\Positions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ticker')
                    ->required(),
                TextInput::make('shares')
                    ->required()
                    ->numeric(),
                Select::make('portfolio_id')
                    ->relationship('portfolio', 'name')
                    ->required(),
            ]);
    }
}

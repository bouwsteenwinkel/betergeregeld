<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('plan_key')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('price_monthly')
                    ->required()
                    ->numeric(),
                TextInput::make('price_yearly')
                    ->numeric()
                    ->default(null),
                TextInput::make('trial_days')
                    ->required()
                    ->numeric()
                    ->default(14),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

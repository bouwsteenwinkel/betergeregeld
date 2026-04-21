<?php

namespace App\Filament\Resources\PlanFeatures\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PlanFeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('plan_id')
                    ->required()
                    ->numeric(),
                TextInput::make('feature_key')
                    ->required(),
                TextInput::make('value')
                    ->default(null),
            ]);
    }
}

<?php

namespace App\Filament\Resources\PlanFeatures\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PlanFeatureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('plan_id')
                    ->numeric(),
                TextEntry::make('feature_key'),
                TextEntry::make('value')
                    ->placeholder('-'),
            ]);
    }
}

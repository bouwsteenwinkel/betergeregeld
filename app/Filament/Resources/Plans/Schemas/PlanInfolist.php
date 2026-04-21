<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('plan_key'),
                TextEntry::make('name'),
                TextEntry::make('price_monthly')
                    ->numeric(),
                TextEntry::make('price_yearly')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('trial_days')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\TenantSubscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TenantSubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant_key'),
                TextEntry::make('plan_id')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('trial_ends_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('current_period_ends_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\TenantSubscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TenantSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_key')
                    ->required(),
                TextInput::make('plan_id')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['trial' => 'Trial', 'active' => 'Active', 'canceled' => 'Canceled', 'expired' => 'Expired'])
                    ->required(),
                DateTimePicker::make('trial_ends_at'),
                DateTimePicker::make('current_period_ends_at'),
            ]);
    }
}

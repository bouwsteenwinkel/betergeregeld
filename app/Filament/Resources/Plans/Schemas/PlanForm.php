<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plan')
                    ->columns(2)
                    ->components([
                        Select::make('product')
                            ->options(['tools' => 'Tools', 'rankdata' => 'Rankdata'])
                            ->default('tools')
                            ->required(),
                        TextInput::make('plan_key')
                            ->required(),
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('price_monthly')
                            ->label('Basisprijs / maand (per klant)')
                            ->required()
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('price_yearly')
                            ->label('Prijs / jaar')
                            ->numeric()
                            ->prefix('€')
                            ->default(null),
                        TextInput::make('trial_days')
                            ->required()
                            ->numeric()
                            ->default(14),
                        Toggle::make('is_active')
                            ->default(true),
                    ]),
                Section::make('Rankdata-prijsmodel')
                    ->description('Alleen relevant voor product = Rankdata: basis per klant + add-on per extra site, met optionele korting.')
                    ->columns(3)
                    ->components([
                        TextInput::make('included_sites')
                            ->label('Inbegrepen sites')
                            ->numeric()
                            ->default(1)
                            ->helperText('Aantal sites in de basisprijs.'),
                        TextInput::make('price_per_site')
                            ->label('Prijs per extra site / maand')
                            ->numeric()
                            ->prefix('€')
                            ->default(null),
                        TextInput::make('discount_percent')
                            ->label('Standaardkorting %')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->helperText('Per bureau te overschrijven.'),
                    ]),
            ]);
    }
}

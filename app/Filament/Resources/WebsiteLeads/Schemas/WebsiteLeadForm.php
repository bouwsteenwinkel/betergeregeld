<?php

namespace App\Filament\Resources\WebsiteLeads\Schemas;

use App\Models\WebsiteLead;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WebsiteLeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bedrijf & contact')
                    ->columns(2)
                    ->schema([
                        TextInput::make('company')->label('Bedrijf')->maxLength(255),
                        Select::make('branche')
                            ->label('Branche')
                            ->options(WebsiteLead::BRANCHES)
                            ->searchable()
                            ->live()
                            ->helperText('Bepaalt de uitvraag én welke voorbeeldsite we vooraf klaarzetten.'),
                        TextInput::make('contact_name')->label('Contactpersoon')->maxLength(255),
                        TextInput::make('email')->label('E-mail')->email()->maxLength(255),
                        TextInput::make('phone')->label('Telefoon')->tel()->maxLength(40),
                        TextInput::make('current_website')->label('Huidige website')->url()->maxLength(255),
                    ]),

                Section::make('Locatie')
                    ->columns(3)
                    ->schema([
                        TextInput::make('postcode')->maxLength(12),
                        TextInput::make('city')->label('Plaats')->maxLength(255),
                        TextInput::make('address')->label('Adres')->maxLength(255),
                        TextInput::make('distance_km')->label('Afstand tot Bussum (km)')->numeric(),
                        Select::make('within_radius')->label('Binnen 50 km?')
                            ->options([1 => 'Ja — bezoek mogelijk', 0 => 'Nee — Google Meet']),
                    ]),

                Section::make('Aanvraag')
                    ->columns(2)
                    ->schema([
                        Select::make('channel')->label('Kanaal/campagne')
                            ->options([
                                'branche-landing'  => 'Branche-landingspagina',
                                'ads'              => 'Advertenties',
                                'koude-acquisitie' => 'Koude acquisitie',
                                'referral'         => 'Doorverwijzing',
                                'overig'           => 'Overig',
                            ])->searchable(),
                        TextInput::make('source')->label('Bron')->default('intake')->maxLength(40),
                        Textarea::make('message')->label('Wensen / bericht')->rows(3)->columnSpanFull(),
                    ]),

                Section::make('Uitvraag (branche-specifiek)')
                    ->description('Gewenste functies + antwoorden — voeden de 1-page design-brief.')
                    ->schema([
                        CheckboxList::make('features')
                            ->label('Gewenste functies')
                            ->options(fn ($get) => WebsiteLead::intakeFeaturesFor($get('branche')))
                            ->columns(2)
                            ->helperText('Kies een branche om de relevante functies te zien.'),
                        KeyValue::make('answers')
                            ->label('Antwoorden uitvraag')
                            ->keyLabel('Vraag')->valueLabel('Antwoord')
                            ->columnSpanFull(),
                    ]),

                Section::make('Voorbeeldsite')
                    ->columns(2)
                    ->schema([
                        TextInput::make('preview_url')->label('Voorbeeldsite-URL')->url()->maxLength(255),
                        Select::make('preview_status')->label('Status voorbeeldsite')
                            ->options(WebsiteLead::PREVIEW_STATUSES),
                    ]),

                Section::make('Afspraak')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('appointment_at')->label('Afspraak op')->seconds(false),
                        Select::make('appointment_type')->label('Type')->options(WebsiteLead::APPOINTMENT_TYPES),
                        Select::make('appointment_status')->label('Afspraak-status')->options(WebsiteLead::APPOINTMENT_STATUSES),
                        TextInput::make('meet_link')->label('Google Meet-link')->url()->maxLength(255),
                    ]),

                Section::make('Opvolging')
                    ->columns(2)
                    ->schema([
                        Select::make('status')->label('Pijplijn-status')
                            ->options(WebsiteLead::STATUSES)->default('new')->required(),
                        TextInput::make('assigned_to')->label('Toegewezen aan')->maxLength(255),
                        DateTimePicker::make('contacted_at')->label('Benaderd op')->seconds(false),
                        Textarea::make('notes')->label('Interne notities')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }
}

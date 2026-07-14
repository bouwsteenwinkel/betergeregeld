<?php

namespace App\Filament\Resources\WebsiteLeads\Schemas;

use App\Models\WebsiteLead;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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

                Section::make('Opgeslagen voorbeelden (klant-account)')
                    ->description('Wat de klant zelf bewaarde via de tool. De favoriet (★) is het ontwerp dat hij het mooist vindt.')
                    ->schema([
                        Placeholder::make('account_status')->label('Account & reminders')
                            ->content(function (?WebsiteLead $record): string {
                                if (! $record) {
                                    return '—';
                                }
                                $bits = ['Opt-in: ' . ($record->consent ? 'ja' : 'nee')];
                                if ($record->unsubscribed_at) {
                                    $bits[] = 'afgemeld';
                                } elseif ($record->next_reminder_at) {
                                    $bits[] = 'volgende reminder ' . $record->next_reminder_at->format('d-m-Y');
                                } elseif ($record->consent) {
                                    $bits[] = 'reminders klaar';
                                }
                                if ($record->saved_at) {
                                    $bits[] = 'opgeslagen ' . $record->saved_at->format('d-m-Y');
                                }

                                return implode(' · ', $bits);
                            }),
                        Placeholder::make('saved_previews_list')->label('Bewaarde voorbeelden')
                            ->content(function (?WebsiteLead $record): HtmlString {
                                if (! $record || $record->savedPreviews->isEmpty()) {
                                    return new HtmlString('<span style="color:#64748b">Nog geen voorbeelden opgeslagen.</span>');
                                }

                                $html = $record->savedPreviews->sortByDesc('favorite')->map(function ($p): string {
                                    $tags = collect($p->designSummary())
                                        ->map(fn ($v, $k) => e(ucfirst($k)) . ': ' . e($v))->implode(' · ');
                                    $star = $p->favorite ? '★ ' : '';
                                    $url = e($p->url());

                                    return '<div style="margin-bottom:.5rem">'
                                        . '<a href="' . $url . '" target="_blank" style="color:#1685c4;font-weight:600">' . $star . 'Open voorbeeld</a>'
                                        . '<br><span style="color:#64748b;font-size:.85em">' . $tags . '</span></div>';
                                })->implode('');

                                return new HtmlString($html);
                            })->columnSpanFull(),
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

<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Services\Scheduling\DriveClient;
use App\Services\Scheduling\SlotEngine;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Het formulier om een afspraak in te plannen.
 *
 * EERST EEN DAG, DAN EEN TIJD UIT EEN LIJST. Hier stond een vrij
 * datum-tijdveld, en dat is een valstrik: de planner werkt met een raster van
 * hele uren binnen werktijden, dus "14:11" wordt geweigerd met een melding die
 * lijkt te zeggen dat de agenda vol zit. Een veld waarin je iets mag typen dat
 * daarna toch niet mag, hoort er niet te zijn.
 *
 * WAT HIER NIET MEER STAAT. ends_at, type, status, hold_expires_at,
 * google_event_id en meet_url zijn weg. Dat zijn allemaal uitkomsten van het
 * inplannen: de eindtijd volgt uit de duur, het type is meet, de status wordt
 * booked, en het event-id en de Meet-link komen van Google terug. Ze invulbaar
 * maken levert alleen een rij op die iets anders beweert dan de agenda.
 */
class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Voor wie')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Naam van de klant')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Zoals het in de uitnodiging komt te staan.'),
                        TextInput::make('email')
                            ->label('E-mailadres')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->helperText('Hier gaan de uitnodiging en de bevestiging heen.'),
                        TextInput::make('company')
                            ->label('Bedrijfsnaam')
                            ->maxLength(255)
                            ->helperText('Optioneel. Komt achter de naam in de agenda te staan.'),
                        TextInput::make('phone')
                            ->label('Telefoon')
                            ->tel()
                            ->maxLength(40),
                        // ALLEEN BIJ BEWERKEN. Wie hier iets aanmaakt plant het zelf
                        // in, dus is de herkomst per definitie "handmatig (admin)" --
                        // dat zet CreateAppointment er zelf op. Het veld tonen maakt
                        // er een vraag van waar geen keuze in zit. Bij bewerken blijft
                        // hij wel staan: dan kun je een afspraak alsnog aan een site
                        // toeschrijven als dat achteraf blijkt.
                        TextInput::make('source_site')
                            ->label('Via welke site')
                            ->hiddenOn('create')
                            ->helperText('De channel-key, bijvoorbeeld bedrijfswebsite of apotheek.')
                            ->maxLength(255),
                    ]),

                Section::make('Wanneer')
                    ->columns(2)
                    ->description(static::uitleg())
                    ->schema([
                        Select::make('type')
                            ->label('Soort afspraak')
                            ->options(Appointment::SOORTEN)
                            ->default('meet')
                            ->required()
                            ->native(false)
                            ->columnSpanFull()
                            ->helperText('Alleen bij Google Meet komt er een videolink in de uitnodiging; '
                                . 'bij de andere zetten we de plek erbij.'),

                        DatePicker::make('slot_datum')
                            ->label('Dag')
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->firstDayOfWeek(1)
                            ->minDate(now()->startOfDay())
                            ->maxDate(now()->addDays((int) config('scheduling.horizon_days', 21)))
                            ->required()
                            ->live()
                            // Hoort niet bij de afspraak zelf: het is alleen de vraag
                            // welke dag we tijden voor moeten tonen.
                            ->dehydrated(false)
                            ->afterStateUpdated(fn ($set) => $set('starts_at', null))
                            ->afterStateHydrated(function ($state, $set, $record) {
                                if (! $state && $record?->starts_at) {
                                    $set('slot_datum', CarbonImmutable::parse($record->starts_at)->toDateString());
                                }
                            }),

                        Select::make('starts_at')
                            ->label('Tijd')
                            ->native(false)
                            ->required()
                            ->placeholder('Kies eerst een dag')
                            ->options(fn ($get, $record) => static::tijden($get('slot_datum'), $record))
                            ->helperText('Alleen tijden die vrij zijn. Staat er niets, dan is die dag vol of gesloten.')
                            ->formatStateUsing(fn ($state) => $state
                                ? CarbonImmutable::parse($state)->format('Y-m-d H:i:s')
                                : null),
                    ]),

                Section::make('Mee te sturen stukken')
                    ->columns(2)
                    ->description('Uit de gedeelde map, met een submap per klant. De genodigde krijgt '
                        . 'leesrecht op precies de stukken die je aanvinkt -- niet op de map.')
                    ->schema([
                        Select::make('drive_map')
                            ->label('Klantmap')
                            ->options(fn () => app(DriveClient::class)->klantmappen())
                            ->searchable()
                            ->native(false)
                            ->live()
                            // Hoort niet bij de afspraak: het is alleen de vraag uit
                            // welke map we bestanden moeten tonen.
                            ->dehydrated(false)
                            ->afterStateUpdated(fn ($set) => $set('attachments', []))
                            ->helperText('Staat er niets? Dan is er nog niet opnieuw gekoppeld met het Drive-recht.'),

                        Select::make('attachments')
                            ->label('Bijlagen')
                            ->multiple()
                            ->native(false)
                            ->placeholder('Kies eerst een klantmap')
                            ->options(fn ($get) => ($m = $get('drive_map'))
                                ? app(DriveClient::class)->bestanden((string) $m)
                                : [])
                            ->helperText('Optioneel. Ze komen als bijlage in de agenda-uitnodiging.'),
                    ]),

                Textarea::make('note')
                    ->label('Notitie')
                    ->rows(3)
                    ->helperText('Waar gaat het gesprek over? Komt mee in de interne melding.')
                    ->columnSpanFull(),
            ]);
    }

    /** De vrije tijden van een dag, als 'Y-m-d H:i:s' => 'H:i'. */
    private static function tijden($datum, ?Appointment $record = null): array
    {
        $tz = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $uit = [];

        if ($datum) {
            $dag = CarbonImmutable::parse($datum, $tz);
            try {
                $vrij = app(SlotEngine::class)->slots($dag->startOfDay(), $dag->endOfDay());
            } catch (\Throwable $e) {
                // De vrij/bezet-vraag gaat langs Google. Valt die weg, dan hoort dit
                // veld leeg te blijven en niet het hele formulier mee te nemen: je
                // bent dan je ingevulde naam en e-mailadres kwijt aan een storing die
                // niets met jouw invoer te maken heeft.
                report($e);

                return [];
            }
            foreach ($vrij[$dag->toDateString()] ?? [] as $hm) {
                $uit[$dag->setTimeFromTimeString($hm)->format('Y-m-d H:i:s')] = $hm;
            }
        }

        // Bij bewerken staat het eigen moment niet in de vrije lijst -- die afspraak
        // bezet hem immers zelf. Zonder deze regel lijkt het veld leeg en zou je bij
        // het opslaan ongemerkt verzetten.
        if ($record?->starts_at) {
            $eigen = CarbonImmutable::parse($record->starts_at)->setTimezone($tz);
            $uit[$eigen->format('Y-m-d H:i:s')] = $eigen->format('H:i') . ' (nu ingepland)';
            ksort($uit);
        }

        return $uit;
    }

    /** Waarom de keuze beperkt is, in gewone taal onder de kop. */
    private static function uitleg(): string
    {
        $duur = (int) config('scheduling.meeting_minutes', 60);
        $notice = (int) config('scheduling.min_notice_hours', 4);
        $horizon = (int) config('scheduling.horizon_days', 21);

        return "Gesprekken duren {$duur} minuten en beginnen op het hele uur, binnen werktijd. "
             . "Niet binnen {$notice} uur vanaf nu, en niet verder dan {$horizon} dagen vooruit.";
    }
}

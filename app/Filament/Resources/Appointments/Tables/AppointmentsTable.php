<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * De lijst met afspraken.
 *
 * DE INDELING. Bovenaan staat wat je bij het doorkijken van de agenda wilt zien:
 * wie, wanneer, wat voor soort en hoe het ervoor staat. De rest -- het event-id
 * bij Google, de Meet-link, de herkomst, de aanmaakdatum -- is administratie die
 * je maar zelden nodig hebt; die staat onder het kolommenmenu en niet standaard
 * in beeld. Dertien kolommen naast elkaar leest niemand.
 *
 * DE TAAL. `type` en `status` staan in de database in het Engels omdat er code
 * aan hangt; hier tonen we de Nederlandse omschrijving uit Appointment::SOORTEN
 * en ::STATUSSEN. Wat er op het scherm staat is Nederlands, wat eronder zit
 * verandert niet.
 */
class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->emptyStateHeading('Nog geen afspraken')
            ->emptyStateDescription('Zodra er een afspraak wordt geboekt of je er zelf een inplant, staat hij hier.')
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Wanneer')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->description(fn (Appointment $r) => $r->ends_at
                        ? 'tot ' . $r->ends_at->format('H:i')
                        : null),

                TextColumn::make('name')
                    ->label('Klant')
                    ->searchable()
                    ->description(fn (Appointment $r) => $r->company ?: null),

                TextColumn::make('email')
                    ->label('E-mailadres')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('E-mailadres gekopieerd'),

                TextColumn::make('phone')
                    ->label('Telefoon')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('type')
                    ->label('Soort')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Appointment::SOORTEN[$state] ?? $state)
                    ->color(fn ($state) => $state === 'meet' ? 'info' : 'gray'),

                TextColumn::make('status')
                    ->label('Stand')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Appointment::STATUSSEN[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'booked'    => 'success',
                        'held'      => 'warning',
                        'cancelled' => 'danger',
                        'no_show'   => 'danger',
                        default     => 'gray',
                    }),

                // Een uitroepteken is hier meer waard dan een kolom vol vinkjes:
                // je wilt alleen zien welke afspraak NIET in de agenda staat.
                TextColumn::make('calendar_error')
                    ->label('Agenda')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => filled($state) ? 'Storing' : null)
                    ->placeholder('—')
                    ->tooltip(fn (Appointment $r) => $r->calendar_error ?: null)
                    ->toggleable(),

                TextColumn::make('meet_url')
                    ->label('Meet-link')
                    ->formatStateUsing(fn ($state) => $state ? 'Openen' : null)
                    ->url(fn (Appointment $r) => $r->meet_url ?: null, shouldOpenInNewTab: true)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('source_site')
                    ->label('Herkomst')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('hold_expires_at')
                    ->label('Reservering verloopt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('google_event_id')
                    ->label('Agenda-item bij Google')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Aangemaakt op')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Gewijzigd op')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stand')
                    ->options(Appointment::STATUSSEN),

                SelectFilter::make('type')
                    ->label('Soort afspraak')
                    ->options(Appointment::SOORTEN),

                Filter::make('komende')
                    ->label('Alleen komende afspraken')
                    ->query(fn (Builder $query) => $query->where('starts_at', '>=', now())),
            ])
            ->recordActions([
                EditAction::make()->label('Bewerken'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

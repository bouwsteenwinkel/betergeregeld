<?php

namespace App\Filament\Resources\WebsiteLeads\Tables;

use App\Mail\PreviewSavedMail;
use App\Models\WebsiteLead;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class WebsiteLeadsTable
{
    public static function configure(Table $table): Table
    {
        $statusColors = [
            'new' => 'gray', 'contacted' => 'info', 'appointment' => 'warning',
            'quoted' => 'primary', 'won' => 'success', 'lost' => 'danger',
        ];

        // Kanaal-opties: 'intake' (algemeen) + alle promotie-kanalen uit config/promo.php.
        $channelOptions = ['intake' => 'Algemeen'];
        foreach ((array) config('promo.channels', []) as $key => $cfg) {
            $channelOptions[$key] = $cfg['title'] ?? $key;
        }

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Binnen')->date('d-m-Y')->sortable(),

                TextColumn::make('company')->label('Bedrijf')->weight('bold')
                    ->description(fn (WebsiteLead $r) => trim(($r->contact_name ?? '') . ($r->city ? ' · ' . $r->city : '')) ?: null)
                    ->searchable(['company', 'contact_name', 'city', 'email'])->limit(40),

                TextColumn::make('branche')->label('Branche')->badge()->color('info')
                    ->formatStateUsing(fn ($state) => WebsiteLead::BRANCHES[$state] ?? ($state ?: '—')),

                TextColumn::make('channel')->label('Kanaal')->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => $channelOptions[$state] ?? ($state ?: '—')),

                TextColumn::make('facet')->label('Groeifase')->badge()->color('warning')
                    ->formatStateUsing(fn ($state) => WebsiteLead::facetOptions()[$state] ?? ($state ?: '—')),

                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => WebsiteLead::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => $statusColors[$state] ?? 'gray'),

                TextColumn::make('appointment_at')->label('Afspraak')->dateTime('d-m-Y H:i')->sortable()
                    ->description(fn (WebsiteLead $r) => $r->appointment_type ? (WebsiteLead::APPOINTMENT_TYPES[$r->appointment_type] ?? $r->appointment_type) : null)
                    ->placeholder('—'),

                IconColumn::make('within_radius')->label('≤50km')->boolean()->toggleable(),

                TextColumn::make('preview_status')->label('Voorbeeldsite')->badge()
                    ->formatStateUsing(fn ($state) => WebsiteLead::PREVIEW_STATUSES[$state] ?? '—')
                    ->color(fn ($state) => $state === 'ready' || $state === 'sent' ? 'success' : ($state ? 'warning' : 'gray')),

                TextColumn::make('savedPreviews_count')->counts('savedPreviews')->label('Bewaard')
                    ->badge()->color(fn ($state) => $state ? 'success' : 'gray'),

                TextColumn::make('reminder_stage')->label('Reminders')->badge()
                    ->state(function (WebsiteLead $r) {
                        if ($r->unsubscribed_at) return 'afgemeld';
                        if (! $r->consent) return 'geen opt-in';
                        if ($r->reminder_stage >= 2 || ! $r->next_reminder_at) return 'klaar';
                        return 'dag ' . ($r->reminder_stage === 0 ? '1' : '4') . ' gepland';
                    })
                    ->color(fn ($state) => str_contains((string) $state, 'gepland') ? 'warning' : 'gray')
                    ->toggleable(),

                TextColumn::make('phone')->label('Telefoon')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('email')->label('E-mail')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('assigned_to')->label('Toegewezen')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('contacted_at')->label('Benaderd')->dateTime('d-m-Y')->toggleable()->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(WebsiteLead::STATUSES),
                SelectFilter::make('channel')->label('Kanaal')->options($channelOptions),
                SelectFilter::make('facet')->label('Groeifase')->options(WebsiteLead::facetOptions()),
                SelectFilter::make('branche')->label('Branche')->options(WebsiteLead::BRANCHES),
                SelectFilter::make('appointment_type')->label('Afspraak-type')->options(WebsiteLead::APPOINTMENT_TYPES),
                TernaryFilter::make('appointment_at')->label('Heeft afspraak')
                    ->nullable()->trueLabel('Met afspraak')->falseLabel('Zonder afspraak'),
                TernaryFilter::make('within_radius')->label('Binnen 50 km'),
            ])
            ->recordActions([
                Action::make('resendLink')->label('Stuur link')->icon('heroicon-o-envelope')->color('gray')
                    ->visible(fn (WebsiteLead $r) => (bool) $r->email && $r->savedPreviews()->exists())
                    ->requiresConfirmation()
                    ->modalHeading('Persoonlijke link opnieuw sturen')
                    ->modalDescription(fn (WebsiteLead $r) => 'Stuurt de bevestigingsmail met de persoonlijke link naar ' . $r->email . '.')
                    ->action(function (WebsiteLead $r) {
                        try {
                            Mail::to($r->email)->send(new PreviewSavedMail($r));
                            Notification::make()->title('Link verstuurd naar ' . $r->email)->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Versturen mislukt')->danger()->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

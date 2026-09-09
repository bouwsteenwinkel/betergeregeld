<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Services\Scheduling\GoogleCalendarGateway;
use Carbon\CarbonImmutable;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * De eindtijd laten meeschuiven met de begintijd.
     *
     * Het formulier vraagt alleen naar het begin; de duur staat vast in de
     * configuratie. Zonder dit blijft ends_at op het oude moment staan zodra
     * iemand hier verzet, en klopt de duur van de afspraak niet meer met wat
     * er in de agenda staat.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['starts_at'])) {
            $data['ends_at'] = CarbonImmutable::parse($data['starts_at'])
                ->addMinutes((int) config('scheduling.meeting_minutes', 60));
        }

        return $data;
    }

    /**
     * Wat hier verandert gaat ook naar Google -- en daarmee naar de klant.
     *
     * WAAROM DIT MOET. Dit scherm schreef alleen onze eigen tabel bij. Verzette je
     * hier een afspraak, dan hield je twee waarheden over: bij ons 15:00, in de
     * agenda van de klant nog 10:00. En hij hoorde niets, want er ging niets naar
     * Google. Hetzelfde gold voor een stuk dat er achteraf bij moest: aangevinkt
     * in onze administratie, maar nergens anders te zien.
     *
     * De bijgewerkte uitnodiging die Google stuurt is meteen het bericht aan de
     * klant. Een eigen mail erbij zou er twee maken waar er één hoort te zijn.
     *
     * FALEN MOET JE ZIEN. Lukt het bijwerken niet, dan blijft de wijziging wel in
     * onze tabel staan -- terugdraaien zou de invuller zijn werk kosten -- maar
     * dan gaat het verschil in `calendar_error` en zeggen we het hardop. Die kolom
     * staat als "Storing" in de lijst, zodat een mislukte wijziging niet in de
     * stilte verdwijnt.
     */
    protected function afterSave(): void
    {
        $afspraak = $this->getRecord();

        if (! $afspraak->google_event_id) {
            $afspraak->forceFill([
                'calendar_error' => 'Geen agenda-item bij Google; deze afspraak staat alleen in onze administratie.',
            ])->saveQuietly();

            Notification::make()
                ->title('Opgeslagen, maar niet in de agenda')
                ->body('Bij deze afspraak hoort geen agenda-item bij Google. Er is dus niets bijgewerkt en de klant heeft niets gekregen.')
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        try {
            $uit = app(GoogleCalendarGateway::class)->updateEvent($afspraak);
        } catch (\Throwable $e) {
            report($e);

            $afspraak->forceFill(['calendar_error' => substr($e->getMessage(), 0, 500)])->saveQuietly();

            Notification::make()
                ->title('Opgeslagen, maar de agenda is niet bijgewerkt')
                ->body('De wijziging staat wel in onze administratie. Bij Google staat nog het oude, en de klant heeft niets gekregen. '
                    . 'Probeer het zo nog eens, of kijk de koppeling na onder Google-agenda.')
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        $afspraak->forceFill([
            'meet_url'           => $uit['meet_url'] ?: $afspraak->meet_url,
            'calendar_synced_at' => now(),
            'calendar_error'     => null,
        ])->saveQuietly();

        Notification::make()
            ->title('Bijgewerkt en verstuurd')
            ->body('Het agenda-item is aangepast. ' . $afspraak->email . ' krijgt een bijgewerkte uitnodiging van Google.')
            ->success()
            ->send();
    }
}

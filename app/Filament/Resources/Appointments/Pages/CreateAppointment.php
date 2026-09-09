<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Services\Scheduling\BookingService;
use App\Services\Scheduling\CalendarUnavailableException;
use App\Services\Scheduling\SlotTakenException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Handmatig een afspraak inplannen.
 *
 * WAAROM DIT NIET DE STANDAARD-CREATE IS. Die schreef alleen een rij in de
 * database weg. Geen agenda-item, geen Meet-link, geen mail: de klant hoorde
 * niets en het uur stond voor ons gewoon vrij. Wie hier een afspraak intikt
 * bedoelt precies hetzelfde als een bezoeker die er een boekt op de site, dus
 * loopt het nu door dezelfde BookingService.
 *
 * Die doet in een keer: kijken of het moment vrij is (bij Google en in onze
 * eigen tabel), de afspraak vastleggen, het agenda-item met Meet-link maken en
 * de bevestiging sturen.
 *
 * DE VELDEN DIE HET FORMULIER VERDER AANBIEDT -- ends_at, type, status,
 * google_event_id, meet_url -- worden hier bewust genegeerd. Die horen bij de
 * uitkomst van het inplannen en niet bij de invoer; ze met de hand zetten
 * levert een rij op die iets anders beweert dan er in de agenda staat.
 */
class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(BookingService::class)->book([
                'name'      => (string) ($data['name'] ?? ''),
                'company'   => $data['company'] ?? null,
                'type'      => $data['type'] ?? 'meet',
                'attachments' => array_values(array_filter((array) ($data['attachments'] ?? []))),
                'email'     => (string) ($data['email'] ?? ''),
                'phone'     => $data['phone'] ?? null,
                'starts_at' => $data['starts_at'],
                'note'      => $data['note'] ?? null,
                // Zo is later terug te zien dat dit met de hand is ingepland en
                // niet door een bezoeker op een van de sites.
                'source_site' => ($data['source_site'] ?? null) ?: 'handmatig (admin)',
            ]);
        } catch (SlotTakenException $e) {
            $this->melding(
                'Dat moment is niet vrij',
                'Er staat al iets in de agenda op dat tijdstip. Kies een ander moment.'
            );
        } catch (CalendarUnavailableException $e) {
            $this->melding(
                'De agenda is niet bereikbaar',
                'We konden bij Google niet nakijken of dit uur vrij is, dus is er niets ingepland. '
                . 'Probeer het zo nog eens, of controleer de koppeling onder Google-agenda.'
            );
        }
    }

    /**
     * Melden en stoppen. halt() breekt het opslaan af, zodat er geen halve
     * afspraak achterblijft en de invuller zijn gegevens houdt.
     */
    private function melding(string $titel, string $tekst): never
    {
        Notification::make()->title($titel)->body($tekst)->danger()->persistent()->send();

        $this->halt();
    }
}

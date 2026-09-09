<?php

namespace App\Services\Scheduling;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * De gedeelde Drive-map met stukken die je aan een uitnodiging kunt hangen.
 *
 * WAAROM DRIVE EN NIET EEN UPLOAD. Google laat aan een agenda-item alleen
 * Drive-bestanden hangen; een los bestand meesturen kan niet. Dus wijzen we naar
 * wat er al in de gedeelde map staat, in plaats van elke keer hetzelfde document
 * opnieuw te uploaden.
 *
 * DE INDELING. Onder de hoofdmap staat per klant een map, en daarin de stukken
 * voor die klant. Dat is de map die in de Verkenner staat als
 * "G:\Shared drives\04] Beter Geregeld ICT\Google Meet".
 *
 * TOEGANG. Een externe genodigde komt niet bij een gedeelde Drive. Daarom geven
 * wij hem bij het versturen leesrecht op precies dat ene bestand -- niet op de
 * map, en niet aan "iedereen met de link". Dat laatste zou elk klantstuk in die
 * map bereikbaar maken voor wie de link heeft.
 *
 * Deze koppeling gebruikt hetzelfde token als de agenda: het is dezelfde
 * koppeling, met een recht erbij. Is er niet (opnieuw) gekoppeld, dan geeft
 * alles hier gewoon niets terug in plaats van te klappen -- een formulier hoort
 * niet om te vallen omdat Drive even niet meewerkt.
 */
class DriveClient
{
    public function __construct(private GoogleCalendarGateway $agenda) {}

    /** Hoe de hoofdmap heet in Drive. */
    private function hoofdmapNaam(): string
    {
        return (string) config('scheduling.drive.folder_name', 'Google Meet');
    }

    /**
     * De id van de hoofdmap. Een uur onthouden: de map verhuist niet, en dit
     * scheelt een zoekopdracht bij elke keer dat het formulier opengaat.
     */
    public function hoofdmapId(): ?string
    {
        $vast = trim((string) config('scheduling.drive.folder_id', ''));
        if ($vast !== '') {
            return $vast;
        }

        return Cache::remember('drive_hoofdmap_id', now()->addHour(), function () {
            $naam = str_replace("'", "\\'", $this->hoofdmapNaam());
            $rijen = $this->lijst([
                'q' => "name = '{$naam}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                'fields' => 'files(id,name)',
                'pageSize' => 10,
            ]);

            return $rijen[0]['id'] ?? null;
        });
    }

    /** De klantmappen onder de hoofdmap, als id => naam. */
    public function klantmappen(): array
    {
        $hoofd = $this->hoofdmapId();
        if (! $hoofd) {
            return [];
        }

        $uit = [];
        foreach ($this->lijst([
            'q' => "'{$hoofd}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
            'fields' => 'files(id,name)',
            'orderBy' => 'name',
            'pageSize' => 200,
        ]) as $map) {
            $uit[$map['id']] = $map['name'];
        }

        return $uit;
    }

    /** De bestanden in een klantmap, als id => naam. Mappen blijven eruit. */
    public function bestanden(string $mapId): array
    {
        if ($mapId === '') {
            return [];
        }

        $uit = [];
        foreach ($this->lijst([
            'q' => "'{$mapId}' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false",
            'fields' => 'files(id,name,mimeType,webViewLink,iconLink)',
            'orderBy' => 'name',
            'pageSize' => 200,
        ]) as $b) {
            $uit[$b['id']] = $b['name'];
        }

        return $uit;
    }

    /** Alles wat het agenda-item van een bestand moet weten. */
    public function bestand(string $bestandId): ?array
    {
        $token = $this->agenda->accessToken();
        if (! $token || $bestandId === '') {
            return null;
        }

        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->agenda->ca()])->timeout(15)
                ->get('https://www.googleapis.com/drive/v3/files/' . rawurlencode($bestandId), [
                    'fields' => 'id,name,mimeType,webViewLink,iconLink',
                    'supportsAllDrives' => 'true',
                ]);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        return $resp->successful() ? $resp->json() : null;
    }

    /**
     * Leesrecht op één bestand voor één adres.
     *
     * sendNotificationEmail staat uit: de genodigde krijgt al een uitnodiging
     * met het stuk eraan. Een tweede mail van Google ("X heeft een bestand met
     * je gedeeld") maakt er twee berichten van waar er één hoorde te zijn.
     *
     * Bestaat het recht al, dan antwoordt Google met een fout die wij hier
     * inslikken: het doel is bereikt en dat is wat telt.
     */
    public function geefLeesrecht(string $bestandId, string $email): bool
    {
        $token = $this->agenda->accessToken();
        if (! $token || $bestandId === '' || $email === '') {
            return false;
        }

        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->agenda->ca()])->timeout(15)
                ->post('https://www.googleapis.com/drive/v3/files/' . rawurlencode($bestandId) . '/permissions?'
                    . http_build_query(['supportsAllDrives' => 'true', 'sendNotificationEmail' => 'false']), [
                        'role' => 'reader',
                        'type' => 'user',
                        'emailAddress' => $email,
                    ]);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return $resp->successful();
    }

    /** Een lijstopdracht op Drive, inclusief gedeelde drives. */
    private function lijst(array $vraag): array
    {
        $token = $this->agenda->accessToken();
        if (! $token) {
            return [];
        }

        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->agenda->ca()])->timeout(15)
                ->get('https://www.googleapis.com/drive/v3/files', $vraag + [
                    // Zonder deze twee ziet de zoekopdracht alleen "Mijn Drive"
                    // en blijft een gedeelde Drive volledig onzichtbaar.
                    'supportsAllDrives' => 'true',
                    'includeItemsFromAllDrives' => 'true',
                    'corpora' => 'allDrives',
                ]);
        } catch (\Throwable $e) {
            report($e);

            return [];
        }

        if (! $resp->successful()) {
            report(new \RuntimeException('Drive gaf status ' . $resp->status() . ': ' . substr($resp->body(), 0, 200)));

            return [];
        }

        return $resp->json('files') ?? [];
    }
}

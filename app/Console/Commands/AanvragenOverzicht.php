<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Wekelijks overzicht: hoeveel échte aanvragen kwamen er binnen, en waar vandaan.
 *
 * WAAROM. Op 15-09-2026 moest dit met de hand uit drie tabellen worden gereconstrueerd,
 * en het antwoord viel tegen: in zes weken één echte aanvraag (Apotheek Avereest), de
 * rest spam en eigen tests. Dat wil je elke week zien, niet één keer per kwartaal.
 *
 * WAT TELT ALS ECHT. Contactberichten met status 'new' (spam heeft een eigen status),
 * website_leads en afspraken — telkens zonder afzenders van onze eigen domeinen en
 * zonder handmatig in de admin aangemaakte afspraken. Erbij: het aantal bezoeken uit de
 * page_view-beacon, als noemer. Dat cijfer telt Googlebot/Bingbot mee (die draaien JS),
 * dus lees het als bovengrens.
 */
class AanvragenOverzicht extends Command
{
    protected $signature = 'aanvragen:overzicht
        {--weken=6 : Aantal weken terug}
        {--mail : Stuur het overzicht naar contact.notify_email}';

    protected $description = 'Echte aanvragen per week en per bron (contactformulier, channel-leads, afspraken), met bezoeken als noemer';

    /** Afzenders die nooit een aanvraag zijn: wijzelf. */
    private const EIGEN_DOMEINEN = ['bouwsteenwinkel.nl', 'betergeregeld.com', 'betergeregeld.nl', 'studloop.com'];

    public function handle(): int
    {
        $weken = max(1, (int) $this->option('weken'));
        $start = Carbon::now()->startOfWeek()->subWeeks($weken - 1);
        $regels = [];

        $regels[] = sprintf('Aanvragen per week, %s t/m vandaag (%s)', $start->format('d-m-Y'), Carbon::now()->format('d-m-Y H:i'));
        $regels[] = '';
        $regels[] = sprintf('%-12s %8s %8s %8s %8s   %s', 'week van', 'contact', 'leads', 'afspr.', 'bezoek', 'details');

        $totaal = ['contact' => 0, 'leads' => 0, 'afspraken' => 0];
        for ($w = 0; $w < $weken; $w++) {
            $van = $start->copy()->addWeeks($w);
            $tot = $van->copy()->addWeek();

            $contact = DB::table('contact_messages')
                ->whereBetween('created_at', [$van, $tot])
                ->where('status', 'new')
                ->where(fn ($q) => $this->nietEigen($q, 'email'))
                ->get(['topic', 'company', 'email']);

            $leads = DB::table('website_leads')
                ->whereBetween('created_at', [$van, $tot])
                ->where(fn ($q) => $this->nietEigen($q, 'email'))
                ->get(['channel', 'source', 'company', 'email']);

            $afspraken = DB::table('appointments')
                ->whereBetween('created_at', [$van, $tot])
                ->where('status', '<>', 'cancelled')
                ->where(fn ($q) => $q->whereNull('source_site')->orWhere('source_site', 'not like', 'handmatig%'))
                ->where(fn ($q) => $this->nietEigen($q, 'email'))
                ->get(['source_site', 'company', 'email']);

            $bezoek = (int) DB::table('channel_events')
                ->whereBetween('created_at', [$van, $tot])
                ->where('event', 'page_view')
                ->distinct()->count('visit_ref');

            $details = [];
            foreach ($contact as $c) {
                $details[] = 'contact:' . ($c->topic ?: '-') . ' ' . $this->wie($c);
            }
            foreach ($leads as $l) {
                $details[] = 'lead:' . ($l->channel ?: '-') . '/' . ($l->source ?: '-') . ' ' . $this->wie($l);
            }
            foreach ($afspraken as $a) {
                $details[] = 'afspraak:' . ($a->source_site ?: '-') . ' ' . $this->wie($a);
            }

            $totaal['contact'] += $contact->count();
            $totaal['leads'] += $leads->count();
            $totaal['afspraken'] += $afspraken->count();

            $regels[] = sprintf('%-12s %8d %8d %8d %8d   %s',
                $van->format('d-m-Y'), $contact->count(), $leads->count(), $afspraken->count(), $bezoek,
                $details ? implode('; ', $details) : '—');
        }

        $regels[] = '';
        $regels[] = sprintf('Totaal %d weken: %d contactberichten, %d channel-leads, %d afspraken.',
            $weken, $totaal['contact'], $totaal['leads'], $totaal['afspraken']);
        $regels[] = '';
        $regels[] = 'Spam en eigen tests zijn eruit (status spam; afzenders @' . implode(', @', self::EIGEN_DOMEINEN) . '; handmatige afspraken).';
        $regels[] = 'Bezoek = unieke page_view-bezoeken over betergeregeld.com + alle channels; Googlebot/Bingbot tellen mee, dus bovengrens.';
        $regels[] = 'Contactberichten die nog op "new" staan: nalezen in de admin; een echte aanvraag krijgt ook een losse [WEBFORM]-mail.';

        $tekst = implode("\n", $regels);
        $this->line($tekst);

        if ($this->option('mail')) {
            $aan = (string) config('contact.notify_email');
            if ($aan === '') {
                $this->warn('contact.notify_email is leeg; geen mail verstuurd.');

                return self::SUCCESS;
            }
            Mail::raw($tekst, fn ($m) => $m->to($aan)->subject('[WEBFORM] Weekoverzicht aanvragen Betergeregeld'));
            $this->info("Gemaild naar {$aan}.");
        }

        return self::SUCCESS;
    }

    private function nietEigen($q, string $kolom): void
    {
        foreach (self::EIGEN_DOMEINEN as $d) {
            $q->where($kolom, 'not like', '%@' . $d);
        }
    }

    private function wie(object $r): string
    {
        $domein = substr((string) strrchr((string) ($r->email ?? ''), '@'), 1);

        return '(' . (($r->company ?? '') ?: $domein ?: '?') . ')';
    }
}

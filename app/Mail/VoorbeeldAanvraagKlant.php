<?php

namespace App\Mail;

use App\Models\WebsiteLead;
use App\Support\ChannelSite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

/**
 * Bevestiging aan de aanvrager van een voorbeeldsite.
 *
 * Doet twee dingen die een standaard "bedankt voor je aanvraag" niet doet: het
 * kondigt het telefoontje aan (dat komt er toch, en aangekondigd voelt het als
 * service in plaats van als verrassing), en het toont wat hij heeft ingevuld.
 * Dat laatste is een controle voor hem én een geheugensteun voor het gesprek.
 */
class VoorbeeldAanvraagKlant extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly WebsiteLead $lead,
        public readonly ChannelSite $site,
        public readonly array $antwoorden = [],
    ) {
    }

    public function envelope(): Envelope
    {
        $bedrijf = $this->lead->company ?: 'je bedrijf';

        return new Envelope(subject: 'Je voorbeeldwebsite voor ' . $bedrijf . ' — dit gebeurt er nu');
    }

    public function content(): Content
    {
        $voornaam = trim((string) $this->lead->contact_name);
        $voornaam = $voornaam !== '' ? explode(' ', $voornaam)[0] : 'daar';

        return new Content(
            view: 'emails.voorbeeld.aanvraag-klant',
            with: [
                'merknaam'     => $this->site->name(),
                'voornaam'     => $voornaam,
                'bedrijf'      => e($this->lead->company ?: 'je bedrijf'),
                'levertijd'    => (string) config('voorbeeld_aanvraag.levertijd', 'binnen 1 werkdag'),
                'afspraakUrl'  => $this->site->url('afspraak'),
                'siteHost'     => $this->site->domain() ?: 'onze site',
                'samenvatting' => new HtmlString($this->samenvatting()),
            ],
        );
    }

    /** Wat hij invulde, als leesbaar lijstje. Lege antwoorden slaan we over. */
    private function samenvatting(): string
    {
        $labels = [
            'type_bedrijf' => 'Wat je doet',
            'plaats'       => 'Plaats',
            'doel'         => 'Belangrijkste doel',
            'sfeer'        => 'Uitstraling',
            'usp'          => 'Wat we zeker moeten weten',
        ];

        $regels = '';
        foreach ($labels as $sleutel => $label) {
            $waarde = trim((string) ($this->antwoorden[$sleutel] ?? ''));
            if ($waarde === '') {
                continue;
            }
            $regels .= '<tr>'
                . '<td style="padding:3px 12px 3px 0;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;color:#8A8681;white-space:nowrap;vertical-align:top;">' . e($label) . '</td>'
                . '<td style="padding:3px 0;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;color:#2E2C29;">' . e($waarde) . '</td>'
                . '</tr>';
        }

        if ($regels === '') {
            return '<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;color:#6B6864;">Je vulde alleen je contactgegevens in — de rest halen we telefonisch op.</div>';
        }

        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0">' . $regels . '</table>';
    }
}

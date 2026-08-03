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
 * Interne melding van een aangevraagde voorbeeldsite.
 *
 * Bewust in dezelfde huisstijl als de klantmail: wie ze naast elkaar in de
 * inbox ziet, weet meteen dat ze bij elkaar horen. De inhoud is wél anders —
 * hier telt alleen wat je nodig hebt om te bellen en te bouwen, met het
 * telefoonnummer als eerste klikbare ding.
 */
class VoorbeeldAanvraagIntern extends Mailable
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
        $wie = $this->lead->company ?: $this->lead->contact_name;

        return new Envelope(subject: 'Voorbeeld aangevraagd: ' . $wie . ' (' . $this->site->key . ')');
    }

    public function content(): Content
    {
        $voornaam = trim((string) $this->lead->contact_name);
        $voornaam = $voornaam !== '' ? explode(' ', $voornaam)[0] : 'de aanvrager';

        return new Content(
            view: 'emails.voorbeeld.aanvraag-intern',
            with: [
                'merknaam'      => $this->site->name(),
                'bedrijf'       => $this->lead->company ?: $this->lead->contact_name,
                'plaats'        => $this->lead->city ?: 'onbekend',
                'voornaam'      => $voornaam,
                'levertijd'     => (string) config('voorbeeld_aanvraag.levertijd', 'binnen 1 werkdag'),
                'siteHost'      => $this->site->domain() ?: $this->site->key,
                'bron'          => $this->lead->source ?: 'voorbeeld_aanvraag',
                'herkomst'      => $this->herkomst(),
                'adminUrl'      => rtrim((string) config('app.url'), '/') . '/admin/website-leads/' . $this->lead->id,
                'contactRijen'  => new HtmlString($this->contactRijen()),
                'antwoordRijen' => new HtmlString($this->antwoordRijen()),
            ],
        );
    }

    /** Telefoon en mail klikbaar: bellen moet één tik zijn, ook op een telefoon. */
    private function contactRijen(): string
    {
        $telefoon = trim((string) $this->lead->phone);
        $rijen = [
            ['Naam',    e($this->lead->contact_name), null],
            ['Bedrijf', e($this->lead->company ?: '—'), null],
            ['Telefoon', e($telefoon ?: '—'), $telefoon !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $telefoon) : null],
            ['E-mail',  e($this->lead->email), 'mailto:' . $this->lead->email],
            ['Plaats',  e($this->lead->city ?: '—'), null],
            ['Huidige site', e($this->lead->current_website ?: '—'), null],
        ];

        return $this->rijenNaarHtml($rijen);
    }

    private function antwoordRijen(): string
    {
        $labels = [
            'type_bedrijf' => 'Wat ze doen',
            'doel'         => 'Belangrijkste doel',
            'sfeer'        => 'Uitstraling',
            'usp'          => 'Wat we moeten weten',
        ];

        $rijen = [];
        foreach ($labels as $sleutel => $label) {
            $waarde = trim((string) ($this->antwoorden[$sleutel] ?? ''));
            $rijen[] = [$label, $waarde !== '' ? e($waarde) : '<span style="color:#8A8681;">niet ingevuld</span>', null];
        }

        return $this->rijenNaarHtml($rijen);
    }

    /** @param array<int,array{0:string,1:string,2:?string}> $rijen */
    private function rijenNaarHtml(array $rijen): string
    {
        $html = '';
        foreach ($rijen as [$label, $waarde, $link]) {
            $inhoud = $link ? '<a href="' . e($link) . '" style="color:#12386B;text-decoration:none;font-weight:600;">' . $waarde . '</a>' : $waarde;
            $html .= '<tr>'
                . '<td width="150" style="padding:8px 16px;border-top:1px solid #F1EFEB;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;color:#8A8681;vertical-align:top;">' . e($label) . '</td>'
                . '<td style="padding:8px 16px 8px 0;border-top:1px solid #F1EFEB;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:14px;color:#2E2C29;vertical-align:top;">' . $inhoud . '</td>'
                . '</tr>';
        }

        return $html;
    }

    /** Advertentie-herkomst, als die er is. Scheelt zoeken bij het terugbellen. */
    private function herkomst(): string
    {
        $delen = array_filter([
            $this->lead->utm_source ? 'bron ' . $this->lead->utm_source : null,
            $this->lead->utm_campaign ? 'campagne ' . $this->lead->utm_campaign : null,
            $this->lead->gclid ? 'via Google Ads' : null,
        ]);

        return implode(' · ', $delen);
    }
}

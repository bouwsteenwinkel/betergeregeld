<?php

namespace App\Http\Controllers\ChannelSite;

use App\Http\Controllers\Controller;
use App\Support\ChannelSite;
use App\Support\TelefonieConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * /ai-telefonie-{key}/infoblad.pdf: het infoblad (PDF) bij de openbare telefoniedemo van
 * dit kanaal (16-09-2026). Welk blad, staat in de telefonieconfig van het kanaal
 * ('infoblad' => 'garage' | 'bakkerij' | 'apotheek'); de inhoud in
 * config/telefonie_infobladen.php. Geen infoblad = 404, net als de pagina zelf.
 *
 * dompdf rendert bij elke aanvraag (zelfde recept als de AccessGuard-handleiding); het
 * blad is twee pagina's en het verkeer klein, dus geen cache. `stream` toont het in de
 * browser met de bestandsnaam uit de config; opslaan kan de bezoeker zelf.
 */
class TelefonieInfobladController extends Controller
{
    public function download(Request $request): Response
    {
        $site = app(ChannelSite::class);
        $c    = TelefonieConfig::for($site);
        abort_if(! $c || (string) $request->route('branche') !== $site->key, 404);

        $blad = (array) config('telefonie_infobladen.' . ($c['infoblad'] ?? ''), []);
        abort_if(! $blad, 404);

        $pdf = Pdf::loadView('pdf.telefonie-infoblad', [
            'b'     => $blad,
            'site'  => $site,
            'datum' => now()->format('d-m-Y'),
        ])
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream($blad['bestand'] ?? 'infoblad.pdf');
    }
}

<?php

namespace App\Http\Controllers\ChannelSite;

use App\Http\Controllers\Controller;
use App\Models\Channel\Block;
use App\Services\ChannelSiteResolver;
use App\Support\ChannelSite;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Rendert één blok-template met representatieve voorbeeld-content, als losse mini-
 * pagina. Wordt in de admin als thumbnail (iframe) getoond zodat je per blok direct
 * ziet of het klopt. Optioneel ?site=<key> voor de huisstijl van die site.
 */
class BlockPreviewController extends Controller
{
    public function show(Request $request, string $type): View
    {
        $site = $this->previewSite($request->query('site'));

        $block = new Block([
            'type'      => $type,
            'block_key' => $type,
            'content'   => $this->sampleContent($type),
        ]);

        $view = view()->exists('channels.blocks.' . $type) ? 'channels.blocks.' . $type : 'channels.blocks._generic';

        return view('channels.block-preview', [
            'site'      => $site,
            'block'     => $block,
            'blockView' => $view,
            'facets'    => (array) config('groeidiamant.facets', []),
        ]);
    }

    private function previewSite(?string $key): ChannelSite
    {
        if ($key) {
            $site = app(ChannelSiteResolver::class)->byKey($key);
            if ($site) {
                return $site;
            }
        }

        // Neutraal preview-thema.
        return new ChannelSite('preview', [
            'name'  => 'Voorbeeld',
            'theme' => [
                'primary' => '#4f46e5', 'accent' => '#06b6d4', 'ink' => '#0f172a',
                'muted' => '#64748b', 'bg' => '#ffffff', 'surface' => '#f8fafc', 'radius' => '14px',
            ],
            'brand' => ['phone' => '085 1303 600', 'email' => 'info@betergeregeld.com', 'address' => 'Bussum'],
            'meta'  => [],
        ]);
    }

    /** @return array<string,mixed> representatieve voorbeeld-content per bloktype */
    private function sampleContent(string $type): array
    {
        return match ($type) {
            'hero' => ['eyebrow' => 'Voor jouw branche', 'title' => 'Een website die klanten oplevert', 'sub' => 'Strak, snel en goed vindbaar — wij regelen de techniek.', 'cta_label' => 'Gratis voorbeeld'],
            'uspbar' => ['items' => [['icon' => '⚡', 'text' => 'Snel live'], ['icon' => '📱', 'text' => 'Mobiel-proof'], ['icon' => '🔍', 'text' => 'Vindbaar in Google']]],
            'features' => ['heading' => 'Alles wat je nodig hebt', 'sub' => 'In één strakke website.', 'items' => [
                ['icon' => '🗓️', 'title' => 'Online afspraken', 'text' => 'Klanten boeken zelf.'],
                ['icon' => '💬', 'title' => 'Contact', 'text' => 'Eenvoudig bereikbaar.'],
                ['icon' => '⭐', 'title' => 'Reviews', 'text' => 'Bouw vertrouwen.'],
                ['icon' => '📍', 'title' => 'Lokaal', 'text' => 'Gevonden in jouw stad.'],
            ]],
            'steps' => ['heading' => 'Zo werkt het', 'items' => [
                ['title' => 'Gratis voorbeeld', 'text' => 'We zetten een voorbeeld klaar.'],
                ['title' => 'Afstemmen', 'text' => 'In één gesprek scherpen we het aan.'],
                ['title' => 'Live', 'text' => 'Wij regelen techniek en hosting.'],
            ]],
            'about' => ['eyebrow' => 'Over ons', 'heading' => 'Mensen die je branche snappen', 'lead' => 'Al jaren bouwen we websites die werken.', 'body' => "Geen ingewikkelde systemen, geen lange contracten.\n\nJe krijgt een strakke site die nieuwe klanten naar je toe trekt.", 'stats' => [['value' => '10+', 'label' => 'jaar ervaring'], ['value' => '4,9', 'label' => 'review'], ['value' => '2 wk', 'label' => 'tot live']]],
            'proof' => ['quote' => 'Sinds de nieuwe site lopen de aanvragen storm.', 'author' => 'ondernemer in Bussum'],
            'reviews' => ['heading' => 'Wat klanten zeggen', 'items' => [
                ['stars' => 5, 'text' => 'Precies wat ik wilde — snel en strak.', 'author' => 'Marloes'],
                ['stars' => 5, 'text' => 'Klanten boeken nu zelf online.', 'author' => 'Dave'],
                ['stars' => 5, 'text' => 'Fijne samenwerking, aanrader.', 'author' => 'Sanne'],
            ]],
            'faq' => ['heading' => 'Veelgestelde vragen', 'items' => [
                ['q' => 'Wat kost het?', 'a' => 'Je krijgt vooraf een helder voorstel.'],
                ['q' => 'Hoe snel ben ik live?', 'a' => 'Meestal binnen twee weken.'],
                ['q' => 'Kan ik zelf aanpassen?', 'a' => 'Ja, je beheert het zelf.'],
            ]],
            'gallery' => ['heading' => 'Een indruk van ons werk', 'sub' => 'Hier komen jouw eigen foto\'s.', 'tiles' => [['label' => 'Werk 1'], ['label' => 'Werk 2'], ['label' => 'Werk 3'], ['label' => 'Werk 4'], ['label' => 'Werk 5'], ['label' => 'Werk 6']]],
            'pricelist' => ['heading' => 'Onze diensten', 'sub' => 'Een greep uit ons aanbod.', 'items' => [
                ['name' => 'Dienst A', 'desc' => 'Korte omschrijving', 'price' => '€ 39'],
                ['name' => 'Dienst B', 'desc' => 'Korte omschrijving', 'price' => 'vanaf € 55'],
                ['name' => 'Dienst C', 'desc' => 'Korte omschrijving', 'price' => '€ 29'],
                ['name' => 'Dienst D', 'desc' => 'Korte omschrijving', 'price' => 'op aanvraag'],
            ]],
            'pricing' => ['heading' => 'Pakketten', 'plans' => [
                ['name' => 'Starter', 'price' => '€ 29', 'period' => 'p/m', 'features' => "1 pagina\nContactformulier\nHosting", 'cta' => 'Kies'],
                ['name' => 'Plus', 'price' => '€ 49', 'period' => 'p/m', 'features' => "Meerdere pagina's\nBlog\nSEO", 'cta' => 'Kies', 'highlight' => true],
                ['name' => 'Pro', 'price' => '€ 89', 'period' => 'p/m', 'features' => "Webshop\nKoppelingen\nSupport", 'cta' => 'Kies'],
            ]],
            'cta' => ['title' => 'Klaar voor een betere website?', 'sub' => 'Vraag vrijblijvend je gratis voorbeeld aan.', 'cta_label' => 'Gratis voorbeeld'],
            'logos' => ['heading' => 'Vertrouwd door', 'items' => [['label' => 'Merk A'], ['label' => 'Merk B'], ['label' => 'Merk C'], ['label' => 'Merk D']]],
            'location' => ['heading' => 'Kom langs', 'hours' => [['day' => 'Ma–Vr', 'time' => '09:00 – 18:00'], ['day' => 'Zaterdag', 'time' => '09:00 – 16:00'], ['day' => 'Zondag', 'time' => 'Gesloten']]],
            'blog' => ['heading' => 'Laatste artikelen', 'limit' => 3],
            'richtext' => ['heading' => 'Een kop', 'html' => '<p>Vrije tekst met <strong>opmaak</strong> en een opsomming:</p><ul><li>Punt één</li><li>Punt twee</li></ul>'],
            default => [],
        };
    }
}

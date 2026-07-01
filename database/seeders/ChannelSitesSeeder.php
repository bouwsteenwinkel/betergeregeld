<?php

namespace Database\Seeders;

use App\Models\Channel\Block;
use App\Models\Channel\Branche;
use App\Models\Channel\Site;
use Illuminate\Database\Seeder;

/**
 * Migreert de bestaande config-kanalen (config/channel_sites.php) éénmalig naar
 * de database, zodat ze in de admin beheerd kunnen worden. Idempotent: branches/
 * sites via updateOrCreate; blokken alleen genereren als de site er nog geen heeft
 * (zo blijven handmatige/designer-edits behouden bij opnieuw draaien).
 */
class ChannelSitesSeeder extends Seeder
{
    /** Standaard-blokkenlijst (blueprint) per generieke branche. */
    private const BLUEPRINT = [
        ['type' => 'hero',     'status' => 'placeholder', 'locked' => false],
        ['type' => 'features', 'status' => 'placeholder', 'locked' => false],
        ['type' => 'steps',    'status' => 'placeholder', 'locked' => false],
        ['type' => 'proof',    'status' => 'placeholder', 'locked' => false],
        ['type' => 'groeipad', 'status' => 'klaar',       'locked' => false],
        ['type' => 'wizard',   'status' => 'klaar',       'locked' => true],
    ];

    public function run(): void
    {
        $channels = (array) config('channel_sites.channels', []);
        $promo    = (array) config('promo.channels', []);

        // 1) Branches: één per generiek kanaal (horeca, kapper) als template.
        $brancheForChannel = [
            'horeca'      => 'horeca',
            'kapper'      => 'kapper',
            'dameskapper' => 'kapper',  // bespoke demo's hangen aan kapper-branche
            'herenkapper' => 'kapper',
        ];

        foreach (['horeca', 'kapper'] as $bKey) {
            $cfg = $channels[$bKey] ?? [];
            Branche::updateOrCreate(['key' => $bKey], [
                'name'         => $cfg['name'] ?? ucfirst($bKey),
                'lead_branche' => $cfg['branche'] ?? 'overig',
                'theme'        => $cfg['theme'] ?? null,
                'blueprint'    => self::BLUEPRINT,
                'intake'       => $this->intakeFor($promo[$bKey] ?? []),
                'active'       => true,
            ]);
        }

        // 2) Sites + blokken.
        foreach ($channels as $key => $cfg) {
            $brancheKey = $brancheForChannel[$key] ?? 'kapper';
            $branche    = Branche::where('key', $brancheKey)->first();

            $site = Site::updateOrCreate(['key' => $key], [
                'channel_branche_id' => $branche?->id,
                'name'        => $cfg['name'] ?? $key,
                'domain'      => $cfg['domain'] ?? null,
                'status'      => $cfg['status'] ?? 'draft',
                'locale'      => $cfg['locale'] ?? 'nl',
                'theme'       => $cfg['theme'] ?? null,
                'brand'       => $cfg['brand'] ?? null,
                'meta'        => $cfg['meta'] ?? null,
                'legacy_view' => $cfg['view'] ?? null,   // bespoke demo's behouden hun blade
            ]);

            // Bespoke demo's (legacy_view) renderen via hun eigen blade → geen blokken.
            if (! empty($cfg['view'])) {
                continue;
            }
            // Niet opnieuw genereren als er al blokken zijn (designer-edits sparen).
            if ($site->blocks()->exists()) {
                continue;
            }

            $home = (array) ($cfg['home'] ?? []);
            $sort = 0;
            foreach (self::BLUEPRINT as $b) {
                Block::create([
                    'channel_site_id' => $site->id,
                    'type'      => $b['type'],
                    'block_key' => $b['type'],
                    'sort'      => $sort += 10,
                    'enabled'   => true,
                    'locked'    => $b['locked'],
                    'status'    => $b['status'],
                    'content'   => $this->contentFor($b['type'], $home),
                ]);
            }
        }
    }

    /** Vertaalt config 'home' naar de content per bloktype. */
    private function contentFor(string $type, array $home): ?array
    {
        return match ($type) {
            'hero' => array_filter([
                'eyebrow'   => $home['hero_eyebrow'] ?? null,
                'title'     => $home['hero_title'] ?? null,
                'sub'       => $home['hero_sub'] ?? null,
                'cta_label' => $home['hero_cta'] ?? null,
                'note'      => $home['hero_note'] ?? null,
                'usps'      => $home['usps'] ?? null,
            ], fn ($v) => $v !== null),
            'features' => [
                'heading' => 'Alles wat je zaak nodig heeft',
                'sub'     => 'In één strakke website — wij regelen de techniek.',
                'items'   => $home['features'] ?? [],
            ],
            'steps' => [
                'heading' => 'Zo werkt het',
                'items'   => $home['steps'] ?? [],
            ],
            'proof' => $this->proofContent($home['proof'] ?? null),
            default => null,
        };
    }

    private function proofContent(?string $proof): ?array
    {
        if (! $proof) {
            return null;
        }
        $proof = trim($proof, " \t\n\r\0\x0B“”\"");
        if (str_contains($proof, '—')) {
            [$quote, $author] = array_map('trim', explode('—', $proof, 2));
            return ['quote' => trim($quote, " “”\""), 'author' => $author];
        }
        return ['quote' => $proof];
    }

    /** Wizard-config (stijl-opties + features) uit config/promo. */
    private function intakeFor(array $promoChannel): ?array
    {
        if (! $promoChannel) {
            return null;
        }
        $style = [];
        foreach (($promoChannel['questions']['general'] ?? []) as $q) {
            if (($q['key'] ?? '') === 'style' && ! empty($q['options'])) {
                $style = $q['options'];
            }
        }
        return array_filter([
            'style'    => $style,
            'features' => $promoChannel['features'] ?? [],
        ]);
    }
}

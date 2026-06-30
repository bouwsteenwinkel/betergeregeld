<?php

namespace Database\Seeders;

use App\Models\Channel\Branche;
use App\Models\Channel\Site;
use Illuminate\Database\Seeder;

/**
 * Zet de taxonomie naar het niche-model: de sector ("Kapper / schoonheid") is het
 * lead-branche-veld; de BRANCHES zijn de niches (dameskapper, herenkapper, …), elk
 * met eigen blueprint + thema. De demo-sites hangen onder hun niche-branche.
 * Idempotent.
 */
class NicheBranchesSeeder extends Seeder
{
    /** Standaard-blokkenlijst voor een nieuwe niche. */
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
        // De brede kapper-branche wordt "Kapper (algemeen)" — voor de generieke
        // SalonSites-marketingsite. De sector blijft het lead_branche-veld.
        Branche::where('key', 'kapper')->update(['name' => 'Kapper (algemeen)']);

        foreach ($this->niches() as $key => $n) {
            $branche = Branche::updateOrCreate(['key' => $key], [
                'name'         => $n['name'],
                'lead_branche' => 'kapper_beauty',
                'theme'        => $n['theme'],
                'active'       => true,
            ]);

            // Blueprint-blokken alleen aanmaken als de niche er nog geen heeft.
            if ($branche->blueprintBlocks()->doesntExist()) {
                $sort = 0;
                foreach (self::BLUEPRINT as $b) {
                    $branche->blueprintBlocks()->create([
                        'type'   => $b['type'],
                        'sort'   => $sort += 10,
                        'status' => $b['status'],
                        'locked' => $b['locked'],
                    ]);
                }
            }
        }

        // Demo-sites onder hun niche-branche hangen.
        $map = ['dameskapper' => 'dameskapper', 'herenkapper' => 'herenkapper'];
        foreach ($map as $siteKey => $brancheKey) {
            $branche = Branche::where('key', $brancheKey)->first();
            if ($branche) {
                Site::where('key', $siteKey)->update(['channel_branche_id' => $branche->id]);
            }
        }
    }

    /** @return array<string,array{name:string,theme:array}> */
    private function niches(): array
    {
        $serif = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap';

        return [
            'dameskapper' => [
                'name'  => 'Dameskapper',
                'theme' => ['primary' => '#b76e79', 'accent' => '#c9a27e', 'ink' => '#2b2724', 'muted' => '#8a807a', 'bg' => '#faf6f2', 'surface' => '#ffffff', 'font' => "'Jost', system-ui, sans-serif", 'font_url' => $serif, 'radius' => '16px'],
            ],
            'herenkapper' => [
                'name'  => 'Herenkapper',
                'theme' => ['primary' => '#c79a3a', 'accent' => '#e6c068', 'ink' => '#ece7df', 'muted' => '#9a9186', 'bg' => '#15120d', 'surface' => '#1f1b14', 'footer_bg' => '#0e0c08', 'font' => "'Barlow', system-ui, sans-serif", 'font_url' => 'https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Barlow:wght@300;400;500;600&display=swap', 'radius' => '8px'],
            ],
            'barbier' => [
                'name'  => 'Barbier',
                'theme' => ['primary' => '#9a6b3f', 'accent' => '#c79a3a', 'ink' => '#eae4da', 'muted' => '#9a9186', 'bg' => '#1a1714', 'surface' => '#241f1a', 'footer_bg' => '#120f0c', 'font' => "'Barlow', system-ui, sans-serif", 'font_url' => 'https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Barlow:wght@300;400;500;600&display=swap', 'radius' => '6px'],
            ],
            'nagelsalon' => [
                'name'  => 'Nagelsalon',
                'theme' => ['primary' => '#d6336c', 'accent' => '#f783ac', 'ink' => '#2b2330', 'muted' => '#8a7f8c', 'bg' => '#fff5f8', 'surface' => '#ffffff', 'font' => "'Quicksand', system-ui, sans-serif", 'font_url' => 'https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap', 'radius' => '20px'],
            ],
            'schoonheidssalon' => [
                'name'  => 'Schoonheidssalon',
                'theme' => ['primary' => '#7c9885', 'accent' => '#c9a27e', 'ink' => '#2e2a26', 'muted' => '#8a857e', 'bg' => '#f6f4f0', 'surface' => '#ffffff', 'font' => "'Jost', system-ui, sans-serif", 'font_url' => $serif, 'radius' => '16px'],
            ],
        ];
    }
}

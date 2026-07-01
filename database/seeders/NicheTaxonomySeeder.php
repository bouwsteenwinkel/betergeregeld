<?php

namespace Database\Seeders;

use App\Models\Channel\Branche;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Canonieke branche-taxonomie: per lead-branche (sector) een reeks niche-branches.
 * Maakt de niches aan als channel_branches (sector-tag + sector-thema + standaard-
 * blueprint), klaar om er sites onder te hangen. Idempotent (firstOrCreate op key):
 * bestaande niches (met eigen thema/blokken) blijven ongemoeid.
 */
class NicheTaxonomySeeder extends Seeder
{
    /** Standaard-blokkenlijst (JSON-blueprint) voor een nieuwe niche. */
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
        foreach ($this->taxonomy() as $sector => $data) {
            $theme = $this->sectorTheme($data['primary'], $data['accent'], $data['bg']);
            foreach ($data['niches'] as $name) {
                Branche::firstOrCreate(
                    ['key' => Str::slug($name)],
                    [
                        'name'         => $name,
                        'lead_branche' => $sector,
                        'theme'        => $theme,
                        'blueprint'    => self::BLUEPRINT,
                        'active'       => true,
                    ]
                );
            }
        }
    }

    private function sectorTheme(string $primary, string $accent, string $bg): array
    {
        return [
            'primary'      => $primary,
            'accent'       => $accent,
            'ink'          => '#1c2530',
            'muted'        => '#5b6b78',
            'bg'           => $bg,
            'surface'      => '#ffffff',
            'radius'       => '12px',
            'font'         => "'Inter', system-ui, sans-serif",
            'font_display' => "'Sora', system-ui, sans-serif",
            'font_url'     => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap',
        ];
    }

    /** @return array<string,array{primary:string,accent:string,bg:string,niches:string[]}> */
    private function taxonomy(): array
    {
        return [
            'bouw_installatie' => ['primary' => '#1f4e79', 'accent' => '#f59e0b', 'bg' => '#f4f7fa', 'niches' => [
                'Loodgieter', 'Elektricien', 'CV-installateur', 'Warmtepomp-installateur', 'Airco-installateur',
                'Zonnepanelen-installateur', 'Schilder', 'Stukadoor', 'Timmerman', 'Aannemer',
                'Dakdekker', 'Dakkapel-specialist', 'Metselaar', 'Tegelzetter', 'Vloerenlegger',
                'Kozijnenbedrijf', 'Glaszetter', 'Hovenier', 'Bestratingsbedrijf', 'Isolatiebedrijf',
                'Sloopbedrijf', 'Rioolontstopper', 'Klusbedrijf', 'Interieurbouwer', 'Keukenmonteur',
                'Badkamerspecialist', 'Zonwering-specialist', 'Garagedeuren-specialist', 'Traprenovatie', 'Asbestverwijdering',
            ]],
            'kapper_beauty' => ['primary' => '#b76e79', 'accent' => '#c9a27e', 'bg' => '#faf6f2', 'niches' => [
                'Dameskapper', 'Herenkapper', 'Barbier', 'Nagelsalon', 'Schoonheidssalon',
                'Wimperstyliste', 'Visagist', 'Pedicure', 'Permanente make-up', 'Massagesalon',
                'Zonnestudio', 'Wellness & spa', 'Ontharingssalon', 'Huidtherapeut', 'Brow bar', 'Kapper aan huis',
            ]],
            'horeca' => ['primary' => '#b91c1c', 'accent' => '#f59e0b', 'bg' => '#fffaf5', 'niches' => [
                'Restaurant', 'Café', 'Lunchroom', 'Koffiebar', 'Cafetaria', 'Pizzeria', 'Bezorgrestaurant',
                'Cateraar', 'Foodtruck', 'Brasserie', 'IJssalon', 'Strandtent', 'Hotel', 'Bed & breakfast',
                'Partycentrum', 'Grillroom',
            ]],
            'detailhandel' => ['primary' => '#0e7490', 'accent' => '#f59e0b', 'bg' => '#f6fafb', 'niches' => [
                'Bloemist', 'Slagerij', 'Bakkerij', 'Kaaswinkel', 'Juwelier', 'Opticien', 'Fietsenwinkel',
                'Kledingwinkel', 'Schoenenwinkel', 'Speelgoedwinkel', 'Dierenspeciaalzaak', 'Boekhandel',
                'Cadeauwinkel', 'Woonwinkel', 'Slijterij', 'Drogisterij', 'Sportwinkel', 'Meubelzaak',
                'Tuincentrum', 'Kringloopwinkel',
            ]],
            'zzp_diensten' => ['primary' => '#4f46e5', 'accent' => '#06b6d4', 'bg' => '#f8fafc', 'niches' => [
                'Fotograaf', 'Videograaf', 'Grafisch ontwerper', 'Webdesigner', 'Tekstschrijver', 'Boekhouder',
                'Administratiekantoor', 'Belastingadviseur', 'Businesscoach', 'Loopbaancoach', 'Consultant',
                'Virtual assistant', 'Vertaler', 'Trainer', 'Marketingbureau', 'Social media bureau', 'Notaris',
                'Advocaat', 'Juridisch adviseur', 'Architect', 'Interieurontwerper', 'Schoonmaakbedrijf',
                'Beveiligingsbedrijf', 'Uitvaartverzorger',
            ]],
            'sport_fitness' => ['primary' => '#111827', 'accent' => '#84cc16', 'bg' => '#f9fafb', 'niches' => [
                'Sportschool', 'Personal trainer', 'Yogastudio', 'Pilatesstudio', 'Dansschool', 'Vechtsportschool',
                'CrossFit-box', 'Bootcamp', 'Tennisschool', 'Golfschool', 'Klimhal', 'Padelclub',
            ]],
            'zorg' => ['primary' => '#0f766e', 'accent' => '#22b3a0', 'bg' => '#f4fbfa', 'niches' => [
                'Fysiotherapeut', 'Tandarts', 'Huisartsenpraktijk', 'Psycholoog', 'Diëtist', 'Podotherapeut',
                'Logopedist', 'Ergotherapeut', 'Osteopaat', 'Chiropractor', 'Verloskundige', 'Orthodontist',
                'Optometrist', 'Apotheek', 'Thuiszorg', 'Kraamzorg', 'Acupuncturist', 'Mondhygiënist',
            ]],
            'automotive' => ['primary' => '#1f2937', 'accent' => '#ef4444', 'bg' => '#f7f7f8', 'niches' => [
                'Autogarage', 'APK-keuringsstation', 'Bandenservice', 'Autoschadeherstel', 'Car detailing',
                'Autoruit-specialist', 'Motorzaak', 'Camperservice', 'Autobedrijf occasions', 'Uitlaat & remmen',
                'Auto-airco service', 'Aanhangwagenspecialist',
            ]],
            'vastgoed' => ['primary' => '#1e3a5f', 'accent' => '#c0a062', 'bg' => '#f8f9fb', 'niches' => [
                'Makelaar', 'Hypotheekadviseur', 'Taxateur', 'VvE-beheer', 'Vastgoedbeheer', 'Aankoopmakelaar',
                'Verhuurmakelaar', 'Bedrijfsmakelaar', 'Bouwkundig keurder', 'Energieadviseur',
            ]],
            'onderwijs_opvang' => ['primary' => '#7c3aed', 'accent' => '#f59e0b', 'bg' => '#faf8ff', 'niches' => [
                'Kinderopvang', 'Gastouderbureau', 'Muziekschool', 'Rijschool', 'Bijlesbureau', 'Tekenacademie',
                'Zwemschool', 'Peuterspeelzaal',
            ]],
            'recreatie_vrije_tijd' => ['primary' => '#0891b2', 'accent' => '#f97316', 'bg' => '#f5fbfd', 'niches' => [
                'Reisbureau', 'Escape room', 'Indoor speelparadijs', 'Bowlingcentrum', 'Camping', 'Vakantiepark',
                'Sauna', 'Museum', 'Pretpark', 'Fietsverhuur',
            ]],
            'transport_logistiek' => ['primary' => '#1e293b', 'accent' => '#eab308', 'bg' => '#f7f8fa', 'niches' => [
                'Koeriersdienst', 'Verhuisbedrijf', 'Taxibedrijf', 'Touringcarbedrijf', 'Self-storage',
                'Autotransport', 'Grondverzet', 'Kraanverhuur',
            ]],
            'agrarisch_dieren' => ['primary' => '#4d7c0f', 'accent' => '#ca8a04', 'bg' => '#f7faf2', 'niches' => [
                'Manege', 'Hondentrimsalon', 'Dierenpension', 'Hoefsmid', 'Loonbedrijf', 'Kwekerij',
                'Boerderijwinkel', 'Imkerij', 'Dierenarts', 'Paardenfysio',
            ]],
            'overig' => ['primary' => '#334155', 'accent' => '#2563eb', 'bg' => '#f8fafc', 'niches' => [
                'Tattooshop', 'Piercingstudio', 'Wasserij & stomerij', 'Slotenmaker', 'Glazenwasser', 'Ongediertebestrijding',
            ]],
        ];
    }
}

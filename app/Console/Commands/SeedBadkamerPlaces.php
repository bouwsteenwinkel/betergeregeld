<?php

namespace App\Console\Commands;

use App\Models\Channel\Branche;
use Illuminate\Console\Command;

/**
 * Zet de (curated) plaatsen-tokens op de badkamer-branche — het eerste domein.
 * De zware SEO-copy is generiek (config/channel_places.php); hier alleen de
 * niche-tokens + de Google-Places-zoekopdracht.
 */
class SeedBadkamerPlaces extends Command
{
    protected $signature = 'seed:badkamer-places';
    protected $description = 'Plaatsen-tokens voor de badkamer-branche instellen';

    public function handle(): int
    {
        $branche = Branche::where('key', 'badkamerspecialist')->first();
        if (! $branche) {
            $this->error('Branche badkamerspecialist niet gevonden.');
            return self::FAILURE;
        }

        $branche->places = [
            'trade'   => 'badkamerbedrijf',
            'trades'  => 'badkamerbedrijven',
            'niche'   => 'badkamer',
            'niches'  => 'badkamers',
            'service' => 'website',

            // Index-overrides (niche-specifiek).
            'h1'    => 'Badkamerbedrijven door heel Nederland online laten groeien',
            'intro' => 'Heb je een badkamerbedrijf en wil je meer klussen binnenhalen? We helpen badkamerspecialisten door heel Nederland aan een website, webshop en slimme tools die aanvragen opleveren. Kies je plaats en zie wat we in jouw regio doen.',

            // Echte lokale bedrijven (Google Places).
            'business' => [
                'label'       => 'Badkamerzaken & showrooms in :city',
                'intro'       => 'Dit zijn badkamerzaken, showrooms en installateurs in :city en omgeving — het landschap waarin jouw badkamerbedrijf moet opvallen. Met een sterke website sta jij bovenaan als iemand in :city een badkamer zoekt.',
                'query'       => 'badkamer showroom of sanitairwinkel in :city',
                'limit'       => 8,
                'min_reviews' => 5,
            ],
        ];
        $branche->save();

        $this->info('Badkamer plaatsen-tokens ingesteld.');
        return self::SUCCESS;
    }
}

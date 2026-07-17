<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsClient;
use Illuminate\Console\Command;

/**
 * Leest de prestaties van de campagnes in het gekoppelde account. Read-only —
 * werkt al op "Verkenner"-niveau, dus vóór Basic access.
 *
 *   php artisan ads:report            (all-time)
 *   php artisan ads:report --days=30  (laatste 30 dagen)
 */
class AdsReport extends Command
{
    protected $signature = 'ads:report {--days= : beperk tot de laatste N dagen (leeg = sinds start)}';

    protected $description = 'Toont impressies, klikken, kosten en conversies per campagne uit het Ads-account.';

    public function handle(GoogleAdsClient $ads): int
    {
        if (! $ads->connected()) {
            $this->error('Niet gekoppeld. Zie ads:status.');

            return self::FAILURE;
        }

        $days  = $this->option('days');
        $where = $days ? " WHERE segments.date DURING LAST_{$days}_DAYS" : '';
        // Zonder datumfilter geeft de API all-time totalen per campagne.
        $gaql = 'SELECT campaign.name, campaign.status, metrics.impressions, metrics.clicks, '
            . 'metrics.cost_micros, metrics.conversions FROM campaign' . $where
            . ' ORDER BY metrics.impressions DESC';

        $res = $ads->search($gaql);
        if (! $res['ok']) {
            $this->error('Mislukt: ' . $res['error']);

            return self::FAILURE;
        }

        $rows = [];
        $tImpr = $tClicks = 0;
        $tCost = $tConv = 0.0;
        foreach ($res['results'] as $r) {
            $c = $r['campaign'] ?? [];
            $m = $r['metrics'] ?? [];
            $impr = (int) ($m['impressions'] ?? 0);
            $clk  = (int) ($m['clicks'] ?? 0);
            $cost = ((int) ($m['costMicros'] ?? 0)) / 1_000_000;
            $conv = (float) ($m['conversions'] ?? 0);
            $tImpr += $impr; $tClicks += $clk; $tCost += $cost; $tConv += $conv;

            $rows[] = [
                mb_strimwidth((string) ($c['name'] ?? '—'), 0, 36, '…'),
                ucfirst(strtolower((string) ($c['status'] ?? ''))),
                number_format($impr, 0, ',', '.'),
                number_format($clk, 0, ',', '.'),
                '€ ' . number_format($cost, 2, ',', '.'),
                rtrim(rtrim(number_format($conv, 1, ',', '.'), '0'), ','),
            ];
        }

        $rows[] = ['—', '', '', '', '', ''];
        $rows[] = [
            'TOTAAL', '',
            number_format($tImpr, 0, ',', '.'),
            number_format($tClicks, 0, ',', '.'),
            '€ ' . number_format($tCost, 2, ',', '.'),
            rtrim(rtrim(number_format($tConv, 1, ',', '.'), '0'), ','),
        ];

        $this->line('');
        $this->line('  Account ' . config('google_ads.customer_id') . ' · ' . ($days ? "laatste {$days} dagen" : 'sinds start'));
        $this->table(['Campagne', 'Status', 'Impressies', 'Klikken', 'Kosten', 'Conv.'], $rows);

        return self::SUCCESS;
    }
}

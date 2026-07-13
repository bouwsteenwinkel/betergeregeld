<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use App\Services\ChannelSites\PreviewSiteGenerator;
use Illuminate\Console\Command;

/**
 * Ruimt verlopen, niet-opgeëiste self-service voorbeeldsites op (key preview-...).
 * Een preview die is aangevraagd (meta.preview.claimed = true) blijft staan, zodat
 * personeel 'm nog kan bekijken bij de lead-opvolging.
 */
class ChannelPreviewsCleanup extends Command
{
    protected $signature = 'channel:previews-cleanup {--dry-run : Alleen tonen wat verwijderd zou worden}';

    protected $description = 'Verwijder verlopen, niet-opgeëiste voorbeeldsites (preview-...)';

    public function handle(): int
    {
        $now = now();
        $deleted = 0;

        $sites = Site::where('key', 'like', 'preview-%')->get();

        foreach ($sites as $site) {
            $preview = (array) data_get($site->meta, 'preview', []);

            // Opgeëiste previews nooit weggooien.
            if (! empty($preview['claimed'])) {
                continue;
            }

            // Verval bepalen; ontbreekt het (oude row), val terug op created_at + TTL.
            $expiresAt = ! empty($preview['expires_at'])
                ? \Illuminate\Support\Carbon::parse($preview['expires_at'])
                : $site->created_at?->copy()->addHours(PreviewSiteGenerator::TTL_HOURS);

            if ($expiresAt && $expiresAt->greaterThan($now)) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("zou verwijderen: {$site->key} ({$site->name})");
                $deleted++;
                continue;
            }

            $site->blocks()->delete();
            $site->delete();
            $deleted++;
        }

        $this->info(($this->option('dry-run') ? 'Zou opruimen: ' : 'Opgeruimd: ') . $deleted . ' preview(s).');

        return self::SUCCESS;
    }
}

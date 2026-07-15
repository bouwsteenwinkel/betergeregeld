<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use App\Models\PreviewIntake;
use App\Services\ChannelSites\PreviewSiteGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Ruimt verlopen, niet-opgeëiste self-service voorbeeldsites op (key preview-...).
 * Een preview die is aangevraagd (meta.preview.claimed = true) blijft staan, zodat
 * personeel 'm nog kan bekijken bij de lead-opvolging.
 *
 * De intake van een verlopen preview wordt eerst weggeschreven naar {@see PreviewIntake}:
 * de bezoeker vulde bedrijfsnaam, plaats, doel en USP in en converteerde niet, en dat is
 * precies de groep die we anders nooit in beeld krijgen.
 */
class ChannelPreviewsCleanup extends Command
{
    protected $signature = 'channel:previews-cleanup {--dry-run : Alleen tonen wat verwijderd zou worden}';

    protected $description = 'Verwijder verlopen, niet-opgeëiste voorbeeldsites (preview-...)';

    public function handle(): int
    {
        $now = now();
        $deleted = 0;
        $archived = 0;

        // Opruimen is de kerntaak, archiveren is er later bij gekomen. Ontbreekt de
        // tabel (deploy zonder de migratie: artisan migrate faalt in dit project, dus
        // dat gaat met de hand), dan mag deze job daar niet permanent op stilvallen:
        // dan stapelen verlopen previews zich onbeperkt op. Eén waarschuwing, en
        // gewoon doorgaan met opruimen.
        $kanArchiveren = \Illuminate\Support\Facades\Schema::hasTable('preview_intakes');
        if (! $kanArchiveren) {
            $this->warn('Tabel preview_intakes ontbreekt: intake wordt NIET gearchiveerd. Draai de migratie 2026_07_15_150000_create_preview_intakes_table.');
            Log::warning('preview_intake_archive: tabel preview_intakes ontbreekt, opruimen gaat door zonder archiveren.');
        }

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

            // Archiveren vóór verwijderen, en bij een fout de site laten staan: mislukt de
            // archivering ná de delete, dan is de intake definitief weg. Andersom kost het
            // hooguit een uur uitstel, want de volgende run pakt 'm gewoon opnieuw op.
            // Dat overslaan geldt alleen bij een INCIDENTELE fout; ontbreekt de tabel
            // helemaal, dan is doorgaan beter dan eindeloos niets opruimen.
            if ($kanArchiveren) {
                try {
                    PreviewIntake::archive($site, $expiresAt);
                    $archived++;
                } catch (\Throwable $e) {
                    Log::warning("preview_intake_archive ({$site->key}): " . $e->getMessage());
                    continue;
                }
            }

            $site->blocks()->delete();
            $site->delete();
            $deleted++;
        }

        $this->info(($this->option('dry-run') ? 'Zou opruimen: ' : 'Opgeruimd: ') . $deleted . ' preview(s).');

        if (! $this->option('dry-run')) {
            $this->info('Intake gearchiveerd: ' . $archived . '.');
        }

        return self::SUCCESS;
    }
}

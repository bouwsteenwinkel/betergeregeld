<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Zet een CMP-tenant 'channels' klaar voor de niche-/trigger-sites, door een
 * bestaande tenant te klonen en de teksten naar de leadgen-context te zetten.
 * De rest (categorieën aan/uit, scripts gaten, teksten, kleuren) blijft volledig
 * beheerbaar in de Betergeregeld-admin (Filament CMP-resources).
 */
class CmpSeedChannels extends Command
{
    protected $signature = 'cmp:seed-channels {--source=bouwsteenwinkel} {--force}';
    protected $description = "CMP-tenant 'channels' aanmaken voor de trigger-sites (kloon + leadgen-teksten)";

    private string $target = 'channels';

    public function handle(): int
    {
        $source = (string) $this->option('source');
        $force  = (bool) $this->option('force');

        if (DB::table('cmp_policy')->where('tenant_key', $this->target)->exists() && ! $force) {
            $this->warn("Tenant '{$this->target}' bestaat al. Gebruik --force om te overschrijven.");
            return self::SUCCESS;
        }
        if (! DB::table('cmp_policy')->where('tenant_key', $source)->exists()) {
            $this->error("Bron-tenant '{$source}' niet gevonden.");
            return self::FAILURE;
        }

        // Schoon bij --force
        foreach (['cmp_policy', 'cmp_categories', 'cmp_texts', 'cmp_branding'] as $t) {
            DB::table($t)->where('tenant_key', $this->target)->delete();
        }

        $this->clone('cmp_policy', $source, ['policy_version', 'config_hash']);
        $this->clone('cmp_categories', $source, ['key', 'is_required', 'is_enabled', 'sort_order']);
        $this->clone('cmp_texts', $source, ['lang', 'text_key', 'text_value']);
        $this->clone('cmp_branding', $source, ['branding_json']);

        // Leadgen-teksten (geen winkelmand/account op de trigger-sites).
        $this->setText('banner.body', 'We gebruiken functionele cookies zodat de website goed werkt en je aanvraag veilig verstuurd wordt. Analytische cookies plaatsen we alleen met jouw toestemming, om de site te verbeteren.');
        $this->setText('cat.necessary.desc', 'Nodig om de website te laten werken en formulieren veilig te versturen. Worden altijd gebruikt.');
        $this->setText('cat.functional.desc', 'Onthouden van je voorkeuren op de site.');

        // Domein voor de preview/host (betergeregeld.com). Live channel-domeinen
        // voeg je later toe in de admin of via cmp_domains.
        DB::table('cmp_domains')->updateOrInsert(
            ['tenant_key' => $this->target, 'domain' => 'betergeregeld.com'],
            ['is_primary' => 1, 'status' => 'active', 'updated_at' => now(), 'created_at' => now()]
        );

        $this->info("CMP-tenant '{$this->target}' klaargezet (geleend van '{$source}'). Beheer verder in de admin.");
        $this->line('Let op: analytics/heatmap toevoegen? Doe dat als CMP-script (admin) in categorie "analytics" — nooit los in de layout.');
        return self::SUCCESS;
    }

    /** Kopieer alle rijen van een tenant naar het target, alleen de gegeven kolommen. */
    private function clone(string $table, string $source, array $cols): void
    {
        $rows = DB::table($table)->where('tenant_key', $source)->get();
        foreach ($rows as $row) {
            $data = ['tenant_key' => $this->target, 'updated_at' => now()];
            foreach ($cols as $c) {
                $data[$c] = $row->$c ?? null;
            }
            if (in_array('created_at', \Schema::getColumnListing($table), true)) {
                $data['created_at'] = now();
            }
            DB::table($table)->insert($data);
        }
        $this->line("  ✓ {$table}: " . count($rows) . ' rijen');
    }

    private function setText(string $key, string $value): void
    {
        DB::table('cmp_texts')
            ->where('tenant_key', $this->target)
            ->where('text_key', $key)
            ->update(['text_value' => $value, 'updated_at' => now()]);
    }
}

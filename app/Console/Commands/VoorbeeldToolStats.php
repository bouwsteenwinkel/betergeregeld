<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use App\Models\PreviewIntake;
use App\Models\SavedPreview;
use App\Models\WebsiteLead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leest hoe de zelfservice-voorbeeldtool gebruikt wordt. ALLEEN LEZEN: dit commando
 * schrijft niets en verwijdert niets, zodat het zonder nadenken op productie mag.
 *
 * WAAROM. Elke nieuwe preview kost ons 1 Claude-call plus 1 à 2 gpt-image-calls, dus
 * misbruik van dit formulier kost per inzending echt geld — anders dan bij het
 * contactformulier, waar spam alleen ergernis was. Voordat we er een mensencheck in
 * bouwen willen we weten óf er misbruik is en hoe groot het is. Gemeten op 12-09-2026
 * bleek ruim een derde van de 648 contactberichten spam; voor deze tool was dat cijfer
 * er simpelweg nog niet.
 *
 * WAT DIT NIET KAN. Er is geen IP en geen user-agent om op te tellen: `website_leads`
 * heeft geen ip-kolom en `preview_intakes` legt IP/user-agent bewust niet vast (zie de
 * migratie). Een niet-bewaarde preview heeft zelfs geen e-mailadres. Een botvlaag is
 * hier dus alleen te herkennen aan het PATROON: veel previews in korte tijd, een piek
 * op een raar uur, veel verlopen tegenover weinig bewaard, of steeds hetzelfde
 * e-maildomein bij de leads. Verwacht geen "30 inzendingen van één IP" zoals bij het
 * contactformulier — dat cijfer bestaat hier niet.
 *
 * WAAR DE AANTALLEN VANDAAN KOMEN. Een preview leeft 48 uur als channel_sites-rij
 * (key preview-...) en wordt daarna opgeruimd; is hij niet opgeëist, dan blijft de
 * intake achter in preview_intakes. Het echte totaal is dus de som van drie bakken:
 * previews die nu nog leven, gearchiveerde intakes, en opgeëiste previews die zijn
 * blijven staan. Dit commando telt ze apart, want bij elkaar optellen zou een deel
 * dubbel tellen.
 */
class VoorbeeldToolStats extends Command
{
    protected $signature = 'voorbeeld:stats {--dagen=30 : Over hoeveel dagen terug gerapporteerd wordt}';

    protected $description = 'Toont het gebruik van de voorbeeld-tool om misbruik te kunnen zien (alleen lezen)';

    public function handle(): int
    {
        $dagen = max(1, (int) $this->option('dagen'));
        $vanaf = now()->subDays($dagen)->startOfDay();

        $this->info("Voorbeeld-tool, laatste {$dagen} dagen (vanaf {$vanaf->toDateString()})");
        $this->newLine();

        // ── 1. Previews die nu nog leven ────────────────────────────────────
        $levend = Site::query()->where('key', 'like', 'preview-%')->get(['key', 'meta', 'created_at']);
        $opgeeist = $levend->filter(fn ($s) => ! empty(data_get($s->meta, 'preview.claimed')))->count();

        $this->line('Previews die nu bestaan : ' . $levend->count() . "  (opgeëist: {$opgeeist}, nog niet opgeëist: " . ($levend->count() - $opgeeist) . ')');

        // ── 2. Gearchiveerde intakes (verlopen én niet opgeëist) ────────────
        $kanIntakes = Schema::hasTable('preview_intakes');
        if (! $kanIntakes) {
            $this->warn('Tabel preview_intakes ontbreekt: de verlopen previews zijn niet te tellen.');
        }

        $intakesInVenster = $kanIntakes
            ? PreviewIntake::where('preview_created_at', '>=', $vanaf)->count()
            : 0;
        $intakesTotaal = $kanIntakes ? PreviewIntake::count() : 0;

        if ($kanIntakes) {
            $this->line("Verlopen, nooit bewaard  : {$intakesInVenster} in dit venster (ooit: {$intakesTotaal})");
        }

        $bewaard = SavedPreview::where('created_at', '>=', $vanaf)->count();
        $this->line("Bewaard door de bezoeker : {$bewaard} in dit venster (ooit: " . SavedPreview::count() . ')');
        $this->newLine();

        // ── 3. Per dag: hier zie je een vlaag meteen ────────────────────────
        $perDagLevend = Site::query()
            ->where('key', 'like', 'preview-%')
            ->where('created_at', '>=', $vanaf)
            ->select(DB::raw('DATE(created_at) as dag'), DB::raw('COUNT(*) as n'))
            ->groupBy('dag')->pluck('n', 'dag');

        $perDagIntake = $kanIntakes
            ? PreviewIntake::query()
                ->where('preview_created_at', '>=', $vanaf)
                ->select(DB::raw('DATE(preview_created_at) as dag'), DB::raw('COUNT(*) as n'))
                ->groupBy('dag')->pluck('n', 'dag')
            : collect();

        $dagen_ = collect($perDagLevend->keys())->merge($perDagIntake->keys())->unique()->sort()->values();

        if ($dagen_->isEmpty()) {
            $this->line('Geen enkele preview in dit venster.');
        } else {
            $rijen = $dagen_->map(fn ($d) => [
                (string) $d,
                (int) ($perDagLevend[$d] ?? 0),
                (int) ($perDagIntake[$d] ?? 0),
                (int) ($perDagLevend[$d] ?? 0) + (int) ($perDagIntake[$d] ?? 0),
            ])->all();
            $this->table(['dag', 'leeft nog', 'verlopen', 'totaal'], $rijen);
        }

        // ── 4. Per uur: bots werken 's nachts, mensen niet ──────────────────
        if ($kanIntakes) {
            $perUur = PreviewIntake::query()
                ->where('preview_created_at', '>=', $vanaf)
                ->select(DB::raw('HOUR(preview_created_at) as uur'), DB::raw('COUNT(*) as n'))
                ->groupBy('uur')->orderBy('uur')->pluck('n', 'uur');

            if ($perUur->isNotEmpty()) {
                $this->line('Verlopen previews per uur van de dag:');
                $this->line('  ' . $perUur->map(fn ($n, $u) => sprintf('%02d:%d', $u, $n))->implode('  '));
                $nacht = collect(range(0, 6))->sum(fn ($u) => (int) ($perUur[$u] ?? 0));
                $this->line("  waarvan tussen 00:00 en 07:00: {$nacht}");
                $this->newLine();
            }

            // ── 5. Vanaf welk kanaal ───────────────────────────────────────
            $perKanaal = PreviewIntake::query()
                ->where('preview_created_at', '>=', $vanaf)
                ->select('source_channel', DB::raw('COUNT(*) as n'))
                ->groupBy('source_channel')->orderByDesc('n')->limit(10)->get();

            if ($perKanaal->isNotEmpty()) {
                $this->table(
                    ['bronkanaal', 'verlopen previews'],
                    $perKanaal->map(fn ($r) => [$r->source_channel ?: '(onbekend)', $r->n])->all()
                );
            }
        }

        // ── 6. Leads per herkomst + e-maildomeinen ─────────────────────────
        $perBron = WebsiteLead::query()
            ->where('created_at', '>=', $vanaf)
            ->select('source', DB::raw('COUNT(*) as n'))
            ->groupBy('source')->orderByDesc('n')->get();

        if ($perBron->isNotEmpty()) {
            $this->table(
                ['lead-herkomst', 'aantal'],
                $perBron->map(fn ($r) => [$r->source ?: '(onbekend)', $r->n])->all()
            );
        }

        // Eén domein dat er bovenuit steekt is het enige identiteits-signaal dat we hier
        // hebben. Wegwerpdomeinen (.top/.xyz/mail.ru) zijn hetzelfde teken als bij het
        // contactformulier; zie App\Support\ContactSpam.
        $domeinen = WebsiteLead::query()
            ->where('created_at', '>=', $vanaf)
            ->whereNotNull('email')
            ->pluck('email')
            ->map(fn ($e) => mb_strtolower(trim(substr((string) $e, (int) strrpos((string) $e, '@') + 1))))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(10);

        if ($domeinen->isNotEmpty()) {
            $this->table(
                ['e-maildomein (leads)', 'aantal'],
                $domeinen->map(fn ($n, $d) => [$d, $n])->values()->all()
            );
        }

        $this->newLine();
        $this->line('Let op: er is geen IP of user-agent vastgelegd, dus "veel vanaf één afzender"');
        $this->line('is hier niet te zien. Beoordeel op het patroon: pieken per dag/uur, de');
        $this->line('verhouding verlopen tegenover bewaard, en verdachte e-maildomeinen.');

        return self::SUCCESS;
    }
}

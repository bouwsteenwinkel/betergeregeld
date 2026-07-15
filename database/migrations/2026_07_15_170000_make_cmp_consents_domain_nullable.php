<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * cmp_consents.domain_id was NOT NULL, terwijl CmpService::saveConsent() bewust null
 * doorgeeft als het domein niet in cmp_domains staat (regel 118-126). Gevolg: op elk
 * domein dat nog niet geregistreerd is, crasht POST /cmp/consent met een 500 en wordt
 * de toestemming HELEMAAL NIET vastgelegd. De bezoeker merkt daar niets van, want de
 * loader vangt die fetch-fout stil op en verbergt de banner op basis van localStorage.
 *
 * Dat is precies wat er nu op jouw-bedrijfswebsite.nl gebeurt: de banner werkt, maar de
 * consent-administratie blijft leeg, terwijl de AVG juist vereist dat je kunt aantonen
 * dat er toestemming is gegeven. Een vergeten rij in cmp_domains mag geen reden zijn om
 * het bewijs kwijt te raken.
 *
 * De koppeling aan een domein blijft gewenst (daarom staat het domein toevoegen ook in
 * het deploy-runbook en waarschuwt channels:preflight erover), maar het is voortaan
 * aanvullend in plaats van een harde voorwaarde.
 */
return new class extends Migration
{
    /**
     * Met raw SQL en niet met ->change(): de kolom hangt aan de foreign key
     * fk_cmp_consents_domain, en MariaDB weigert een kolomwijziging zolang die er ligt
     * (error 1832). Doctrine/Schema::change() lost dat niet op. De constraint zelf
     * blijft ongewijzigd (geen cascade), hij wordt alleen even losgemaakt.
     */
    /** Beide tabellen krijgen dezelfde rij aangeboden, dus beide moeten mee. */
    private const TABELLEN = [
        'cmp_consents'     => 'fk_cmp_consents_domain',
        'cmp_consent_logs' => 'fk_cmp_consent_logs_domain',
    ];

    public function up(): void
    {
        foreach (self::TABELLEN as $tabel => $fk) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }
            $this->zetNull($tabel, $fk, true);
        }
    }

    public function down(): void
    {
        // Terugdraaien kan alleen als er geen rijen zonder domein staan; anders kan de
        // kolom niet meer NOT NULL worden. Bewust geen rijen weggooien: dat is bewijs van
        // toestemming.
        foreach (self::TABELLEN as $tabel => $fk) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }
            $this->zetNull($tabel, $fk, false);
        }
    }

    /** Maakt de foreign key los, wijzigt de kolom en hangt de key onveranderd terug. */
    private function zetNull(string $tabel, string $fk, bool $nullable): void
    {
        $heeftFk = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tabel)
            ->where('CONSTRAINT_NAME', $fk)
            ->exists();

        if ($heeftFk) {
            DB::statement("ALTER TABLE `{$tabel}` DROP FOREIGN KEY `{$fk}`");
        }

        DB::statement("ALTER TABLE `{$tabel}` MODIFY `domain_id` int(11) " . ($nullable ? 'NULL' : 'NOT NULL'));

        if ($heeftFk) {
            DB::statement("ALTER TABLE `{$tabel}` ADD CONSTRAINT `{$fk}` FOREIGN KEY (`domain_id`) REFERENCES cmp_domains (`id`)");
        }
    }
};

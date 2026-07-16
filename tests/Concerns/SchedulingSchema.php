<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Zet de tabellen van de afspraken-keten klaar op de sqlite-testdatabase.
 *
 * Waarom niet gewoon RefreshDatabase: die draait `migrate:fresh`, en dat kan in dit
 * project niet. De oude migraties zitten samengevouwen in database/schema/mariadb-schema.sql
 * (alleen te laden met de mysql-CLI, die hier ontbreekt), en de migraties die daarna komen
 * bevatten rauwe MariaDB-DDL (`ALTER TABLE ... MODIFY`) waar sqlite meteen op stukloopt.
 * Vandaar deze selectie: precies de tabellen die de afspraken-keten aanraakt, gebouwd uit
 * de echte migratiebestanden, zodat een schemawijziging automatisch in de tests landt.
 *
 * Geen van de gebruikte migraties heeft een foreign key naar de weggevouwen tabellen, dus
 * de selectie is compleet: appointments en website_leads staan los.
 */
trait SchedulingSchema
{
    /** Migraties in volgorde; alleen Blueprint-DDL, dus draaibaar op sqlite. */
    private const MIGRATIONS = [
        '2026_06_28_120000_create_website_leads_table',
        '2026_06_28_130000_add_intake_to_website_leads',
        '2026_06_29_120000_add_facet_to_website_leads',
        '2026_07_14_140000_extend_website_leads_account',
        '2026_07_15_120000_add_attribution_to_website_leads',
        '2026_07_04_141000_create_availability_rules_table',
        '2026_07_04_141002_create_availability_exceptions_table',
        '2026_07_04_141005_create_appointments_table',
        '2026_07_15_120000_add_reminder_columns_to_appointments',
        '2026_07_16_090000_retime_appointment_reminders_and_track_calendar_sync',
    ];

    /**
     * Bewust NIET 'setUpSchedulingSchema' genoemd: Laravel roept een methode die
     * setUp<TraitNaam> heet zelf al aan vanuit TestCase::setUpTraits(), en dan draait
     * dit twee keer ("table already exists").
     */
    protected function bootSchedulingSchema(): void
    {
        // Harde grendel. De echte database is een lokale MariaDB-zandbak met werk van
        // anderen erin; deze tests maken tabellen aan en gooien rijen weg, dus als de
        // testconfiguratie ooit wegvalt (phpunit.xml aangepast, .env die wint) moet dat
        // hier stoppen en niet in de zandbak.
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'sqlite') {
            $this->fail("Tests draaien op '{$driver}' in plaats van sqlite. Afgebroken: deze suite mag alleen op een wegwerpdatabase draaien.");
        }

        foreach (self::MIGRATIONS as $name) {
            $migration = require database_path("migrations/{$name}.php");
            $migration->up();
        }
    }

    /** Elke test krijgt een verse :memory:-database, dus dit is puur een vangnet. */
    protected function dropSchedulingSchema(): void
    {
        foreach (['appointments', 'availability_exceptions', 'availability_rules', 'website_leads'] as $table) {
            Schema::dropIfExists($table);
        }
    }
}

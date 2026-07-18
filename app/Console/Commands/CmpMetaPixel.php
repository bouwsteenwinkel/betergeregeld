<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Zet de Meta (Facebook) pixel als marketing-CMP-script neer, zodat hij UITSLUITEND
 * laadt nadat de bezoeker akkoord gaat op marketing-cookies (AVG). Nooit los in een
 * layout — dit is de consent-gated route die de CMP zelf injecteert.
 *
 *   php artisan cmp:meta-pixel 1234567890123456
 *   php artisan cmp:meta-pixel 1234567890123456 --tenant=channels
 *   php artisan cmp:meta-pixel --disable            (zet 'm uit zonder te verwijderen)
 *
 * De pixel-basis doet init + PageView. Het Lead-conversie-event vuurt apart op de
 * afspraak-bevestigd-pagina (resources/views/channels/afspraak-bevestigd.blade.php),
 * en alleen als fbq bestaat — dus ook dat is automatisch consent-gated.
 */
class CmpMetaPixel extends Command
{
    protected $signature = 'cmp:meta-pixel {pixel_id? : Meta Pixel-ID (numeriek, 15-16 cijfers)}
        {--tenant=channels : CMP-tenant (funnel-sites = channels)}
        {--disable : Bestaande pixel uitzetten i.p.v. plaatsen/bijwerken}';

    protected $description = 'Meta (Facebook) pixel als consent-gated marketing-CMP-script plaatsen/bijwerken';

    public function handle(): int
    {
        $tenant = (string) $this->option('tenant');
        $name = 'Meta Pixel';

        if (! DB::table('cmp_categories')->where('tenant_key', $tenant)->where('key', 'marketing')->where('is_enabled', 1)->exists()) {
            $this->error("Tenant '{$tenant}' heeft geen ingeschakelde 'marketing'-categorie. Eerst in de CMP-admin aanzetten.");

            return self::FAILURE;
        }

        if ($this->option('disable')) {
            $n = DB::table('cmp_scripts')->where('tenant_key', $tenant)->where('name', $name)
                ->update(['is_enabled' => 0, 'updated_at' => now()]);
            $this->info($n ? "Meta Pixel uitgezet voor tenant '{$tenant}'." : "Geen Meta Pixel gevonden voor tenant '{$tenant}'.");

            return self::SUCCESS;
        }

        $pixelId = preg_replace('/\D/', '', (string) $this->argument('pixel_id'));
        if (strlen($pixelId) < 15 || strlen($pixelId) > 16) {
            $this->error('Geef een geldige Meta Pixel-ID mee (15-16 cijfers uit Meta Events Manager).');

            return self::FAILURE;
        }

        // Standaard Meta-pixel-basis: definieert fbq (queue-stub), laadt fbevents.js,
        // init + PageView. Wordt door de CMP pas geïnjecteerd na marketing-consent.
        $inline = <<<JS
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init','{$pixelId}');
        fbq('track','PageView');
        JS;

        DB::table('cmp_scripts')->updateOrInsert(
            ['tenant_key' => $tenant, 'name' => $name],
            [
                'category_key' => 'marketing',
                'script_type' => 'inline',
                'src_url' => null,
                'inline_code' => $inline,
                'attributes_json' => null,
                'is_enabled' => 1,
                'sort_order' => 10,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->info("Meta Pixel ({$pixelId}) geplaatst als marketing-script voor tenant '{$tenant}'.");
        $this->line('Laadt alleen na akkoord op marketing-cookies. Lead-event vuurt op /afspraak-bevestigd.');
        $this->line('Deploy: dev = prod-DB, dus dit staat meteen live. Even de CMP-scriptcache laten verlopen (max 60s).');

        return self::SUCCESS;
    }
}

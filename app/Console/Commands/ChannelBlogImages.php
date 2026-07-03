<?php

namespace App\Console\Commands;

use App\Models\Blog\BlogPost;
use App\Services\ChannelSiteResolver;
use App\Services\ChannelSites\ChannelImageGenerator;
use Illuminate\Console\Command;

/**
 * Genereert een niche-relevant coverbeeld per blogpost van een channel-site via
 * de OpenAI-image-pijplijn (gpt-image-1) en zet blog_posts.image. Idempotent:
 * posts met een beeld worden overgeslagen (tenzij --force). Kost geld per beeld,
 * dus met --limit te doseren.
 *
 *   php artisan channel:blog:images badkamerspecialist
 *   php artisan channel:blog:images badkamerspecialist --limit=5
 */
class ChannelBlogImages extends Command
{
    protected $signature = 'channel:blog:images {channel : channel-key} {--force} {--limit=0}';
    protected $description = 'Coverbeelden per blogpost genereren (OpenAI) voor een channel-site';

    /** Scene-variatie per post, zodat de beelden onderling verschillen. */
    private array $scenes = [
        'het afgeleverde eindresultaat', 'een sfeervol totaalbeeld', 'een strak detail van het werk',
        'een moderne, lichte setting', 'een vakman aan het werk', 'een net afgerond project',
        'een stijlvol overzicht', 'een close-up van de afwerking',
    ];

    public function handle(ChannelSiteResolver $resolver, ChannelImageGenerator $img): int
    {
        $key  = (string) $this->argument('channel');
        $site = $resolver->byKey($key);
        if (! $site) {
            $this->error("Onbekend channel: {$key}");
            return self::FAILURE;
        }
        if ($img->isFake()) {
            $this->warn('Fake-mode (geen OpenAI-key): er komen SVG-placeholders i.p.v. echte beelden.');
        }

        $tokens = (array) $site->get('places', []);
        $niche  = (string) ($tokens['niche'] ?? 'vak');
        $trade  = (string) ($tokens['trade'] ?? 'bedrijf');

        $posts = BlogPost::where('channel', $key)->orderBy('id')->get();
        $limit = (int) $this->option('limit');
        $done = 0;
        $skipped = 0;

        foreach ($posts as $post) {
            if ($post->image && ! $this->option('force')) {
                $skipped++;
                continue;
            }
            if ($limit > 0 && $done >= $limit) {
                break;
            }

            $scene = $this->scenes[abs(crc32($post->slug)) % count($this->scenes)];
            $prompt = "Professionele, fotorealistische redactionele foto voor de blog van een {$trade}. "
                . "Onderwerp: {$scene} rond {$niche}. Helder, modern, natuurlijk daglicht, strak en stijlvol, "
                . 'hoge kwaliteit. Geen tekst, geen logo, geen watermerk, geen herkenbare gezichten.';

            $res = $img->generateRaw($key, 'blog-' . $post->slug, $prompt, '1536x1024', (bool) $this->option('force'));
            if (! empty($res['file'])) {
                // Relatief pad opslaan (geen host), zodat het op elk domein/omgeving
                // werkt (dev = productie-DB: geen localhost bakken).
                $rel = parse_url($res['file'], PHP_URL_PATH) ?: $res['file'];
                $post->update(['image' => $rel]);
                $this->line("  ✓ {$post->slug}  [{$res['status']}]");
                $done++;
            } else {
                $this->warn("  ✗ {$post->slug}  [{$res['status']}]");
            }
        }

        $this->info("Klaar. {$done} beelden gegenereerd, {$skipped} overgeslagen (had al een beeld).");
        return self::SUCCESS;
    }
}

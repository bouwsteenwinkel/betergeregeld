<?php

namespace App\Console\Commands;

use App\Models\Blog\BlogPost;
use App\Services\ChannelSiteResolver;
use Illuminate\Console\Command;

/**
 * Publiceert de handgeschreven, niche-specifieke blog-artikelen uit
 * config/blog_{channel}.php als blog_posts voor die channel-site. Idempotent
 * (bestaande (channel, slug) worden bijgewerkt). Publicatiedata worden gespreid
 * over een langere periode, zodat het een natuurlijke blog-historie oogt.
 *
 * Voor niches zonder handgeschreven set: gebruik `channel:blog:generate` (AI).
 *
 *   php artisan channel:blog:seed badkamerspecialist
 */
class ChannelBlogSeed extends Command
{
    protected $signature = 'channel:blog:seed {channel : channel-key} {--force}';
    protected $description = 'Handgeschreven blog-artikelen (config/blog_{channel}.php) publiceren voor een channel-site';

    public function handle(ChannelSiteResolver $resolver): int
    {
        $key  = (string) $this->argument('channel');
        $site = $resolver->byKey($key);
        if (! $site) {
            $this->error("Onbekend channel: {$key}");
            return self::FAILURE;
        }

        $articles = (array) config("blog_{$key}", []);
        if (! $articles) {
            $this->error("Geen artikelen in config/blog_{$key}.php. Gebruik `channel:blog:generate {$key}` voor AI-content.");
            return self::FAILURE;
        }

        $catId = \App\Models\Blog\BlogCategory::firstOrCreate(
            ['slug' => 'online-groeien'],
            ['name' => 'Online groeien', 'sort_order' => 100]
        )->id;

        $created = 0;
        $skipped = 0;
        $i = 0;
        foreach ($articles as $slug => $a) {
            $slug = (string) $slug;
            $exists = BlogPost::where('channel', $key)->where('slug', $slug)->first();
            if ($exists && ! $this->option('force')) {
                $skipped++;
                $i++;
                continue;
            }

            $body = (string) ($a['body'] ?? '');
            // Gespreide, gevarieerde publicatiedatum (verschillende datums per post).
            $daysAgo = 4 + $i * 12 + (abs(crc32($slug)) % 9);

            $data = [
                'channel'          => $key,
                'locale'           => $site->locale(),
                'category_id'      => $catId,
                'title'            => (string) ($a['title'] ?? $slug),
                'meta_title'       => (string) ($a['title'] ?? $slug),
                'excerpt'          => (string) ($a['excerpt'] ?? ''),
                'body'             => $body,
                'reading_time_min' => max(1, (int) round(str_word_count(strip_tags($body)) / 200)),
                'featured'         => $i === 0,
                'ai_generated'     => false,
                'published_at'     => now()->subDays($daysAgo),
            ];

            if ($exists) {
                $exists->update($data);
            } else {
                BlogPost::create($data + ['slug' => $slug]);
            }
            $created++;
            $i++;
        }

        $this->info("Klaar. {$created} artikelen gepubliceerd, {$skipped} overgeslagen (bestond al). Spreiding: laatste ~" . (4 + ($i - 1) * 12) . ' dagen.');
        return self::SUCCESS;
    }
}

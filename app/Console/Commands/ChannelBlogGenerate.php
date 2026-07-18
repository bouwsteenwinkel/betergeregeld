<?php

namespace App\Console\Commands;

use App\Models\Blog\BlogPost;
use App\Services\ChannelSiteResolver;
use App\Services\ChannelSites\ChannelContentGenerator;
use Illuminate\Console\Command;

/**
 * AI-batch: genereert per niche één UNIEK blog-concept per topic uit de matrix
 * (config/channel_blog.php) via Claude. Concepten komen als draft (published_at
 * = NULL, ai_generated = true) → controleren en publiceren in de admin.
 *
 * Draai op productie (waar ANTHROPIC_API_KEY staat). Zonder key = fake-mode.
 *
 *   php artisan channel:blog:generate loodgieter --write
 *   php artisan channel:blog:generate --all --write
 */
class ChannelBlogGenerate extends Command
{
    protected $signature = 'channel:blog:generate {channel? : channel-key} {--all} {--write} {--limit=0}';
    protected $description = 'AI-blogconcepten per niche genereren uit de topic-matrix';

    public function handle(ChannelContentGenerator $gen, ChannelSiteResolver $resolver): int
    {
        $keys = $this->option('all')
            ? \App\Models\Channel\Site::pluck('key')->all()
            : array_filter([$this->argument('channel')]);
        if (! $keys) {
            $this->error('Geef een channel-key op, of gebruik --all.');
            return self::FAILURE;
        }

        $topics = (array) config('channel_blog.topics', []);
        if (! $topics) {
            $this->error('Geen topics in config/channel_blog.php.');
            return self::FAILURE;
        }
        if ($gen->isFake()) {
            $this->warn('Fake-mode (geen ANTHROPIC_API_KEY): concepten zijn placeholders.');
        }
        if (! $this->option('write')) {
            $this->warn('Dry-run (geen --write): er wordt niets weggeschreven.');
        }

        $catId = \App\Models\Blog\BlogCategory::firstOrCreate(
            ['slug' => 'online-groeien'],
            ['name' => 'Online groeien', 'sort_order' => 100]
        )->id;

        $limit = (int) $this->option('limit');
        $created = 0;
        $skipped = 0;

        foreach ($keys as $key) {
            $site = $resolver->byKey($key);
            if (! $site) {
                $this->error("Onbekend channel: {$key}");
                continue;
            }
            $tokens = (array) $site->get('places', []);
            $this->line("<info>{$key}</info> ({$site->branche()})");

            $n = 0;
            foreach ($topics as $topic) {
                if ($limit > 0 && $n >= $limit) {
                    break;
                }
                $n++;

                $slug = (string) ($topic['slug'] ?? '');
                if ($slug !== '' && BlogPost::where('channel', $key)->where('slug', $slug)->exists()) {
                    $skipped++;
                    continue;
                }

                $d = $gen->blogDraftForTopic($site, $topic, $tokens);
                $this->line("  · {$d['title']}" . ($d['fake'] ? '  [fake]' : ''));

                if (! $this->option('write')) {
                    continue;
                }

                $body = $d['body'];
                BlogPost::create([
                    'channel'          => $key,
                    'locale'           => $site->locale(),
                    'category_id'      => $catId,
                    'slug'             => $d['slug'],
                    'title'            => $d['title'],
                    'meta_title'       => $d['title'],
                    'excerpt'          => $d['excerpt'],
                    'body'             => $body,
                    'reading_time_min' => max(1, (int) ceil(str_word_count(strip_tags($body)) / 200)),
                    'published_at'     => null,               // concept — publiceren in admin
                    'ai_generated'     => true,
                    'ai_model'         => $d['model'],
                    'ai_prompt_meta'   => ['type' => 'channel-blog-topic', 'topic' => $slug, 'product' => $topic['product'] ?? null],
                ]);
                $created++;
            }
        }

        $this->info("Klaar. {$created} concepten geschreven, {$skipped} overgeslagen (bestond al). Publiceer ze in de admin.");
        return self::SUCCESS;
    }
}

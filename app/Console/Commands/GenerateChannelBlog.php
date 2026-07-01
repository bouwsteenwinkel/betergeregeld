<?php

namespace App\Console\Commands;

use App\Models\Blog\BlogPost;
use App\Services\ChannelSites\ChannelContentGenerator;
use App\Services\ChannelSiteResolver;
use Illuminate\Console\Command;

/**
 * Genereert per kanaal een branche-specifiek blog-CONCEPT via Claude.
 *
 *   php artisan channels:blog installateur          # dry-run: toont wat het zou maken
 *   php artisan channels:blog --all --write         # schrijft concepten naar de DB
 *
 * Concepten komen binnen als published_at = NULL + ai_generated = true, zodat ze
 * in de admin gecontroleerd en handmatig gepubliceerd worden (geen auto-publish).
 * Zonder ANTHROPIC_API_KEY draait de generatie in fake-mode.
 */
class GenerateChannelBlog extends Command
{
    protected $signature = 'channels:blog
        {key? : kanaal-key}
        {--all : alle kanalen}
        {--write : schrijf het concept naar de database (anders dry-run)}';

    protected $description = 'Genereer een branche-specifiek blog-concept per channel-site';

    public function handle(ChannelContentGenerator $gen, ChannelSiteResolver $resolver): int
    {
        $key = $this->argument('key');
        if (! $key && ! $this->option('all')) {
            $this->error('Geef een kanaal-key op, of gebruik --all.');
            return self::FAILURE;
        }

        if ($gen->isFake()) {
            $this->warn('Fake-mode (geen ANTHROPIC_API_KEY): concept-tekst is een sjabloon.');
        }
        if (! $this->option('write')) {
            $this->warn('Dry-run (geen --write): er wordt niets naar de database geschreven.');
        }

        foreach ($this->option('all') ? array_keys($resolver->all()) : [$key] as $k) {
            $site = $resolver->byKey($k);
            if (! $site) {
                $this->error("Onbekend kanaal: {$k}");
                continue;
            }

            $d = $gen->blogDraft($site);
            $this->line("<info>{$k}</info> ({$site->branche()}) — \"{$d['title']}\"");
            $this->line('  ' . $d['excerpt']);

            if (! $this->option('write')) {
                continue;
            }

            try {
                BlogPost::create([
                    'channel'        => $site->key,
                    'locale'         => $site->locale(),
                    'slug'           => $d['slug'],
                    'title'          => $d['title'],
                    'excerpt'        => $d['excerpt'],
                    'body'           => $d['body'],
                    'published_at'   => null,            // concept, handmatig publiceren in admin
                    'ai_generated'   => true,
                    'ai_model'       => $d['model'],
                    'ai_prompt_meta' => ['type' => 'channel-blog', 'branche' => $site->branche()],
                ]);
                $this->line('  <info>✓ concept aangemaakt</info> (controleer + publiceer in de admin)');
            } catch (\Throwable $e) {
                $this->error('  DB-schrijf mislukt: ' . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}

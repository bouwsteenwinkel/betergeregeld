<?php

namespace Tests\Feature\Blog;

use App\Support\BlogCta;
use Tests\TestCase;

/** Doorverwijsblok onder blogposts (config/blog_cta.php): eerste passende regel wint, anders geen blok. */
class BlogCtaTest extends TestCase
{
    public function test_onderwerp_kiest_de_juiste_tool_of_dienst(): void
    {
        $this->assertSame('/nl/tools/iban-check', BlogCta::voor('iban-check-met-naam-uitleg', 'boekhouding')['url']);
        $this->assertSame('/nl/tools/vat-check', BlogCta::voor('vies-btw-nummer-controleren-uitleg', 'boekhouding')['url']);
        $this->assertSame('/nl/backup-check', BlogCta::voor('backup-strategie-mkb', 'security')['url']);
        $this->assertSame('/nl/tools/pdf-redact', BlogCta::voor('zwarte-balken-pdf-waarom-niet', 'pdf-redaction')['url']);
        $this->assertSame('/nl/diensten/toegang-check', BlogCta::voor('waterdichte-offboarding-stappen', 'offboarding')['url']);
        $this->assertSame('/nl/ai-telefoniste', BlogCta::voor('telefoonnummer-op-website-hoe-voorkom-je-spookoproepen', 'online-groeien')['url']);
    }

    public function test_achteraan_bellen_is_boekhouding_geen_telefonie(): void
    {
        $this->assertSame('/nl/processen-automatiseren', BlogCta::voor('van-facturen-achteraan-bellen-naar-automatisch', 'boekhouding')['url']);
    }

    public function test_terugval_per_categorie_en_geen_blok_bij_eigen_nieuws(): void
    {
        $this->assertSame('/nl/tools', BlogCta::voor('ceo-fraude-herkennen', 'security')['url']);
        $this->assertNull(BlogCta::voor('nieuwe-website-live', 'betergeregeld'));
    }

    public function test_teksten_zonder_em_dashes(): void
    {
        foreach (config('blog_cta') as $regel) {
            foreach (['title', 'text', 'button'] as $veld) {
                $this->assertDoesNotMatchRegularExpression('/[—–]/u', $regel[$veld], "$veld bevat een em-dash");
            }
        }
    }

    public function test_402_pagina_legt_uit_in_plaats_van_kale_fout(): void
    {
        // Gratis plan zonder PDF-tool; de testdatabase heeft geen plans-tabel.
        $bag = new \App\Services\Features\FeatureBag(new \App\Models\Plan(), []);
        $resolver = \Mockery::mock(\App\Services\Features\FeatureResolver::class);
        $resolver->shouldReceive('forRequest')->andReturn($bag);
        $this->app->instance(\App\Services\Features\FeatureResolver::class, $resolver);
        // De gebruiksteller per tool (middleware) leest deze tabel; de wegwerpdatabase heeft hem niet.
        if (! \Illuminate\Support\Facades\Schema::hasTable('tool_usage_daily')) {
            \Illuminate\Support\Facades\Schema::create('tool_usage_daily', function ($t) {
                $t->id();
                $t->string('identity_key')->nullable();
                $t->string('tool');
                $t->date('date');
                $t->unsignedInteger('count')->default(0);
                $t->timestamps();
            });
        }

        $html = $this->get('/nl/tools/pdf-redact')->assertStatus(402)->getContent();
        $this->assertStringContainsString('Deze tool zit in een abonnement', $html);
        $this->assertStringContainsString('/nl/prijzen', $html);
        $this->assertStringNotContainsString('Payment Required', $html);
    }
}

<?php

namespace Tests\Feature\Blog;

use Tests\TestCase;

/**
 * /index.html is een overblijfsel van de oude site.
 *
 * Search Console meldt hem sinds mei als "Niet gevonden", in drie varianten: http, https en www.
 * Die laatste twee lossen zichzelf op zodra de apex het goed doet — www en http 301'en al naar
 * https://betergeregeld.com (gemeten 10-08-2026) — dus één regel dekt alle drie.
 */
class LegacyIndexHtmlTest extends TestCase
{
    public function test_index_html_verwijst_blijvend_naar_de_nederlandse_home(): void
    {
        $this->get('/index.html')
            ->assertStatus(301)
            ->assertRedirect('/nl');
    }
}

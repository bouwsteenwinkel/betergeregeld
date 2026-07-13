@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp

@if ($block->c('google'))
    @php
        $gLogo = '<svg viewBox="0 0 48 48" width="26" height="26" aria-hidden="true"><path fill="#4285F4" d="M45.1 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h11.8c-.5 2.8-2 5.1-4.4 6.6v5.5h7.1c4.2-3.8 6.6-9.5 6.6-16.1z"/><path fill="#34A853" d="M24 46c5.9 0 10.9-2 14.6-5.3l-7.1-5.5c-2 1.3-4.5 2.1-7.5 2.1-5.7 0-10.6-3.9-12.3-9.1H4.3v5.7C8 41.1 15.4 46 24 46z"/><path fill="#FBBC05" d="M11.7 28.2c-.4-1.3-.7-2.7-.7-4.2s.3-2.9.7-4.2v-5.7H4.3C2.9 17.1 2 20.4 2 24s.9 6.9 2.3 9.9l7.4-5.7z"/><path fill="#EA4335" d="M24 10.8c3.2 0 6.1 1.1 8.4 3.3l6.3-6.3C34.9 4.2 29.9 2 24 2 15.4 2 8 6.9 4.3 14.1l7.4 5.7c1.7-5.2 6.6-9 12.3-9z"/></svg>';
        $palette = ['#4285F4', '#EA4335', '#0F9D58', '#AB47BC', '#00ACC1', '#F4511E'];
        $rating = $block->c('rating', '4,9');
        $count  = $block->c('review_count');
    @endphp
    <section data-block="reviews" class="greviews">
        <div class="wrap">
            <div class="grev-head">
                <span class="grev-g">{!! $gLogo !!}</span>
                <div>
                    <div class="grev-rating"><strong>{{ $rating }}</strong><span class="grev-stars">★★★★★</span></div>
                    <div class="grev-sub">{{ $block->c('heading', 'Google-beoordelingen') }}{{ $count ? ' · ' . $count . ' reviews' : '' }}</div>
                </div>
            </div>
            <div class="grid cols-3 grev-grid">
                @foreach ($items as $r)
                    @php
                        $name = trim((string) ($r['author'] ?? '')) ?: 'Klant';
                        $init = mb_strtoupper(mb_substr($name, 0, 1));
                        $col  = $palette[$loop->index % count($palette)];
                    @endphp
                    <div class="grev-card">
                        <div class="grev-top">
                            <span class="grev-av" style="background:{{ $col }}">{{ $init }}</span>
                            <div class="grev-meta">
                                <span class="grev-name">{{ $name }}</span>
                                <span class="grev-time">Lokale gids · Google</span>
                            </div>
                            <span class="grev-gbadge">{!! $gLogo !!}</span>
                        </div>
                        <div class="grev-stars-row">{{ str_repeat('★', (int) ($r['stars'] ?? 5)) }}</div>
                        <p class="grev-text">{{ $r['text'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <style>
        .greviews .grev-head{display:flex;align-items:center;justify-content:center;gap:.9rem;margin-bottom:2rem}
        .greviews .grev-g svg{display:block}
        .greviews .grev-rating{display:flex;align-items:center;gap:.5rem;color:var(--c-ink)}
        .greviews .grev-rating strong{font-size:1.5rem;line-height:1}
        .greviews .grev-stars{color:#fbbc04;letter-spacing:1px;font-size:1.05rem}
        .greviews .grev-sub{color:var(--c-muted);font-size:.9rem;margin-top:.2rem}
        .greviews .grev-grid{align-items:stretch}
        .greviews .grev-card{background:#fff;border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent);border-radius:8px;
            padding:1.3rem 1.35rem;box-shadow:0 12px 32px -28px rgba(0,0,0,.55);display:flex;flex-direction:column;gap:.7rem}
        .greviews .grev-top{display:flex;align-items:center;gap:.7rem}
        .greviews .grev-av{width:42px;height:42px;border-radius:50%;color:#fff;font-weight:700;display:grid;place-items:center;font-size:1.05rem;flex:0 0 auto}
        .greviews .grev-meta{display:flex;flex-direction:column;line-height:1.2;flex:1;min-width:0}
        .greviews .grev-name{font-weight:700;color:var(--c-ink)}
        .greviews .grev-time{color:var(--c-muted);font-size:.78rem}
        .greviews .grev-gbadge svg{display:block;width:20px;height:20px;opacity:.95}
        .greviews .grev-stars-row{color:#fbbc04;letter-spacing:2px;font-size:1.05rem}
        .greviews .grev-text{color:var(--c-ink);font-size:.95rem;line-height:1.55;margin:0}
    </style>
@else
    <section data-block="reviews">
        <div class="wrap">
            @if ($block->c('heading'))<h2 style="text-align:center;margin-bottom:1.6rem">{{ $block->c('heading') }}</h2>@endif
            <div class="grid cols-3">
                @foreach ($items as $r)
                    <div class="card">
                        <div style="color:var(--c-accent);letter-spacing:2px;margin-bottom:.6rem">{{ str_repeat('★', (int) ($r['stars'] ?? 5)) }}</div>
                        <p style="font-weight:600">“{{ $r['text'] ?? '' }}”</p>
                        @if (!empty($r['author']))<p class="muted" style="margin-top:.6rem;font-size:.85rem;text-transform:uppercase;letter-spacing:.08em">{{ $r['author'] }}</p>@endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

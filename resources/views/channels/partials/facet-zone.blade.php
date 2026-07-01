@php
    /** @var \App\Support\ChannelSite $site */
    // Facet-afhankelijke blokken. Wordt zowel vanuit home.blade geïnclude als
    // los gerenderd door ChannelSiteController@homeFragment (AJAX fase-switch).
    $h = $h ?? ($home ?? $site->get('home', []));
    $heroImg = $site->image('hero');
    $hasWidget = !empty($site->get('hero_widget'));
@endphp
<section class="hero {{ $heroImg ? 'hero--shot' : '' }} {{ $hasWidget ? 'has-widget' : '' }}" data-section="hero">
    @if ($heroImg)
        <div class="hero-bg" aria-hidden="true">
            <img src="{{ $heroImg }}" srcset="{{ $site->imageSrcset('hero') }}" sizes="100vw" alt="" loading="eager" fetchpriority="high">
        </div>
    @endif
    <div class="wrap">
        <div class="hero-text">
            <h1>{{ $h['hero_title'] ?? $site->name() }}</h1>
            <p class="lead">{{ $h['hero_sub'] ?? '' }}</p>
            <a href="#contact" class="btn">{{ $h['hero_cta'] ?? 'Gratis voorbeeld aanvragen' }}</a>
            @if (!empty($h['hero_note']))<p class="muted" style="margin-top:.8rem;font-size:.9rem">{{ $h['hero_note'] }}</p>@endif

            @if (!empty($h['usps']))
                <ul class="hero-usps">
                    @foreach ($h['usps'] as $usp)<li>{{ $usp }}</li>@endforeach
                </ul>
            @endif
        </div>

        @if ($hasWidget)
            <div class="hero-aside">
                @include('channels.partials.hero-mockup')
            </div>
        @endif
    </div>
</section>

@if (!empty($h['features']))
    <section data-section="features">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Wat je krijgt</span>
            <h2>Alles wat je zaak nodig heeft</h2>
            <p class="muted section-lead">In één strakke website, wij regelen de techniek.</p>
            <div class="grid cols-4 feature-grid">
                @foreach ($h['features'] as $f)
                    <div class="feature-card">
                        <span class="feature-ico">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</span>
                        <h3>{{ $f['title'] }}</h3>
                        <span class="feature-rule" aria-hidden="true"></span>
                        <p class="muted">{{ $f['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if (!empty($h['testimonials']))
    @php $rs = $h['reviews_summary'] ?? null; @endphp
    <section id="reviews" data-section="reviews">
        <div class="wrap">
            <div class="reviews-head">
                <div>
                    <span class="kicker"><span class="kicker-line"></span> Ambassadeurs &amp; reviews</span>
                    <h2>{{ $h['reviews_title'] ?? 'Ondernemers die je voorgingen' }}</h2>
                </div>
                @if ($rs)
                    <div class="reviews-summary">
                        <div class="rev-stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                        <div class="rev-score">{{ $rs['score'] }}<span>/5</span></div>
                        <div class="rev-count">Gebaseerd op <strong>{{ $rs['count'] }}</strong> {{ $rs['source'] ?? 'reviews' }}</div>
                    </div>
                @endif
            </div>

            <div class="reviews-rail">
                @foreach ($h['testimonials'] as $t)
                    <article class="rev-card">
                        <div class="rev-stars" aria-label="5 van 5 sterren">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                        @if (!empty($t['title']))<h3 class="rev-title">{{ $t['title'] }}</h3>@endif
                        <p class="rev-body">{{ $t['quote'] }}</p>
                        <div class="rev-foot">
                            <div class="rev-who">
                                <strong>{{ $t['name'] }}</strong>
                                <span>{{ $t['place'] ?? '' }}@if (!empty($t['when'])) &middot; {{ $t['when'] }}@endif</span>
                            </div>
                            <span class="rev-verified">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                Geverifieerd
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($rs)
                <p class="reviews-summary-line">{{ $rs['count'] }} {{ $rs['noun'] ?? 'ondernemers' }} gaven {{ $site->name() }} gemiddeld een {{ $rs['score'] }}. <a href="#contact">Vraag je eigen voorbeeld aan &rarr;</a></p>
            @endif
        </div>
    </section>
@elseif (!empty($h['proof']))
    <section>
        <div class="wrap">
            <figure class="pullquote">
                <span class="pullquote-mark" aria-hidden="true">&ldquo;</span>
                <blockquote>{{ $h['proof'] }}</blockquote>
            </figure>
        </div>
    </section>
@endif

@if (!empty($h['steps']))
    <section data-section="steps" style="background:var(--c-surface)">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Zo simpel is het</span>
            <h2>Zo werkt het</h2>
            <div class="steps">
                @foreach ($h['steps'] as $i => $s)
                    <div class="step">
                        <span class="step-num">{{ $i + 1 }}</span>
                        <h3>{{ $s['title'] }}</h3>
                        <p class="muted">{{ $s['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@php $detailImg = $site->image('detail'); @endphp
@if ($detailImg)
    {{-- Compacte sfeer-band (visueel adempauze, geen funnel-actie). --}}
    <section class="showcase showcase--compact" data-section="showcase">
        <div class="wrap showcase-inner">
            <div class="showcase-media">
                <img src="{{ $detailImg }}" srcset="{{ $site->imageSrcset('detail') }}" sizes="(min-width:760px) 40vw, 100vw" alt="Vakwerk in een barbershop, close-up" loading="lazy">
            </div>
            <div class="showcase-text">
                <span class="kicker"><span class="kicker-line"></span> In beeld</span>
                <h2>Je vak, scherp in beeld</h2>
                <p class="muted">Een galerij die laat zien wat je kunt. Zo ziet een nieuwe klant meteen waarom hij bij jou in de stoel moet.</p>
            </div>
        </div>
    </section>
@endif

{{-- Herhaalde CTA op het vertrouwens-hoogtepunt (na de social proof). --}}
<section class="cta-band" data-section="cta">
    <div class="wrap cta-band-inner">
        <div>
            <h2>Klaar om je agenda te vullen?</h2>
            <p>Vraag vrijblijvend een gratis voorbeeld van jouw zaak aan, binnen 2 werkdagen klaar.</p>
        </div>
        <a href="#contact" class="btn">Gratis voorbeeld aanvragen</a>
    </div>
</section>

@php $faq = $site->faq(); @endphp
@if (!empty($faq))
    <section id="faq" data-section="faq" style="background:var(--c-surface)">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Vragen</span>
            <h2>Veelgestelde vragen</h2>
            <p class="muted" style="margin-bottom:1.6rem">Geen account nodig, geen kleine lettertjes.</p>
            <div class="faq">
                @foreach ($faq as $i => $qa)
                    <details @if($i === 0) open @endif>
                        <summary>{{ $qa[0] ?? '' }}</summary>
                        <p>{{ $qa[1] ?? '' }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif

@include('channels.partials.groeipad')

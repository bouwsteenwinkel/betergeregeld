@php
    /** @var \App\Models\Channel\Block $block */
    $heroImg = $site->image('hero');
    $heroSet = $site->imageSrcset('hero');
@endphp
<section class="hero" data-block="hero">
    <div class="wrap">
        <div @if ($heroImg) class="grid cols-2" style="align-items:center;gap:2.5rem" @endif>
            <div>
                @if ($block->c('eyebrow'))<span class="eyebrow">{{ $block->c('eyebrow') }}</span>@endif
                <h1>{{ $block->c('title', $site->name()) }}</h1>
                @if ($block->c('sub'))<p class="lead">{{ $block->c('sub') }}</p>@endif
                <a href="#gratis-voorbeeld" class="btn">{{ $block->c('cta_label', 'Gratis voorbeeld aanvragen') }}</a>
                @if ($block->c('cta2_label'))<a href="{{ $site->navHref($block->c('cta2_href', '#gratis-voorbeeld')) }}" class="btn btn-ghost" style="margin-left:.6rem">{{ $block->c('cta2_label') }}</a>@endif
                @if ($block->c('note'))<p class="muted" style="margin-top:.8rem;font-size:.9rem">{{ $block->c('note') }}</p>@endif

                @if ($block->c('usps'))
                    <ul class="hero-usps">
                        @foreach ((array) $block->c('usps') as $usp)<li>{{ is_array($usp) ? ($usp['text'] ?? '') : $usp }}</li>@endforeach
                    </ul>
                @endif
            </div>

            @if ($heroImg)
                <div>
                    <img src="{{ $heroImg }}"
                         @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                         alt="{{ $site->name() }}" loading="eager" decoding="async"
                         style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                </div>
            @endif
        </div>
    </div>
</section>

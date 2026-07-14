@php /** @var \App\Models\Channel\Block $block */ @endphp
<section data-block="cta" style="background:var(--c-primary);color:#fff">
    <div class="wrap" style="text-align:center">
        <h2 style="color:#fff">{{ $block->c('title', 'Klaar voor een betere website?') }}</h2>
        @if ($block->c('sub'))<p style="opacity:.9;max-width:54ch;margin:.6rem auto 1.4rem">{{ $block->c('sub') }}</p>@endif
        <a href="{{ $block->c('cta_href', '#gratis-voorbeeld') }}" class="btn" style="background:#fff;color:var(--c-primary)">{{ $block->c('cta_label', 'Gratis voorbeeld aanvragen') }}</a>
    </div>
</section>

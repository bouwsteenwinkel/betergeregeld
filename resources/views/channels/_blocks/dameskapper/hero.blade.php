@include('channels._blocks.dameskapper._style')
@php /** @var \App\Models\Channel\Block $block */ $cta2 = $block->c('cta2_label'); @endphp
<header class="lum lum-hero"><div class="wrap">
    @if ($block->c('eyebrow'))<span class="eyebrow">{{ $block->c('eyebrow') }}</span>@endif
    <h1>{{ $block->c('title', $site->name()) }}</h1>
    @if ($block->c('sub'))<p class="lead">{{ $block->c('sub') }}</p>@endif
    <div class="cta">
        <a href="#gratis-voorbeeld" class="btn">{{ $block->c('cta_label', 'Online afspraak maken') }}</a>
        @if ($cta2)<a href="{{ $site->navHref($block->c('cta2_href', '#behandelingen')) }}" class="btn btn-ghost">{{ $cta2 }}</a>@endif
    </div>
</div></header>

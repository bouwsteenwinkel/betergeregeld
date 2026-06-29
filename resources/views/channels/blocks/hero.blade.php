@php /** @var \App\Models\Channel\Block $block */ @endphp
<section class="hero" data-block="hero">
    <div class="wrap">
        @if ($block->c('eyebrow'))<span class="eyebrow">{{ $block->c('eyebrow') }}</span>@endif
        <h1>{{ $block->c('title', $site->name()) }}</h1>
        @if ($block->c('sub'))<p class="lead">{{ $block->c('sub') }}</p>@endif
        <a href="#gratis-voorbeeld" class="btn">{{ $block->c('cta_label', 'Gratis voorbeeld aanvragen') }}</a>
        @if ($block->c('note'))<p class="muted" style="margin-top:.8rem;font-size:.9rem">{{ $block->c('note') }}</p>@endif

        @if ($block->c('usps'))
            <ul class="hero-usps">
                @foreach ((array) $block->c('usps') as $usp)<li>{{ is_array($usp) ? ($usp['text'] ?? '') : $usp }}</li>@endforeach
            </ul>
        @endif
    </div>
</section>

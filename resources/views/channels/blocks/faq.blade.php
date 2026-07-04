@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
<section data-block="faq">
    <div class="wrap">
        <div style="text-align:center;max-width:640px;margin:0 auto 2rem">
            @if ($block->c('eyebrow'))<span class="kicker" style="justify-content:center"><span class="kicker-line"></span> {{ $block->c('eyebrow') }}</span>@endif
            @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
            @if ($block->c('sub'))<p class="muted" style="margin-top:.5rem">{{ $block->c('sub') }}</p>@endif
        </div>
        @include('channels.partials.faq-accordion', ['items' => $items])
    </div>
</section>

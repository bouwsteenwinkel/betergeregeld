@include('channels._blocks.herenkapper._style')
@php /** @var \App\Models\Channel\Block $block */ @endphp
<section data-block="gallery" id="galerij" class="brink" style="background:var(--c-surface)"><div class="wrap">
    <div class="brink-sec-head">
        @if ($block->c('eyebrow'))<span class="eyebrow">{{ $block->c('eyebrow') }}</span>@endif
        @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
        @if ($block->c('sub'))<p>{{ $block->c('sub') }}</p>@endif
    </div>
    <div class="brink-gallery">
        @foreach ((array) $block->c('tiles', []) as $t)
            @php $img = is_array($t) ? ($t['image'] ?? null) : null; $label = is_array($t) ? ($t['label'] ?? '') : (string) $t; @endphp
            <div class="tile" @if ($img)style="background:center/cover url('{{ $img }}')"@endif>@if ($label)<span>{{ $label }}</span>@endif</div>
        @endforeach
    </div>
</div></section>

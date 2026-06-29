@include('channels._blocks.herenkapper._style')
@php /** @var \App\Models\Channel\Block $block */ @endphp
<section data-block="pricelist" id="diensten" class="brink"><div class="wrap">
    <div class="brink-sec-head">
        @if ($block->c('eyebrow'))<span class="eyebrow">{{ $block->c('eyebrow') }}</span>@endif
        @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
        @if ($block->c('sub'))<p>{{ $block->c('sub') }}</p>@endif
    </div>
    <div class="brink-price">
        @foreach ((array) $block->c('items', []) as $it)
            <div class="row"><span class="nm">{{ $it['name'] ?? '' }}</span><span class="ds">{{ $it['desc'] ?? '' }}</span><span class="pr">{{ $it['price'] ?? '' }}</span></div>
        @endforeach
    </div>
</div></section>

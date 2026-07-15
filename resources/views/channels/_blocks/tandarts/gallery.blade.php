@php
    /** @var \App\Models\Channel\Block $block */
    /** @var \App\Support\ChannelSite $site */
    $tiles = array_filter([
        ['src' => $site->image('gallery1'), 'label' => 'Behandelkamer'],
        ['src' => $site->image('gallery2'), 'label' => 'Op je gemak'],
        ['src' => $site->image('gallery3'), 'label' => 'Onze balie'],
        ['src' => $site->image('detail'),   'label' => 'Vakwerk'],
    ], fn ($t) => ! empty($t['src']));
@endphp
@if (count($tiles))
<section data-block="gallery" style="background:var(--c-surface)">
    <div class="wrap">
        <h2 style="text-align:center">{{ $block->c('heading', 'Een kijkje in de praktijk') }}</h2>
        @if ($block->c('sub'))<p class="muted" style="text-align:center;margin-bottom:1.6rem">{{ $block->c('sub') }}</p>@endif
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.9rem;margin-top:1.4rem">
            @foreach ($tiles as $t)
                <div style="position:relative;aspect-ratio:3/4;border-radius:var(--radius);overflow:hidden;display:flex;align-items:flex-end;color:#fff;background:center/cover url('{{ $t['src'] }}')">
                    <span style="padding:.7rem .8rem;font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;background:linear-gradient(0deg,rgba(0,0,0,.55),transparent);width:100%">{{ $t['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

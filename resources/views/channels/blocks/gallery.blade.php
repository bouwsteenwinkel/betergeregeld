@php /** @var \App\Models\Channel\Block $block */ $tiles = (array) $block->c('tiles', []); @endphp
<section data-block="gallery" style="background:var(--c-surface)">
    <div class="wrap">
        @if ($block->c('heading'))<h2 style="text-align:center">{{ $block->c('heading') }}</h2>@endif
        @if ($block->c('sub'))<p class="muted" style="text-align:center;margin-bottom:1.6rem">{{ $block->c('sub') }}</p>@endif
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.9rem">
            @foreach ($tiles as $t)
                @php $img = is_array($t) ? ($t['image'] ?? null) : (filter_var($t, FILTER_VALIDATE_URL) ? $t : null); $label = is_array($t) ? ($t['label'] ?? '') : (string) $t; @endphp
                <div style="position:relative;aspect-ratio:3/4;border-radius:var(--radius);overflow:hidden;display:flex;align-items:flex-end;color:#fff;background:@if($img) center/cover url('{{ $img }}') @else linear-gradient(160deg,var(--c-primary),var(--c-accent)) @endif">
                    @if ($label)<span style="padding:.7rem .8rem;font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;background:linear-gradient(0deg,rgba(0,0,0,.4),transparent);width:100%">{{ $label }}</span>@endif
                </div>
            @endforeach
        </div>
    </div>
</section>

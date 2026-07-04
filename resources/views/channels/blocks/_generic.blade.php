@php
    /** @var \App\Support\ChannelSite $site */
    /** @var \App\Models\Channel\Block $block */
    // Universele placeholder: zorgt dat ELK bloktype meteen rendert, ook zolang er
    // nog geen bespoke/branche-template voor bestaat. De designer/Claude vervangt
    // 'm later door channels/blocks/{type}.blade.php of een bespoke override.
    $title = $block->c('title') ?? ucfirst($block->type);
    $text  = $block->c('text') ?? $block->c('sub');
@endphp
<section data-block="{{ $block->type }}" data-block-key="{{ $block->block_key }}">
    <div class="wrap">
        @unless ($site->isLive())
            <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--c-muted);margin-bottom:.6rem">
                Blok “{{ $block->type }}” (nog te stylen)
                @if ($block->design_notes)· <em>{{ $block->design_notes }}</em>@endif
            </div>
        @endunless
        @if ($title)<h2>{{ $title }}</h2>@endif
        @if ($text)<p class="muted" style="max-width:60ch;margin-top:.4rem">{{ $text }}</p>@endif
    </div>
</section>

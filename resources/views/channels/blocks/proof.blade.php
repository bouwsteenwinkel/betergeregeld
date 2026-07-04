@php /** @var \App\Models\Channel\Block $block */ @endphp
@if ($block->c('quote'))
<section data-block="proof">
    <div class="wrap">
        <blockquote class="card" style="font-size:1.3rem;font-weight:600;border-left:5px solid var(--c-accent)">
            “{{ $block->c('quote') }}”
            @if ($block->c('author'))<footer class="muted" style="font-size:.95rem;font-weight:500;margin-top:.6rem">{{ $block->c('author') }}</footer>@endif
        </blockquote>
    </div>
</section>
@endif

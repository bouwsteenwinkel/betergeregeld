{{-- Bouwblok: hero. Thema-agnostisch (gebruikt CSS-vars --primary/--ink/--muted met fallback). --}}
@props([
    'eyebrow' => null,
    'title'   => '',
    'sub'     => null,
    'cta'     => null, 'ctaUrl' => '#',
    'cta2'    => null, 'cta2Url' => '#',
    'note'    => null,
])
<section class="relative overflow-hidden text-center px-6 py-20">
    <div class="max-w-[760px] mx-auto">
        @if($eyebrow)
            <span class="inline-block uppercase tracking-[.24em] text-[11px] font-semibold mb-4" style="color:var(--primary,#b76e79)">{{ $eyebrow }}</span>
        @endif
        <h1 class="text-4xl md:text-5xl font-bold leading-tight" style="color:var(--ink,#1c1c1f)">{{ $title }}</h1>
        @if($sub)<p class="mt-4 text-lg max-w-[56ch] mx-auto" style="color:var(--muted,#6b7280)">{{ $sub }}</p>@endif
        @if($cta || $cta2)
            <div class="flex gap-3 justify-center flex-wrap mt-7">
                @if($cta)<a href="{{ $ctaUrl }}" class="rounded-xl px-6 py-3 text-sm font-semibold text-white" style="background:var(--primary,#111)">{{ $cta }}</a>@endif
                @if($cta2)<a href="{{ $cta2Url }}" class="rounded-xl px-6 py-3 text-sm font-semibold border" style="border-color:rgba(0,0,0,.18);color:var(--ink,#1c1c1f)">{{ $cta2 }}</a>@endif
            </div>
        @endif
        @if($note)<p class="mt-4 text-sm" style="color:var(--muted,#9a9a93)">{{ $note }}</p>@endif
        {{ $slot }}
    </div>
</section>

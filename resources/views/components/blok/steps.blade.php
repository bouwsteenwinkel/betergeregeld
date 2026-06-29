{{-- Bouwblok: stappen (zo werkt het). $items = [['title'=>'..','text'=>'..'], ...] --}}
@props(['eyebrow' => null, 'title' => null, 'items' => []])
<section class="px-6 py-20" style="background:var(--bg,#faf6f2)">
    <div class="max-w-[1000px] mx-auto">
        @if($eyebrow || $title)
            <div class="text-center mb-12">
                @if($eyebrow)<span class="inline-block uppercase tracking-[.24em] text-[11px] font-semibold mb-3" style="color:var(--primary,#b76e79)">{{ $eyebrow }}</span>@endif
                @if($title)<h2 class="text-3xl md:text-4xl font-bold" style="color:var(--ink,#1c1c1f)">{{ $title }}</h2>@endif
            </div>
        @endif
        <div class="grid gap-6" style="grid-template-columns:repeat({{ max(1, count($items)) }},minmax(0,1fr))">
            @foreach($items as $i => $s)
                <div>
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-white mb-3" style="background:var(--primary,#111)">{{ $i + 1 }}</div>
                    <h3 class="font-semibold mb-1" style="color:var(--ink,#1c1c1f)">{{ $s['title'] ?? '' }}</h3>
                    @if(!empty($s['text']))<p class="text-sm" style="color:var(--muted,#6b7280)">{{ $s['text'] }}</p>@endif
                </div>
            @endforeach
        </div>
    </div>
</section>

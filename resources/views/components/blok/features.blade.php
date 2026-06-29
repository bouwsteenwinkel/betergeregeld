{{-- Bouwblok: features-grid. $items = [['icon'=>'..','title'=>'..','text'=>'..'], ...] --}}
@props(['eyebrow' => null, 'title' => null, 'sub' => null, 'items' => [], 'cols' => 4])
<section class="px-6 py-20">
    <div class="max-w-[1080px] mx-auto">
        @if($eyebrow || $title)
            <div class="text-center max-w-[60ch] mx-auto mb-12">
                @if($eyebrow)<span class="inline-block uppercase tracking-[.24em] text-[11px] font-semibold mb-3" style="color:var(--primary,#b76e79)">{{ $eyebrow }}</span>@endif
                @if($title)<h2 class="text-3xl md:text-4xl font-bold" style="color:var(--ink,#1c1c1f)">{{ $title }}</h2>@endif
                @if($sub)<p class="mt-3" style="color:var(--muted,#6b7280)">{{ $sub }}</p>@endif
            </div>
        @endif
        <div class="grid gap-5" style="grid-template-columns:repeat({{ (int) $cols }},minmax(0,1fr))">
            @foreach($items as $f)
                <div class="rounded-xl border p-5" style="border-color:rgba(0,0,0,.08);background:var(--surface,#fff)">
                    @if(!empty($f['icon']))<div class="text-2xl mb-2">{{ $f['icon'] }}</div>@endif
                    <h3 class="font-semibold mb-1" style="color:var(--ink,#1c1c1f)">{{ $f['title'] ?? '' }}</h3>
                    @if(!empty($f['text']))<p class="text-sm" style="color:var(--muted,#6b7280)">{{ $f['text'] }}</p>@endif
                </div>
            @endforeach
        </div>
    </div>
</section>

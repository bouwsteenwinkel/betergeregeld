@php
    /** @var \App\Models\Channel\Block $block */
    $posts = \App\Models\Blog\BlogPost::query()
        ->forChannel($site->key)->published()
        ->orderByDesc('published_at')->limit((int) $block->c('limit', 3))->get();
@endphp
@if ($posts->isNotEmpty())
<section data-block="blog">
    <div class="wrap">
        @if ($block->c('heading'))<h2 style="text-align:center;margin-bottom:1.6rem">{{ $block->c('heading') }}</h2>@endif
        <div class="grid cols-3">
            @foreach ($posts as $post)
                <a href="{{ $site->url('blog/' . $post->slug) }}" class="card" style="display:block">
                    <h3>{{ $post->title }}</h3>
                    @if ($post->excerpt)<p class="muted" style="font-size:.95rem;margin-top:.4rem">{{ \Illuminate\Support\Str::limit($post->excerpt, 110) }}</p>@endif
                    <span class="eyebrow" style="margin-top:.8rem">Lees verder →</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

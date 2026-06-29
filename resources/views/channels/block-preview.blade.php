@php /** @var \App\Support\ChannelSite $site */ $t = $site->theme(); @endphp
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=1100">
<meta name="robots" content="noindex,nofollow">
@if (!empty($t['font_url']))<link href="{{ $t['font_url'] }}" rel="stylesheet">@endif
<style>
    :root{ {!! $site->cssVars() !!} }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:var(--font,system-ui,sans-serif);color:var(--c-ink);background:var(--c-bg);line-height:1.6;width:1100px;overflow:hidden}
    a{color:inherit;text-decoration:none}
    img{max-width:100%;display:block}
    .wrap{max-width:1100px;margin:0 auto;padding:0 22px}
    .btn{display:inline-block;background:var(--c-primary);color:#fff;padding:.85rem 1.5rem;border-radius:var(--radius);font-weight:700;border:0}
    .btn-ghost{background:transparent;color:var(--c-primary);border:2px solid var(--c-primary)}
    .eyebrow{display:inline-block;background:color-mix(in srgb,var(--c-accent) 18%,transparent);color:var(--c-primary);font-weight:700;font-size:.8rem;letter-spacing:.04em;text-transform:uppercase;padding:.35rem .8rem;border-radius:999px;margin-bottom:1rem}
    h1{font-size:clamp(2rem,5vw,3.3rem);line-height:1.1;font-weight:800}
    h2{font-size:2rem;font-weight:800;margin-bottom:.4rem}
    h3{font-size:1.2rem;font-weight:700}
    section{padding:48px 0}
    .muted{color:var(--c-muted)}
    .lead{font-size:1.2rem;margin:1rem 0 1.6rem}
    .card{background:var(--c-surface);border-radius:var(--radius);padding:1.6rem;box-shadow:0 10px 30px -18px rgba(0,0,0,.25)}
    .grid{display:grid;gap:1.2rem}
    .cols-2{grid-template-columns:1fr 1fr}.cols-3{grid-template-columns:repeat(3,1fr)}.cols-4{grid-template-columns:repeat(4,1fr)}
    .hero{padding:52px 0;background:linear-gradient(180deg,color-mix(in srgb,var(--c-accent) 10%,transparent),transparent)}
    .hero .lead{max-width:38ch}
    .hero-usps{list-style:none;margin:1.4rem 0 0;display:grid;gap:.5rem}
    .hero-usps li{padding-left:1.7rem;position:relative;font-weight:600}
    .hero-usps li:before{content:"✓";position:absolute;left:0;color:var(--c-primary);font-weight:800}
    .prose p{margin:0 0 1rem}.prose ul{margin:0 0 1rem 1.2rem}
</style>
</head>
<body>
@include($blockView, ['site' => $site, 'block' => $block, 'facet' => 'website', 'facets' => $facets])
</body>
</html>

{{-- Gedeelde homepage-stijl helpers voor de bespoke plaatsen/blog-pagina's van het
     bedrijfswebsite-kanaal (hover-states + prose-typografie). Één keer in de head. --}}
@once
@push('head')
<style>
    .bg-card { transition: border-color .15s ease, transform .15s ease, box-shadow .15s ease; }
    .bg-card:hover { border-color: #12386B; transform: translateY(-2px); box-shadow: 0 12px 30px rgba(18,56,107,0.10); }
    .bg-alink:hover { color: #12386B !important; }
    .bg-faq > summary { cursor: pointer; list-style: none; }
    .bg-faq > summary::-webkit-details-marker { display: none; }
    .bg-prose a { color: #12386B; text-decoration: underline; text-underline-offset: 3px; }
    .bg-prose h2 { font-size: clamp(24px,3vw,32px); font-weight: 900; letter-spacing: -0.02em; margin: 1.6em 0 .5em; line-height: 1.18; color: #1A1A1A; }
    .bg-prose h3 { font-size: 22px; font-weight: 800; letter-spacing: -0.01em; margin: 1.5em 0 .4em; color: #1A1A1A; }
    .bg-prose p { font-size: 18px; line-height: 1.7; color: #2E2C29; margin: 0 0 1.1em; }
    .bg-prose ul, .bg-prose ol { font-size: 18px; line-height: 1.7; color: #2E2C29; padding-left: 1.4em; margin: 0 0 1.1em; }
    .bg-prose li { margin: 0 0 .4em; }
    .bg-prose img { max-width: 100%; height: auto; border-radius: 8px; margin: 1.4em 0; display: block; }
    .bg-prose blockquote { margin: 1.4em 0; padding: 18px 24px; background: #F1EFEB; border-left: 4px solid #12386B; border-radius: 6px; font-size: 19px; font-weight: 600; color: #1A1A1A; }
</style>
@endpush
@endonce

@php
    // Gedeeld FAQ-accordion: altijd max. 1 open, vloeiende CSS-animatie (grid-rows,
    // werkt ook zonder JS). Verwacht $items = [['q'=>, 'a'=>], ...] (title/text mag ook).
    $faqItems = array_values(array_filter((array) ($items ?? []), fn ($x) => is_array($x) && ($x['q'] ?? $x['title'] ?? '') !== ''));
@endphp
@if ($faqItems)
    <div class="faq faq-acc">
        @foreach ($faqItems as $i => $qa)
            <details @if ($i === 0) open @endif>
                <summary>{{ $qa['q'] ?? $qa['title'] ?? '' }}</summary>
                <div class="faq-a"><div class="faq-a-inner"><p>{{ $qa['a'] ?? $qa['text'] ?? '' }}</p></div></div>
            </details>
        @endforeach
    </div>
    @once
        <script>
        (function () {
            function init() {
                document.querySelectorAll('.faq-acc').forEach(function (acc) {
                    if (acc.dataset.faqInit) return;
                    acc.dataset.faqInit = '1';
                    acc.addEventListener('click', function (e) {
                        var sum = e.target.closest('summary');
                        if (!sum) return;
                        var d = sum.parentElement;
                        // Alleen bij openen de andere sluiten (native toggle + CSS-animatie doet de rest).
                        if (!d.open) {
                            acc.querySelectorAll('details[open]').forEach(function (o) { if (o !== d) o.open = false; });
                        }
                    });
                });
            }
            if (document.readyState !== 'loading') init();
            else document.addEventListener('DOMContentLoaded', init);
        })();
        </script>
    @endonce
@endif

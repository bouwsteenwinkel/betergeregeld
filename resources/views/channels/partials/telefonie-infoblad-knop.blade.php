@php
    // Opvallende "spiekbriefje"-knop naar het infoblad (PDF) bij een telefoniedemo
    // (16-09-2026). Bewust geen rechte knop: een scheef geplakt kaartje met een
    // omgevouwen hoek, met een lichte wiebel (uit bij verminderde beweging). Opent het
    // paneel uit telefonie-infoblad-paneel; zonder JavaScript opent de href de PDF.
    // Verwacht $url; $inline = true zet hem naast een knop in plaats van eronder.
    $inline = $inline ?? false;
@endphp
<a href="{{ $url }}" class="spiek {{ $inline ? 'spiek-inline' : '' }}">
    <span class="spiek-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/></svg>
    </span>
    <span class="spiek-tekst">
        <strong>Spiekbriefje voor de demo</strong>
        <span>Wat je kunt vragen en welke gegevens je mag gebruiken</span>
    </span>
</a>
@once
<style>
.spiek{position:relative;display:inline-flex;align-items:center;gap:.85rem;max-width:100%;margin:.2rem 0 1rem;padding:.85rem 1.6rem .85rem 1.1rem;background:#fff8c2;color:#1c1a12;text-decoration:none;border-radius:4px 4px 4px 12px;border:2px dashed color-mix(in srgb,var(--c-cta) 70%,#000);transform:rotate(-1.5deg);box-shadow:0 16px 28px -14px rgba(0,0,0,.45),0 2px 0 rgba(0,0,0,.05);transition:transform .2s ease,box-shadow .2s ease}
.spiek::after{content:"";position:absolute;right:-2px;bottom:-2px;width:22px;height:22px;background:linear-gradient(315deg,var(--c-bg,#fff) 50%,#e9dc8f 50%);border-radius:0 0 4px 0}
.spiek::before{content:"";position:absolute;left:50%;top:-10px;width:56px;height:18px;margin-left:-28px;background:color-mix(in srgb,var(--c-cta) 55%,#fff);opacity:.85;transform:rotate(3deg);border-radius:2px;box-shadow:0 1px 2px rgba(0,0,0,.2)}
.spiek-ico{display:inline-flex;flex:0 0 auto;width:44px;height:44px;align-items:center;justify-content:center;border-radius:50%;background:var(--c-cta);color:var(--c-on-cta)}
.spiek-tekst{display:flex;flex-direction:column;line-height:1.25;min-width:0}
.spiek-tekst strong{font-family:var(--font-display);font-size:1.05rem;letter-spacing:.01em}
.spiek-tekst span{font-size:.82rem;color:#5a5540}
.spiek:hover,.spiek:focus-visible{transform:rotate(0deg) translateY(-2px);box-shadow:0 22px 34px -14px rgba(0,0,0,.5),0 0 0 4px color-mix(in srgb,var(--c-cta) 25%,transparent);outline:none}
.spiek-inline{margin:0}
@keyframes spiek-wiebel{0%,100%{transform:rotate(-1.5deg)}50%{transform:rotate(1deg)}}
.spiek:not(:hover){animation:spiek-wiebel 6s ease-in-out 2s infinite}
@media (prefers-reduced-motion:reduce){.spiek{animation:none!important;transition:none}.spiek:hover,.spiek:focus-visible{transform:rotate(-1.5deg)}}
@media (max-width:480px){.spiek{transform:rotate(-1deg);padding:.75rem 1.3rem .75rem .9rem}.spiek-tekst strong{font-size:.98rem}}
</style>
@endonce

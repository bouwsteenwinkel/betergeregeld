{{-- Self-hosted Archivo — vervangt de Google-Fonts-hotlink (AVG: geen bezoeker-IP
     naar fonts.gstatic; CWV: same-origin + preload). Bestanden in
     public/fonts/archivo/ (latin + latin-ext, 6 gewichten), gegenereerd uit de
     Google-CSS2 met behoud van font-display:swap. Op IIS moet .woff2 als
     font/woff2 geserveerd worden (staticContent-MIME in public/web.config). --}}
<link rel="preload" href="{{ asset('fonts/archivo/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLydOxI.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('fonts/archivo/archivo.css') }}">

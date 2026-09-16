# Testhandleidingen AI-telefonie (demo's)

Twee PDF's in Betergeregeld-stijl waarmee een klant de demo's zelf kan bellen:

- `Testhandleiding-AI-telefonie-Bakkerij-Kruimel.pdf` — 088 254 5170, bestelnummers K-1042/1057/1063/1071
- `Testhandleiding-AI-telefonie-Autobedrijf-De-Wissel.pdf` — 088 254 5160, kentekens 12-KLM-3, 7-XRP-88, KP-482-T, 3-VBH-21

De inhoud is overgenomen uit `bouwsteenwinkel_v3/telefonie/beleid/{bakkerij,garage}/` en de
demogegevens uit `scripts/_aitest-*-tabellen.php`. Verandert daar iets (prijzen, openingstijden,
kaartjes), pas dan `maak-testhandleidingen.py` aan en druk opnieuw af:

```
python maak-testhandleidingen.py
"C:\Program Files\Google\Chrome\Application\chrome.exe" --headless=new --disable-gpu --no-pdf-header-footer --print-to-pdf=bakkerij.pdf --virtual-time-budget=8000 file:///<pad>/bakkerij.html
```

(De Inter-webfont wordt via Google Fonts geladen; zonder netwerk valt hij terug op Segoe UI.)

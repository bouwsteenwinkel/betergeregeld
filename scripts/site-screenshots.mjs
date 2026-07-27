/**
 * Schermafdrukken van echte websites, voor de etalage op een kanaalsite.
 *
 * Stuurt de lokaal geïnstalleerde Chrome aan via het DevTools-protocol — geen npm-pakketten
 * nodig, Node 22+ heeft WebSocket ingebouwd. Per opdracht: pagina laden, cookiebalk wegklikken,
 * even laten bezinken (lettertypen, lazy-loaded beelden) en dan de zichtbare pagina afdrukken.
 * Geen browserbalk eromheen: het is de pagina-inhoud zelf.
 *
 *   node scripts/site-screenshots.mjs opdrachten.json
 *   node scripts/site-screenshots.mjs --links https://voorbeeld.nl/    (paden verkennen)
 *
 * Opdrachtenbestand: [{ "slot": "case-24werk-1", "url": "https://24werk.com/", "wait": 2500 }]
 * De PNG's landen in public/channel-media/<kanaal>/; de webp-varianten maakt Laravel daarna
 * met ChannelImageGenerator::optimize().
 */
import { spawn } from 'node:child_process';
import { mkdir, writeFile, rm } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import os from 'node:os';

const CHROME = [
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
].find((p) => existsSync(p));

const PORT = 9333;
const BREEDTE = 1280;
const HOOGTE = 800;
const SCHAAL = 2; // retina: 2560px breed, ruim genoeg voor de grootste weergave

if (!CHROME) {
    console.error('Chrome niet gevonden.');
    process.exit(1);
}

/** Klikt cookiemeldingen weg en verbergt wat er blijft plakken. */
const OPRUIMEN = `(() => {
  const woorden = /(accepteer|accepteren|akkoord|toestaan|alles toestaan|accept all|accept|ok, begrepen|sluiten)/i;
  const isConsent = (el) => {
    const t = (el.id + ' ' + (typeof el.className === 'string' ? el.className : '')).toLowerCase();
    return /cookie|consent|cmp|gdpr|privacy/.test(t);
  };
  // 1. Knop in een cookiebalk indrukken.
  for (const b of document.querySelectorAll('button, a[role=button], .btn, [class*=accept], [id*=accept]')) {
    const tekst = (b.textContent || '').trim();
    if (!woorden.test(tekst) || tekst.length > 40) continue;
    let p = b, diep = 0;
    while (p && diep++ < 6) { if (isConsent(p)) { b.click(); break; } p = p.parentElement; }
  }
  // 2. Wat na het klikken nog vastgeplakt over de pagina ligt, weghalen. Twee soorten:
  //    grote overlays (cookiebalk, modal) én kleine aangeplakte widgets aan een schermrand
  //    (cookie-icoon, keurmerk-tab, chatbel) — die horen niet in een etalagebeeld.
  const V = { b: window.innerWidth, h: window.innerHeight };
  for (const el of document.querySelectorAll('body *')) {
    const s = getComputedStyle(el);
    if (s.position !== 'fixed' && s.position !== 'sticky') continue;
    const r = el.getBoundingClientRect();
    if (r.width === 0 || r.height === 0) continue;
    const overlay = r.width * r.height > V.b * V.h * 0.12 && +s.zIndex > 500;
    const raaktRand = r.left <= 2 || r.top <= 2 || r.right >= V.b - 2 || r.bottom >= V.h - 2;
    // Een kopbalk die aan de bovenrand plakt is gewoon navigatie: alleen kleine dingen weg.
    const widget = raaktRand && r.width < V.b * 0.25 && r.height < V.h * 0.5;
    if (isConsent(el) || overlay || widget) el.style.setProperty('display', 'none', 'important');
  }
  document.documentElement.style.setProperty('scroll-behavior', 'auto');
  window.scrollTo(0, 0);
  return document.title;
})()`;

async function verbind(url) {
    const ws = new WebSocket(url);
    await new Promise((ok, fout) => { ws.onopen = ok; ws.onerror = fout; });
    let id = 0;
    const wachtenden = new Map();
    ws.onmessage = (e) => {
        const bericht = JSON.parse(e.data);
        if (bericht.id && wachtenden.has(bericht.id)) {
            wachtenden.get(bericht.id)(bericht.result ?? {});
            wachtenden.delete(bericht.id);
        }
    };
    const stuur = (method, params = {}) => new Promise((ok) => {
        const n = ++id;
        wachtenden.set(n, ok);
        ws.send(JSON.stringify({ id: n, method, params }));
    });

    return { stuur, sluit: () => ws.close() };
}

const pauze = (ms) => new Promise((r) => setTimeout(r, ms));

async function start() {
    const profiel = path.join(os.tmpdir(), 'chrome-shots-' + process.pid);
    // Cloudflare laat een headless browser niet door ("Just a moment..."). Met --zichtbaar
    // draait een echte Chrome, buiten beeld geparkeerd, die de controle wél passeert.
    const zichtbaar = process.argv.includes('--zichtbaar');
    // --map="MAP voorbeeld.nl:80 127.0.0.1:8099" laat een domein naar de lokale dev-server
    // wijzen, zodat je een kanaalsite kunt afdrukken zoals hij straks live staat.
    const map = process.argv.find((a) => a.startsWith('--map='));
    const chrome = spawn(CHROME, [
        ...(map ? [`--host-resolver-rules=${map.slice(6)}`] : []),
        ...(zichtbaar ? ['--window-position=-2400,-2400'] : ['--headless=new', '--disable-gpu']),
        '--hide-scrollbars', '--mute-audio',
        '--no-first-run', '--no-default-browser-check', '--disable-extensions',
        `--remote-debugging-port=${PORT}`, `--user-data-dir=${profiel}`,
        `--window-size=${BREEDTE},${HOOGTE}`, 'about:blank',
    ], { stdio: 'ignore' });

    for (let i = 0; i < 60; i++) {
        try {
            const r = await fetch(`http://127.0.0.1:${PORT}/json/version`);
            if (r.ok) return { chrome, profiel };
        } catch { /* nog niet op */ }
        await pauze(250);
    }
    throw new Error('Chrome kwam niet op.');
}

/** Opent een tab, wacht tot de pagina echt staat, en geeft de verbinding terug. */
async function openTab(url) {
    const doel = await (await fetch(`http://127.0.0.1:${PORT}/json/new?${encodeURIComponent(url)}`, { method: 'PUT' })).json();
    const tab = await verbind(doel.webSocketDebuggerUrl);
    await tab.stuur('Page.enable');
    await tab.stuur('Emulation.setDeviceMetricsOverride', {
        width: BREEDTE, height: HOOGTE, deviceScaleFactor: SCHAAL, mobile: false,
    });

    return { tab, id: doel.id };
}

async function titel(tab) {
    const r = await tab.stuur('Runtime.evaluate', { expression: 'document.title', returnByValue: true });

    return String(r?.result?.value ?? '');
}

async function afdruk(opdracht, uit) {
    const { tab, id } = await openTab(opdracht.url);
    try {
        await tab.stuur('Page.navigate', { url: opdracht.url });
        await pauze(opdracht.wait ?? 3000);

        // Cloudflare houdt de eerste seconden een tussenpagina vast ("Just a moment...").
        for (let i = 0; i < 12 && /just a moment|even geduld|checking your browser/i.test(await titel(tab)); i++) {
            await pauze(1500);
        }
        await tab.stuur('Runtime.evaluate', { expression: OPRUIMEN, returnByValue: true, awaitPromise: false });
        await pauze(1200); // uitklap-animaties van de cookiebalk laten aflopen

        // "naar": een selector om naartoe te scrollen — handig om één sectie te controleren.
        if (opdracht.naar) {
            await tab.stuur('Runtime.evaluate', {
                expression: `document.querySelector(${JSON.stringify(opdracht.naar)})?.scrollIntoView({block:'start'})`,
            });
            await pauze(1000);
        }

        const t = await titel(tab);
        const shot = await tab.stuur('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
        if (!shot?.data) throw new Error('geen beelddata');
        await writeFile(uit, Buffer.from(shot.data, 'base64'));
        console.log(`  ✓ ${path.basename(uit)}  ${t.slice(0, 60)}`);
    } finally {
        tab.sluit();
        await fetch(`http://127.0.0.1:${PORT}/json/close/${id}`).catch(() => {});
    }
}

async function toonLinks(url) {
    const { tab, id } = await openTab(url);
    await tab.stuur('Page.navigate', { url });
    await pauze(4000);
    for (let i = 0; i < 12 && /just a moment/i.test(await titel(tab)); i++) await pauze(1500);
    const r = await tab.stuur('Runtime.evaluate', {
        returnByValue: true,
        expression: `[...new Set([...document.querySelectorAll('a[href]')].map(a => a.href))]
            .filter(h => h.startsWith(location.origin)).slice(0, 80).join('\\n')`,
    });
    console.log(r?.result?.value ?? '(geen links)');
    tab.sluit();
    await fetch(`http://127.0.0.1:${PORT}/json/close/${id}`).catch(() => {});
}

const { chrome, profiel } = await start();
try {
    if (process.argv[2] === '--links') {
        await toonLinks(process.argv[3]);
    } else {
        const opdrachten = JSON.parse(await (await import('node:fs/promises')).readFile(process.argv[2], 'utf8'));
        for (const o of opdrachten) {
            const uit = path.resolve(o.out);
            await mkdir(path.dirname(uit), { recursive: true });
            try {
                await afdruk(o, uit);
            } catch (e) {
                console.log(`  ✗ ${o.slot}: ${e.message}`);
            }
        }
    }
} finally {
    chrome.kill();
    await rm(profiel, { recursive: true, force: true }).catch(() => {});
}

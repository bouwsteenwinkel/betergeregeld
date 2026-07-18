/* CMP Loader v1 */
(function() {
  'use strict';
  var CFG = /*@CMP_CONFIG@*/null/*@CMP_CONFIG_END@*/;
  var COOKIE_NAME = 'cmp_consent_id';
  var COOKIE_DAYS = 365;
  var STORAGE_KEY = 'cmp_choices';
  var POLICY_KEY  = 'cmp_policy_version';

  // ----- helpers -----
  function setCookie(name, value, days) {
    var d = new Date();
    d.setTime(d.getTime() + days * 86400000);
    document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
  }
  function getCookie(name) {
    var nameEQ = name + '=';
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i].trim();
      if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length));
    }
    return null;
  }
  function loadChoices() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null'); } catch (e) { return null; }
  }
  function generateUuid() {
    // RFC 4122 v4 — gebruikt crypto.randomUUID waar beschikbaar
    if (typeof crypto !== 'undefined' && crypto.randomUUID) return crypto.randomUUID();
    var s = '', r;
    for (var i = 0; i < 32; i++) {
      r = Math.random() * 16 | 0;
      if (i === 12) r = 4;
      else if (i === 16) r = (r & 3) | 8;
      s += (i === 8 || i === 12 || i === 16 || i === 20 ? '-' : '') + r.toString(16);
    }
    return s;
  }
  // Google Consent Mode v2. Pusht de keuze naar de dataLayer zodat Google-tags
  // (GTM/GA4/Ads) weten wat mag. Puur additief: zonder container blijft het een
  // ongelezen item in de queue, dus dit is veilig voor elke tenant.
  // De defaults ('denied') staan in de <head>, zie channels/partials/analytics-head.
  function pushConsentMode(choices) {
    try {
      window.dataLayer = window.dataLayer || [];
      var yes = function (k) { return choices[k] === true || choices[k] === 1 || choices[k] === '1'; };
      // Consent-commando's moeten als arguments-object in de dataLayer, niet als
      // gewone array — vandaar deze gtag-vorm in plaats van een directe push.
      function gtag() { window.dataLayer.push(arguments); }
      gtag('consent', 'update', {
        ad_storage: yes('marketing') ? 'granted' : 'denied',
        ad_user_data: yes('marketing') ? 'granted' : 'denied',
        ad_personalization: yes('marketing') ? 'granted' : 'denied',
        analytics_storage: yes('analytics') ? 'granted' : 'denied',
        functionality_storage: yes('functional') ? 'granted' : 'denied',
        personalization_storage: yes('functional') ? 'granted' : 'denied'
      });
    } catch (e) {}
  }

  function saveChoices(choices, status) {
    // Persist lokaal METEEN — onafhankelijk van of de server-fetch slaagt.
    // Zonder dit: bij CORS-/netwerk-fout krijgt de gebruiker bij elke
    // refresh weer de banner ondanks "ja, accepteer".
    var consentId = getCookie(COOKIE_NAME);
    if (!consentId || !/^[0-9a-f-]{36}$/i.test(consentId)) {
      consentId = generateUuid();
    }
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(choices));
      localStorage.setItem(POLICY_KEY, String(CFG.policy_version));
    } catch (e) {}
    setCookie(COOKIE_NAME, consentId, COOKIE_DAYS);
    pushConsentMode(choices);
    injectScripts(consentId);

    // Sync naar server in achtergrond — server upsert met dezelfde UUID
    // (saveConsent in CmpService respecteert de meegegeven consent_id).
    // Als deze fetch faalt: niet erg, lokale staat is al opgeslagen en
    // bij volgende page-load proberen we 't opnieuw via syncConsentIfNeeded.
    fetch(CFG.endpoint_consent, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        tenant: CFG.tenant,
        consent_id: consentId,
        domain: location.hostname,
        choices: choices,
        status: status,
        policy_version: CFG.policy_version,
      })
    }).catch(function() { /* server-sync mislukt; client-state is al goed */ });
  }
  function injectScripts(consentId) {
    var s = document.createElement('script');
    s.async = true;
    s.src = CFG.endpoint_scripts + '?tenant=' + encodeURIComponent(CFG.tenant) + '&consent_id=' + encodeURIComponent(consentId);
    document.head.appendChild(s);
  }
  function txt(key, fallback) {
    var s = (CFG.texts && CFG.texts[key]) || fallback || '';
    // Huisstijl: em-dashes zijn in Nederlandse copy niet toegestaan. De brontekst
    // staat in de CMP-teksten (DB/seeder), dus normaliseren we bij het renderen.
    return String(s).replace(/\s*—\s*/g, ', ');
  }
  function escapeHtml(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  // ----- Branding-vars (gebruikt door zowel banner als floating button) -----
  var BR = CFG.branding || {};
  var col = BR.colors || {};
  var rad = (BR.corner_radius_px || 12) + 'px';

  // CSS injecteren we ALTIJD, ook bij existing-consent — anders verschijnt
  // het 🍪-icoontje en de prefs-modal unstyled (de banner-styles + button
  // + modal-styles staan in dezelfde block).
  injectStyles();

  // ----- Status check -----
  var existingConsent = getCookie(COOKIE_NAME);
  var savedChoices = loadChoices();
  var savedPolicy = parseInt(localStorage.getItem(POLICY_KEY) || '0', 10);
  if (existingConsent && savedChoices && savedPolicy === CFG.policy_version) {
    injectScripts(existingConsent);
    setupReopenLink();
    setupFloatingButton();
    return;
  }

  function injectStyles() {
    if (document.getElementById('cmp-style')) return;
    var style = document.createElement('style');
    style.id = 'cmp-style';
    style.textContent =
      '#cmp-banner{position:fixed;left:16px;right:16px;bottom:16px;max-width:780px;margin:0 auto;background:' + (col.banner_bg || '#1F1F1D') + ';color:' + (col.banner_text || '#F5F1E6') + ';padding:18px 20px;border-radius:' + rad + ';border:1px solid ' + (col.banner_border || 'transparent') + ';font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:14px;line-height:1.5;box-shadow:0 8px 30px rgba(0,0,0,.25);z-index:2147483000;display:flex;flex-direction:column;gap:12px;animation:cmpFade .3s ease-out}' +
      '#cmp-banner h2{margin:0;font-size:15px;font-weight:700}' +
      '#cmp-banner p{margin:0}' +
      '#cmp-banner a{color:' + (col.link || '#F5B400') + ';text-decoration:underline}' +
      '#cmp-banner-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}' +
      '#cmp-banner button{font:inherit;font-weight:600;padding:9px 16px;border-radius:8px;border:1px solid transparent;cursor:pointer;transition:opacity .15s}' +
      '#cmp-banner button:hover{opacity:.85}' +
      '.cmp-btn-primary{background:' + (col.btn_primary_bg || '#D85A30') + ';color:' + (col.btn_primary_text || '#fff') + '}' +
      '.cmp-btn-secondary{background:' + (col.btn_secondary_bg || 'transparent') + ';color:' + (col.btn_secondary_text || '#F5F1E6') + ';border-color:' + (col.btn_secondary_border || 'rgba(255,255,255,.45)') + '}' +
      // Specifieker dan '#cmp-banner button' (id+element), anders wint de
      // transparante border-shorthand daar en heeft de knop geen zichtbare rand.
      '#cmp-banner .cmp-btn-secondary{background:' + (col.btn_secondary_bg || 'rgba(255,255,255,.06)') + ';color:' + (col.btn_secondary_text || '#F5F1E6') + ';border-color:' + (col.btn_secondary_border || 'rgba(255,255,255,.45)') + '}' +
      '@keyframes cmpFade{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}' +
      '#cmp-prefs{position:fixed!important;inset:0!important;background:rgba(0,0,0,.6)!important;z-index:2147483001!important;display:none;align-items:center;justify-content:center;padding:16px;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif!important;-webkit-font-smoothing:antialiased;box-sizing:border-box}' +
      '#cmp-prefs.is-open{display:flex!important}' +
      '#cmp-prefs *,#cmp-prefs *::before,#cmp-prefs *::after{box-sizing:border-box}' +
      '#cmp-prefs-inner{background:#fff!important;color:#1F1F1D!important;border-radius:14px!important;max-width:600px!important;width:100%!important;max-height:90vh!important;overflow-y:auto;padding:26px 28px!important;font-size:14px!important;line-height:1.55!important;box-shadow:0 20px 60px rgba(0,0,0,.25)}' +
      '#cmp-prefs h2{margin:0 0 6px!important;font-size:20px!important;font-weight:700!important;color:#1F1F1D!important;letter-spacing:-0.01em}' +
      '#cmp-prefs > #cmp-prefs-inner > p{margin:0 0 16px!important;color:#475569!important;font-size:14px!important;line-height:1.55!important}' +
      '#cmp-prefs label{display:flex!important;gap:14px!important;padding:14px 16px!important;border:1px solid #e5e7eb!important;border-radius:10px!important;margin-top:10px!important;cursor:pointer;align-items:flex-start;background:#fff!important;transition:border-color .15s,background .15s}' +
      '#cmp-prefs label:hover{border-color:#d1d5db!important;background:#fafafa!important}' +
      '#cmp-prefs label input[type=checkbox]{flex:0 0 18px!important;width:18px!important;height:18px!important;margin:2px 0 0 0!important;padding:0!important;cursor:pointer;accent-color:' + (col.btn_primary_bg || '#D85A30') + ';-webkit-appearance:auto!important;appearance:auto!important;min-width:0!important;max-width:none!important}' +
      '#cmp-prefs label input[type=checkbox]:disabled{cursor:not-allowed;opacity:.6}' +
      '#cmp-prefs label .meta{flex:1 1 auto!important;min-width:0!important}' +
      '#cmp-prefs label strong{display:block!important;margin:0 0 3px!important;font-size:14px!important;font-weight:700!important;color:#1F1F1D!important;line-height:1.4!important}' +
      '#cmp-prefs label small{display:block!important;color:#6b7280!important;font-size:13px!important;line-height:1.5!important;font-weight:400!important}' +
      '#cmp-prefs-actions{display:flex!important;gap:10px!important;justify-content:flex-end!important;margin-top:20px!important;padding-top:16px!important;border-top:1px solid #f0f0f0}' +
      '#cmp-prefs-actions button{font:inherit!important;font-weight:600!important;padding:10px 18px!important;border-radius:8px!important;border:1px solid transparent!important;cursor:pointer;font-size:14px!important;line-height:1.2!important;transition:opacity .15s,background .15s;width:auto!important;height:auto!important;min-width:0!important;max-width:none!important}' +
      '#cmp-prefs-actions button:hover{opacity:.88}' +
      '#cmp-prefs-actions .cmp-btn-secondary{background:#f3f4f6!important;color:#1F1F1D!important;border-color:#e5e7eb!important}' +
      '#cmp-prefs-actions .cmp-btn-primary{background:' + (col.btn_primary_bg || '#D85A30') + '!important;color:' + (col.btn_primary_text || '#fff') + '!important;border-color:' + (col.btn_primary_bg || '#D85A30') + '!important}' +
      '#cmp-reopen{position:fixed;left:14px;bottom:14px;width:42px;height:42px;border-radius:50%;background:' + (col.banner_bg || '#1F1F1D') + ';color:' + (col.banner_text || '#F5F1E6') + ';border:1px solid ' + (col.banner_border || 'rgba(255,255,255,.15)') + ';box-shadow:0 4px 14px rgba(0,0,0,.18);cursor:pointer;z-index:2147482999;display:flex;align-items:center;justify-content:center;font-size:20px;line-height:1;padding:0;transition:transform .15s,box-shadow .15s;font-family:inherit}' +
      '#cmp-reopen:hover{transform:scale(1.08);box-shadow:0 6px 18px rgba(0,0,0,.25)}' +
      '#cmp-reopen:focus-visible{outline:2px solid ' + (col.btn_primary_bg || '#D85A30') + ';outline-offset:2px}' +
      '@media (max-width:520px){' +
        // 38x38 was onder de 44px-tiknorm en de knop stond permanent bovenop de
        // lopende tekst. Nu 44x44 en standaard weggeklapt: hij verschijnt pas
        // onderaan de pagina (zie toggleReopenVisibility), waar de footer al
        // ruimte reserveert. Werkt op elke pagina, niet alleen de homepage.
        // Zwevende knop uit op mobiel: de ingang zit onderaan het hamburger-menu.
        // Een vaste knop zweeft altijd over iets heen; in het menu staat hij in de
        // normale tekststroom en dekt hij niets af.
        '#cmp-reopen{display:none}' +
        '#cmp-banner{padding:14px 16px;gap:10px;max-height:70vh;overflow-y:auto}' +
        // Grid i.p.v. wrap: 2 secundaire knoppen naast elkaar, primair vol over de
        // breedte. DOM-volgorde blijft gelijk en de rij wrapt niet meer rafelig.
        '#cmp-banner-actions{display:grid;grid-template-columns:1fr 1fr;gap:12px}' +
        '#cmp-banner-actions .cmp-btn-primary{grid-column:1/-1}' +
        '#cmp-banner button{width:100%;padding:12px 12px;min-height:44px}' +
      '}';
    document.head.appendChild(style);
  }

  // ----- Banner UI -----
  var banner = document.createElement('div');
  banner.id = 'cmp-banner';
  banner.setAttribute('role', 'dialog');
  banner.setAttribute('aria-label', txt('banner.title', 'Cookie melding'));
  banner.innerHTML =
    '<div><h2>' + escapeHtml(txt('banner.title', 'Wij gebruiken cookies')) + '</h2></div>' +
    '<p>' + escapeHtml(txt('banner.body', '')) + ' <a href="/cookiebeleid" target="_blank" rel="noopener">' + escapeHtml(txt('banner.link_more', 'Meer over cookies')) + '</a></p>' +
    '<div id="cmp-banner-actions">' +
      '<button type="button" class="cmp-btn-secondary" data-cmp-action="reject">' + escapeHtml(txt('banner.btn_reject_all', 'Alleen functioneel')) + '</button>' +
      '<button type="button" class="cmp-btn-secondary" data-cmp-action="customize">' + escapeHtml(txt('banner.btn_customize', 'Voorkeuren')) + '</button>' +
      '<button type="button" class="cmp-btn-primary" data-cmp-action="accept">' + escapeHtml(txt('banner.btn_accept_all', 'Accepteer alles')) + '</button>' +
    '</div>';

  document.documentElement.appendChild(banner);

  banner.addEventListener('click', function(e) {
    var t = e.target;
    if (!t || !t.dataset || !t.dataset.cmpAction) return;
    var action = t.dataset.cmpAction;
    if (action === 'accept') applyAll(true, 'accepted');
    else if (action === 'reject') applyAll(false, 'rejected');
    else if (action === 'customize') openPrefs();
  });

  function applyAll(allYes, status) {
    var choices = {};
    (CFG.categories || []).forEach(function(cat) {
      choices[cat.key] = cat.required ? true : !!allYes;
    });
    saveChoices(choices, status);
    closeBanner();
  }

  function closeBanner() {
    if (banner && banner.parentNode) banner.parentNode.removeChild(banner);
    setupReopenLink();
    setupFloatingButton();
  }

  function openPrefs() {
    var modal = document.createElement('div');
    modal.id = 'cmp-prefs';
    var html = '<div id="cmp-prefs-inner">';
    html += '<h2>' + escapeHtml(txt('prefs.title', 'Cookie-voorkeuren')) + '</h2>';
    html += '<p style="margin:0 0 8px;color:#475569">' + escapeHtml(txt('prefs.intro', '')) + '</p>';
    var current = loadChoices() || {};
    (CFG.categories || []).forEach(function(cat) {
      var disabled = cat.required ? ' disabled' : '';
      var checked = (cat.required || current[cat.key]) ? ' checked' : '';
      var name = txt('cat.' + cat.key + '.name', cat.key);
      var desc = txt('cat.' + cat.key + '.desc', '');
      html += '<label><input type="checkbox" data-cmp-cat="' + escapeHtml(cat.key) + '"' + disabled + checked + '><div class="meta"><strong>' + escapeHtml(name) + '</strong><small>' + escapeHtml(desc) + '</small></div></label>';
    });
    // Buttons via inline-styles om host-CSS-resets (button{padding:0;…})
    // bulletproof te neutraliseren. Brand-kleur uit branding-config.
    var primaryBg   = col.btn_primary_bg   || '#D85A30';
    var primaryFg   = col.btn_primary_text || '#fff';
    var btnBaseCss = 'all:revert;display:inline-block;box-sizing:border-box;font:inherit;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:14px;font-weight:600;line-height:1.2;padding:10px 18px;border-radius:8px;border:1px solid transparent;cursor:pointer;text-decoration:none;text-transform:none;letter-spacing:normal;width:auto;height:auto;min-width:0;max-width:none;margin:0;text-shadow:none;box-shadow:none;outline:0;transition:opacity .15s,background .15s';
    var btnSecCss   = btnBaseCss + ';background:#f3f4f6;color:#1F1F1D;border-color:#e5e7eb';
    var btnPrimCss  = btnBaseCss + ';background:' + primaryBg + ';color:' + primaryFg + ';border-color:' + primaryBg;

    html += '<div id="cmp-prefs-actions">';
    html += '<button type="button" class="cmp-btn-secondary" data-cmp-action="prefs-cancel" style="' + btnSecCss  + '">' + escapeHtml(txt('prefs.btn_cancel', 'Annuleren')) + '</button>';
    html += '<button type="button" class="cmp-btn-primary"   data-cmp-action="prefs-save"   style="' + btnPrimCss + '">' + escapeHtml(txt('prefs.btn_save',   'Opslaan'))   + '</button>';
    html += '</div></div>';
    modal.innerHTML = html;
    document.documentElement.appendChild(modal);
    modal.classList.add('is-open');
    modal.addEventListener('click', function(e) {
      if (e.target === modal) { modal.parentNode.removeChild(modal); return; }
      var act = e.target && e.target.dataset && e.target.dataset.cmpAction;
      if (act === 'prefs-cancel') {
        modal.parentNode.removeChild(modal);
      } else if (act === 'prefs-save') {
        var choices = {};
        modal.querySelectorAll('input[data-cmp-cat]').forEach(function(inp) {
          choices[inp.getAttribute('data-cmp-cat')] = inp.checked;
        });
        saveChoices(choices, 'custom');
        modal.parentNode.removeChild(modal);
        closeBanner();
      }
    });
  }

  function setupReopenLink() {
    document.querySelectorAll('[data-cmp-open-prefs]').forEach(function(el) {
      if (el.__cmpBound) return;
      el.__cmpBound = true;
      el.addEventListener('click', function(e) {
        e.preventDefault();
        openPrefs();
      });
    });
  }

  function setupFloatingButton() {
    if (document.getElementById('cmp-reopen')) return;  // al aanwezig
    var btn = document.createElement('button');
    btn.id = 'cmp-reopen';
    btn.type = 'button';
    btn.setAttribute('aria-label', txt('footer.cookie_settings', 'Cookievoorkeuren'));
    btn.title = txt('footer.cookie_settings', 'Cookievoorkeuren');
    // Inline Heroicon-stijl SVG (geen emoji): volgt de bannerkleur via currentColor.
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
      '<path d="M21.8 14.2a9.8 9.8 0 0 1-9.8 7.8 10 10 0 1 1 0-20 7.6 7.6 0 0 0 7.6 7.6 7.6 7.6 0 0 0 2.2 4.6Z"/>' +
      '<path d="M8.5 9.5h.01"/><path d="M12.5 14.5h.01"/><path d="M7.5 15.5h.01"/><path d="M15.5 17.5h.01"/>' +
      '</svg>';
    btn.addEventListener('click', function() { openPrefs(); });
    document.documentElement.appendChild(btn);
  }

  // Op mobiel is de zwevende knop overbodig én schadelijk: hij zweeft per definitie
  // over de inhoud en dekte lopende tekst af. De ingang zit daar nu onderaan het
  // hamburger-menu (data-cmp-open-prefs), dat op elke pagina bereikbaar is. Op desktop
  // blijft de knop wél staan, daar is geen menu-ingang en is er ruimte in de marge.
  // Eerder stond hier een scroll-truc die de knop wegklapte tijdens het lezen; die is
  // vervallen. Een middel om toestemming in te trekken hoort niet afhankelijk te zijn
  // van hoe ver je gescrold hebt.

  // Public API for footer "cookie-voorkeuren"-link
  window.cmpOpenPrefs = openPrefs;
})();

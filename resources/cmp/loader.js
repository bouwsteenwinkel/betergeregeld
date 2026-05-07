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
  function saveChoices(choices, status) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(choices));
      localStorage.setItem(POLICY_KEY, String(CFG.policy_version));
    } catch (e) {}
    var consentId = getCookie(COOKIE_NAME);
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
    }).then(function(r) { return r.json(); }).then(function(data) {
      if (data && data.consent_id) {
        setCookie(COOKIE_NAME, data.consent_id, COOKIE_DAYS);
        injectScripts(data.consent_id);
      }
    }).catch(function() { /* silent retry next page */ });
  }
  function injectScripts(consentId) {
    var s = document.createElement('script');
    s.async = true;
    s.src = CFG.endpoint_scripts + '?tenant=' + encodeURIComponent(CFG.tenant) + '&consent_id=' + encodeURIComponent(consentId);
    document.head.appendChild(s);
  }
  function txt(key, fallback) {
    return (CFG.texts && CFG.texts[key]) || fallback || '';
  }
  function escapeHtml(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

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

  // ----- Banner UI -----
  var BR = CFG.branding || {};
  var col = BR.colors || {};
  var rad = (BR.corner_radius_px || 12) + 'px';

  var styleId = 'cmp-style';
  if (!document.getElementById(styleId)) {
    var style = document.createElement('style');
    style.id = styleId;
    style.textContent =
      '#cmp-banner{position:fixed;left:16px;right:16px;bottom:16px;max-width:780px;margin:0 auto;background:' + (col.banner_bg || '#1F1F1D') + ';color:' + (col.banner_text || '#F5F1E6') + ';padding:18px 20px;border-radius:' + rad + ';font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:14px;line-height:1.5;box-shadow:0 8px 30px rgba(0,0,0,.25);z-index:2147483000;display:flex;flex-direction:column;gap:12px;animation:cmpFade .3s ease-out}' +
      '#cmp-banner h2{margin:0;font-size:15px;font-weight:700}' +
      '#cmp-banner p{margin:0}' +
      '#cmp-banner a{color:' + (col.link || '#F5B400') + ';text-decoration:underline}' +
      '#cmp-banner-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}' +
      '#cmp-banner button{font:inherit;font-weight:600;padding:9px 16px;border-radius:8px;border:1px solid transparent;cursor:pointer;transition:opacity .15s}' +
      '#cmp-banner button:hover{opacity:.85}' +
      '.cmp-btn-primary{background:' + (col.btn_primary_bg || '#D85A30') + ';color:' + (col.btn_primary_text || '#fff') + '}' +
      '.cmp-btn-secondary{background:' + (col.btn_secondary_bg || 'transparent') + ';color:' + (col.btn_secondary_text || '#F5F1E6') + ';border-color:rgba(255,255,255,.25)}' +
      '@keyframes cmpFade{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}' +
      '#cmp-prefs{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:2147483001;display:none;align-items:center;justify-content:center;padding:16px}' +
      '#cmp-prefs.is-open{display:flex}' +
      '#cmp-prefs-inner{background:#fff;color:#1F1F1D;border-radius:14px;max-width:540px;width:100%;max-height:90vh;overflow-y:auto;padding:24px;font-size:14px}' +
      '#cmp-prefs h2{margin:0 0 8px;font-size:18px}' +
      '#cmp-prefs label{display:flex;gap:12px;padding:12px;border:1px solid #eee;border-radius:8px;margin-top:10px;cursor:pointer}' +
      '#cmp-prefs label .meta{flex:1}' +
      '#cmp-prefs label strong{display:block;margin-bottom:2px;font-size:14px}' +
      '#cmp-prefs label small{color:#666;font-size:12px;line-height:1.4}' +
      '#cmp-prefs-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:16px}' +
      '#cmp-reopen{position:fixed;left:14px;bottom:14px;width:42px;height:42px;border-radius:50%;background:' + (col.banner_bg || '#1F1F1D') + ';color:' + (col.banner_text || '#F5F1E6') + ';border:1px solid rgba(255,255,255,.15);box-shadow:0 4px 14px rgba(0,0,0,.18);cursor:pointer;z-index:2147482999;display:flex;align-items:center;justify-content:center;font-size:20px;line-height:1;padding:0;transition:transform .15s,box-shadow .15s;font-family:inherit}' +
      '#cmp-reopen:hover{transform:scale(1.08);box-shadow:0 6px 18px rgba(0,0,0,.25)}' +
      '#cmp-reopen:focus-visible{outline:2px solid ' + (col.btn_primary_bg || '#D85A30') + ';outline-offset:2px}' +
      '@media (max-width:520px){#cmp-reopen{left:10px;bottom:10px;width:38px;height:38px;font-size:18px}}';
    document.head.appendChild(style);
  }

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
    html += '<div id="cmp-prefs-actions">';
    html += '<button type="button" class="cmp-btn-secondary" data-cmp-action="prefs-cancel">' + escapeHtml(txt('prefs.btn_cancel', 'Annuleren')) + '</button>';
    html += '<button type="button" class="cmp-btn-primary" data-cmp-action="prefs-save">' + escapeHtml(txt('prefs.btn_save', 'Opslaan')) + '</button>';
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
    btn.textContent = '🍪';
    btn.addEventListener('click', function() { openPrefs(); });
    document.documentElement.appendChild(btn);
  }

  // Public API for footer "cookie-voorkeuren"-link
  window.cmpOpenPrefs = openPrefs;
})();

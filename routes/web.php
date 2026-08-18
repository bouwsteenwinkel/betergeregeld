<?php

use App\Http\Controllers\AccessGuardHandleidingController;
use App\Http\Controllers\AccessGuardLandingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ToolsIndexController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Settings\TwoFactorSettingsController;
use App\Http\Controllers\Tools\AccessGuard\AccessGuardController;
use App\Http\Controllers\Tools\Radar\RadarAssetController;
use App\Http\Controllers\Tools\Radar\RadarDashboardController;
use App\Http\Controllers\Tools\Radar\RadarFindingController;
use App\Http\Controllers\Tools\AccessGuard\AccessItemController as AccessGuardAccessItemController;
use App\Http\Controllers\Tools\AccessGuard\AccessProfileController as AccessGuardAccessProfileController;
use App\Http\Controllers\Tools\AccessGuard\AiExplainController as AccessGuardAiExplainController;
use App\Http\Controllers\Tools\AccessGuard\DataController as AccessGuardDataController;
use App\Http\Controllers\Tools\AccessGuard\DemoController as AccessGuardDemoController;
use App\Http\Controllers\Tools\AccessGuard\DirectoryController as AccessGuardDirectoryController;
use App\Http\Controllers\Tools\AccessGuard\FeedbackController as AccessGuardFeedbackController;
use App\Http\Controllers\Tools\AccessGuard\FirstRunController as AccessGuardFirstRunController;
use App\Http\Controllers\Tools\AccessGuard\MatrixController as AccessGuardMatrixController;
use App\Http\Controllers\Tools\AccessGuard\NotificationSettingsController as AccessGuardNotificationSettingsController;
use App\Http\Controllers\Tools\AccessGuard\PersonController as AccessGuardPersonController;
use App\Http\Controllers\Tools\AccessGuard\ProcessController as AccessGuardProcessController;
use App\Http\Controllers\Tools\AccessGuard\ReminderController as AccessGuardReminderController;
use App\Http\Controllers\Tools\AccessGuard\ReviewActionController as AccessGuardReviewActionController;
use App\Http\Controllers\Tools\AccessGuard\RiskFlagController as AccessGuardRiskFlagController;
use App\Http\Controllers\Tools\AccessGuard\ReviewController as AccessGuardReviewController;
use App\Http\Controllers\Tools\AccessGuard\SystemController as AccessGuardSystemController;
use App\Http\Controllers\Tools\AccessGuard\VaultController as AccessGuardVaultController;
use App\Http\Controllers\Tools\BookkeepingAuditLogController;
use App\Http\Controllers\Tools\BookkeepingCategoryController;
use App\Http\Controllers\Tools\BookkeepingController;
use App\Http\Controllers\Tools\BookkeepingImportController;
use App\Http\Controllers\Tools\BookkeepingInvoiceController;
use App\Http\Controllers\Tools\BookkeepingReceiptController;
use App\Http\Controllers\Tools\BookkeepingRecurringController;
use App\Http\Controllers\Tools\BookkeepingRelationController;
use App\Http\Controllers\Tools\BookkeepingReportsController;
use App\Http\Controllers\Tools\BookkeepingSettingsController;
use App\Http\Controllers\Tools\BookkeepingVatRateController;
use App\Http\Controllers\Tools\DiffController;
use App\Http\Controllers\Tools\FaviconGeneratorController;
use App\Http\Controllers\Tools\IbanCheckController;
use App\Http\Controllers\Tools\IpLookupController;
use App\Http\Controllers\Tools\JsonFormatterController;
use App\Http\Controllers\Tools\LegoLookupController;
use App\Http\Controllers\Tools\PdfMergeController;
use App\Http\Controllers\Tools\PdfRedactController;
use App\Http\Controllers\Tools\PostcodeCheckController;
use App\Http\Controllers\Tools\ShippingRatesController;
use App\Http\Controllers\Tools\SpeedTestController;
use App\Http\Controllers\Tools\VatCheckController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Channel-sites (eigen domeinen + /_site/{key} preview). MOET vóór de domein-
// loze routes hieronder, anders vangt '/' het channel-domein af.
require __DIR__ . '/channels.php';

// Afsprakenplanner (platform-breed; werkt op elk trigger-domein, same-origin).
Route::prefix('afspraak')->group(function () {
    Route::get('/beschikbaarheid', [\App\Http\Controllers\AppointmentController::class, 'availability']);
    Route::post('/boeken', [\App\Http\Controllers\AppointmentController::class, 'book'])->middleware('throttle:20,1');

    // Annuleren/verzetten via de persoonlijke cancel_token-link uit de bevestigingsmail.
    // Staat hier (buiten de locale-prefix) zodat de mail-link schoon en stabiel is, net
    // als de revisit-link hieronder. De POST's zijn publiek en token-geraden, dus geknepen.
    Route::get('/annuleren/{token}', [\App\Http\Controllers\AppointmentCancelController::class, 'show'])
        ->where('token', '[A-Za-z0-9]+')->name('appointment.cancel');
    Route::post('/annuleren/{token}', [\App\Http\Controllers\AppointmentCancelController::class, 'cancel'])
        ->where('token', '[A-Za-z0-9]+')->middleware('throttle:10,1')->name('appointment.cancel.submit');
    Route::post('/annuleren/{token}/verzetten', [\App\Http\Controllers\AppointmentCancelController::class, 'reschedule'])
        ->where('token', '[A-Za-z0-9]+')->middleware('throttle:10,1')->name('appointment.reschedule');
});

// Wachtwoordloze klant-pagina "mijn voorbeelden" (persoonlijke token-link uit de mail).
// Buiten de locale-prefix zodat de e-maillink schoon en stabiel is.
Route::get('/mijn-voorbeelden/{token}', [\App\Http\Controllers\SavedPreviewsController::class, 'show'])
    ->where('token', '[A-Za-z0-9]+')->name('saved-previews.show');
Route::post('/mijn-voorbeelden/{token}/favoriet', [\App\Http\Controllers\SavedPreviewsController::class, 'favorite'])
    ->where('token', '[A-Za-z0-9]+')->middleware('throttle:30,1')->name('saved-previews.favorite');
Route::get('/mijn-voorbeelden/{token}/afmelden', [\App\Http\Controllers\SavedPreviewsController::class, 'unsubscribe'])
    ->where('token', '[A-Za-z0-9]+')->name('saved-previews.unsubscribe');

// Google Ads OAuth-callback. Bewust publiek en buiten de Filament-admin-auth,
// zodat de Google-redirect hier landt zonder loginmuur. De state-check ('ads')
// is een lichte bescherming; de code is verder alleen bruikbaar met onze eigen
// client_secret. Wisselt de code in voor een refresh-token (google-ads.json).
Route::get('/admin/ads/oauth/callback', function (\Illuminate\Http\Request $request, \App\Services\Ads\GoogleAdsClient $ads) {
    if ($request->query('state') !== 'ads' || ! $request->filled('code')) {
        return response('Ongeldige Google Ads-callback.', 400);
    }
    $res = $ads->exchangeCode((string) $request->query('code'));

    return response(
        $res['ok']
            ? 'Google Ads gekoppeld. Je kunt dit tabblad sluiten en terug naar de terminal (php artisan ads:status).'
            : ('Koppelen mislukt: ' . $res['error']),
        $res['ok'] ? 200 : 400
    );
})->name('ads.oauth.callback');

// Google-agenda koppelen (afsprakenplanner, fase 1b). Alleen ingelogde admin.
Route::middleware(['web', 'auth'])->prefix('admin/google-agenda')->group(function () {
    Route::get('/', [\App\Http\Controllers\GoogleAgendaController::class, 'status'])->name('google-agenda.status');
    Route::get('/connect', [\App\Http\Controllers\GoogleAgendaController::class, 'connect'])->name('google-agenda.connect');
    Route::get('/callback', [\App\Http\Controllers\GoogleAgendaController::class, 'callback'])->name('google-agenda.callback');
    Route::post('/disconnect', [\App\Http\Controllers\GoogleAgendaController::class, 'disconnect'])->name('google-agenda.disconnect');
});

Route::get('/', fn () => redirect('/' . config('app.locale', 'nl')));

// Blok-template preview (thumbnail in de admin). Buiten de locale-prefix.
Route::get('/blok-voorbeeld/{type}', [\App\Http\Controllers\ChannelSite\BlockPreviewController::class, 'show'])
    ->where('type', '[a-z-]+');

// Webhooks live outside the locale prefix and must not use CSRF.
Route::post('/webhooks/mollie', [WebhookController::class, 'mollie'])->name('webhooks.mollie');

// Ops-endpoints: deploy (git pull + caches + OPcache) en één artisan-commando draaien.
// Server-to-server, beschermd met DEPLOY_TOKEN in DeployController — geen sessie/CSRF.
// Zonder token in de .env geven ze 404, dus ze staan standaard uit.
Route::post('/webhooks/deploy', [\App\Http\Controllers\DeployController::class, 'handle'])
    ->middleware('throttle:6,1')->name('webhooks.deploy');
Route::post('/webhooks/artisan', [\App\Http\Controllers\DeployController::class, 'command'])
    ->middleware('throttle:12,1')->name('webhooks.artisan');

// Speedtest data endpoints live outside locale/CSRF — they carry raw bytes
// and are throttled per IP to protect the server from abuse.
Route::middleware('throttle:speedtest')->group(function () {
	Route::get('/tools/speedtest/ping', [SpeedTestController::class, 'ping'])->name('tools.speedtest.ping');
	Route::get('/tools/speedtest/download', [SpeedTestController::class, 'download'])->name('tools.speedtest.download');
	Route::post('/tools/speedtest/upload', [SpeedTestController::class, 'upload'])->name('tools.speedtest.upload');
});

// Sitemap is locale-agnostic — Google expects it at the document root.
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'sitemap'])->name('sitemap');

/*
 * robots.txt van het HOOFDdomein.
 *
 * Stond tot nu toe als statisch bestand in public/. Dat werd door de webserver
 * geserveerd vóórdat Laravel aan bod kwam, dus ook op elk channel-domein: die kregen
 * allemaal deze lijst, mét "Sitemap: https://betergeregeld.com/sitemap.xml". Hun eigen
 * per-kanaal robots-route (routes/channels.php) draaide nooit, en hun eigen sitemap
 * (622 URL's op jouw-bedrijfswebsite.nl) werd dus aan geen enkele crawler gemeld.
 *
 * Nu een route: de channel-domeingroepen worden vóór dit bestand geladen, dus daar wint
 * hun eigen robots-route en valt alleen het hoofddomein hier terug.
 */
Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Disallow: /admin/',
        'Disallow: /login',
        'Disallow: /logout',
        'Disallow: /register',
        'Disallow: /verify-email',
        'Disallow: /two-factor',
        'Disallow: /dashboard',
        'Disallow: /password',
        'Disallow: /settings',
        'Disallow: /billing',
        'Disallow: /webhooks/',
        'Disallow: /api/',
        '',
        '# Tool-deeppaths waar je geen URL voor zou willen indexeren',
        'Disallow: /*/tools/favicon-generator/result/',
        'Disallow: /*/tools/favicon-generator/download/',
        'Disallow: /*/tools/pdf-merge/download/',
        'Disallow: /*/tools/boekhouden',
        '',
        '# AccessGuard token-flows',
        'Disallow: /*/accessguard/notifications/unsubscribe/',
        '',
        '# Cloudflare e-mail-protection artefact (geen echte pagina)',
        'Disallow: /cdn-cgi/',
        '',
        'Sitemap: ' . url('/sitemap.xml'),
    ];

    return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

// CMP — public-facing endpoints. Locale-agnostic; loader/scripts worden
// geserveerd zonder authenticatie en met CORS open zodat externe sites
// (bouwsteenwinkel.nl, brikl.nl, etc.) ze kunnen embedden.
Route::get('/cmp/loader.js',  [\App\Http\Controllers\CmpController::class, 'loader'])->name('cmp.loader');
Route::get('/cmp/scripts.js', [\App\Http\Controllers\CmpController::class, 'scripts'])->name('cmp.scripts');
Route::post('/cmp/consent',   [\App\Http\Controllers\CmpController::class, 'consent'])->name('cmp.consent');
Route::options('/cmp/consent',[\App\Http\Controllers\CmpController::class, 'consentOptions']);

// VPS monitoring ingest — host collectors POST samples here, authenticated by a
// per-server token (Bearer / X-Monitor-Token). Locale-agnostic + CSRF-exempt.
Route::post('/monitor/ingest', \App\Http\Controllers\Monitor\IngestController::class)->name('monitor.ingest');
Route::post('/security/ingest/{token}', \App\Http\Controllers\Security\SoftwareIngestController::class)->name('security.ingest');

// SocketLabs event-webhook — deliverability/queue-events. Auth via SecretKey +
// ServerID in de payload (zie config/socketlabs.php). Locale-agnostisch + CSRF-exempt.
Route::post('/monitor/socketlabs/webhook', \App\Http\Controllers\Monitor\SocketLabsWebhookController::class)->name('monitor.socketlabs.webhook');

// Cron-monitoring heartbeat — bewaakte jobs pingen hun ping_token (GET of POST).
// {signal} = success (default) | start | fail. Locale-agnostisch + CSRF-exempt.
Route::match(['get', 'post'], '/cron/ping/{token}/{signal?}', \App\Http\Controllers\Monitor\CronPingController::class)
	->name('cron.ping');

// Uitgefaseerde ENGELSE BLOG (18-08-2026) → 301 naar het Nederlandse artikel.
//
// Alleen de blog: /en/tools en /en/diensten blijven gewoon bestaan, dus de locale
// 'en' blijft in SetLocale::SUPPORTED staan. De slugs van de vertalingen zijn
// gelijk aan die van het origineel (/en/blog/factuur-in-nederlands-en-engels), dus
// het pad kan één-op-één mee. Een enkele vertaling draagt '-en' in de slug; die
// suffix wordt gestript, net als destijds bij de/fr/es.
//
// 301 en geen 410: deze URL's stáán in de index en een deel rankt (positie 10 voor
// 'factuur in het engels'). Die waarde hoort naar het Nederlandse artikel te gaan,
// niet verloren.
//
// MOET vóór de {locale}-groep staan.
Route::get('/en/blog/{rest?}', function (?string $rest = null) {
	$rest = (string) $rest;
	if ($rest !== '') {
		$delen = explode('/', $rest);
		$laatste = array_pop($delen);
		$zonder = preg_replace('/-en$/', '', $laatste);
		if ($zonder !== '' && $zonder !== $laatste) {
			$delen[] = $zonder;
			$rest = implode('/', $delen);
		}
	}

	return redirect('/nl/blog' . ($rest !== '' ? '/' . $rest : ''), 301);
})->where('rest', '.*');

// Uitgefaseerde locales de/fr/es (waren onvertaalde NL-duplicaten) → 301 naar /nl.
// Behoudt link-equity voor reeds geïndexeerde URL's i.p.v. kale 404's. MOET vóór
// de {locale}-groep staan.
//
// Het pad wordt niet één-op-één overgenomen. Hier stond "slugs waren toch al
// Nederlands, dus /nl/{rest} resolvet" — dat klopt voor de meeste, maar 63 van de
// 152 gepubliceerde de/fr/es-artikelen dragen de taal ín hun slug
// (wat-kost-een-website-de). Die kwamen na de 301 uit op een 410 Gone: eerst een
// omleiding kosten en dan alsnog zeggen dat de pagina weg is, terwijl het
// Nederlandse artikel gewoon bestaat onder dezelfde slug zónder suffix.
//
// Alleen de suffix van de taal waar we vandaan komen wordt gestript, en alleen op
// het laatste paddeel. Bestaat de gestripte slug toch niet, dan pakt de gewone
// blog-afhandeling het op (die 301't naar de juiste locale of geeft 410).
// Gecontroleerd op 11-08-2026: alle 63 komen zo op een bestaand artikel uit.
Route::get('/{oldloc}/{rest?}', function (string $oldloc, ?string $rest = null) {
	$rest = (string) $rest;
	if ($rest !== '') {
		$delen = explode('/', $rest);
		$laatste = array_pop($delen);
		$zonder = preg_replace('/-' . preg_quote($oldloc, '/') . '$/', '', $laatste);
		if ($zonder !== '' && $zonder !== $laatste) {
			$delen[] = $zonder;
			$rest = implode('/', $delen);
		}
	}

	return redirect('/nl' . ($rest !== '' ? '/' . $rest : ''), 301);
})->where('oldloc', 'de|fr|es')->where('rest', '.*');

// ── Legacy landings-URL's → 301 naar huidige gelokaliseerde structuur ─────────
// Oude korte-URL's (pre-locale-prefix root `/seo-check`, of `/nl/seo-check`)
// wezen uit GSC naar 404. Slug-gestuurd uit de catalogus zodat het meegroeit.
// MOET vóór de {locale}-groep staan zodat deze eerst matchen.
$__legacyServices = array_keys(config('services_catalog', []));
$__legacyTools = [
	'iban-check', 'vat-check', 'postcode-check', 'shipping-rates', 'ip-lookup',
	'lego-lookup', 'json-formatter', 'diff', 'favicon-generator', 'pdf-merge',
	'dns-inspector', 'mail-auth-check', 'ssl-check', 'security-headers', 'whois-lookup',
];
// Overblijfsel van de oude site: /index.html. Search Console meldt hem sinds mei als 404, in drie
// varianten (http, https en www) - die laatste twee lossen zichzelf op zodra de apex het goed doet,
// want www en http 301'en al naar https://betergeregeld.com. Een regel scheelt drie meldingen en
// houdt de link-equity van wat er ooit naar wees.
Route::redirect('/index.html', '/nl', 301);

Route::redirect('/diensten', '/nl/diensten', 301);
Route::redirect('/tools', '/nl/tools', 301);
foreach ($__legacyServices as $__s) {
	Route::redirect("/{$__s}", "/nl/diensten/{$__s}", 301);           // root → nl/diensten
	Route::redirect("/diensten/{$__s}", "/nl/diensten/{$__s}", 301);  // zonder locale
	Route::redirect("/nl/{$__s}", "/nl/diensten/{$__s}", 301);        // oude /nl korte vorm
	Route::redirect("/en/{$__s}", "/en/diensten/{$__s}", 301);
}
foreach ($__legacyTools as $__t) {
	Route::redirect("/{$__t}", "/nl/tools/{$__t}", 301);
	Route::redirect("/tools/{$__t}", "/nl/tools/{$__t}", 301);
}
// Hernoemde content-pagina's → dichtstbijzijnde huidige equivalent. Alleen waar
// een écht relevant target bestaat; pers/cases/terms/etc. hebben er geen en
// blijven bewust 404 (redirect naar een generieke pagina = soft-404, slecht).
foreach (['nl', 'en'] as $__loc) {
	Route::redirect("/{$__loc}/over-ons", "/{$__loc}/over", 301);
	Route::redirect("/{$__loc}/over-betergeregeld", "/{$__loc}/over", 301);
	Route::redirect("/{$__loc}/support", "/{$__loc}/contact", 301);
	foreach (['business', 'teams', 'freelancers'] as $__seg) {
		Route::redirect("/{$__loc}/solutions/{$__seg}", "/{$__loc}/prijzen", 301);
	}
}
Route::redirect('/nl/servicios', '/nl/diensten', 301);   // oude es-slug onder /nl
Route::redirect('/nl/sobre-nosotros', '/nl/over', 301);
// Oude legal-URL's → de nieuwe juridische pagina's op de hoofdsite.
foreach (['nl', 'en'] as $__loc) {
	Route::redirect("/{$__loc}/privacy", "/{$__loc}/privacybeleid", 301);
	Route::redirect("/{$__loc}/cookies", "/{$__loc}/cookiebeleid", 301);
	Route::redirect("/{$__loc}/terms", "/{$__loc}/algemene-voorwaarden", 301);
	Route::redirect("/{$__loc}/voorwaarden", "/{$__loc}/algemene-voorwaarden", 301);
	Route::redirect("/{$__loc}/disclaimer", "/{$__loc}/algemene-voorwaarden", 301);
}

Route::prefix('{locale}')
	->whereIn('locale', SetLocale::SUPPORTED)
	->middleware(SetLocale::class)
	->group(function () {
		Route::get('/', fn () => view('welcome'))->name('home');
		Route::get('/over', fn () => view('pages.about'))->name('about');

		// Juridische pagina's (generiek uit config/legal.php). noindex,follow.
		Route::get('/privacybeleid', fn () => view('pages.legal.privacy'))->name('legal.privacy');
		Route::get('/cookiebeleid', fn () => view('pages.legal.cookies'))->name('legal.cookies');
		Route::get('/algemene-voorwaarden', fn () => view('pages.legal.terms'))->name('legal.terms');
		Route::get('/accessguard', [AccessGuardLandingController::class, 'show'])->name('accessguard.landing');
		Route::get('/accessguard/demo', [AccessGuardDemoController::class, 'show'])->name('accessguard.demo');

		Route::get('/blog', [\App\Http\Controllers\Blog\BlogController::class, 'index'])->name('blog.index');
		Route::get('/blog/rss', [\App\Http\Controllers\SitemapController::class, 'rss'])->name('blog.rss');
		Route::get('/blog/categorie/{categorySlug}', [\App\Http\Controllers\Blog\BlogController::class, 'category'])->name('blog.category');
		Route::get('/blog/tag/{tagSlug}', [\App\Http\Controllers\Blog\BlogController::class, 'tag'])->name('blog.tag');
		Route::get('/blog/{slug}', [\App\Http\Controllers\Blog\BlogController::class, 'show'])->name('blog.show');
		Route::get('/accessguard/handleiding', [AccessGuardHandleidingController::class, 'download'])->name('accessguard.handleiding');
		Route::get('/accessguard/notifications/unsubscribe/{token}', [AccessGuardNotificationSettingsController::class, 'unsubscribe'])->name('tools.accessguard.notifications.unsubscribe');

		Route::middleware('guest')->group(function () {
			Route::get('/login', [LoginController::class, 'show'])->name('login');
			Route::post('/login', [LoginController::class, 'login']);

			Route::get('/register', [RegisterController::class, 'show'])->name('register');
			Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1');
			Route::get('/register/sent', [RegisterController::class, 'sent'])->name('register.sent');

			Route::get('/verify/{user}/{token}', [VerifyEmailController::class, 'verify'])
				->name('verify.email');

			Route::get('/2fa/challenge', [TwoFactorChallengeController::class, 'show'])->name('2fa.challenge');
			Route::post('/2fa/challenge', [TwoFactorChallengeController::class, 'verify']);
		});

		Route::post('/logout', [LoginController::class, 'logout'])
			->middleware('auth')
			->name('logout');

		Route::middleware('auth')->group(function () {
			Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

			// Rankdata — bureau-portal (overzicht klanten) + leesbaar klant-dashboard.
			Route::get('/bureau', [\App\Http\Controllers\RankdataDashboardController::class, 'agency'])->name('rankdata.agency');
			Route::get('/mijn-statistieken', [\App\Http\Controllers\RankdataDashboardController::class, 'me'])->name('rankdata.me');
			Route::get('/rankdata/{tenant}', [\App\Http\Controllers\RankdataDashboardController::class, 'show'])->name('rankdata.client');
			Route::post('/rankdata/{tenant}/checkout', [\App\Http\Controllers\RankdataDashboardController::class, 'checkout'])->name('rankdata.checkout');
			Route::post('/rankdata/{tenant}/report', [\App\Http\Controllers\RankdataDashboardController::class, 'mailReport'])->name('rankdata.report');
			Route::post('/rankdata/{tenant}/vraag', [\App\Http\Controllers\RankdataDashboardController::class, 'ask'])
				->middleware('throttle:20,1')->name('rankdata.ask');
			Route::get('/security/agent-plugin', function () {
				abort_unless(auth()->user()?->isSuperAdmin() || auth()->user()?->isAgencyAdmin(), 403);

				return response()->download(resource_path('security-agent/beter-geregeld-monitor.php'), 'beter-geregeld-monitor.php');
			})->name('security.agent-plugin');

			Route::post('/billing/trial', [BillingController::class, 'startTrial'])->name('billing.trial');
			Route::post('/billing/checkout', [BillingController::class, 'startCheckout'])->name('billing.checkout');
			Route::get('/billing/return/{intent}', [BillingController::class, 'return'])->name('billing.return');
			Route::get('/billing/fake-confirm/{intent}', [BillingController::class, 'fakeConfirm'])->name('billing.fake-confirm');

			Route::get('/settings/2fa', [TwoFactorSettingsController::class, 'show'])->name('settings.2fa');
			Route::post('/settings/2fa/enable', [TwoFactorSettingsController::class, 'enable'])->name('settings.2fa.enable');
			Route::post('/settings/2fa/disable', [TwoFactorSettingsController::class, 'disable'])->name('settings.2fa.disable');
		});

		Route::get('/tools', [ToolsIndexController::class, 'show'])->name('tools.index');

		Route::prefix('tools')->name('tools.')->group(function () {
			Route::middleware('tool.limit:iban')->group(function () {
				Route::get('/iban-check', [IbanCheckController::class, 'show'])->name('iban-check');
				Route::post('/iban-check', [IbanCheckController::class, 'check'])->name('iban-check.check');
			});

			Route::middleware('tool.limit:vies')->group(function () {
				Route::get('/vat-check', [VatCheckController::class, 'show'])->name('vat-check');
				Route::post('/vat-check', [VatCheckController::class, 'check'])->name('vat-check.check');
			});

			Route::middleware('tool.limit:postcode')->group(function () {
				Route::get('/postcode-check', [PostcodeCheckController::class, 'show'])->name('postcode-check');
				Route::post('/postcode-check', [PostcodeCheckController::class, 'check'])->name('postcode-check.check');
			});

			Route::middleware('tool.limit:ip_lookup')->group(function () {
				Route::get('/ip-lookup', [IpLookupController::class, 'show'])->name('ip-lookup');
			});

			Route::get('/json-formatter', [JsonFormatterController::class, 'show'])->name('json-formatter');
			Route::post('/json-formatter', [JsonFormatterController::class, 'run'])->name('json-formatter.run');

			Route::get('/diff', [DiffController::class, 'show'])->name('diff');
			Route::post('/diff', [DiffController::class, 'compare'])->name('diff.compare');

			Route::get('/shipping-rates', [ShippingRatesController::class, 'show'])->name('shipping-rates');

			// DNS / security / cert-tools — server-side lookups, geen externe API-keys.
			Route::middleware('tool.limit:dns_inspector')->group(function () {
				Route::get('/dns-inspector', [\App\Http\Controllers\Tools\DnsInspectorController::class, 'show'])->name('dns-inspector');
				Route::post('/dns-inspector', [\App\Http\Controllers\Tools\DnsInspectorController::class, 'check'])->name('dns-inspector.check');
			});
			Route::middleware('tool.limit:mail_auth')->group(function () {
				Route::get('/mail-auth-check', [\App\Http\Controllers\Tools\MailAuthCheckController::class, 'show'])->name('mail-auth');
				Route::post('/mail-auth-check', [\App\Http\Controllers\Tools\MailAuthCheckController::class, 'check'])->name('mail-auth.check');
			});
			Route::middleware('tool.limit:ssl_check')->group(function () {
				Route::get('/ssl-check', [\App\Http\Controllers\Tools\SslCheckController::class, 'show'])->name('ssl-check');
				Route::post('/ssl-check', [\App\Http\Controllers\Tools\SslCheckController::class, 'check'])->name('ssl-check.check');
			});
			Route::middleware('tool.limit:headers')->group(function () {
				Route::get('/security-headers', [\App\Http\Controllers\Tools\HeadersAnalyzerController::class, 'show'])->name('headers-analyzer');
				Route::post('/security-headers', [\App\Http\Controllers\Tools\HeadersAnalyzerController::class, 'check'])->name('headers-analyzer.check');
			});
			Route::middleware('tool.limit:whois')->group(function () {
				Route::get('/whois-lookup', [\App\Http\Controllers\Tools\WhoisLookupController::class, 'show'])->name('whois-lookup');
				Route::post('/whois-lookup', [\App\Http\Controllers\Tools\WhoisLookupController::class, 'check'])->name('whois-lookup.check');
			});

			Route::middleware('tool.limit:lego_lookup')->group(function () {
				Route::get('/lego-lookup', [LegoLookupController::class, 'show'])->name('lego-lookup');
				Route::post('/lego-lookup', [LegoLookupController::class, 'check'])->name('lego-lookup.check');
			});

			Route::middleware('tool.limit:favicon')->group(function () {
				Route::get('/favicon-generator', [FaviconGeneratorController::class, 'show'])->name('favicon-generator');
				Route::post('/favicon-generator', [FaviconGeneratorController::class, 'generate'])->name('favicon-generator.generate');
				Route::get('/favicon-generator/result/{key}', [FaviconGeneratorController::class, 'result'])->name('favicon-generator.result');
				Route::get('/favicon-generator/download/{key}/{what}', [FaviconGeneratorController::class, 'download'])->name('favicon-generator.download');
			});

			// Speedtest: tool.limit counts only the page-load; the streaming
			// sub-endpoints are throttled by IP separately so a single test
			// doesn't burn the whole daily quota.
			Route::middleware('tool.limit:speedtest')->group(function () {
				Route::get('/speedtest', [SpeedTestController::class, 'show'])->name('speedtest');
			});
			Route::post('/speedtest/record', [SpeedTestController::class, 'record'])->name('speedtest.record');

			Route::middleware('tool.limit:pdf_merge')->group(function () {
				Route::get('/pdf-merge', [PdfMergeController::class, 'show'])->name('pdf-merge');
				Route::post('/pdf-merge/upload', [PdfMergeController::class, 'upload'])->name('pdf-merge.upload');
				Route::delete('/pdf-merge/file/{fileId}', [PdfMergeController::class, 'remove'])->name('pdf-merge.remove');
				Route::post('/pdf-merge/merge', [PdfMergeController::class, 'merge'])->name('pdf-merge.merge');
				Route::get('/pdf-merge/download/{key}', [PdfMergeController::class, 'download'])->name('pdf-merge.download');
				Route::post('/pdf-merge/reset', [PdfMergeController::class, 'reset'])->name('pdf-merge.reset');
			});

			Route::middleware('auth')->prefix('boekhouden')->name('bookkeeping.')->group(function () {
				Route::get('/', [BookkeepingController::class, 'index'])->name('index');
				Route::get('/nieuw', [BookkeepingController::class, 'create'])->name('create');
				Route::post('/', [BookkeepingController::class, 'store'])->name('store');
				Route::get('/rapporten/winst-verlies', [BookkeepingReportsController::class, 'profitLoss'])->name('reports.profit-loss');
				Route::get('/rapporten/winst-verlies/export', [BookkeepingReportsController::class, 'profitLossExport'])->name('reports.profit-loss.export');
				Route::get('/rapporten/btw-aangifte', [BookkeepingReportsController::class, 'vatReturn'])->name('reports.vat');
				Route::get('/rapporten/btw-aangifte/export', [BookkeepingReportsController::class, 'vatReturnExport'])->name('reports.vat.export');

				Route::get('/categorieen', [BookkeepingCategoryController::class, 'index'])->name('categories.index');
				Route::get('/categorieen/nieuw', [BookkeepingCategoryController::class, 'create'])->name('categories.create');
				Route::post('/categorieen', [BookkeepingCategoryController::class, 'store'])->name('categories.store');
				Route::get('/categorieen/{id}/bewerken', [BookkeepingCategoryController::class, 'edit'])->name('categories.edit');
				Route::put('/categorieen/{id}', [BookkeepingCategoryController::class, 'update'])->name('categories.update');
				Route::delete('/categorieen/{id}', [BookkeepingCategoryController::class, 'destroy'])->name('categories.destroy');

				Route::get('/btw-tarieven', [BookkeepingVatRateController::class, 'index'])->name('vat-rates.index');
				Route::get('/btw-tarieven/nieuw', [BookkeepingVatRateController::class, 'create'])->name('vat-rates.create');
				Route::post('/btw-tarieven', [BookkeepingVatRateController::class, 'store'])->name('vat-rates.store');
				Route::get('/btw-tarieven/{id}/bewerken', [BookkeepingVatRateController::class, 'edit'])->name('vat-rates.edit');
				Route::put('/btw-tarieven/{id}', [BookkeepingVatRateController::class, 'update'])->name('vat-rates.update');
				Route::delete('/btw-tarieven/{id}', [BookkeepingVatRateController::class, 'destroy'])->name('vat-rates.destroy');

				Route::get('/{id}/bonnetje', [BookkeepingReceiptController::class, 'view'])
					->whereUuid('id')->name('receipt.view');
				Route::get('/{id}/bonnetje/download', [BookkeepingReceiptController::class, 'download'])
					->whereUuid('id')->name('receipt.download');
				Route::delete('/{id}/bonnetje', [BookkeepingReceiptController::class, 'destroy'])
					->whereUuid('id')->name('receipt.destroy');

				Route::get('/logboek', [BookkeepingAuditLogController::class, 'index'])->name('audit-log.index');

				Route::get('/instellingen', [BookkeepingSettingsController::class, 'edit'])->name('settings.edit');
				Route::put('/instellingen', [BookkeepingSettingsController::class, 'update'])->name('settings.update');
				Route::delete('/instellingen/logo', [BookkeepingSettingsController::class, 'destroyLogo'])->name('settings.logo.destroy');
				Route::get('/instellingen/logo', [BookkeepingSettingsController::class, 'viewLogo'])->name('settings.logo.view');

				Route::get('/facturen', [BookkeepingInvoiceController::class, 'index'])->name('invoices.index');
				Route::get('/facturen/nieuw', [BookkeepingInvoiceController::class, 'create'])->name('invoices.create');
				Route::post('/facturen', [BookkeepingInvoiceController::class, 'store'])->name('invoices.store');
				Route::get('/facturen/{id}', [BookkeepingInvoiceController::class, 'show'])->whereUuid('id')->name('invoices.show');
				Route::get('/facturen/{id}/bewerken', [BookkeepingInvoiceController::class, 'edit'])->whereUuid('id')->name('invoices.edit');
				Route::put('/facturen/{id}', [BookkeepingInvoiceController::class, 'update'])->whereUuid('id')->name('invoices.update');
				Route::delete('/facturen/{id}', [BookkeepingInvoiceController::class, 'destroy'])->whereUuid('id')->name('invoices.destroy');
				Route::get('/facturen/{id}/pdf', [BookkeepingInvoiceController::class, 'pdf'])->whereUuid('id')->name('invoices.pdf');
				Route::post('/facturen/{id}/verzenden', [BookkeepingInvoiceController::class, 'markSent'])->whereUuid('id')->name('invoices.mark-sent');
				Route::post('/facturen/{id}/betaald', [BookkeepingInvoiceController::class, 'markPaid'])->whereUuid('id')->name('invoices.mark-paid');
				Route::post('/facturen/{id}/annuleren', [BookkeepingInvoiceController::class, 'markCancelled'])->whereUuid('id')->name('invoices.mark-cancelled');
				Route::post('/facturen/{id}/herinnering', [BookkeepingInvoiceController::class, 'sendReminder'])->whereUuid('id')->name('invoices.send-reminder');

				Route::get('/terugkerend', [BookkeepingRecurringController::class, 'index'])->name('recurring.index');
				Route::get('/terugkerend/nieuw', [BookkeepingRecurringController::class, 'create'])->name('recurring.create');
				Route::post('/terugkerend', [BookkeepingRecurringController::class, 'store'])->name('recurring.store');
				Route::get('/terugkerend/{id}/bewerken', [BookkeepingRecurringController::class, 'edit'])->whereUuid('id')->name('recurring.edit');
				Route::put('/terugkerend/{id}', [BookkeepingRecurringController::class, 'update'])->whereUuid('id')->name('recurring.update');
				Route::delete('/terugkerend/{id}', [BookkeepingRecurringController::class, 'destroy'])->whereUuid('id')->name('recurring.destroy');
				Route::post('/terugkerend/{id}/nu', [BookkeepingRecurringController::class, 'runNow'])->whereUuid('id')->name('recurring.run-now');

				Route::get('/import', [BookkeepingImportController::class, 'show'])->name('import.show');
				Route::post('/import', [BookkeepingImportController::class, 'upload'])->name('import.upload');
				Route::get('/import/{key}/voorbeeld', [BookkeepingImportController::class, 'preview'])->name('import.preview');
				Route::post('/import/{key}/commit', [BookkeepingImportController::class, 'commit'])->name('import.commit');

				Route::get('/relaties', [BookkeepingRelationController::class, 'index'])->name('relations.index');
				Route::get('/relaties/nieuw', [BookkeepingRelationController::class, 'create'])->name('relations.create');
				Route::post('/relaties', [BookkeepingRelationController::class, 'store'])->name('relations.store');
				Route::get('/relaties/{id}/bewerken', [BookkeepingRelationController::class, 'edit'])->name('relations.edit');
				Route::put('/relaties/{id}', [BookkeepingRelationController::class, 'update'])->name('relations.update');
				Route::delete('/relaties/{id}', [BookkeepingRelationController::class, 'destroy'])->name('relations.destroy');

				Route::get('/{id}/bewerken', [BookkeepingController::class, 'edit'])->name('edit');
				Route::put('/{id}', [BookkeepingController::class, 'update'])->name('update');
				Route::delete('/{id}', [BookkeepingController::class, 'destroy'])->name('destroy');
			});

			Route::middleware('auth')->prefix('accessguard')->name('accessguard.')->group(function () {
				Route::get('/', [AccessGuardController::class, 'index'])->name('index');
				Route::post('/first-run/seed', [AccessGuardFirstRunController::class, 'seedDemo'])->name('first-run.seed');
				Route::post('/first-run/wipe', [AccessGuardFirstRunController::class, 'wipeDemo'])->name('first-run.wipe');

				Route::get('/matrix', [AccessGuardMatrixController::class, 'index'])->name('matrix');
				Route::post('/matrix/cell', [AccessGuardMatrixController::class, 'updateCell'])->name('matrix.update');
				Route::get('/matrix/cel/{personId}/{systemId}', [AccessGuardMatrixController::class, 'cellDetail'])->whereNumber(['personId', 'systemId'])->name('matrix.cell');
				Route::post('/matrix/item', [AccessGuardMatrixController::class, 'updateItemState'])->name('matrix.update-item');

				Route::get('/personen', [AccessGuardPersonController::class, 'index'])->name('people.index');
				Route::get('/personen/nieuw', [AccessGuardPersonController::class, 'create'])->name('people.create');
				Route::post('/personen', [AccessGuardPersonController::class, 'store'])->name('people.store');
				Route::get('/personen/{id}/bewerken', [AccessGuardPersonController::class, 'edit'])->whereNumber('id')->name('people.edit');
				Route::put('/personen/{id}', [AccessGuardPersonController::class, 'update'])->whereNumber('id')->name('people.update');
				Route::delete('/personen/{id}', [AccessGuardPersonController::class, 'destroy'])->whereNumber('id')->name('people.destroy');

				Route::get('/systemen', [AccessGuardSystemController::class, 'index'])->name('systems.index');
				Route::get('/systemen/nieuw', [AccessGuardSystemController::class, 'create'])->name('systems.create');
				Route::post('/systemen', [AccessGuardSystemController::class, 'store'])->name('systems.store');
				Route::get('/systemen/{id}/bewerken', [AccessGuardSystemController::class, 'edit'])->whereNumber('id')->name('systems.edit');
				Route::put('/systemen/{id}', [AccessGuardSystemController::class, 'update'])->whereNumber('id')->name('systems.update');
				Route::delete('/systemen/{id}', [AccessGuardSystemController::class, 'destroy'])->whereNumber('id')->name('systems.destroy');

				Route::get('/systemen/{systemId}/items', [AccessGuardAccessItemController::class, 'index'])->whereNumber('systemId')->name('systems.items.index');
				Route::get('/systemen/{systemId}/items/nieuw', [AccessGuardAccessItemController::class, 'create'])->whereNumber('systemId')->name('systems.items.create');
				Route::post('/systemen/{systemId}/items', [AccessGuardAccessItemController::class, 'store'])->whereNumber('systemId')->name('systems.items.store');
				Route::get('/systemen/{systemId}/items/{id}/bewerken', [AccessGuardAccessItemController::class, 'edit'])->whereNumber(['systemId', 'id'])->name('systems.items.edit');
				Route::put('/systemen/{systemId}/items/{id}', [AccessGuardAccessItemController::class, 'update'])->whereNumber(['systemId', 'id'])->name('systems.items.update');
				Route::delete('/systemen/{systemId}/items/{id}', [AccessGuardAccessItemController::class, 'destroy'])->whereNumber(['systemId', 'id'])->name('systems.items.destroy');

				Route::get('/reviews', [AccessGuardReviewController::class, 'index'])->name('reviews.index');
				Route::get('/reviews/nieuw', [AccessGuardReviewController::class, 'create'])->name('reviews.create');
				Route::post('/reviews', [AccessGuardReviewController::class, 'store'])->name('reviews.store');
				Route::get('/reviews/{id}', [AccessGuardReviewController::class, 'show'])->whereNumber('id')->name('reviews.show');
				Route::post('/reviews/{id}/complete', [AccessGuardReviewController::class, 'complete'])->whereNumber('id')->name('reviews.complete');
				Route::post('/reviews/{id}/annuleren', [AccessGuardReviewController::class, 'cancel'])->whereNumber('id')->name('reviews.cancel');
				Route::post('/reviews/{id}/items/{itemId}/beslissing', [AccessGuardReviewController::class, 'decide'])->whereNumber(['id', 'itemId'])->name('reviews.decide');
				Route::post('/reviews/{id}/items/bulk', [AccessGuardReviewController::class, 'bulkDecide'])->whereNumber('id')->name('reviews.bulk-decide');
				Route::post('/reviews/{id}/ai-suggest', [AccessGuardReviewController::class, 'aiSuggest'])->whereNumber('id')->name('reviews.ai-suggest');
				Route::get('/reviews/{id}/ai-summary', [AccessGuardReviewController::class, 'aiSummary'])->whereNumber('id')->name('reviews.ai-summary');

				Route::get('/acties', [AccessGuardReviewActionController::class, 'index'])->name('actions.index');
				Route::post('/acties/{id}/afgerond', [AccessGuardReviewActionController::class, 'markDone'])->whereNumber('id')->name('actions.done');
				Route::post('/acties/{id}/annuleren', [AccessGuardReviewActionController::class, 'cancel'])->whereNumber('id')->name('actions.cancel');

				Route::get('/processen', [AccessGuardProcessController::class, 'index'])->name('processes.index');
				Route::get('/processen/nieuw', [AccessGuardProcessController::class, 'create'])->name('processes.create');
				Route::post('/processen', [AccessGuardProcessController::class, 'store'])->name('processes.store');
				Route::get('/processen/{id}', [AccessGuardProcessController::class, 'show'])->whereNumber('id')->name('processes.show');
				Route::post('/processen/{id}/items/{itemId}', [AccessGuardProcessController::class, 'updateItem'])->whereNumber(['id', 'itemId'])->name('processes.update-item');
				Route::post('/processen/{id}/items/{itemId}/bewijs', [AccessGuardProcessController::class, 'uploadEvidence'])->whereNumber(['id', 'itemId'])->name('processes.upload-evidence');
				Route::get('/processen/{id}/bewijs/{evidenceId}', [AccessGuardProcessController::class, 'downloadEvidence'])->whereNumber(['id', 'evidenceId'])->name('processes.download-evidence');
				Route::delete('/processen/{id}/bewijs/{evidenceId}', [AccessGuardProcessController::class, 'deleteEvidence'])->whereNumber(['id', 'evidenceId'])->name('processes.delete-evidence');
				Route::post('/processen/{id}/complete', [AccessGuardProcessController::class, 'complete'])->whereNumber('id')->name('processes.complete');
				Route::post('/processen/{id}/annuleren', [AccessGuardProcessController::class, 'cancel'])->whereNumber('id')->name('processes.cancel');

				Route::get('/risicos', [AccessGuardRiskFlagController::class, 'index'])->name('risks.index');
				Route::post('/risicos/scan', [AccessGuardRiskFlagController::class, 'scanNow'])->name('risks.scan');
				Route::post('/risicos/{id}/bevestig', [AccessGuardRiskFlagController::class, 'acknowledge'])->whereNumber('id')->name('risks.acknowledge');
				Route::post('/risicos/{id}/oplossen', [AccessGuardRiskFlagController::class, 'resolve'])->whereNumber('id')->name('risks.resolve');
				Route::post('/risicos/{id}/heropen', [AccessGuardRiskFlagController::class, 'reopen'])->whereNumber('id')->name('risks.reopen');

				Route::get('/reminders', [AccessGuardReminderController::class, 'index'])->name('reminders.index');
				Route::post('/reminders/bouw', [AccessGuardReminderController::class, 'buildNow'])->name('reminders.build');
				Route::post('/reminders/{id}/klaar', [AccessGuardReminderController::class, 'markDone'])->whereNumber('id')->name('reminders.done');
				Route::post('/reminders/{id}/weg', [AccessGuardReminderController::class, 'dismiss'])->whereNumber('id')->name('reminders.dismiss');

				Route::get('/vault', [AccessGuardVaultController::class, 'index'])->name('vault.index');
				Route::get('/vault/nieuw', [AccessGuardVaultController::class, 'create'])->name('vault.create');
				Route::post('/vault', [AccessGuardVaultController::class, 'store'])->name('vault.store');
				Route::get('/vault/{id}', [AccessGuardVaultController::class, 'show'])->whereNumber('id')->name('vault.show');
				Route::get('/vault/{id}/bewerken', [AccessGuardVaultController::class, 'edit'])->whereNumber('id')->name('vault.edit');
				Route::put('/vault/{id}', [AccessGuardVaultController::class, 'update'])->whereNumber('id')->name('vault.update');
				Route::delete('/vault/{id}', [AccessGuardVaultController::class, 'destroy'])->whereNumber('id')->name('vault.destroy');
				Route::post('/vault/{id}/decrypt', [AccessGuardVaultController::class, 'decrypt'])->whereNumber('id')->name('vault.decrypt');
				Route::post('/vault/{id}/acl', [AccessGuardVaultController::class, 'grantAcl'])->whereNumber('id')->name('vault.grant-acl');
				Route::post('/vault/{id}/acl/{aclId}/intrekken', [AccessGuardVaultController::class, 'revokeAcl'])->whereNumber(['id', 'aclId'])->name('vault.revoke-acl');

				Route::post('/ai/explain', [AccessGuardAiExplainController::class, 'explain'])->name('ai.explain');

				Route::get('/notificaties', [AccessGuardNotificationSettingsController::class, 'edit'])->name('notifications.edit');
				Route::put('/notificaties', [AccessGuardNotificationSettingsController::class, 'update'])->name('notifications.update');

				Route::get('/data', [AccessGuardDataController::class, 'show'])->name('data.show');
				Route::get('/data/import', [AccessGuardDataController::class, 'importStart'])->name('data.import-start');
				Route::post('/data/import', [AccessGuardDataController::class, 'importUpload'])->name('data.import-upload');
				Route::get('/data/import/map', [AccessGuardDataController::class, 'importMap'])->name('data.import-map');
				Route::post('/data/import/commit', [AccessGuardDataController::class, 'importCommit'])->name('data.import-commit');
				Route::get('/data/export/matrix-wide', [AccessGuardDataController::class, 'exportMatrixWide'])->name('data.export-matrix-wide');
				Route::get('/data/export/matrix-long', [AccessGuardDataController::class, 'exportMatrixLong'])->name('data.export-matrix-long');
				Route::get('/data/export/cycle/{cycleId}', [AccessGuardDataController::class, 'exportCycleLog'])->whereNumber('cycleId')->name('data.export-cycle');

				Route::post('/data/import/ai-map', [AccessGuardDataController::class, 'aiSmartMap'])->name('data.ai-smart-map');
				Route::get('/data/screenshot', [AccessGuardDataController::class, 'screenshotStart'])->name('data.screenshot-start');
				Route::post('/data/screenshot', [AccessGuardDataController::class, 'screenshotUpload'])->name('data.screenshot-upload');

				Route::get('/profielen', [AccessGuardAccessProfileController::class, 'index'])->name('profiles.index');
				Route::get('/profielen/nieuw', [AccessGuardAccessProfileController::class, 'create'])->name('profiles.create');
				Route::post('/profielen', [AccessGuardAccessProfileController::class, 'store'])->name('profiles.store');
				Route::get('/profielen/{id}/bewerken', [AccessGuardAccessProfileController::class, 'edit'])->whereNumber('id')->name('profiles.edit');
				Route::put('/profielen/{id}', [AccessGuardAccessProfileController::class, 'update'])->whereNumber('id')->name('profiles.update');
				Route::delete('/profielen/{id}', [AccessGuardAccessProfileController::class, 'destroy'])->whereNumber('id')->name('profiles.destroy');
				Route::get('/profielen/{id}/toepassen', [AccessGuardAccessProfileController::class, 'applyForm'])->whereNumber('id')->name('profiles.apply-form');
				Route::post('/profielen/{id}/toepassen', [AccessGuardAccessProfileController::class, 'apply'])->whereNumber('id')->name('profiles.apply');
				Route::post('/profielen/{id}/toepassen-op-leden', [AccessGuardAccessProfileController::class, 'applyToMembers'])->whereNumber('id')->name('profiles.apply-to-members');

				Route::post('/feedback', [AccessGuardFeedbackController::class, 'submit'])->name('feedback.submit');

				Route::get('/directory', [AccessGuardDirectoryController::class, 'index'])->name('directory.index');
				Route::get('/directory/connect/m365', [AccessGuardDirectoryController::class, 'connect'])->name('directory.connect');
				Route::get('/directory/callback/m365', [AccessGuardDirectoryController::class, 'callback'])->name('directory.callback');
				Route::post('/directory/sync', [AccessGuardDirectoryController::class, 'syncNow'])->name('directory.sync');
				Route::post('/directory/disconnect', [AccessGuardDirectoryController::class, 'disconnect'])->name('directory.disconnect');
			});

			Route::middleware('auth')->prefix('radar')->name('radar.')->group(function () {
				Route::get('/', [RadarDashboardController::class, 'index'])->name('index');

				Route::get('/assets', [RadarAssetController::class, 'index'])->name('assets.index');
				Route::get('/assets/nieuw', [RadarAssetController::class, 'create'])->name('assets.create');
				Route::post('/assets', [RadarAssetController::class, 'store'])->name('assets.store');
				Route::get('/assets/{id}', [RadarAssetController::class, 'show'])->whereNumber('id')->name('assets.show');
				Route::delete('/assets/{id}', [RadarAssetController::class, 'destroy'])->whereNumber('id')->name('assets.destroy');
				Route::post('/assets/{id}/scan', [RadarAssetController::class, 'scan'])->whereNumber('id')->name('assets.scan');

				Route::post('/findings/{id}/status', [RadarFindingController::class, 'updateStatus'])->whereNumber('id')->name('findings.update-status');
			});

			Route::middleware('tool.limit:pdf_redact')->group(function () {
				Route::get('/pdf-redact', [PdfRedactController::class, 'show'])->name('pdf-redact');
				Route::post('/pdf-redact/upload', [PdfRedactController::class, 'upload'])->name('pdf-redact.upload');
				Route::get('/pdf-redact/preview/{page}', [PdfRedactController::class, 'preview'])->name('pdf-redact.preview');
				Route::post('/pdf-redact/apply', [PdfRedactController::class, 'apply'])->name('pdf-redact.apply');
				Route::get('/pdf-redact/download/{key}', [PdfRedactController::class, 'download'])->name('pdf-redact.download');
				Route::post('/pdf-redact/reset', [PdfRedactController::class, 'reset'])->name('pdf-redact.reset');
			});
		});

		Route::get('/diensten', [ServiceController::class, 'index'])->name('services.index');
		Route::get('/diensten/{slug}', [ServiceController::class, 'show'])->name('services.show');

		Route::get('/prijzen', [PricingController::class, 'show'])->name('pricing');

		Route::get('/contact', [ContactController::class, 'show'])->name('contact');
		Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
		Route::get('/contact/sent', [ContactController::class, 'sent'])->name('contact.sent');
	});

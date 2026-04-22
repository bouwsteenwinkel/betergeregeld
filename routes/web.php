<?php

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
use App\Http\Controllers\Tools\BookkeepingAuditLogController;
use App\Http\Controllers\Tools\BookkeepingCategoryController;
use App\Http\Controllers\Tools\BookkeepingController;
use App\Http\Controllers\Tools\BookkeepingReceiptController;
use App\Http\Controllers\Tools\BookkeepingRelationController;
use App\Http\Controllers\Tools\BookkeepingReportsController;
use App\Http\Controllers\Tools\BookkeepingVatRateController;
use App\Http\Controllers\Tools\DiffController;
use App\Http\Controllers\Tools\FaviconGeneratorController;
use App\Http\Controllers\Tools\IbanCheckController;
use App\Http\Controllers\Tools\IpLookupController;
use App\Http\Controllers\Tools\JsonFormatterController;
use App\Http\Controllers\Tools\PdfMergeController;
use App\Http\Controllers\Tools\PdfRedactController;
use App\Http\Controllers\Tools\PostcodeCheckController;
use App\Http\Controllers\Tools\SpeedTestController;
use App\Http\Controllers\Tools\VatCheckController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/' . config('app.locale', 'nl')));

// Webhooks live outside the locale prefix and must not use CSRF.
Route::post('/webhooks/mollie', [WebhookController::class, 'mollie'])->name('webhooks.mollie');

// Speedtest data endpoints live outside locale/CSRF — they carry raw bytes
// and are throttled per IP to protect the server from abuse.
Route::middleware('throttle:speedtest')->group(function () {
	Route::get('/tools/speedtest/ping', [SpeedTestController::class, 'ping'])->name('tools.speedtest.ping');
	Route::get('/tools/speedtest/download', [SpeedTestController::class, 'download'])->name('tools.speedtest.download');
	Route::post('/tools/speedtest/upload', [SpeedTestController::class, 'upload'])->name('tools.speedtest.upload');
});

Route::prefix('{locale}')
	->whereIn('locale', SetLocale::SUPPORTED)
	->middleware(SetLocale::class)
	->group(function () {
		Route::get('/', fn () => view('welcome'))->name('home');
		Route::get('/over', fn () => view('pages.about'))->name('about');

		Route::middleware('guest')->group(function () {
			Route::get('/login', [LoginController::class, 'show'])->name('login');
			Route::post('/login', [LoginController::class, 'login']);

			Route::get('/register', [RegisterController::class, 'show'])->name('register');
			Route::post('/register', [RegisterController::class, 'store']);
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
				Route::get('/rapporten/btw-aangifte', [BookkeepingReportsController::class, 'vatReturn'])->name('reports.vat');

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

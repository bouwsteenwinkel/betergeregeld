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
use App\Http\Controllers\Tools\AccessGuard\AccessItemController as AccessGuardAccessItemController;
use App\Http\Controllers\Tools\AccessGuard\AccessProfileController as AccessGuardAccessProfileController;
use App\Http\Controllers\Tools\AccessGuard\AiExplainController as AccessGuardAiExplainController;
use App\Http\Controllers\Tools\AccessGuard\DataController as AccessGuardDataController;
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
		Route::get('/accessguard', [AccessGuardLandingController::class, 'show'])->name('accessguard.landing');
		Route::get('/accessguard/handleiding', [AccessGuardHandleidingController::class, 'download'])->name('accessguard.handleiding');
		Route::get('/accessguard/notifications/unsubscribe/{token}', [AccessGuardNotificationSettingsController::class, 'unsubscribe'])->name('tools.accessguard.notifications.unsubscribe');

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

			Route::get('/shipping-rates', [ShippingRatesController::class, 'show'])->name('shipping-rates');

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

				Route::get('/profielen', [AccessGuardAccessProfileController::class, 'index'])->name('profiles.index');
				Route::get('/profielen/nieuw', [AccessGuardAccessProfileController::class, 'create'])->name('profiles.create');
				Route::post('/profielen', [AccessGuardAccessProfileController::class, 'store'])->name('profiles.store');
				Route::get('/profielen/{id}/bewerken', [AccessGuardAccessProfileController::class, 'edit'])->whereNumber('id')->name('profiles.edit');
				Route::put('/profielen/{id}', [AccessGuardAccessProfileController::class, 'update'])->whereNumber('id')->name('profiles.update');
				Route::delete('/profielen/{id}', [AccessGuardAccessProfileController::class, 'destroy'])->whereNumber('id')->name('profiles.destroy');
				Route::get('/profielen/{id}/toepassen', [AccessGuardAccessProfileController::class, 'applyForm'])->whereNumber('id')->name('profiles.apply-form');
				Route::post('/profielen/{id}/toepassen', [AccessGuardAccessProfileController::class, 'apply'])->whereNumber('id')->name('profiles.apply');
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

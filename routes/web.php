<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Settings\TwoFactorSettingsController;
use App\Http\Controllers\Tools\IbanCheckController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/' . config('app.locale', 'nl')));

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
			Route::get('/settings/2fa', [TwoFactorSettingsController::class, 'show'])->name('settings.2fa');
			Route::post('/settings/2fa/enable', [TwoFactorSettingsController::class, 'enable'])->name('settings.2fa.enable');
			Route::post('/settings/2fa/disable', [TwoFactorSettingsController::class, 'disable'])->name('settings.2fa.disable');
		});

		Route::prefix('tools')->name('tools.')->group(function () {
			Route::get('/iban-check', [IbanCheckController::class, 'show'])->name('iban-check');
			Route::post('/iban-check', [IbanCheckController::class, 'check'])->name('iban-check.check');
		});
	});

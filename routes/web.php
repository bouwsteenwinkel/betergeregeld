<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/' . config('app.locale', 'nl')));

Route::prefix('{locale}')
	->whereIn('locale', SetLocale::SUPPORTED)
	->middleware(SetLocale::class)
	->group(function () {
		Route::get('/', fn () => view('welcome'))->name('home');

		Route::middleware('guest')->group(function () {
			Route::get('/login', [LoginController::class, 'show'])->name('login');
			Route::post('/login', [LoginController::class, 'login']);

			Route::get('/register', [RegisterController::class, 'show'])->name('register');
			Route::post('/register', [RegisterController::class, 'store']);
			Route::get('/register/sent', [RegisterController::class, 'sent'])->name('register.sent');

			Route::get('/verify/{user}/{token}', [VerifyEmailController::class, 'verify'])
				->name('verify.email');
		});

		Route::post('/logout', [LoginController::class, 'logout'])
			->middleware('auth')
			->name('logout');
	});

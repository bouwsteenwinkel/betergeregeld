<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use App\Models\Agency;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Afgeschermd bureau-panel (white-label) op /bureau. Een bureau-beheerder
 * (role 'agency') logt hier in en ziet/beheert UITSLUITEND z'n eigen klanten —
 * dit panel registreert alleen de Bureau-resources, dus het kan principieel
 * niets van het platform tonen. Merknaam/logo komen per bureau van de agency.
 */
class BureauPanelProvider extends PanelProvider
{
	public function panel(Panel $panel): Panel
	{
		return $panel
			->id('bureau')
			->path('bureau')
			->login()
			->brandName(fn (): string => self::brandingAgency()?->name ?? 'Bureau-portal')
			->brandLogo(fn () => self::brandingAgency()?->logoUrl())
			->favicon(asset('favicon.ico'))
			->maxContentWidth(Width::Full)
			->sidebarWidth('15rem')
			->colors(['primary' => Color::Indigo])
			// White-label: overschrijf de primary-kleur per bureau (na auth, bij render)
			// met de merkkleur van de agency. Filament v5 emit --primary-{shade} als
			// oklch; we overschrijven dezelfde variabelen na Filaments eigen styles.
			->renderHook(PanelsRenderHook::HEAD_END, function (): string {
				$hex = self::brandingAgency()?->primary_color;
				if (! $hex || ! preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) {
					return '';
				}
				$vars = '';
				foreach (Color::hex($hex) as $shade => $value) {
					$vars .= "--primary-{$shade}:{$value};";
				}

				return '<style>:root{' . $vars . '}</style>';
			})
			->font('Inter')
			->discoverResources(in: app_path('Filament/Bureau/Resources'), for: 'App\\Filament\\Bureau\\Resources')
			->discoverPages(in: app_path('Filament/Bureau/Pages'), for: 'App\\Filament\\Bureau\\Pages')
			->pages([
				Dashboard::class,
			])
			->middleware([
				EncryptCookies::class,
				AddQueuedCookiesToResponse::class,
				StartSession::class,
				AuthenticateSession::class,
				ShareErrorsFromSession::class,
				PreventRequestForgery::class,
				SubstituteBindings::class,
				DisableBladeIconComponents::class,
				DispatchServingFilamentEvent::class,
			])
			->authMiddleware([
				Authenticate::class,
			]);
	}

	/**
	 * Het bureau dat de branding bepaalt: de agency van de ingelogde gebruiker,
	 * of — vóór inloggen — de agency die bij het subdomein hoort (white-label).
	 */
	private static function brandingAgency(): ?Agency
	{
		return auth()->user()?->agency ?? Agency::resolveFromHost(request()?->getHost());
	}
}

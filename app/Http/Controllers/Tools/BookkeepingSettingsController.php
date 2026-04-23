<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingTenantSettings;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookkeepingSettingsController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function edit(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$settings = BookkeepingTenantSettings::firstOrNew(['tenant_id' => $request->user()->tenant_id]);
		return view('tools.bookkeeping.settings.edit', ['settings' => $settings]);
	}

	public function update(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $request->validate([
			'company_name' => ['nullable', 'string', 'max:190'],
			'address' => ['nullable', 'string', 'max:190'],
			'postal_code' => ['nullable', 'string', 'max:16'],
			'city' => ['nullable', 'string', 'max:120'],
			'country' => ['nullable', 'string', 'size:2'],
			'kvk_number' => ['nullable', 'string', 'max:20'],
			'vat_number' => ['nullable', 'string', 'max:20'],
			'iban' => ['nullable', 'string', 'max:34'],
			'email' => ['nullable', 'email', 'max:190'],
			'phone' => ['nullable', 'string', 'max:50'],
			'default_payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
			'invoice_footer' => ['nullable', 'string', 'max:500'],
			'auto_reminders_enabled' => ['nullable', 'boolean'],
			'logo' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
		]) + [
			'auto_reminders_enabled' => (bool) $request->boolean('auto_reminders_enabled', false),
		];
		unset($data['logo']); // handled separately below

		$settings = BookkeepingTenantSettings::updateOrCreate(
			['tenant_id' => $request->user()->tenant_id],
			$data + ['tenant_id' => $request->user()->tenant_id],
		);

		if ($request->hasFile('logo')) {
			$this->storeLogo($settings, $request->file('logo'));
		}

		return redirect()->route('tools.bookkeeping.settings.edit', ['locale' => $locale])
			->with('bookkeeping_message', __('Instellingen opgeslagen.'));
	}

	public function destroyLogo(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$settings = BookkeepingTenantSettings::where('tenant_id', $request->user()->tenant_id)->firstOrFail();
		if ($settings->logo_path && is_file($settings->logo_path)) {
			@unlink($settings->logo_path);
		}
		$settings->update(['logo_path' => null]);
		return redirect()->route('tools.bookkeeping.settings.edit', ['locale' => $locale])
			->with('bookkeeping_message', __('Logo verwijderd.'));
	}

	public function viewLogo(Request $request, string $locale)
	{
		$this->mustHaveAccess($request);
		$settings = BookkeepingTenantSettings::where('tenant_id', $request->user()->tenant_id)->firstOrFail();
		abort_unless($settings->logo_path && is_file($settings->logo_path), 404);

		$ext = strtolower(pathinfo($settings->logo_path, PATHINFO_EXTENSION));
		$mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
		return response()->file($settings->logo_path, [
			'Content-Type' => $mime,
			'Cache-Control' => 'private, max-age=600',
		]);
	}

	private function storeLogo(BookkeepingTenantSettings $settings, $file): void
	{
		// Remove existing
		if ($settings->logo_path && is_file($settings->logo_path)) {
			@unlink($settings->logo_path);
		}

		$ext = strtolower($file->getClientOriginalExtension() ?: 'png');
		if ($ext === 'jpeg') {
			$ext = 'jpg';
		}

		$dir = storage_path('app/bookkeeping/logos/' . $settings->tenant_id);
		if (! is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		$path = $dir . '/logo.' . $ext;
		$file->move(dirname($path), basename($path));

		$settings->update(['logo_path' => $path]);
	}

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		abort_unless(
			$this->features->forUser($request->user())->bool('tool.bookkeeping.enabled'),
			402,
		);
	}
}

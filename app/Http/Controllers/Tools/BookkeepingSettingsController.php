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
		]);

		BookkeepingTenantSettings::updateOrCreate(
			['tenant_id' => $request->user()->tenant_id],
			$data + ['tenant_id' => $request->user()->tenant_id],
		);

		return redirect()->route('tools.bookkeeping.settings.edit', ['locale' => $locale])
			->with('bookkeeping_message', __('Instellingen opgeslagen.'));
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

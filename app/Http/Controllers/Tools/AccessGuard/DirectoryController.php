<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\DirectoryConnection;
use App\Services\AccessGuard\Directory\DirectorySyncService;
use App\Services\AccessGuard\Directory\MsGraphClient;
use App\Services\Features\FeatureResolver;
use Carbon\CarbonImmutable;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class DirectoryController extends Controller
{
	private const OAUTH_AUTHORIZE = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
	private const OAUTH_TOKEN = 'https://login.microsoftonline.com/%s/oauth2/v2.0/token';

	public function __construct(
		private readonly FeatureResolver $features,
		private readonly DirectorySyncService $sync,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$connection = DirectoryConnection::query()
			->where('tenant_id', $request->user()->tenant_id)
			->where('provider', 'm365')
			->first();

		return view('tools.accessguard.directory.index', [
			'connection' => $connection,
			'configured' => $this->msConfigured(),
		]);
	}

	public function connect(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);

		if (! $this->msConfigured()) {
			return back()->with('error', __('Microsoft OAuth is nog niet geconfigureerd op de server.'));
		}

		$state = Str::random(40);
		$request->session()->put('ag_ms_oauth_state', $state);

		$params = http_build_query([
			'client_id' => config('services.microsoft.client_id'),
			'response_type' => 'code',
			'redirect_uri' => config('services.microsoft.redirect_uri'),
			'response_mode' => 'query',
			'scope' => config('services.microsoft.scope', 'User.Read.All Directory.Read.All offline_access'),
			'state' => $state,
			'prompt' => 'select_account',
		]);

		return redirect()->away(self::OAUTH_AUTHORIZE . '?' . $params);
	}

	public function callback(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);

		if ($err = $request->query('error')) {
			$desc = $request->query('error_description', '');
			return redirect()
				->route('tools.accessguard.directory.index', ['locale' => $locale])
				->with('error', __('Verbinding mislukt: :err', ['err' => $err . ' ' . $desc]));
		}

		$sessionState = $request->session()->pull('ag_ms_oauth_state');
		if (! $sessionState || $sessionState !== $request->query('state')) {
			abort(400, 'OAuth state mismatch.');
		}

		$code = (string) $request->query('code');
		if ($code === '') {
			abort(400, 'Missing authorization code.');
		}

		$tokenResp = Http::withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->asForm()
			->post(sprintf(self::OAUTH_TOKEN, 'common'), [
				'client_id' => config('services.microsoft.client_id'),
				'client_secret' => config('services.microsoft.client_secret'),
				'code' => $code,
				'redirect_uri' => config('services.microsoft.redirect_uri'),
				'grant_type' => 'authorization_code',
				'scope' => config('services.microsoft.scope'),
			]);

		if ($tokenResp->failed()) {
			return redirect()
				->route('tools.accessguard.directory.index', ['locale' => $locale])
				->with('error', __('Token-exchange mislukt: :msg', ['msg' => $tokenResp->body()]));
		}

		$body = $tokenResp->json();
		$idPayload = $this->decodeIdToken($body['id_token'] ?? null);

		$tenantId = $request->user()->tenant_id;
		$conn = DirectoryConnection::query()
			->where('tenant_id', $tenantId)
			->where('provider', 'm365')
			->first() ?? new DirectoryConnection([
				'tenant_id' => $tenantId,
				'provider' => 'm365',
			]);

		$conn->external_tenant_id = $idPayload['tid'] ?? null;
		$conn->display_name = $idPayload['preferred_username'] ?? ($idPayload['email'] ?? null);
		$conn->access_token = $body['access_token'];
		if (! empty($body['refresh_token'])) {
			$conn->refresh_token = $body['refresh_token'];
		}
		$conn->expires_at = CarbonImmutable::now()->addSeconds((int) ($body['expires_in'] ?? 3600));
		$conn->scopes = $body['scope'] ?? null;
		$conn->status = 'connected';
		$conn->save();

		return redirect()
			->route('tools.accessguard.directory.index', ['locale' => $locale])
			->with('status', __('Microsoft 365 verbonden. Klik "Nu synchroniseren" om te starten.'));
	}

	public function syncNow(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);

		$conn = DirectoryConnection::query()
			->where('tenant_id', $request->user()->tenant_id)
			->where('provider', 'm365')
			->firstOrFail();

		try {
			$result = $this->sync->sync($conn, new MsGraphClient($conn));
			return redirect()
				->route('tools.accessguard.directory.index', ['locale' => $locale])
				->with('status', __('Sync klaar: :s gezien, :c nieuw, :u bijgewerkt.', [
					's' => $result['seen'], 'c' => $result['created'], 'u' => $result['updated'],
				]));
		} catch (RuntimeException $e) {
			return redirect()
				->route('tools.accessguard.directory.index', ['locale' => $locale])
				->with('error', __('Sync mislukt: :msg', ['msg' => $e->getMessage()]));
		}
	}

	public function disconnect(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);

		DirectoryConnection::query()
			->where('tenant_id', $request->user()->tenant_id)
			->where('provider', 'm365')
			->delete();

		return redirect()
			->route('tools.accessguard.directory.index', ['locale' => $locale])
			->with('status', __('Verbinding verwijderd. Gesyncte personen blijven staan.'));
	}

	protected function msConfigured(): bool
	{
		return (bool) config('services.microsoft.client_id')
			&& (bool) config('services.microsoft.client_secret')
			&& (bool) config('services.microsoft.redirect_uri');
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
		abort_unless($bag->bool('tool.accessguard.directory_sync'), 402);
	}

	private function decodeIdToken(?string $idToken): array
	{
		if (! $idToken) return [];
		$parts = explode('.', $idToken);
		if (count($parts) < 2) return [];
		$payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
		return is_array($payload) ? $payload : [];
	}
}

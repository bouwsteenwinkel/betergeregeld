<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\VaultAccessLog;
use App\Models\AccessGuard\VaultAcl;
use App\Models\AccessGuard\VaultCredential;
use App\Services\AccessGuard\VaultService;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VaultController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly VaultService $vault,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;
		$userId = (string) $request->user()->getAuthIdentifier();

		// All creds the user owns OR has an active ACL on.
		$acledIds = VaultAcl::query()
			->where('tenant_id', $tenantId)
			->where('user_id', $userId)
			->whereNull('revoked_at')
			->pluck('credential_id');

		$credentials = VaultCredential::query()
			->where('tenant_id', $tenantId)
			->where(function ($q) use ($userId, $acledIds) {
				$q->where('created_by_user_id', $userId)
					->orWhereIn('id', $acledIds);
			})
			->with('system:id,name', 'accessItem:id,name')
			->orderBy('name')
			->get();

		return view('tools.accessguard.vault.index', compact('credentials'));
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		return view('tools.accessguard.vault.form', [
			'credential' => new VaultCredential(['type' => 'password']),
			'systems' => BusinessSystem::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
			'items' => AccessItem::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'system_id', 'name']),
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validateData($request, creating: true);

		$cred = $this->vault->create(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
			$data['name'],
			$data['type'],
			$data['secret'],
			[
				'system_id' => $data['system_id'] ?? null,
				'access_item_id' => $data['access_item_id'] ?? null,
				'username' => $data['username'] ?? null,
				'expires_at' => $data['expires_at'] ?? null,
				'rotation_interval_days' => $data['rotation_interval_days'] ?? null,
				'metadata' => $data['metadata'] ?? null,
			],
			$request,
		);

		return redirect()
			->route('tools.accessguard.vault.show', ['locale' => $locale, 'id' => $cred->id])
			->with('status', __('Credential opgeslagen (versleuteld).'));
	}

	public function show(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;
		$userId = (string) $request->user()->getAuthIdentifier();

		$cred = $this->findAndAuthorize($request, $id);

		$acl = VaultAcl::query()
			->where('credential_id', $cred->id)
			->with([])
			->orderByDesc('granted_at')
			->get();

		// Resolve user-emails for the ACL + log displays
		$userIds = $acl->pluck('user_id')->merge([$cred->created_by_user_id])->unique()->all();
		$userEmails = DB::table('users')->whereIn('id', $userIds)->pluck('email', 'id')->all();

		$logs = VaultAccessLog::query()
			->where('tenant_id', $tenantId)
			->where('credential_id', $cred->id)
			->orderByDesc('occurred_at')
			->limit(50)
			->get();

		$canDecrypt = $this->vault->canDecrypt($tenantId, $userId, $cred);
		$canAdmin = $this->vault->canAdmin($tenantId, $userId, $cred);

		// Log the metadata view (not the secret — that's a separate endpoint)
		VaultAccessLog::create([
			'tenant_id' => $tenantId,
			'credential_id' => $cred->id,
			'user_id' => $userId,
			'action' => 'viewed',
			'ip_hash' => hash('sha256', ($request->ip() ?? '') . '|' . config('app.key')),
			'occurred_at' => now(),
		]);

		return view('tools.accessguard.vault.show', compact(
			'cred', 'acl', 'userEmails', 'logs', 'canDecrypt', 'canAdmin',
		));
	}

	public function edit(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;
		$userId = (string) $request->user()->getAuthIdentifier();

		$cred = $this->findAndAuthorize($request, $id);
		abort_unless($this->vault->canAdmin($tenantId, $userId, $cred), 403);

		return view('tools.accessguard.vault.form', [
			'credential' => $cred,
			'systems' => BusinessSystem::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
			'items' => AccessItem::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'system_id', 'name']),
		]);
	}

	public function update(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$cred = $this->findAndAuthorize($request, $id);

		$data = $this->validateData($request, creating: false);

		$this->vault->update(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
			$cred,
			array_intersect_key($data, array_flip([
				'name', 'type', 'username', 'system_id', 'access_item_id',
				'metadata', 'expires_at', 'rotation_interval_days',
			])),
			$data['secret'] ?? null,
			$request,
		);

		return redirect()
			->route('tools.accessguard.vault.show', ['locale' => $locale, 'id' => $cred->id])
			->with('status', __('Credential bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$cred = $this->findAndAuthorize($request, $id);

		$this->vault->delete(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
			$cred,
			$request,
		);

		return redirect()
			->route('tools.accessguard.vault.index', ['locale' => $locale])
			->with('status', __('Credential verwijderd.'));
	}

	public function decrypt(Request $request, string $locale, int $id): JsonResponse
	{
		$this->mustHaveAccess($request);
		$cred = $this->findAndAuthorize($request, $id);

		try {
			$plaintext = $this->vault->reveal(
				$request->user()->tenant_id,
				(string) $request->user()->getAuthIdentifier(),
				$cred,
				$request,
			);
		} catch (\RuntimeException $e) {
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 403);
		}

		return response()->json(['ok' => true, 'secret' => $plaintext]);
	}

	public function grantAcl(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$cred = $this->findAndAuthorize($request, $id);

		$data = $request->validate([
			'user_email' => ['required', 'email', 'max:190'],
			'can_view' => ['nullable', 'boolean'],
			'can_decrypt' => ['nullable', 'boolean'],
			'can_rotate' => ['nullable', 'boolean'],
			'can_admin' => ['nullable', 'boolean'],
		]);

		$target = DB::table('users')
			->where('tenant_id', $request->user()->tenant_id)
			->where('email', $data['user_email'])
			->first();
		if (! $target) {
			return back()->with('error', __('Gebruiker met dat e-mailadres niet gevonden binnen je tenant.'));
		}

		$this->vault->grant(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
			$cred,
			(string) $target->id,
			[
				'can_view' => $request->boolean('can_view', true),
				'can_decrypt' => $request->boolean('can_decrypt'),
				'can_rotate' => $request->boolean('can_rotate'),
				'can_admin' => $request->boolean('can_admin'),
			],
			$request,
		);

		return back()->with('status', __('Toegang verleend aan :email', ['email' => $data['user_email']]));
	}

	public function revokeAcl(Request $request, string $locale, int $id, int $aclId): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$cred = $this->findAndAuthorize($request, $id);

		$acl = VaultAcl::query()->where('credential_id', $cred->id)->findOrFail($aclId);
		$this->vault->revoke(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
			$acl,
			$request,
		);

		return back()->with('status', __('Toegang ingetrokken.'));
	}

	private function validateData(Request $request, bool $creating): array
	{
		$rules = [
			'name' => ['required', 'string', 'max:160'],
			'type' => ['required', 'in:password,token,api_key,ssh_key,cert,other'],
			'username' => ['nullable', 'string', 'max:190'],
			'system_id' => ['nullable', 'integer'],
			'access_item_id' => ['nullable', 'integer'],
			'secret' => [$creating ? 'required' : 'nullable', 'string', 'max:20000'],
			'expires_at' => ['nullable', 'date'],
			'rotation_interval_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
			'metadata' => ['nullable', 'array'],
		];
		return $request->validate($rules);
	}

	/**
	 * Find the credential and enforce tenant + at-least-view permission.
	 * Owner or any ACL row with can_view=1 is required.
	 */
	private function findAndAuthorize(Request $request, int $id): VaultCredential
	{
		$tenantId = $request->user()->tenant_id;
		$userId = (string) $request->user()->getAuthIdentifier();

		$cred = VaultCredential::query()
			->where('tenant_id', $tenantId)
			->findOrFail($id);

		abort_unless($this->vault->canView($tenantId, $userId, $cred), 403);
		return $cred;
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}

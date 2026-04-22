<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingCategory;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookkeepingCategoryController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$user = $request->user();

		$defaults = BookkeepingCategory::query()
			->whereNull('tenant_id')
			->orderBy('type')
			->orderBy('sort_order')
			->orderBy('name')
			->get();

		$own = BookkeepingCategory::query()
			->where('tenant_id', $user->tenant_id)
			->orderBy('type')
			->orderBy('sort_order')
			->orderBy('name')
			->get();

		return view('tools.bookkeeping.categories.index', [
			'defaults' => $defaults,
			'own' => $own,
		]);
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.bookkeeping.categories.form', [
			'category' => null,
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validated($request);
		$data['tenant_id'] = $request->user()->tenant_id;

		BookkeepingCategory::create($data);

		return redirect()->route('tools.bookkeeping.categories.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Categorie toegevoegd.'));
	}

	public function edit(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$category = $this->mustOwn($request, $id);
		return view('tools.bookkeeping.categories.form', [
			'category' => $category,
		]);
	}

	public function update(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$category = $this->mustOwn($request, $id);
		$category->update($this->validated($request));

		return redirect()->route('tools.bookkeeping.categories.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Categorie bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$category = $this->mustOwn($request, $id);
		$category->delete();

		return redirect()->route('tools.bookkeeping.categories.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Categorie verwijderd.'));
	}

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		abort_unless(
			$this->features->forUser($request->user())->bool('tool.bookkeeping.enabled'),
			402,
		);
	}

	private function mustOwn(Request $request, int $id): BookkeepingCategory
	{
		return BookkeepingCategory::query()
			->where('id', $id)
			->where('tenant_id', $request->user()->tenant_id)
			->firstOrFail();
	}

	private function validated(Request $request): array
	{
		return $request->validate([
			'name' => ['required', 'string', 'max:120'],
			'type' => ['required', 'in:expense,income,both'],
			'is_active' => ['nullable', 'boolean'],
			'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
		]) + [
			'is_active' => (bool) $request->boolean('is_active', true),
			'sort_order' => (int) ($request->input('sort_order') ?? 0),
		];
	}
}

<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Services\AccessGuard\Ai\CsvSmartMapper;
use App\Services\AccessGuard\Ai\ScreenshotIngestService;
use App\Services\AccessGuard\CsvExportService;
use App\Services\AccessGuard\CsvImportService;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly CsvImportService $importer,
		private readonly CsvExportService $exporter,
		private readonly CsvSmartMapper $smartMapper,
		private readonly ScreenshotIngestService $shots,
	) {}

	public function show(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.accessguard.data.index');
	}

	public function importStart(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$kind = $request->query('kind', CsvImportService::KIND_PEOPLE);
		abort_unless(in_array($kind, [CsvImportService::KIND_PEOPLE, CsvImportService::KIND_SYSTEMS]), 404);
		return view('tools.accessguard.data.import-start', [
			'kind' => $kind,
			'fields' => $this->importer->fieldsFor($kind),
		]);
	}

	public function importUpload(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $request->validate([
			'kind' => ['required', 'in:people,systems'],
			'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
		]);

		$contents = file_get_contents($request->file('file')->getRealPath());

		try {
			$result = $this->importer->preview(
				$request->user()->tenant_id,
				$data['kind'],
				$contents,
			);
		} catch (\Throwable $e) {
			return back()->with('error', $e->getMessage());
		}

		return redirect()->route('tools.accessguard.data.import-map', [
			'locale' => $locale,
			'key' => $result['key'],
		]);
	}

	public function importMap(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$key = $request->query('key');
		$stash = Cache::get($key);
		abort_unless($stash && $stash['tenant_id'] === $request->user()->tenant_id, 404);

		$kind = $stash['kind'];
		$headers = $stash['headers'];
		$preview = array_slice($stash['rows'], 0, 5);
		$suggested = $this->importer->suggestMapping($kind, $headers);
		$fields = $this->importer->fieldsFor($kind);

		return view('tools.accessguard.data.import-map', compact('key', 'kind', 'headers', 'preview', 'suggested', 'fields', 'stash'));
	}

	public function importCommit(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $request->validate([
			'key' => ['required', 'string'],
			'mapping' => ['required', 'array'],
		]);

		try {
			$result = $this->importer->commit(
				$request->user()->tenant_id,
				$data['key'],
				$data['mapping'],
				(string) $request->user()->getAuthIdentifier(),
			);
		} catch (\Throwable $e) {
			return back()->with('error', $e->getMessage());
		}

		$msg = __(':n toegevoegd, :u bijgewerkt', ['n' => $result['inserted'], 'u' => $result['updated']]);
		if ($result['errors']) {
			$msg .= '. ' . count($result['errors']) . ' fouten.';
		}

		return redirect()->route('tools.accessguard.data.show', ['locale' => $locale])
			->with('status', $msg)
			->with('import_errors', $result['errors']);
	}

	public function exportMatrixWide(Request $request, string $locale): StreamedResponse
	{
		$this->mustHaveAccess($request);
		return $this->exporter->matrixWide($request->user()->tenant_id);
	}

	public function exportMatrixLong(Request $request, string $locale): StreamedResponse
	{
		$this->mustHaveAccess($request);
		return $this->exporter->matrixLong($request->user()->tenant_id);
	}

	public function exportCycleLog(Request $request, string $locale, int $cycleId): StreamedResponse
	{
		$this->mustHaveAccess($request);
		return $this->exporter->cycleLog($request->user()->tenant_id, $cycleId);
	}

	public function importPublic(Request $request, string $locale): View
	{
		return $this->importStart($request, $locale);
	}

	public function aiSmartMap(Request $request, string $locale): JsonResponse
	{
		$this->mustHaveAccess($request);
		$this->mustHaveAi($request);

		$data = $request->validate(['key' => ['required', 'string']]);
		$stash = Cache::get($data['key']);
		abort_unless($stash && $stash['tenant_id'] === $request->user()->tenant_id, 404);

		try {
			$mapping = $this->smartMapper->suggest(
				$stash['kind'],
				$stash['headers'],
				array_slice($stash['rows'], 0, 5),
			);
		} catch (\Throwable $e) {
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 502);
		}

		return response()->json(['ok' => true, 'mapping' => $mapping]);
	}

	public function screenshotStart(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$this->mustHaveAi($request);
		return view('tools.accessguard.data.screenshot-start');
	}

	public function screenshotUpload(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$this->mustHaveAi($request);

		$request->validate([
			'file' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
		]);

		try {
			$extract = $this->shots->extract($request->file('file'));
		} catch (\Throwable $e) {
			return back()->with('error', $e->getMessage());
		}

		if (empty($extract['users'])) {
			return back()->with('error', __('Geen gebruikers gedetecteerd in de screenshot.'));
		}

		$preview = $this->shots->toCsvPreview($extract['users']);

		$key = 'ag-csv-import:' . $request->user()->tenant_id . ':' . Str::uuid();
		Cache::put($key, [
			'kind' => CsvImportService::KIND_PEOPLE,
			'tenant_id' => $request->user()->tenant_id,
			'headers' => $preview['headers'],
			'rows' => $preview['rows'],
			'source_app' => $extract['source_app'],
		], now()->addMinutes(30));

		return redirect()
			->route('tools.accessguard.data.import-map', ['locale' => $locale, 'key' => $key])
			->with('status', __(':n gebruikers gedetecteerd uit :app. Controleer de mapping en klik Importeren.', [
				'n' => count($extract['users']),
				'app' => $extract['source_app'],
			]));
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}

	protected function mustHaveAi(Request $request): void
	{
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.ai_explain'), 402);
		abort_unless(config('accessguard.ai.enabled', true), 503);
	}
}

<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\Features\FeatureResolver;
use App\Services\FaviconGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FaviconGeneratorController extends Controller
{
	/** Canonical set presented on the result page. */
	private const DISPLAYED_SIZES = [16, 32, 48, 180, 192, 512];

	/** Free plan still gets the core set — upgrade unlocks extras. */
	private const FREE_SIZES = [16, 32, 180, 192];

	public function __construct(
		private readonly FaviconGenerator $gen,
		private readonly FeatureResolver $features,
	) {}

	public function show(Request $request, string $locale): View
	{
		return view('tools.favicon-generator', [
			'result' => null,
			'features' => $this->features->forRequest($request->user()),
		]);
	}

	public function generate(Request $request, string $locale): View|RedirectResponse
	{
		$request->validate([
			'source' => ['required', 'file', 'image', 'max:5120'],
		]);

		$bag = $this->features->forRequest($request->user());
		$allSizes = $bag->bool('tool.favicon.all_sizes');
		$sizes = $allSizes ? self::DISPLAYED_SIZES : self::FREE_SIZES;

		$key = Str::uuid()->toString();
		$workDir = storage_path('app/favicon-gen/' . $key);
		$uploaded = $request->file('source');
		$sourcePath = $workDir . '/source.' . strtolower($uploaded->getClientOriginalExtension() ?: 'png');
		if (! is_dir($workDir)) {
			mkdir($workDir, 0755, true);
		}
		$uploaded->move(dirname($sourcePath), basename($sourcePath));

		$produced = $this->gen->generate($sourcePath, $workDir, $sizes);
		$zipPath = $workDir . '/favicons.zip';
		$this->gen->zip($produced, $zipPath);

		$request->session()->put('favicon_gen_' . $key, [
			'workDir' => $workDir,
			'files' => $produced,
			'zip' => $zipPath,
			'sizes' => $sizes,
			'all_sizes' => $allSizes,
		]);

		ToolEvent::create([
			'tool' => 'favicon-generator',
			'event' => 'generate',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'size_count' => count($produced),
				'all_sizes' => $allSizes,
			],
		]);

		return redirect()->route('tools.favicon-generator.result', ['locale' => $locale, 'key' => $key]);
	}

	public function result(Request $request, string $locale, string $key): View|RedirectResponse
	{
		$state = $request->session()->get('favicon_gen_' . $key);
		if (! $state) {
			return redirect()->route('tools.favicon-generator', ['locale' => $locale]);
		}

		return view('tools.favicon-generator', [
			'result' => $state + ['key' => $key],
			'features' => $this->features->forRequest($request->user()),
		]);
	}

	public function download(Request $request, string $locale, string $key, string $what): BinaryFileResponse
	{
		$state = $request->session()->get('favicon_gen_' . $key);
		abort_unless($state, 404);

		if ($what === 'zip') {
			return response()->download($state['zip'], 'favicons.zip');
		}
		if ($what === 'ico' && isset($state['files']['ico'])) {
			return response()->download($state['files']['ico'], 'favicon.ico');
		}
		if (ctype_digit($what) && isset($state['files'][(int) $what])) {
			return response()->download($state['files'][(int) $what], 'favicon-' . $what . 'x' . $what . '.png');
		}
		abort(404);
	}
}

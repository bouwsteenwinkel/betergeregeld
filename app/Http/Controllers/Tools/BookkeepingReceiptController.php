<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingTransaction;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookkeepingReceiptController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function download(Request $request, string $locale, string $id): BinaryFileResponse
	{
		$this->mustHaveAccess($request);
		$tx = $this->mustOwn($request, $id);

		abort_unless($tx->receipt_path && is_file($tx->receipt_path), 404);

		$filename = 'bonnetje-' . $tx->transaction_date->format('Y-m-d') . '.' . pathinfo($tx->receipt_path, PATHINFO_EXTENSION);
		return response()->download($tx->receipt_path, $filename);
	}

	public function view(Request $request, string $locale, string $id): BinaryFileResponse
	{
		$this->mustHaveAccess($request);
		$tx = $this->mustOwn($request, $id);

		abort_unless($tx->receipt_path && is_file($tx->receipt_path), 404);

		$ext = strtolower(pathinfo($tx->receipt_path, PATHINFO_EXTENSION));
		$mime = match ($ext) {
			'pdf' => 'application/pdf',
			'jpg', 'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			default => 'application/octet-stream',
		};

		return response()->file($tx->receipt_path, [
			'Content-Type' => $mime,
			'Cache-Control' => 'private, max-age=600',
		]);
	}

	public function destroy(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tx = $this->mustOwn($request, $id);

		if ($tx->receipt_path && is_file($tx->receipt_path)) {
			@unlink($tx->receipt_path);
		}
		$tx->update(['receipt_path' => null]);

		return redirect()
			->route('tools.bookkeeping.edit', ['locale' => $locale, 'id' => $tx->id])
			->with('bookkeeping_message', __('Bonnetje verwijderd.'));
	}

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		abort_unless(
			$this->features->forUser($request->user())->bool('tool.bookkeeping.enabled'),
			402,
		);
	}

	private function mustOwn(Request $request, string $id): BookkeepingTransaction
	{
		return BookkeepingTransaction::query()
			->where('id', $id)
			->where('tenant_id', $request->user()->tenant_id)
			->firstOrFail();
	}
}

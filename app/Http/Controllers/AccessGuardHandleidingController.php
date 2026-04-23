<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessGuardHandleidingController extends Controller
{
	public function download(Request $request, string $locale): Response
	{
		$isEn = $locale === 'en';

		$pdf = Pdf::loadView('pages.accessguard-handleiding', [
			'isEn' => $isEn,
			'locale' => $locale,
			'generatedAt' => now()->format('d-m-Y'),
		])
			->setPaper('a4')
			->setOption('isRemoteEnabled', false)
			->setOption('isHtml5ParserEnabled', true)
			->setOption('defaultFont', 'DejaVu Sans');

		$filename = $isEn ? 'AccessGuard-manual.pdf' : 'AccessGuard-handleiding.pdf';

		return $pdf->stream($filename);
	}
}

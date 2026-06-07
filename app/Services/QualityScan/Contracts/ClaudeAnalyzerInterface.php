<?php

namespace App\Services\QualityScan\Contracts;

use App\Services\QualityScan\Data\ExtractedContent;

interface ClaudeAnalyzerInterface
{
	/**
	 * Analyseer het pakket met de AI. Geeft een gestructureerd resultaat:
	 *   ['ok'=>bool, 'findings'=>Finding[], 'free_observations'=>string[],
	 *    'model'=>?string, 'input_tokens'=>?int, 'output_tokens'=>?int, 'error'=>?string]
	 */
	public function analyze(ExtractedContent $content): array;
}

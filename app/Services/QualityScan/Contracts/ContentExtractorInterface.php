<?php

namespace App\Services\QualityScan\Contracts;

use App\Services\QualityScan\Data\ExtractedContent;
use App\Services\QualityScan\Data\FetchResult;

interface ContentExtractorInterface
{
	public function extract(FetchResult $result): ExtractedContent;
}

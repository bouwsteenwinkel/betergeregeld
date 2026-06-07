<?php

namespace App\Services\QualityScan\Contracts;

use App\Services\QualityScan\Data\FetchResult;

interface PageFetcherInterface
{
	/** @throws \App\Services\QualityScan\Exceptions\PageFetchException */
	public function fetch(string $url): FetchResult;
}

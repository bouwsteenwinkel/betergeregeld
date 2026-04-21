<?php

return [
	'qpdf_path' => env('QPDF_PATH', base_path('bin/qpdf/qpdf.exe')),
	// Absolute ceilings, regardless of plan. Per-plan caps live in plan_features.
	'hard_max_files' => env('PDF_HARD_MAX_FILES', 200),
	'hard_max_total_mb' => env('PDF_HARD_MAX_TOTAL_MB', 1024),
];

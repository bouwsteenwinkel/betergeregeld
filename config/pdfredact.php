<?php

return [
	'gs_path' => env('GS_PATH'), // auto-detect if null
	'render_dpi' => 150,         // page preview resolution
	'hard_max_pages' => 100,     // refuse PDFs with more than this
	'hard_max_mb' => 50,         // refuse uploads larger than this
];

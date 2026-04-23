<?php

namespace App\Services\AccessGuard\Ai;

use App\Services\AccessGuard\CsvImportService;

/**
 * AI-assisted CSV column mapping. Given headers + first rows of a
 * user's CSV, ask the LLM to map each column to one of our known
 * target fields (or null for "ignore").
 *
 * Response schema: {"mapping": {"<column_index_as_string>": "<field_key_or_null>"}}
 */
class CsvSmartMapper
{
	private const RESPONSE_SCHEMA = [
		'type' => 'object',
		'additionalProperties' => false,
		'required' => ['mapping'],
		'properties' => [
			'mapping' => [
				'type' => 'object',
				'additionalProperties' => [
					'type' => ['string', 'null'],
				],
			],
		],
	];

	public function __construct(private readonly OpenAiClient $client) {}

	/**
	 * @return array<int,?string> column_index → target_field_key (or null = ignore)
	 */
	public function suggest(string $kind, array $headers, array $sampleRows): array
	{
		$fields = $kind === CsvImportService::KIND_PEOPLE
			? array_keys(CsvImportService::FIELDS_PEOPLE)
			: array_keys(CsvImportService::FIELDS_SYSTEMS);

		$rowsPreview = array_slice($sampleRows, 0, 5);

		$system = "You are a data-mapping assistant for AccessGuard. "
			. "Given CSV headers and a few example rows, decide which column maps to which known field. "
			. "Return JSON only, matching the schema: {\"mapping\": {\"0\": \"first_name\", \"1\": null, ...}}. "
			. "Use null for columns that don't fit any known field.";

		$user = "Target fields for kind '{$kind}':\n"
			. implode(', ', $fields) . "\n\n"
			. "Headers (with index):\n"
			. json_encode(array_values($headers), JSON_UNESCAPED_UNICODE) . "\n\n"
			. "Sample rows:\n"
			. json_encode($rowsPreview, JSON_UNESCAPED_UNICODE);

		$result = $this->client->chat($system, $user, self::RESPONSE_SCHEMA);
		$mapping = $result['json']['mapping'] ?? [];

		// Normalise: only keep keys that are valid integer indices into the header list
		// and values that are one of our fields or null.
		$out = [];
		foreach ($mapping as $idx => $field) {
			if (! is_numeric($idx)) continue;
			$idx = (int) $idx;
			if (! isset($headers[$idx])) continue;
			if ($field !== null && ! in_array($field, $fields, true)) continue;
			$out[$idx] = $field;
		}
		return $out;
	}
}

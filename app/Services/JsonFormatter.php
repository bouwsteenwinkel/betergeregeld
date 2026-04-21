<?php

namespace App\Services;

use Throwable;

class JsonFormatter
{
	public function run(string $raw, string $mode = 'format', int $indent = 2, bool $sortKeys = false): array
	{
		$rawTrim = trim($raw);
		if ($rawTrim === '') {
			return ['ok' => false, 'error' => 'json.empty', 'suggestions' => []];
		}

		$indent = max(2, min(8, $indent));

		try {
			$decoded = json_decode($rawTrim, true, 512, JSON_THROW_ON_ERROR);
		} catch (Throwable $e) {
			$msg = $e->getMessage();
			$pos = null;
			if (preg_match('/position\s+(\d+)/i', $msg, $m)) {
				$pos = (int) $m[1];
			}
			$lineCol = $pos !== null ? $this->posToLineCol($rawTrim, $pos) : ['line' => null, 'col' => null];

			return [
				'ok' => false,
				'error' => $msg,
				'details' => [
					'position' => $pos,
					'line' => $lineCol['line'],
					'col' => $lineCol['col'],
				],
				'suggestions' => $this->suggestionsFor($msg),
			];
		}

		if ($sortKeys) {
			$decoded = $this->sortKeysRecursive($decoded);
		}

		if ($mode === 'validate') {
			return [
				'ok' => true,
				'mode' => 'validate',
				'valid' => true,
				'stats' => ['bytes_in' => strlen($rawTrim)],
			];
		}

		if ($mode === 'minify') {
			$min = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			return [
				'ok' => true,
				'mode' => 'minify',
				'output' => $min,
				'stats' => ['bytes_in' => strlen($rawTrim), 'bytes_out' => strlen((string) $min)],
			];
		}

		$pretty = $this->prettyEncode($decoded, $indent);

		return [
			'ok' => true,
			'mode' => 'format',
			'output' => $pretty,
			'stats' => [
				'bytes_in' => strlen($rawTrim),
				'bytes_out' => strlen((string) $pretty),
				'indent' => $indent,
				'sorted' => $sortKeys,
			],
		];
	}

	private function prettyEncode(mixed $data, int $indent): ?string
	{
		$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		if ($json === false) {
			return null;
		}
		if ($indent !== 4) {
			$json = preg_replace_callback('/\n( +)/', function ($m) use ($indent) {
				$level = (int) (strlen($m[1]) / 4);
				return "\n" . str_repeat(' ', $level * $indent);
			}, $json);
		}
		return $json;
	}

	private function sortKeysRecursive(mixed $value): mixed
	{
		if (is_array($value)) {
			if ($this->isAssoc($value)) {
				ksort($value);
			}
			foreach ($value as $k => $v) {
				$value[$k] = $this->sortKeysRecursive($v);
			}
		}
		return $value;
	}

	private function isAssoc(array $arr): bool
	{
		$i = 0;
		foreach ($arr as $k => $v) {
			if ($k !== $i) {
				return true;
			}
			$i++;
		}
		return false;
	}

	private function posToLineCol(string $s, int $pos): array
	{
		$pos = max(0, min(strlen($s), $pos));
		$before = substr($s, 0, $pos);
		$line = substr_count($before, "\n") + 1;
		$lastNl = strrpos($before, "\n");
		$col = $lastNl === false ? ($pos + 1) : ($pos - $lastNl);
		return ['line' => $line, 'col' => $col];
	}

	private function suggestionsFor(string $msg): array
	{
		$m = strtolower($msg);
		$out = [];
		if (str_contains($m, 'unexpected') || str_contains($m, 'syntax')) {
			$out[] = 'json.suggest_missing_comma';
			$out[] = 'json.suggest_quotes';
			$out[] = 'json.suggest_brackets';
		}
		if (str_contains($m, 'malformed')) {
			$out[] = 'json.suggest_quote_char';
		}
		if (str_contains($m, 'depth')) {
			$out[] = 'json.suggest_depth';
		}
		if (empty($out)) {
			$out[] = 'json.suggest_generic';
		}
		return $out;
	}
}

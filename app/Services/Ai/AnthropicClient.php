<?php

namespace App\Services\Ai;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper rond Anthropic Messages API. Geen externe SDK — raw HTTP.
 *
 * Key-resolutie (in volgorde):
 *   1. ANTHROPIC_API_KEY env-var
 *   2. Pad uit ANTHROPIC_KEY_PATH (PHP-config-file met return ['api_key' => ...])
 *   3. Lokale dev-fallback: ../bouwsteenwinkel_v3/config/anthropic.php
 *
 * Default models per use-case:
 *   - writer:     claude-opus-4-7   (kwalitatief beste, content-generation)
 *   - translator: claude-sonnet-4-6 (goedkoper, nl↔en vertalen)
 *
 * Prompt caching aangezet voor system-prompts > 1024 tokens.
 */
class AnthropicClient
{
	public const API_URL = 'https://api.anthropic.com/v1/messages';
	public const API_VERSION = '2023-06-01';

	public ?array $lastResponse = null;
	public ?string $lastError = null;

	public function apiKey(): string
	{
		// TIJDELIJKE DIAGNOSE — verwijderen zodra het key-mysterie is opgelost.
		\Illuminate\Support\Facades\Log::warning('AIKEY-DIAG', [
			'sapi'         => PHP_SAPI,
			'pid'          => getmypid(),
			'cfg_len'      => strlen((string) config('services.anthropic.api_key')),
			'env_len'      => strlen((string) env('ANTHROPIC_API_KEY')),
			'cached'       => app()->configurationIsCached(),
			'base'         => base_path(),
			'storage_file' => is_file(storage_path('app/anthropic.php')),
			'storage_path' => storage_path('app/anthropic.php'),
		]);

		// config() i.p.v. env() zodat de key na `config:cache` op prod
		// gevonden blijft (env() buiten config-files geeft dan null).
		// env() blijft als fallback voor niet-gecachete omgevingen.
		$key = (string) (config('services.anthropic.api_key') ?: env('ANTHROPIC_API_KEY', ''));
		if ($key !== '') {
			return $key;
		}

		$path = (string) (config('services.anthropic.key_path') ?: env('ANTHROPIC_KEY_PATH', ''));
		if ($path === '') {
			// Fallback-bestanden (geen .env nodig): drop een config-file met
			// return ['api_key' => 'sk-ant-...'] op een van deze paden.
			//   1. storage/app/anthropic.php — productie-vriendelijk, gitignored,
			//      overleeft deploys en config:cache.
			//   2. ../bouwsteenwinkel_v3/config/anthropic.php — lokale dev-fallback.
			foreach ([storage_path('app/anthropic.php'), base_path('../bouwsteenwinkel_v3/config/anthropic.php')] as $candidate) {
				if (is_file($candidate)) {
					$path = $candidate;
					break;
				}
			}
		}
		if ($path !== '' && is_file($path)) {
			$cfg = require $path;
			if (is_array($cfg) && !empty($cfg['api_key'])) {
				return (string) $cfg['api_key'];
			}
		}
		throw new RuntimeException('Anthropic API key niet gevonden. Zet ANTHROPIC_API_KEY in .env of ANTHROPIC_KEY_PATH naar een config-file met return ["api_key"=>"sk-ant-..."].');
	}

	public function writerModel(): string
	{
		return (string) env('ANTHROPIC_MODEL_WRITER', 'claude-opus-4-7');
	}

	public function translatorModel(): string
	{
		return (string) env('ANTHROPIC_MODEL_TRANSLATOR', 'claude-sonnet-4-6');
	}

	/**
	 * Roep /v1/messages aan met tool_use: Claude vult de tool met
	 * gestructureerde input, gegarandeerd valide JSON. Veiliger dan
	 * de tekst-extract-route bij content met HTML / aanhalingstekens.
	 *
	 * @param array{model:string,system?:string,user:string,max_tokens?:int,tool_name:string,tool_input_schema:array} $opts
	 * @return array|null  De `input` van de tool_use-block (assoc array), of null bij fout
	 */
	public function structuredCall(array $opts): ?array
	{
		$this->lastError = null;
		$this->lastResponse = null;

		$payload = [
			'model'      => $opts['model'] ?? $this->writerModel(),
			'max_tokens' => $opts['max_tokens'] ?? 8000,
			'messages'   => [['role' => 'user', 'content' => $opts['user']]],
			'tools'      => [[
				'name'         => $opts['tool_name'],
				'description'  => $opts['tool_description'] ?? 'Submit the result via this tool — do not respond in chat-text.',
				'input_schema' => $opts['tool_input_schema'],
			]],
			'tool_choice' => ['type' => 'tool', 'name' => $opts['tool_name']],
		];
		if (!empty($opts['system'])) {
			$payload['system'] = [[
				'type' => 'text',
				'text' => $opts['system'],
				'cache_control' => ['type' => 'ephemeral'],
			]];
		}

		try {
			$resp = Http::timeout(180)
				->withHeaders([
					'x-api-key'         => $this->apiKey(),
					'anthropic-version' => self::API_VERSION,
					'content-type'      => 'application/json',
				])
				->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
				->post(self::API_URL, $payload);

			if (!$resp->ok()) {
				$body = $resp->json();
				$this->lastError = 'Anthropic HTTP ' . $resp->status() . ': ' . ($body['error']['message'] ?? substr($resp->body(), 0, 200));
				return null;
			}

			$json = $resp->json();
			$this->lastResponse = $json;
			foreach ($json['content'] ?? [] as $block) {
				if (($block['type'] ?? '') === 'tool_use' && is_array($block['input'] ?? null)) {
					return $block['input'];
				}
			}
			$this->lastError = 'Tool_use block ontbreekt in response.';
			return null;
		} catch (\Throwable $e) {
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	/**
	 * Roep /v1/messages aan met een eenvoudige user-prompt + optionele
	 * system-prompt. Returnt de tekst-content van de eerste content-block,
	 * of null bij fout (lastError gezet).
	 *
	 * @param array{model:string,system?:string,user:string,max_tokens?:int,temperature?:float} $opts
	 */
	public function chat(array $opts): ?string
	{
		$this->lastError = null;
		$this->lastResponse = null;

		$payload = [
			'model'      => $opts['model'] ?? $this->writerModel(),
			'max_tokens' => $opts['max_tokens'] ?? 4096,
			'messages'   => [['role' => 'user', 'content' => $opts['user']]],
		];
		if (!empty($opts['system'])) {
			// Met cache_control aan: prompt-caching gaat aan zodra de
			// system-prompt > 1024 tokens is. Op kleinere prompts is dit
			// een no-op met negligible overhead.
			$payload['system'] = [[
				'type' => 'text',
				'text' => $opts['system'],
				'cache_control' => ['type' => 'ephemeral'],
			]];
		}
		if (isset($opts['temperature'])) {
			$payload['temperature'] = (float) $opts['temperature'];
		}

		try {
			$resp = Http::timeout(180)
				->withHeaders([
					'x-api-key'         => $this->apiKey(),
					'anthropic-version' => self::API_VERSION,
					'content-type'      => 'application/json',
				])
				->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
				->post(self::API_URL, $payload);

			if (!$resp->ok()) {
				$body = $resp->json();
				$this->lastError = 'Anthropic HTTP ' . $resp->status() . ': ' . ($body['error']['message'] ?? substr($resp->body(), 0, 200));
				return null;
			}

			$json = $resp->json();
			$this->lastResponse = $json;
			$text = '';
			foreach ($json['content'] ?? [] as $block) {
				if (($block['type'] ?? '') === 'text' && !empty($block['text'])) {
					$text .= $block['text'];
				}
			}
			return $text !== '' ? $text : null;
		} catch (\Throwable $e) {
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	/**
	 * Tokens uit lastResponse (voor cost-tracking).
	 *
	 * @return array{input:int,output:int,cache_create?:int,cache_read?:int}
	 */
	public function lastUsage(): array
	{
		$u = $this->lastResponse['usage'] ?? [];
		return [
			'input'        => (int) ($u['input_tokens'] ?? 0),
			'output'       => (int) ($u['output_tokens'] ?? 0),
			'cache_create' => (int) ($u['cache_creation_input_tokens'] ?? 0),
			'cache_read'   => (int) ($u['cache_read_input_tokens'] ?? 0),
		];
	}
}

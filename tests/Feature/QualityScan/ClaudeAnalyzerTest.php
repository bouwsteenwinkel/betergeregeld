<?php

namespace Tests\Feature\QualityScan;

use App\Services\Ai\AnthropicClient;
use App\Services\QualityScan\ClaudeAnalyzer;
use App\Services\QualityScan\Data\ExtractedContent;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClaudeAnalyzerTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		// API-key zodat AnthropicClient::apiKey() niet gooit; echte requests worden gefaket.
		putenv('ANTHROPIC_API_KEY=sk-ant-test');
		$_ENV['ANTHROPIC_API_KEY'] = 'sk-ant-test';
		$_SERVER['ANTHROPIC_API_KEY'] = 'sk-ant-test';
		config(['quality-scan.ai.enabled' => true]);
	}

	private function content(): ExtractedContent
	{
		return new ExtractedContent('Titel', 'Omschrijving', null, null, [], null, 'nl', [], [], [], 'tekst', null, 'https://klant.nl/', true);
	}

	private function apiResponse(string $modelText): array
	{
		return [
			'model'   => 'claude-haiku-4-5-20251001',
			'content' => [['type' => 'text', 'text' => $modelText]],
			'usage'   => ['input_tokens' => 120, 'output_tokens' => 60],
		];
	}

	private function validJson(): string
	{
		// Met markdown-codefence eromheen, om de defensieve parse te testen.
		return "```json\n" . json_encode([
			'score'  => 80,
			'checks' => [[
				'id' => 'ai_taalfout', 'status' => 'warn', 'severity' => 'middel',
				'finding' => 'Een afgebroken zin gevonden.', 'suggestion' => 'Maak de zin af.', 'element' => 'Wij bieden de',
			]],
			'vrije_observaties' => ['De toon is consistent.'],
		]) . "\n```";
	}

	public function test_geldige_json_levert_findings_en_tokens(): void
	{
		Http::fake(['api.anthropic.com/*' => Http::response($this->apiResponse($this->validJson()), 200)]);

		$result = (new ClaudeAnalyzer(new AnthropicClient()))->analyze($this->content());

		$this->assertTrue($result['ok']);
		$this->assertCount(1, $result['findings']);
		$this->assertSame('ai_taalfout', $result['findings'][0]->checkId);
		$this->assertSame('ai', $result['findings'][0]->source);
		$this->assertSame(120, $result['input_tokens']);
		$this->assertSame(60, $result['output_tokens']);
		$this->assertSame(['De toon is consistent.'], $result['free_observations']);
		Http::assertSentCount(1);
	}

	public function test_ongeldige_json_doet_exact_een_retry(): void
	{
		Http::fakeSequence('api.anthropic.com/*')
			->push($this->apiResponse('dit is geen json'), 200)
			->push($this->apiResponse($this->validJson()), 200);

		$result = (new ClaudeAnalyzer(new AnthropicClient()))->analyze($this->content());

		$this->assertTrue($result['ok']);
		$this->assertCount(1, $result['findings']);
		Http::assertSentCount(2); // eerste poging + exact één retry
	}

	public function test_tweemaal_ongeldige_json_faalt_netjes(): void
	{
		Http::fake(['api.anthropic.com/*' => Http::response($this->apiResponse('nog steeds geen json'), 200)]);

		$result = (new ClaudeAnalyzer(new AnthropicClient()))->analyze($this->content());

		$this->assertFalse($result['ok']);
		$this->assertEmpty($result['findings']);
		$this->assertNotNull($result['error']);
		Http::assertSentCount(2);
	}
}

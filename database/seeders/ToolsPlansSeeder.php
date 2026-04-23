<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class ToolsPlansSeeder extends Seeder
{
	public function run(): void
	{
		$plans = [
			[
				'plan_key' => 'tools_free',
				'name' => 'Free',
				'price_monthly' => 0,
				'price_yearly' => 0,
				'trial_days' => 0,
				'sort_order' => 10,
				'features' => [
					'seats' => '1',
					'api.enabled' => '0',
					'history.retention_days' => '7',

					'tool.iban.daily.anon' => '10',
					'tool.iban.daily.user' => '50',
					'tool.iban.risk_detail' => '0',
					'tool.iban.bulk' => '0',

					'tool.vies.daily.anon' => '10',
					'tool.vies.daily.user' => '50',
					'tool.vies.history_days' => '0',
					'tool.vies.bulk' => '0',

					'tool.postcode.daily.anon' => '20',
					'tool.postcode.daily.user' => '100',
					'tool.postcode.bulk' => '0',

					'tool.ip_lookup.daily.anon' => '20',
					'tool.ip_lookup.daily.user' => '100',
					'tool.ip_lookup.asn_detail' => '0',

					'tool.json_format.save_snippets' => '0',
					'tool.json_format.schema_infer' => '0',

					'tool.diff.save' => '0',
					'tool.diff.three_way' => '0',

					'tool.favicon.daily.anon' => '3',
					'tool.favicon.daily.user' => '20',
					'tool.favicon.all_sizes' => '0',

					'tool.lego_lookup.daily.anon' => '20',
					'tool.lego_lookup.daily.user' => '200',

					'tool.pdf_merge.max_files' => '3',
					'tool.pdf_merge.max_size_mb' => '10',
					'tool.pdf_merge.watermark' => '1',
					'tool.pdf_merge.ocr' => '0',
					'tool.pdf_redact.enabled' => '0',

					'tool.speedtest.daily.anon' => '3',
					'tool.speedtest.daily.user' => '10',
					'tool.speedtest.scheduled' => '0',

					'tool.loss.active_cases' => '1',
					'tool.loss.team_view' => '0',

					'tool.bookkeeping.enabled' => '0',
				],
			],
			[
				'plan_key' => 'tools_pro',
				'name' => 'Pro',
				'price_monthly' => 12.00,
				'price_yearly' => 120.00,
				'trial_days' => 14,
				'sort_order' => 20,
				'features' => [
					'seats' => '1',
					'api.enabled' => '0',
					'history.retention_days' => '90',

					'tool.iban.daily.user' => '1000',
					'tool.iban.risk_detail' => '1',
					'tool.iban.bulk' => '0',

					'tool.vies.daily.user' => '1000',
					'tool.vies.history_days' => '90',
					'tool.vies.bulk' => '0',

					'tool.postcode.daily.user' => 'unlimited',

					'tool.ip_lookup.daily.user' => 'unlimited',
					'tool.ip_lookup.asn_detail' => '1',

					'tool.json_format.save_snippets' => '1',
					'tool.json_format.schema_infer' => '1',

					'tool.diff.save' => '1',
					'tool.diff.three_way' => '1',

					'tool.favicon.daily.user' => 'unlimited',
					'tool.favicon.all_sizes' => '1',

					'tool.lego_lookup.daily.user' => 'unlimited',

					'tool.pdf_merge.max_files' => '50',
					'tool.pdf_merge.max_size_mb' => '500',
					'tool.pdf_merge.watermark' => '0',
					'tool.pdf_merge.ocr' => '0',
					'tool.pdf_redact.enabled' => '1',
					'tool.pdf_redact.pattern_mode' => '0',

					'tool.speedtest.daily.user' => 'unlimited',
					'tool.speedtest.scheduled' => '1',

					'tool.loss.active_cases' => '10',
					'tool.loss.team_view' => '0',

					'tool.bookkeeping.enabled' => '1',
				],
			],
			[
				'plan_key' => 'tools_business',
				'name' => 'Business',
				'price_monthly' => 39.00,
				'price_yearly' => 390.00,
				'trial_days' => 14,
				'sort_order' => 30,
				'features' => [
					'seats' => '5',
					'api.enabled' => '1',
					'history.retention_days' => 'unlimited',

					'tool.iban.daily.user' => 'unlimited',
					'tool.iban.risk_detail' => '1',
					'tool.iban.bulk' => '1',

					'tool.vies.daily.user' => 'unlimited',
					'tool.vies.history_days' => 'unlimited',
					'tool.vies.bulk' => '1',

					'tool.postcode.bulk' => '1',

					'tool.ip_lookup.asn_detail' => '1',

					'tool.json_format.team_library' => '1',
					'tool.diff.team_library' => '1',

					'tool.favicon.brand_presets' => '1',

					'tool.pdf_merge.max_files' => 'unlimited',
					'tool.pdf_merge.max_size_mb' => 'unlimited',
					'tool.pdf_merge.ocr' => '1',
					'tool.pdf_merge.bulk' => '1',
					'tool.pdf_redact.enabled' => '1',
					'tool.pdf_redact.pattern_mode' => '1',
					'tool.pdf_redact.audit_log' => '1',

					'tool.speedtest.team_dashboard' => '1',

					'tool.loss.active_cases' => 'unlimited',
					'tool.loss.team_view' => '1',

					'tool.bookkeeping.enabled' => '1',
					'tool.bookkeeping.multi_user' => '1',
				],
			],
		];

		foreach ($plans as $p) {
			$features = $p['features'];
			unset($p['features']);

			$plan = Plan::where('plan_key', $p['plan_key'])->first();
			if ($plan) {
				$plan->fill($p + ['product' => 'tools', 'is_active' => true]);
				$plan->save();
			} else {
				$plan = Plan::create($p + ['product' => 'tools', 'is_active' => true]);
			}

			foreach ($features as $key => $value) {
				PlanFeature::updateOrCreate(
					['plan_id' => $plan->id, 'feature_key' => $key],
					['value' => (string) $value],
				);
			}
		}
	}
}

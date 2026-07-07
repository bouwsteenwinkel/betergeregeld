<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

/**
 * Abonnementen voor de AI-Assistent (product = 'assistant'), losstaand van de
 * 'tools'-productlijn. Feature-keys volgen het bestaande 'tool.<slug>.*'-patroon
 * zodat FeatureBag/ToolUsageTracker ze zonder aanpassing kunnen lezen.
 *
 * Draaien:  php artisan db:seed --class=Database\\Seeders\\AssistantPlansSeeder
 */
class AssistantPlansSeeder extends Seeder
{
	public function run(): void
	{
		$plans = [
			[
				'plan_key' => 'assistant_free',
				'name' => 'Gratis',
				'price_monthly' => 0,
				'price_yearly' => 0,
				'trial_days' => 0,
				'sort_order' => 10,
				'features' => [
					'seats' => '1',
					'api.enabled' => '0',
					'history.retention_days' => '7',

					'tool.assistant.enabled' => '1',
					'tool.assistant.agents.max' => '1',

					'tool.assistant.channel.web' => '1',
					'tool.assistant.channel.voice' => '0',
					'tool.assistant.channel.whatsapp' => '0',
					'tool.assistant.channel.email' => '0',

					'tool.assistant.chats.monthly' => '100',
					'tool.assistant.voice_minutes.monthly' => '0',
					'tool.assistant.knowledge_mb.max' => '10',

					'tool.assistant.functions.enabled' => '0',
					'tool.assistant.workflows.enabled' => '0',
				],
			],
			[
				'plan_key' => 'assistant_starter',
				'name' => 'Starter',
				'price_monthly' => 29.00,
				'price_yearly' => 290.00,
				'trial_days' => 14,
				'sort_order' => 20,
				'features' => [
					'seats' => '2',
					'api.enabled' => '0',
					'history.retention_days' => '90',

					'tool.assistant.enabled' => '1',
					'tool.assistant.agents.max' => '1',

					'tool.assistant.channel.web' => '1',
					'tool.assistant.channel.voice' => '0',
					'tool.assistant.channel.whatsapp' => '0',
					'tool.assistant.channel.email' => '0',

					'tool.assistant.chats.monthly' => '1000',
					'tool.assistant.voice_minutes.monthly' => '0',
					'tool.assistant.knowledge_mb.max' => '100',

					'tool.assistant.functions.enabled' => '1',
					'tool.assistant.functions.allowed' => 'checkBeschikbaarheid,maakAfspraak,maakLead',
					'tool.assistant.workflows.enabled' => '0',
				],
			],
			[
				'plan_key' => 'assistant_growth',
				'name' => 'Groei',
				'price_monthly' => 79.00,
				'price_yearly' => 790.00,
				'trial_days' => 14,
				'sort_order' => 30,
				'features' => [
					'seats' => '5',
					'api.enabled' => '0',
					'history.retention_days' => 'unlimited',

					'tool.assistant.enabled' => '1',
					'tool.assistant.agents.max' => '2',

					'tool.assistant.channel.web' => '1',
					'tool.assistant.channel.voice' => '1',
					'tool.assistant.channel.whatsapp' => '1',
					'tool.assistant.channel.email' => '0',

					'tool.assistant.chats.monthly' => 'unlimited',
					'tool.assistant.voice_minutes.monthly' => '500',
					'tool.assistant.knowledge_mb.max' => '500',

					'tool.assistant.functions.enabled' => '1',
					'tool.assistant.functions.allowed' => 'checkBeschikbaarheid,maakAfspraak,verzetAfspraak,maakLead,zoekKlant,maakTicket',
					'tool.assistant.workflows.enabled' => '0',
				],
			],
			[
				'plan_key' => 'assistant_pro',
				'name' => 'Pro',
				'price_monthly' => 199.00,
				'price_yearly' => 1990.00,
				'trial_days' => 14,
				'sort_order' => 40,
				'features' => [
					'seats' => '10',
					'api.enabled' => '1',
					'history.retention_days' => 'unlimited',

					'tool.assistant.enabled' => '1',
					'tool.assistant.agents.max' => 'unlimited',

					'tool.assistant.channel.web' => '1',
					'tool.assistant.channel.voice' => '1',
					'tool.assistant.channel.whatsapp' => '1',
					'tool.assistant.channel.email' => '1',

					'tool.assistant.chats.monthly' => 'unlimited',
					'tool.assistant.voice_minutes.monthly' => '2000',
					'tool.assistant.knowledge_mb.max' => 'unlimited',

					'tool.assistant.functions.enabled' => '1',
					'tool.assistant.functions.allowed' => 'unlimited',
					'tool.assistant.workflows.enabled' => '1',
				],
			],
			[
				'plan_key' => 'assistant_enterprise',
				'name' => 'Enterprise',
				'price_monthly' => 0,   // op aanvraag
				'price_yearly' => 0,
				'trial_days' => 0,
				'sort_order' => 50,
				'features' => [
					'seats' => 'unlimited',
					'api.enabled' => '1',
					'history.retention_days' => 'unlimited',

					'tool.assistant.enabled' => '1',
					'tool.assistant.agents.max' => 'unlimited',

					'tool.assistant.channel.web' => '1',
					'tool.assistant.channel.voice' => '1',
					'tool.assistant.channel.whatsapp' => '1',
					'tool.assistant.channel.email' => '1',

					'tool.assistant.chats.monthly' => 'unlimited',
					'tool.assistant.voice_minutes.monthly' => 'unlimited',
					'tool.assistant.knowledge_mb.max' => 'unlimited',

					'tool.assistant.functions.enabled' => '1',
					'tool.assistant.functions.allowed' => 'unlimited',
					'tool.assistant.workflows.enabled' => '1',
					'tool.assistant.own_model' => '1',
					'tool.assistant.sso' => '1',
					'tool.assistant.on_premise' => '1',
				],
			],
		];

		foreach ($plans as $p) {
			$features = $p['features'];
			unset($p['features']);

			$plan = Plan::where('plan_key', $p['plan_key'])->first();
			if ($plan) {
				$plan->fill($p + ['product' => 'assistant', 'is_active' => true]);
				$plan->save();
			} else {
				$plan = Plan::create($p + ['product' => 'assistant', 'is_active' => true]);
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

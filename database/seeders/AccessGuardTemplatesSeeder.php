<?php

namespace Database\Seeders;

use App\Models\AccessGuard\ChecklistTemplate;
use App\Models\AccessGuard\ChecklistTemplateItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessGuardTemplatesSeeder extends Seeder
{
	public function run(): void
	{
		$tenants = DB::table('tenants')->pluck('id');
		foreach ($tenants as $tenantId) {
			$this->seedForTenant($tenantId);
		}
	}

	public function seedForTenant(string $tenantId): void
	{
		$onboarding = [
			['Intake-gesprek HR', 'Eerste kennismaking, overhandiging arbeidsvoorwaarden en huishoudelijk reglement.', false],
			['IT-account aanmaken', 'M365/Google Workspace account + primair e-mailadres registreren.', true],
			['Laptop uitleveren', 'Hardware, opladerset en randapparatuur uitgeven — noteer serienummers.', true],
			['Toegangspas uitgeven', 'Fysieke toegang tot kantoor en parkeergarage.', false],
			['Chat + agenda-tool', 'Slack/Teams uitnodiging + agenda-koppeling.', false],
			['Rollen + systemen instellen', 'Bekijk de Access Matrix en activeer de juiste systemen.', false],
			['Welkomstmail versturen', 'Introductie naar het team inclusief dag-1 planning.', false],
		];

		$offboarding = [
			['Exit-gesprek HR', 'Afronding contract, feedback en verplichtingen bespreken.', false],
			['Kennis-overdracht', 'Lopende zaken overdragen aan collega of opvolger — vastleggen in Confluence/Notion.', true],
			['Hardware retour', 'Laptop, adapters, telefoon, tokens — administreer serienummers.', true],
			['Toegangspas innemen', 'Fysieke pas, sleutels en parkeertoken retour.', false],
			['Primair account disable', 'M365/Google Workspace account deactiveren, e-mail-forward instellen.', true],
			['Toegang intrekken', 'Alle has_access cellen worden automatisch als revoke-actie aangemaakt bij afronding.', false],
			['Eindafrekening + papieren', 'Laatste salaris, vakantiedagen, getuigschrift, pensioen.', false],
		];

		$this->upsertTemplate($tenantId, 'onboarding', 'Standaard onboarding', $onboarding);
		$this->upsertTemplate($tenantId, 'offboarding', 'Standaard offboarding', $offboarding);
	}

	private function upsertTemplate(string $tenantId, string $kind, string $name, array $items): void
	{
		$template = ChecklistTemplate::query()
			->where('tenant_id', $tenantId)
			->where('kind', $kind)
			->where('is_default', true)
			->first();

		if ($template) {
			return; // already seeded for this tenant
		}

		$template = ChecklistTemplate::create([
			'tenant_id' => $tenantId,
			'kind' => $kind,
			'name' => $name,
			'is_default' => true,
		]);

		foreach ($items as $i => [$title, $description, $requiresEvidence]) {
			ChecklistTemplateItem::create([
				'template_id' => $template->id,
				'title' => $title,
				'description' => $description,
				'sort_order' => ($i + 1) * 10,
				'requires_evidence' => $requiresEvidence,
			]);
		}
	}
}

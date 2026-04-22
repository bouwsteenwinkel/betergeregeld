<?php

namespace Database\Seeders;

use App\Models\BookkeepingCategory;
use App\Models\BookkeepingVatRate;
use Illuminate\Database\Seeder;

/**
 * Seeds the 31 default NL expense categories and three standard VAT rates
 * as tenant_id=null (shared defaults). Per-tenant overrides can be added later
 * without touching these.
 */
class BookkeepingDefaultsSeeder extends Seeder
{
	public function run(): void
	{
		$vatRates = [
			['name' => '21%', 'rate' => 21.00, 'is_default' => true,  'effective_from' => '2019-01-01'],
			['name' => '9%',  'rate' =>  9.00, 'is_default' => false, 'effective_from' => '2019-01-01'],
			['name' => '0%',  'rate' =>  0.00, 'is_default' => false, 'effective_from' => '2019-01-01'],
		];
		foreach ($vatRates as $vr) {
			BookkeepingVatRate::updateOrCreate(
				['tenant_id' => null, 'name' => $vr['name']],
				$vr + ['tenant_id' => null],
			);
		}

		$expenseCategories = [
			'Auto & transport', 'Brandstof', 'Parkeer- en tolkosten', 'Reiskosten',
			'Kantoorartikelen', 'Abonnementen', 'Telefonie & internet', 'Porti & post',
			'Huisvesting', 'Gas, water, licht', 'Schoonmaak',
			'Verzekeringen', 'Belastingen & heffingen',
			'Marketing & advertenties', 'Representatie', 'Relatiegeschenken',
			'Accountant & advies', 'Juridische kosten', 'Bankkosten',
			'Software & licenties', 'Hosting & domeinen',
			'Opleiding & cursussen', 'Vakliteratuur',
			'Kleine aanschaffingen', 'Onderhoud & reparaties',
			'Voorraden & inkopen', 'Verzendkosten',
			'Eten & drinken zakelijk', 'Personeelskosten',
			'Overige kosten', 'Afschrijvingen',
		];
		foreach ($expenseCategories as $i => $name) {
			BookkeepingCategory::updateOrCreate(
				['tenant_id' => null, 'name' => $name, 'type' => 'expense'],
				['is_active' => true, 'sort_order' => $i * 10, 'tenant_id' => null],
			);
		}

		$incomeCategories = ['Omzet', 'Overige inkomsten', 'Rente-inkomsten'];
		foreach ($incomeCategories as $i => $name) {
			BookkeepingCategory::updateOrCreate(
				['tenant_id' => null, 'name' => $name, 'type' => 'income'],
				['is_active' => true, 'sort_order' => $i * 10, 'tenant_id' => null],
			);
		}
	}
}

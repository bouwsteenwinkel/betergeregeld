<?php

namespace App\Services\Rankdata;

use App\Models\Agency;
use App\Models\BookkeepingInvoice;
use App\Models\BookkeepingRelation;
use App\Models\BookkeepingVatRate;
use App\Services\InvoiceNumberer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Genereert de maandelijkse bureau-factuur (platform → bureau, te voldoen per
 * overschrijving) bovenop het bestaande boekhoud-factuursysteem. De verkoper is
 * de platform-tenant (Beter Geregeld); de koper is een boekhoud-relatie die het
 * bureau vertegenwoordigt. Eén regel per klant + één kortingsregel.
 */
class RankdataInvoiceService
{
	private const NL_MONTHS = [1 => 'januari', 'februari', 'maart', 'april', 'mei', 'juni',
		'juli', 'augustus', 'september', 'oktober', 'november', 'december'];

	public function __construct(
		private readonly RankdataBilling $billing,
		private readonly InvoiceNumberer $numberer,
	) {
	}

	public function generateForAgency(Agency $agency, int $termsDays = 14): BookkeepingInvoice
	{
		$tenant = (string) config('monitor.platform_tenant_id');
		$vat = $this->vatRate($tenant);
		$relation = $this->relationFor($agency, $tenant);
		$createdBy = DB::table('users')->where('tenant_id', $tenant)->value('id');
		$clients = $agency->tenants()->where('is_active', true)->orderBy('name')->get();

		return DB::transaction(function () use ($agency, $clients, $tenant, $vat, $relation, $createdBy, $termsDays) {
			$period = self::NL_MONTHS[(int) now()->month] . ' ' . now()->year;

			$invoice = new BookkeepingInvoice();
			$invoice->id = (string) Str::uuid();
			$invoice->tenant_id = $tenant;
			$invoice->relation_id = $relation->id;
			$invoice->created_by_user_id = $createdBy;
			$invoice->invoice_number = $this->numberer->next($tenant, (int) now()->year);
			$invoice->status = 'draft';
			$invoice->issue_date = now()->toDateString();
			$invoice->due_date = now()->addDays($termsDays)->toDateString();
			$invoice->payment_terms_days = $termsDays;
			$invoice->reference = 'Rankdata ' . $period;
			$invoice->notes = 'Maandelijkse Rankdata-dienstverlening (' . $period . ') — ' . $clients->count()
				. ' klant' . ($clients->count() === 1 ? '' : 'en') . '.';
			$invoice->subtotal = 0;
			$invoice->vat_amount = 0;
			$invoice->total = 0;
			$invoice->save();

			$sort = 0;
			$discountTotal = 0.0;
			$discountPct = 0.0;
			foreach ($clients as $client) {
				$c = $this->billing->costForTenant($client, $agency);
				$invoice->lines()->create([
					'description' => 'Rankdata — ' . $client->name . ' (' . $c['sites'] . ' site' . ($c['sites'] === 1 ? '' : 's') . ')',
					'quantity'    => 1,
					'unit_price'  => $c['subtotal'],
					'vat_rate_id' => $vat?->id,
					'sort_order'  => $sort++,
				]);
				$discountTotal += $c['discount'];
				$discountPct = $c['discount_percent'];
			}

			if ($discountTotal > 0) {
				$invoice->lines()->create([
					'description' => 'Korting ' . $this->pct($discountPct) . '%',
					'quantity'    => 1,
					'unit_price'  => -round($discountTotal, 2),
					'vat_rate_id' => $vat?->id,
					'sort_order'  => $sort++,
				]);
			}

			$invoice->load('lines.vatRate');
			$invoice->recalculate();
			$invoice->save();

			return $invoice;
		});
	}

	/** Btw-tarief van de platform-tenant; maakt een 21%-default aan als die ontbreekt. */
	private function vatRate(string $tenant): ?BookkeepingVatRate
	{
		$rate = BookkeepingVatRate::query()->where('tenant_id', $tenant)->orderByDesc('is_default')->first();
		if ($rate) {
			return $rate;
		}

		return BookkeepingVatRate::create([
			'tenant_id'      => $tenant,
			'name'           => 'BTW 21%',
			'rate'           => 21,
			'effective_from' => '2020-01-01',
			'is_default'     => true,
		]);
	}

	/** Boekhoud-relatie die het bureau vertegenwoordigt (maakt aan indien nodig). */
	private function relationFor(Agency $agency, string $tenant): BookkeepingRelation
	{
		return BookkeepingRelation::firstOrCreate(
			['tenant_id' => $tenant, 'name' => $agency->name, 'type' => 'client'],
			['email' => $agency->contact_email, 'is_active' => true, 'country' => 'NL'],
		);
	}

	private function pct(float $p): string
	{
		return rtrim(rtrim(number_format($p, 2, ',', '.'), '0'), ',');
	}
}

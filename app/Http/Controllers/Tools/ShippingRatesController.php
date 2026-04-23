<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Models\ToolEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingRatesController extends Controller
{
	public function show(Request $request, string $locale): View
	{
		$input = $request->validate([
			'carrier' => ['nullable', 'string', 'max:50'],
			'zone' => ['nullable', 'string', 'max:50'],
			'service' => ['nullable', 'string', 'max:120'],
			'weight_g' => ['nullable', 'integer', 'min:1', 'max:1000000'],
		]);

		$carrier = $input['carrier'] ?? null;
		$zone = $input['zone'] ?? null;
		$service = $input['service'] ?? null;
		$weightG = $input['weight_g'] ?? null;

		$filters = $this->buildFilterOptions();

		$rates = ShippingRate::query()
			->where('active', true)
			->when($carrier, fn ($q) => $q->where('carrier', $carrier))
			->when($zone, fn ($q) => $q->where('zone', $zone))
			->when($service, fn ($q) => $q->where('service', $service))
			->orderBy('carrier')
			->orderBy('service')
			->orderBy('zone')
			->orderBy('sort_order')
			->orderBy('weight_from_g')
			->get();

		$best = null;
		if ($weightG !== null && $weightG > 0) {
			$best = ShippingRate::query()
				->where('active', true)
				->where('weight_from_g', '<=', $weightG)
				->where('weight_to_g', '>=', $weightG)
				->when($carrier, fn ($q) => $q->where('carrier', $carrier))
				->when($zone, fn ($q) => $q->where('zone', $zone))
				->when($service, fn ($q) => $q->where('service', $service))
				->orderByRaw('(price_ex + handling_ex) ASC')
				->orderBy('sort_order')
				->orderBy('weight_from_g')
				->first();

			ToolEvent::create([
				'tool' => 'shipping-rates',
				'event' => 'calc',
				'session_id' => substr($request->session()->getId(), 0, 64),
				'meta_json' => [
					'weight_g' => $weightG,
					'carrier' => $carrier,
					'zone' => $zone,
					'service' => $service,
					'matched' => $best !== null,
				],
			]);
		}

		return view('tools.shipping-rates', [
			'input' => [
				'carrier' => $carrier ?? '',
				'zone' => $zone ?? '',
				'service' => $service ?? '',
				'weight_g' => $weightG,
			],
			'filters' => $filters,
			'rates' => $rates,
			'best' => $best,
		]);
	}

	private function buildFilterOptions(): array
	{
		return [
			'carriers' => ShippingRate::query()
				->where('active', true)
				->distinct()
				->orderBy('carrier')
				->pluck('carrier')
				->all(),
			'zones' => ShippingRate::query()
				->where('active', true)
				->distinct()
				->orderBy('zone')
				->pluck('zone')
				->all(),
			'services' => ShippingRate::query()
				->where('active', true)
				->distinct()
				->orderBy('service')
				->pluck('service')
				->all(),
		];
	}
}

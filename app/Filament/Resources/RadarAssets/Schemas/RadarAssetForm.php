<?php

namespace App\Filament\Resources\RadarAssets\Schemas;

use App\Models\AccessGuard\Radar\Asset;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RadarAssetForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make('Asset')
					->columns(2)
					->components([
						Select::make('tenant_id')
							->label('Tenant')
							->options(fn () => Tenant::query()->orderBy('name')->pluck('name', 'id'))
							->searchable()
							->preload()
							->required()
							->disabledOn('edit'),
						Select::make('kind')
							->options(self::optionPairs(Asset::KINDS))
							->required(),
						TextInput::make('name')
							->required()
							->maxLength(200),
						TextInput::make('url')
							->label('URL')
							->url()
							->maxLength(500)
							->placeholder('https://shop.example.com')
							->helperText('Required for http_probe scans.'),
						TextInput::make('hostname')
							->maxLength(255),
						Select::make('environment')
							->options(self::optionPairs(Asset::ENVIRONMENTS))
							->default('production')
							->required(),
					]),

				Section::make('Exposure & criticality')
					->columns(2)
					->components([
						Select::make('is_public')
							->label('Publicly reachable')
							->options(self::optionPairs(Asset::TRISTATE))
							->default('unknown')
							->required(),
						Select::make('auth_required')
							->options(self::optionPairs(Asset::TRISTATE))
							->default('unknown')
							->required(),
						Select::make('criticality')
							->options(self::optionPairs(Asset::CRITICALITY))
							->default('medium')
							->required(),
						Select::make('status')
							->options(self::optionPairs(Asset::STATUSES))
							->default('active')
							->required(),
					]),

				Section::make('Ownership & notes')
					->columns(2)
					->components([
						Select::make('owner_user_id')
							->label('Owner')
							->options(fn () => User::query()->orderBy('email')->pluck('email', 'id'))
							->searchable()
							->preload(),
						TextInput::make('vendor')
							->helperText('Auto-filled by scanner; override only if known.')
							->maxLength(120),
						TextInput::make('product')
							->helperText('Auto-filled by scanner.')
							->maxLength(120),
						TextInput::make('current_version')
							->helperText('Auto-filled by scanner.')
							->maxLength(80),
						Textarea::make('notes')
							->rows(3)
							->columnSpanFull(),
					]),
			]);
	}

	/**
	 * @param  string[]  $values
	 * @return array<string,string>
	 */
	private static function optionPairs(array $values): array
	{
		return array_combine($values, array_map(
			fn (string $v) => ucfirst(str_replace('_', ' ', $v)),
			$values,
		));
	}
}

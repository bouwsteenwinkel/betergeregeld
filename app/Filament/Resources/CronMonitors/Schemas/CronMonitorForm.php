<?php

namespace App\Filament\Resources\CronMonitors\Schemas;

use App\Models\Monitor\CronMonitor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CronMonitorForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make('Cron-job')
					->columns(2)
					->components([
						TextInput::make('name')
							->label('Naam')
							->required()
							->maxLength(120)
							->placeholder('Dagelijkse database-backup'),
						Select::make('tenant_id')
							->label('Tenant')
							->relationship('tenant', 'name')
							->searchable()
							->preload()
							->placeholder('Platform / gedeeld')
							->helperText('Leeg = platform-breed of gedeeld over tenants.'),
						TextInput::make('website')
							->label('Website')
							->maxLength(190)
							->placeholder('betergeregeld.com')
							->helperText('Leeg = gedeeld binnen de tenant (niet aan één site gebonden).'),
						Textarea::make('description')
							->label('Omschrijving')
							->rows(2)
							->maxLength(2000)
							->columnSpanFull()
							->placeholder('Wat doet deze job en waar draait hij?'),
					]),

				Section::make('Verwachte cadans')
					->columns(2)
					->description('Komt er binnen periode + speling geen succes-ping, dan geldt de job als "te laat".')
					->components([
						TextInput::make('expected_period_minutes')
							->label('Periode')
							->numeric()
							->required()
							->default(1440)
							->minValue(1)
							->suffix('minuten')
							->helperText('5 = elke 5 min · 60 = elk uur · 1440 = dagelijks · 10080 = wekelijks.'),
						TextInput::make('grace_minutes')
							->label('Speling')
							->numeric()
							->required()
							->default(60)
							->minValue(0)
							->suffix('minuten')
							->helperText('Marge bovenop de periode voordat "te laat" wordt gemeld.'),
					]),

				Section::make('Alerts')
					->columns(2)
					->components([
						Toggle::make('is_active')
							->label('Actief')
							->default(true)
							->helperText('Inactieve monitors weigeren pings en worden niet gecheckt.'),
						Toggle::make('alerts_enabled')
							->label('Alerts aan')
							->default(true)
							->helperText('Mail bij te-laat / fout en bij herstel.'),
						Toggle::make('is_source')
							->label('Bron-modus')
							->default(false)
							->helperText('Aan = deze monitor maakt via ?job= automatisch onder-monitors aan (voor projecten met veel cron-jobs, bijv. Bouwsteenwinkel). De bron zelf alarmeert niet.')
							->columnSpanFull(),
						TextInput::make('notify_email')
							->label('Meld-adres')
							->email()
							->maxLength(190)
							->placeholder(config('monitor.alert_email'))
							->helperText('Leeg = standaard monitoring-inbox.')
							->columnSpanFull(),
					]),

				Section::make('Ping-URL')
					->components([
						Placeholder::make('ping_url')
							->label('Ping deze URL aan het einde van je cron-job')
							->content(fn (?CronMonitor $record) => $record
								? route('cron.ping', $record->ping_token)
								: '—'),
						Placeholder::make('ping_help')
							->label('')
							->content('Gebruik de "Ping-URL"-actie in de lijst voor kant-en-klare curl-/PowerShell-voorbeelden (incl. start- en fout-signaal).'),
					])
					->hiddenOn('create'),
			]);
	}
}

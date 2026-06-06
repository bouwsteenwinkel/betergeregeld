<?php

namespace App\Filament\Resources\MonitorServers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MonitorServerForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make('Server')
					->columns(2)
					->components([
						TextInput::make('name')
							->required()
							->maxLength(120)
							->placeholder('Productie-VPS'),
						TextInput::make('hostname')
							->maxLength(255)
							->placeholder('vps.example.com'),
						TextInput::make('ip_address')
							->label('IP-adres')
							->maxLength(45)
							->placeholder('85.215.166.3'),
						TextInput::make('provider')
							->maxLength(80)
							->placeholder('IONOS / Hetzner / …'),
						TextInput::make('location')
							->label('Locatie')
							->maxLength(80)
							->placeholder('Frankfurt'),
						Toggle::make('is_active')
							->label('Actief')
							->default(true)
							->helperText('Inactieve servers weigeren ingest en tellen niet mee in overzichten.'),
						Toggle::make('alerts_enabled')
							->label('Alerts aan')
							->default(true)
							->helperText('Mail bij offline gaan of volle schijf.'),
					]),

				Section::make('Collector')
					->columns(1)
					->components([
						TextInput::make('ingest_token')
							->label('Ingest-token')
							->readOnly()
							->dehydrated(false)
							->placeholder('Wordt automatisch gegenereerd bij opslaan')
							->helperText('Geef dit token mee aan de collector op de VPS. Gebruik de "Install"-actie in de lijst voor het kant-en-klare commando.'),
					])
					->hiddenOn('create'),
			]);
	}
}

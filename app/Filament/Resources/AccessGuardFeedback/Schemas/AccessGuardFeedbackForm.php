<?php

namespace App\Filament\Resources\AccessGuardFeedback\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AccessGuardFeedbackForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema
			->components([
				Select::make('status')
					->options([
						'new' => 'New',
						'triaged' => 'Triaged',
						'resolved' => 'Resolved',
						'wontfix' => 'Won\'t fix',
					])
					->required(),
				Select::make('category')
					->options([
						'bug' => 'Bug',
						'feature' => 'Feature',
						'question' => 'Vraag',
						'praise' => 'Compliment',
						'other' => 'Anders',
					])
					->disabled(),
				Textarea::make('message')
					->disabled()
					->rows(5)
					->columnSpanFull(),
				Textarea::make('admin_note')
					->label('Interne notitie')
					->rows(3)
					->columnSpanFull()
					->helperText('Alleen voor admins zichtbaar.'),
				DateTimePicker::make('resolved_at')
					->label('Opgelost op'),
			]);
	}
}

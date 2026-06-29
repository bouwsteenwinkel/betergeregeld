<?php

namespace App\Filament\Support;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Type-bewuste content-velden voor een channel-blok. De admin-form toont per
 * bloktype de juiste velden (statePath 'content.*'). Onbekende types vallen terug
 * op een vrije sleutel-waarde-editor zodat álles bewerkbaar blijft.
 */
class BlockContentSchema
{
    /** @return array<int, \Filament\Forms\Components\Component> */
    public static function for(string $type): array
    {
        return match ($type) {
            'hero' => [
                TextInput::make('content.eyebrow')->label('Eyebrow'),
                TextInput::make('content.title')->label('Titel')->columnSpanFull(),
                Textarea::make('content.sub')->label('Subtekst')->rows(2)->columnSpanFull(),
                TextInput::make('content.cta_label')->label('Knoptekst')->placeholder('Gratis voorbeeld aanvragen'),
                TextInput::make('content.note')->label('Geruststelling onder de knop'),
                TextInput::make('content.cta2_label')->label('2e knop (optioneel)'),
                TextInput::make('content.cta2_href')->label('2e knop-link')->placeholder('#behandelingen'),
                Repeater::make('content.usps')->label('USP\'s')->schema([
                    TextInput::make('text')->label('USP')->required(),
                ])->columnSpanFull()->collapsed()->defaultItems(0),
            ],

            'uspbar' => [
                Repeater::make('content.items')->label('Items')->schema([
                    TextInput::make('icon')->label('Icoon')->placeholder('✓')->maxLength(4),
                    TextInput::make('text')->label('Tekst')->required(),
                ])->columns(2)->columnSpanFull(),
            ],

            'features' => [
                TextInput::make('content.heading')->label('Kop'),
                TextInput::make('content.sub')->label('Subtekst'),
                Repeater::make('content.items')->label('Functies')->schema([
                    TextInput::make('icon')->label('Icoon')->maxLength(4),
                    TextInput::make('title')->label('Titel')->required(),
                    Textarea::make('text')->label('Tekst')->rows(2),
                ])->columns(3)->columnSpanFull()->collapsed(),
            ],

            'steps' => [
                TextInput::make('content.heading')->label('Kop'),
                Repeater::make('content.items')->label('Stappen')->schema([
                    TextInput::make('title')->label('Titel')->required(),
                    Textarea::make('text')->label('Tekst')->rows(2),
                ])->columns(2)->columnSpanFull(),
            ],

            'about' => [
                TextInput::make('content.eyebrow')->label('Eyebrow'),
                TextInput::make('content.heading')->label('Kop')->columnSpanFull(),
                Textarea::make('content.lead')->label('Lead (introzin)')->rows(2)->columnSpanFull(),
                Textarea::make('content.body')->label('Tekst (lege regel = nieuwe alinea)')->rows(5)->columnSpanFull(),
                Repeater::make('content.stats')->label('Kerncijfers')->schema([
                    TextInput::make('value')->label('Waarde')->placeholder('15+'),
                    TextInput::make('label')->label('Label')->placeholder('jaar ervaring'),
                ])->columns(2)->columnSpanFull()->collapsed(),
            ],

            'proof' => [
                Textarea::make('content.quote')->label('Citaat')->rows(2)->columnSpanFull(),
                TextInput::make('content.author')->label('Door (naam/zaak)'),
            ],

            'pricelist' => [
                TextInput::make('content.eyebrow')->label('Eyebrow'),
                TextInput::make('content.heading')->label('Kop'),
                TextInput::make('content.sub')->label('Subtekst')->columnSpanFull(),
                Repeater::make('content.items')->label('Diensten')->schema([
                    TextInput::make('name')->label('Naam')->required(),
                    TextInput::make('desc')->label('Omschrijving'),
                    TextInput::make('price')->label('Prijs')->placeholder('€ 39'),
                ])->columns(3)->columnSpanFull()->collapsed(),
            ],

            'reviews' => [
                TextInput::make('content.heading')->label('Kop'),
                Repeater::make('content.items')->label('Reviews')->schema([
                    TextInput::make('stars')->label('Sterren')->numeric()->default(5)->minValue(1)->maxValue(5),
                    Textarea::make('text')->label('Review')->rows(2)->required(),
                    TextInput::make('author')->label('Naam'),
                ])->columns(3)->columnSpanFull()->collapsed(),
            ],

            'faq' => [
                TextInput::make('content.heading')->label('Kop'),
                Repeater::make('content.items')->label('Vragen')->schema([
                    TextInput::make('q')->label('Vraag')->required(),
                    Textarea::make('a')->label('Antwoord')->rows(2),
                ])->columnSpanFull()->collapsed(),
            ],

            'gallery' => [
                TextInput::make('content.eyebrow')->label('Eyebrow'),
                TextInput::make('content.heading')->label('Kop'),
                TextInput::make('content.sub')->label('Subtekst'),
                Repeater::make('content.tiles')->label('Tegels')->schema([
                    TextInput::make('label')->label('Label'),
                    TextInput::make('image')->label('Afbeelding-URL')->url(),
                ])->columns(2)->columnSpanFull()->collapsed(),
            ],

            'pricing' => [
                TextInput::make('content.heading')->label('Kop'),
                TextInput::make('content.sub')->label('Subtekst'),
                Repeater::make('content.plans')->label('Pakketten')->schema([
                    TextInput::make('name')->label('Naam')->required(),
                    TextInput::make('price')->label('Prijs')->placeholder('€ 49'),
                    TextInput::make('period')->label('Periode')->placeholder('p/m'),
                    Textarea::make('features')->label('Inbegrepen (één per regel)')->rows(4)->columnSpanFull(),
                    TextInput::make('cta')->label('Knoptekst'),
                    \Filament\Forms\Components\Toggle::make('highlight')->label('Uitlichten'),
                ])->columns(3)->columnSpanFull()->collapsed(),
            ],

            'cta' => [
                TextInput::make('content.title')->label('Titel')->columnSpanFull(),
                Textarea::make('content.sub')->label('Subtekst')->rows(2)->columnSpanFull(),
                TextInput::make('content.cta_label')->label('Knoptekst'),
            ],

            'logos' => [
                TextInput::make('content.heading')->label('Kop'),
                Repeater::make('content.items')->label('Logo\'s')->schema([
                    TextInput::make('label')->label('Naam'),
                    TextInput::make('image')->label('Logo-URL')->url(),
                ])->columns(2)->columnSpanFull()->collapsed(),
            ],

            'location' => [
                TextInput::make('content.heading')->label('Kop'),
                TextInput::make('content.address')->label('Adres')->placeholder('leeg = merk-adres'),
                TextInput::make('content.phone')->label('Telefoon')->placeholder('leeg = merk-telefoon'),
                TextInput::make('content.email')->label('E-mail')->placeholder('leeg = merk-e-mail'),
                Repeater::make('content.hours')->label('Openingstijden')->schema([
                    TextInput::make('day')->label('Dag'),
                    TextInput::make('time')->label('Tijd')->placeholder('09:00 – 18:00'),
                ])->columns(2)->columnSpanFull()->collapsed(),
            ],

            'blog' => [
                TextInput::make('content.heading')->label('Kop'),
                TextInput::make('content.limit')->label('Aantal artikelen')->numeric()->default(3),
            ],

            'richtext' => [
                TextInput::make('content.heading')->label('Kop'),
                Textarea::make('content.html')->label('HTML')->rows(8)->columnSpanFull()
                    ->helperText('Rauwe HTML — wordt ongefilterd getoond.'),
            ],

            // groeipad + wizard hebben geen instelbare content.
            'groeipad', 'wizard' => [],

            default => [
                KeyValue::make('content')->label('Inhoud (vrij)')->columnSpanFull(),
            ],
        };
    }
}

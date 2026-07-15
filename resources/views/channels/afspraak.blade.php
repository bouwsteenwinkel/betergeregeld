{{-- Kennismaking inplannen. Hergebruikt de bestaande, werkende afsprakenwidget
     (channels.partials.booking → /afspraak/beschikbaarheid + /afspraak/boeken,
     platform-brede agenda). Bestaat als eigen pagina omdat /contact op het
     bedrijfswebsite-kanaal geblokkeerd is, terwijl de preview-CTA ("Wil je dit op
     je eigen site? Plan een gesprek") juist hier moet landen. --}}
@extends('channels.layout')

@section('title', 'Plan een gesprek')
@section('description', 'Kies zelf een moment voor een korte, vrijblijvende kennismaking. Telefonisch of via een videogesprek.')

@section('content')

    @include('channels.partials.booking', ['site' => $site])

@endsection

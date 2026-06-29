@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', $site->homeTitle())
@section('description', $site->homeDescription())

@section('content')
    {{-- Blok-gedreven homepage: loopt de (gesorteerde) blokken van deze site en
         rendert elk via de view-resolutie van ChannelSite::blockView(). --}}
    @foreach ($site->blocks() as $block)
        @continue(! $block->enabled)
        @include($site->blockView($block->type, $block->block_key), [
            'site'   => $site,
            'block'  => $block,
            'facet'  => $facet ?? 'website',
            'facets' => $facets ?? [],
        ])
    @endforeach
@endsection

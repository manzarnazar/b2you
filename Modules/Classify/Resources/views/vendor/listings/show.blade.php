@extends('layouts.vendor.app')

@section('title', $listing->title)

@section('content')
<div class="content container-fluid">
    <div class="page-header d-flex justify-content-between">
        <h1 class="page-header-title">{{ $listing->title }}</h1>
        <div>
            <a href="{{ route('vendor.classify.listings.edit', $listing->id) }}" class="btn btn-primary">{{ translate('Edit') }}</a>
            <form action="{{ route('vendor.classify.listings.sold', $listing->id) }}" method="post" class="d-inline">@csrf
                <button class="btn btn-success">{{ translate('Mark Sold') }}</button>
            </form>
            <form action="{{ route('vendor.classify.listings.renew', $listing->id) }}" method="post" class="d-inline">@csrf
                <button class="btn btn-info">{{ translate('Renew') }}</button>
            </form>
            <form action="{{ route('vendor.classify.listings.archive', $listing->id) }}" method="post" class="d-inline">@csrf
                <button class="btn btn-secondary">{{ translate('Archive') }}</button>
            </form>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>{{ translate('Status') }}:</strong> {{ $listing->status }}</p>
            <p><strong>{{ translate('Price') }}:</strong> {{ \App\CentralLogics\Helpers::format_currency($listing->price) }}</p>
            <p><strong>{{ translate('Condition') }}:</strong> {{ $listing->condition }}</p>
            <p>{{ $listing->description }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-header">{{ translate('Statistics') }}</div>
        <div class="card-body">
            <p>{{ translate('Views') }}: {{ $stats['views_count'] }}</p>
            <p>{{ translate('Favorites') }}: {{ $stats['favorites_count'] }}</p>
            <p>{{ translate('Chats') }}: {{ $stats['chats_count'] }}</p>
            <p>{{ translate('Expires') }}: {{ $stats['expires_at'] }}</p>
        </div>
    </div>
</div>
@endsection

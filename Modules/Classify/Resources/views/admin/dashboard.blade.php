@extends('layouts.admin.app')

@section('title', translate('Classify Dashboard') ?: 'Classify Dashboard')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center">
            @if($mod)
                <img class="onerror-image" data-onerror-image="{{ asset('/public/assets/admin/img/160x160/img2.jpg') }}"
                     src="{{ $mod->icon_full_url }}" width="38" alt="">
            @endif
            <div class="pl-2">
                <h1 class="page-header-title mb-0">{{ $mod->module_name ?? 'Classify' }} {{ translate('messages.Dashboard') }}</h1>
                <p class="page-header-text m-0">{{ translate('Manage classified listings, sellers, and reports.') }}</p>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">{{ translate('Total Listings') }}</h6>
                    <h2 class="mb-0">{{ $stats['total_listings'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">{{ translate('Pending') }}</h6>
                    <h2 class="mb-0 text-warning">{{ $stats['pending'] }}</h2>
                    <a href="{{ route('admin.classify.listings.index', ['status' => 'pending']) }}">{{ translate('Review') }}</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">{{ translate('Published') }}</h6>
                    <h2 class="mb-0 text-success">{{ $stats['published'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">{{ translate('Pending Reports') }}</h6>
                    <h2 class="mb-0 text-danger">{{ $stats['reports'] }}</h2>
                    <a href="{{ route('admin.classify.reports.index') }}">{{ translate('Moderate') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><small>{{ translate('Sold') }}</small><h4>{{ $stats['sold'] }}</h4></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><small>{{ translate('Expired') }}</small><h4>{{ $stats['expired'] }}</h4></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><small>{{ translate('Rejected') }}</small><h4>{{ $stats['rejected'] }}</h4></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><small>{{ translate('Featured') }}</small><h4>{{ $stats['featured'] }}</h4></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><small>{{ translate('Premium') }}</small><h4>{{ $stats['premium'] }}</h4></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><small>{{ translate('Sellers') }}</small><h4>{{ $stats['sellers'] }}</h4></div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ translate('Recent Listings') }}</h5>
            <a href="{{ route('admin.classify.listings.index') }}" class="btn btn-sm btn-primary">{{ translate('View All') }}</a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-borderless">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Title') }}</th>
                    <th>{{ translate('Store') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Price') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recent as $key => $listing)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td><a href="{{ route('admin.classify.listings.show', $listing->id) }}">{{ $listing->title }}</a></td>
                        <td>{{ $listing->store->name ?? '-' }}</td>
                        <td><span class="badge badge-soft-secondary">{{ $listing->status }}</span></td>
                        <td>{{ \App\CentralLogics\Helpers::format_currency($listing->price) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">{{ translate('No data found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

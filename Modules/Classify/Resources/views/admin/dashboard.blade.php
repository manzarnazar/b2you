@php
    $statusBadge = function ($status) {
        return match ($status) {
            'published' => 'badge-soft-success',
            'pending' => 'badge-soft-warning',
            'sold' => 'badge-soft-info',
            'expired', 'archived' => 'badge-soft-secondary',
            'rejected' => 'badge-soft-danger',
            default => 'badge-soft-dark',
        };
    };
@endphp

@extends('layouts.admin.app')

@section('title', translate('Classify Dashboard') ?: 'Classify Dashboard')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0 d-flex align-items-center">
                @if($mod)
                    <img class="avatar avatar-lg avatar-4by3 rounded mr-2 onerror-image"
                         data-onerror-image="{{ asset('/public/assets/admin/img/160x160/img2.jpg') }}"
                         src="{{ $mod->icon_full_url }}" alt="">
                @endif
                <div>
                    <h1 class="page-header-title mb-0">{{ $mod->module_name ?? 'Classify' }} {{ translate('Dashboard') }}</h1>
                    <p class="text-muted mb-0">{{ translate('Manage classified listings, sellers, and reports.') }}</p>
                </div>
            </div>
            <div class="col-sm-auto">
                <a href="{{ route('admin.classify.listings.index') }}" class="btn btn-primary">
                    {{ translate('All listings') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row g-2 g-sm-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-hover-shadow h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">{{ translate('Total Listings') }}</h6>
                    <h2 class="card-title mb-0">{{ $stats['total_listings'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('admin.classify.listings.index', ['status' => 'pending']) }}" class="card card-hover-shadow h-100 text-decoration-none">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">{{ translate('Pending') }}</h6>
                    <h2 class="card-title mb-0 text-warning">{{ $stats['pending'] }}</h2>
                    <small class="text-primary">{{ translate('Review') }} →</small>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('admin.classify.listings.index', ['status' => 'published']) }}" class="card card-hover-shadow h-100 text-decoration-none">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">{{ translate('Published') }}</h6>
                    <h2 class="card-title mb-0 text-success">{{ $stats['published'] }}</h2>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('admin.classify.reports.index') }}" class="card card-hover-shadow h-100 text-decoration-none">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">{{ translate('Pending Reports') }}</h6>
                    <h2 class="card-title mb-0 text-danger">{{ $stats['reports'] }}</h2>
                    <small class="text-primary">{{ translate('Moderate') }} →</small>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-2 g-sm-3 mb-4">
        @foreach([
            ['label' => translate('Sold'), 'value' => $stats['sold'], 'status' => 'sold'],
            ['label' => translate('Expired'), 'value' => $stats['expired'], 'status' => 'expired'],
            ['label' => translate('Rejected'), 'value' => $stats['rejected'], 'status' => 'rejected'],
            ['label' => translate('Featured'), 'value' => $stats['featured'], 'status' => null],
            ['label' => translate('Premium'), 'value' => $stats['premium'], 'status' => null],
            ['label' => translate('Sellers'), 'value' => $stats['sellers'], 'status' => null],
        ] as $mini)
            <div class="col-6 col-md-4 col-lg-2">
                @if($mini['status'])
                    <a href="{{ route('admin.classify.listings.index', ['status' => $mini['status']]) }}"
                       class="card card-hover-shadow h-100 text-decoration-none">
                        <div class="card-body py-3 text-center">
                            <small class="text-muted d-block">{{ $mini['label'] }}</small>
                            <span class="h4 mb-0 text-dark">{{ $mini['value'] }}</span>
                        </div>
                    </a>
                @else
                    <div class="card h-100">
                        <div class="card-body py-3 text-center">
                            <small class="text-muted d-block">{{ $mini['label'] }}</small>
                            <span class="h4 mb-0 text-dark">{{ $mini['value'] }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header border-0">
            <div class="row justify-content-between align-items-center flex-grow-1">
                <div class="col">
                    <h5 class="card-header-title">{{ translate('Recent Listings') }}</h5>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.classify.listings.index') }}" class="btn btn-sm btn-primary">
                        {{ translate('View all') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="table-responsive datatable-custom">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                <tr>
                    <th style="width:56px"></th>
                    <th>{{ translate('Listing') }}</th>
                    <th>{{ translate('Store') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Price') }}</th>
                    <th class="text-end">{{ translate('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recent as $listing)
                    <tr>
                        <td>
                            <img class="avatar avatar-sm avatar-4by3 rounded"
                                 src="{{ $listing->primary_image_full_url }}"
                                 alt=""
                                 onerror="this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}'">
                        </td>
                        <td>
                            <a class="font-weight-bold text-dark" href="{{ route('admin.classify.listings.show', $listing->id) }}">
                                {{ Str::limit($listing->title, 40) }}
                            </a>
                        </td>
                        <td>{{ $listing->store->name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $statusBadge($listing->status) }} text-capitalize">
                                {{ $listing->status }}
                            </span>
                        </td>
                        <td class="font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($listing->price) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.classify.listings.show', $listing->id) }}" class="btn btn-sm btn-white">
                                <i class="tio-visible-outlined"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">{{ translate('No data found') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted small mt-3 mb-0">
        {{ translate('Add home carousel banners from Banners while the Classify module is selected.') }}
    </p>
</div>
@endsection

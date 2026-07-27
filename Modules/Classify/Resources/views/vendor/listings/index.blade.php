@php
    $statusBadge = function ($status) {
        return match ($status) {
            'published' => 'badge-soft-success',
            'pending' => 'badge-soft-warning',
            'sold' => 'badge-soft-info',
            'archived' => 'badge-soft-secondary',
            'rejected' => 'badge-soft-danger',
            default => 'badge-soft-dark',
        };
    };
    $currentStatus = request('status');
@endphp

@extends('layouts.vendor.app')

@section('title', translate('My Listings') ?: 'My Listings')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">{{ translate('My Listings') ?: 'My Listings' }}</h1>
                <p class="text-muted mb-0">{{ translate('Manage your classified ads') ?: 'Manage your classified ads' }}</p>
            </div>
            <div class="col-sm-auto">
                <a href="{{ route('vendor.classify.listings.create') }}" class="btn btn-primary">
                    <i class="tio-add"></i> {{ translate('Add Listing') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row g-2 g-sm-3 mb-3">
        @foreach([
            ['key' => null, 'label' => translate('All') ?: 'All', 'count' => $statusCounts['all'] ?? 0],
            ['key' => 'published', 'label' => translate('Published') ?: 'Published', 'count' => $statusCounts['published'] ?? 0],
            ['key' => 'pending', 'label' => translate('Pending') ?: 'Pending', 'count' => $statusCounts['pending'] ?? 0],
            ['key' => 'sold', 'label' => translate('Sold') ?: 'Sold', 'count' => $statusCounts['sold'] ?? 0],
        ] as $chip)
            <div class="col-6 col-md-3">
                <a href="{{ route('vendor.classify.listings.index', $chip['key'] ? ['status' => $chip['key']] : []) }}"
                   class="card card-hover-shadow h-100 text-decoration-none {{ ($currentStatus === $chip['key']) || (!$currentStatus && !$chip['key']) ? 'border-primary' : '' }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-body">{{ $chip['label'] }}</span>
                            <span class="h4 mb-0 text-dark">{{ $chip['count'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header border-0">
            <h5 class="card-header-title">{{ translate('Listings') }}</h5>
        </div>
        <div class="table-responsive datatable-custom">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                <tr>
                    <th style="width:72px">{{ translate('Image') }}</th>
                    <th>{{ translate('Listing') }}</th>
                    <th>{{ translate('Category') }}</th>
                    <th>{{ translate('Price') }}</th>
                    <th>{{ translate('Condition') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th class="text-end">{{ translate('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($listings as $listing)
                    <tr>
                        <td>
                            <a href="{{ route('vendor.classify.listings.show', $listing->id) }}">
                                <img class="avatar avatar-lg avatar-4by3 rounded"
                                     src="{{ $listing->primary_image_full_url }}"
                                     alt="{{ $listing->title }}"
                                     onerror="this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}'">
                            </a>
                        </td>
                        <td>
                            <a class="d-block font-weight-bold text-dark mb-1"
                               href="{{ route('vendor.classify.listings.show', $listing->id) }}">
                                {{ Str::limit($listing->title, 48) }}
                            </a>
                            <small class="text-muted">
                                {{ translate('Updated') }}: {{ $listing->updated_at?->format('M d, Y') }}
                            </small>
                            @if($listing->is_premium || $listing->is_featured)
                                <div class="mt-1">
                                    @if($listing->is_premium)
                                        <span class="badge badge-soft-warning badge-pill">{{ translate('Premium') }}</span>
                                    @endif
                                    @if($listing->is_featured)
                                        <span class="badge badge-soft-info badge-pill">{{ translate('Featured') }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>{{ $listing->category?->name ?? '—' }}</td>
                        <td>
                            <span class="font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($listing->price) }}</span>
                            @if($listing->is_negotiable)
                                <br><small class="text-muted">{{ translate('Negotiable') }}</small>
                            @endif
                        </td>
                        <td><span class="text-capitalize">{{ $listing->condition }}</span></td>
                        <td>
                            <span class="badge {{ $statusBadge($listing->status) }} text-capitalize">
                                {{ $listing->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('vendor.classify.listings.show', $listing->id) }}"
                                   class="btn btn-sm btn-white" title="{{ translate('View') }}">
                                    <i class="tio-visible-outlined"></i>
                                </a>
                                <a href="{{ route('vendor.classify.listings.edit', $listing->id) }}"
                                   class="btn btn-sm btn-white" title="{{ translate('Edit') }}">
                                    <i class="tio-edit"></i>
                                </a>
                                <form action="{{ route('vendor.classify.listings.destroy', $listing->id) }}" method="post"
                                      class="d-inline"
                                      onsubmit="return confirm('{{ translate('Delete this listing permanently?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-white text-danger" title="{{ translate('Delete') }}">
                                        <i class="tio-delete"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <img class="mb-3" src="{{ asset('public/assets/admin/img/empty-state.png') }}" alt="" width="120"
                                 onerror="this.style.display='none'">
                            <p class="mb-2 text-muted">{{ translate('No listings yet') ?: 'No listings yet' }}</p>
                            <a href="{{ route('vendor.classify.listings.create') }}" class="btn btn-sm btn-primary">
                                {{ translate('Create your first listing') }}
                            </a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($listings->hasPages())
            <div class="card-footer">
                {!! $listings->links() !!}
            </div>
        @endif
    </div>
</div>
@endsection

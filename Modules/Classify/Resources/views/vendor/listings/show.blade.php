@php
    $statusBadge = match ($listing->status) {
        'published' => 'badge-soft-success',
        'pending' => 'badge-soft-warning',
        'sold' => 'badge-soft-info',
        'archived' => 'badge-soft-secondary',
        'rejected' => 'badge-soft-danger',
        default => 'badge-soft-dark',
    };
    $images = $listing->images_full_url ?? [];
    if (empty($images) && $listing->primary_image_full_url) {
        $images = [$listing->primary_image_full_url];
    }
@endphp

@extends('layouts.vendor.app')

@section('title', $listing->title)

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item">
                            <a href="{{ route('vendor.classify.listings.index') }}">{{ translate('My Listings') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($listing->title, 40) }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h1 class="page-header-title mb-0">{{ $listing->title }}</h1>
                    <span class="badge {{ $statusBadge }} text-capitalize">{{ $listing->status }}</span>
                    @if($listing->is_premium)
                        <span class="badge badge-soft-warning">{{ translate('Premium') }}</span>
                    @endif
                    @if($listing->is_featured)
                        <span class="badge badge-soft-info">{{ translate('Featured') }}</span>
                    @endif
                </div>
            </div>
            <div class="col-sm-auto">
                <div class="d-flex flex-wrap gap-2 justify-content-sm-end">
                    <a href="{{ route('vendor.classify.listings.edit', $listing->id) }}" class="btn btn-primary btn-sm">
                        <i class="tio-edit"></i> {{ translate('Edit') }}
                    </a>
                    @if($listing->status !== 'sold')
                        <form action="{{ route('vendor.classify.listings.sold', $listing->id) }}" method="post" class="d-inline">@csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="tio-checkmark-circle"></i> {{ translate('Mark Sold') }}
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('vendor.classify.listings.renew', $listing->id) }}" method="post" class="d-inline">@csrf
                        <button type="submit" class="btn btn-white btn-sm">
                            <i class="tio-refresh"></i> {{ translate('Renew') }}
                        </button>
                    </form>
                    <form action="{{ route('vendor.classify.listings.archive', $listing->id) }}" method="post" class="d-inline"
                          onsubmit="return confirm('{{ translate('Archive this listing?') }}');">@csrf
                        <button type="submit" class="btn btn-white btn-sm text-danger">
                            <i class="tio-archive"></i> {{ translate('Archive') }}
                        </button>
                    </form>
                    <form action="{{ route('vendor.classify.listings.destroy', $listing->id) }}" method="post" class="d-inline"
                          onsubmit="return confirm('{{ translate('Delete this listing permanently? This cannot be undone.') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="tio-delete"></i> {{ translate('Delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    @if(count($images))
                        <div class="row g-2 mb-4">
                            @foreach($images as $img)
                                <div class="col-6 col-md-4">
                                    <img src="{{ $img }}" alt="" class="img-fluid rounded border w-100"
                                         style="object-fit:cover;aspect-ratio:4/3;max-height:200px;">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">{{ translate('Price') }}</small>
                                <span class="h4 mb-0">{{ \App\CentralLogics\Helpers::format_currency($listing->price) }}</span>
                                @if($listing->is_negotiable)
                                    <small class="text-muted d-block">{{ translate('Negotiable') }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">{{ translate('Condition') }}</small>
                                <span class="h5 mb-0 text-capitalize">{{ $listing->condition }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">{{ translate('Category') }}</small>
                                <span class="h5 mb-0">{{ $listing->category?->name ?? '—' }}</span>
                                @if($listing->subCategory)
                                    <small class="text-muted d-block">{{ $listing->subCategory->name }}</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-2">{{ translate('Description') }}</h5>
                    <div class="text-body" style="white-space: pre-line;">{{ $listing->description ?: '—' }}</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-header-title mb-0">{{ translate('Contact & location') }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-muted">{{ translate('Phone') }}</dt>
                        <dd class="col-sm-9">{{ $listing->phone ?: '—' }}</dd>
                        <dt class="col-sm-3 text-muted">{{ translate('Address') }}</dt>
                        <dd class="col-sm-9">{{ $listing->address ?: '—' }}</dd>
                        @if($listing->published_at)
                            <dt class="col-sm-3 text-muted">{{ translate('Published') }}</dt>
                            <dd class="col-sm-9">{{ $listing->published_at->format('M d, Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-header-title mb-0">{{ translate('Performance') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <div class="h3 mb-0 text-primary">{{ $stats['views_count'] }}</div>
                                <small class="text-muted">{{ translate('Views') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <div class="h3 mb-0 text-danger">{{ $stats['favorites_count'] }}</div>
                                <small class="text-muted">{{ translate('Favorites') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <div class="h3 mb-0 text-success">{{ $stats['chats_count'] }}</div>
                                <small class="text-muted">{{ translate('Chats') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <div class="h3 mb-0 text-dark">{{ $listing->images?->count() ?? 0 }}</div>
                                <small class="text-muted">{{ translate('Photos') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-header-title mb-0">{{ translate('Listing timeline') }}</h5>
                </div>
                <ul class="list-group list-group-flush list-group-no-gutters">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">{{ translate('Expires') }}</span>
                        <span>{{ $stats['expires_at'] ? \Carbon\Carbon::parse($stats['expires_at'])->format('M d, Y') : '—' }}</span>
                    </li>
                    @if($stats['sold_at'])
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">{{ translate('Sold on') }}</span>
                            <span>{{ \Carbon\Carbon::parse($stats['sold_at'])->format('M d, Y') }}</span>
                        </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">{{ translate('Last updated') }}</span>
                        <span>{{ $listing->updated_at?->format('M d, Y') }}</span>
                    </li>
                </ul>
                <div class="card-body border-top">
                    <a href="{{ route('vendor.classify.chats.index') }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="tio-chat"></i> {{ translate('View classified chats') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

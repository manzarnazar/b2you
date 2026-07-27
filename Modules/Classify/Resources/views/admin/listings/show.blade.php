@php
    $statusBadge = match ($listing->status) {
        'published' => 'badge-soft-success',
        'pending' => 'badge-soft-warning',
        'sold' => 'badge-soft-info',
        'expired', 'archived' => 'badge-soft-secondary',
        'rejected' => 'badge-soft-danger',
        default => 'badge-soft-dark',
    };
    $images = $listing->images_full_url ?? [];
    if (empty($images) && $listing->primary_image_full_url) {
        $images = [$listing->primary_image_full_url];
    }
@endphp

@extends('layouts.admin.app')

@section('title', $listing->title)

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.classify.listings.index') }}">{{ translate('Listings') }}</a>
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
                    <a href="{{ route('admin.classify.listings.index') }}" class="btn btn-white btn-sm">{{ translate('Back') }}</a>
                    <a href="{{ route('admin.classify.listings.edit', $listing->id) }}" class="btn btn-primary btn-sm">
                        <i class="tio-edit"></i> {{ translate('Edit') }}
                    </a>
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

                    @if($listing->rejection_reason)
                        <div class="alert alert-soft-danger mt-3 mb-0">
                            <strong>{{ translate('Rejection reason') }}:</strong> {{ $listing->rejection_reason }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-header-title mb-0">{{ translate('Seller & location') }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-muted">{{ translate('Store') }}</dt>
                        <dd class="col-sm-9">{{ $listing->store->name ?? '—' }}</dd>
                        <dt class="col-sm-3 text-muted">{{ translate('Phone') }}</dt>
                        <dd class="col-sm-9">{{ $listing->phone ?: '—' }}</dd>
                        <dt class="col-sm-3 text-muted">{{ translate('Address') }}</dt>
                        <dd class="col-sm-9">{{ $listing->address ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-header-title mb-0">{{ translate('Moderation') }}</h5>
                </div>
                <div class="card-body">
                    @if($listing->status === 'pending')
                        <form action="{{ route('admin.classify.listings.approve', $listing->id) }}" method="post" class="mb-2">@csrf
                            <button class="btn btn-success btn-block btn-sm">
                                <i class="tio-checkmark-circle"></i> {{ translate('Approve') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.classify.listings.reject', $listing->id) }}" method="post" class="mb-3">@csrf
                            <textarea name="reason" class="form-control form-control-sm mb-2" rows="2"
                                      placeholder="{{ translate('Rejection reason') }}"></textarea>
                            <button class="btn btn-danger btn-block btn-sm">{{ translate('Reject') }}</button>
                        </form>
                    @endif
                    <form action="{{ route('admin.classify.listings.feature', $listing->id) }}" method="post" class="mb-2">@csrf
                        <input type="hidden" name="status" value="{{ $listing->is_featured ? 0 : 1 }}">
                        <input type="hidden" name="days" value="7">
                        <button class="btn btn-white btn-block btn-sm">
                            {{ $listing->is_featured ? translate('Remove featured') : translate('Mark featured') }}
                        </button>
                    </form>
                    <form action="{{ route('admin.classify.listings.premium', $listing->id) }}" method="post" class="mb-3">@csrf
                        <input type="hidden" name="status" value="{{ $listing->is_premium ? 0 : 1 }}">
                        <input type="hidden" name="days" value="7">
                        <button class="btn btn-white btn-block btn-sm">
                            {{ $listing->is_premium ? translate('Remove premium') : translate('Mark premium') }}
                        </button>
                    </form>
                    <form action="{{ route('admin.classify.listings.destroy', $listing->id) }}" method="post"
                          onsubmit="return confirm('{{ translate('Delete this listing permanently? This cannot be undone.') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block btn-sm">
                            <i class="tio-delete"></i> {{ translate('Delete listing') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-header-title mb-0">{{ translate('Performance') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <div class="h3 mb-0 text-primary">{{ $listing->views_count }}</div>
                                <small class="text-muted">{{ translate('Views') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <div class="h3 mb-0 text-danger">{{ $listing->favorites_count }}</div>
                                <small class="text-muted">{{ translate('Favorites') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <div class="h3 mb-0 text-success">{{ $listing->chats_count }}</div>
                                <small class="text-muted">{{ translate('Chats') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <div class="h3 mb-0 text-dark">{{ $listing->reports?->count() ?? 0 }}</div>
                                <small class="text-muted">{{ translate('Reports') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <ul class="list-group list-group-flush list-group-no-gutters">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">{{ translate('Expires') }}</span>
                        <span>{{ $listing->expires_at ? $listing->expires_at->format('M d, Y') : '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">{{ translate('Published') }}</span>
                        <span>{{ $listing->published_at ? $listing->published_at->format('M d, Y') : '—' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

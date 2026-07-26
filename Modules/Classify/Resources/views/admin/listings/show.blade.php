@extends('layouts.admin.app')

@section('title', $listing->title)

@section('content')
<div class="content container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1 class="page-header-title">{{ $listing->title }}</h1>
        <a href="{{ route('admin.classify.listings.index') }}" class="btn btn-secondary">{{ translate('Back') }}</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <p><strong>{{ translate('Status') }}:</strong> {{ $listing->status }}</p>
                    <p><strong>{{ translate('Price') }}:</strong> {{ \App\CentralLogics\Helpers::format_currency($listing->price) }}</p>
                    <p><strong>{{ translate('Condition') }}:</strong> {{ $listing->condition }}</p>
                    <p><strong>{{ translate('Category') }}:</strong> {{ $listing->category->name ?? '-' }} / {{ $listing->subCategory->name ?? '-' }}</p>
                    <p><strong>{{ translate('Store') }}:</strong> {{ $listing->store->name ?? '-' }}</p>
                    <p><strong>{{ translate('Phone') }}:</strong> {{ $listing->phone }}</p>
                    <p><strong>{{ translate('Address') }}:</strong> {{ $listing->address }}</p>
                    <p>{{ $listing->description }}</p>
                    @if($listing->rejection_reason)
                        <p class="text-danger"><strong>{{ translate('Rejection reason') }}:</strong> {{ $listing->rejection_reason }}</p>
                    @endif
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($listing->images as $img)
                            <img src="{{ \App\CentralLogics\Helpers::get_full_url('classify', $img->image, $img->storage) }}" style="height:100px;object-fit:cover;border-radius:8px;" alt="">
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">{{ translate('Moderation') }}</div>
                <div class="card-body">
                    @if($listing->status === 'pending')
                        <form action="{{ route('admin.classify.listings.approve', $listing->id) }}" method="post" class="mb-2">@csrf
                            <button class="btn btn-success btn-block">{{ translate('Approve') }}</button>
                        </form>
                        <form action="{{ route('admin.classify.listings.reject', $listing->id) }}" method="post" class="mb-2">@csrf
                            <textarea name="reason" class="form-control mb-2" placeholder="{{ translate('Reason') }}"></textarea>
                            <button class="btn btn-danger btn-block">{{ translate('Reject') }}</button>
                        </form>
                    @endif
                    <form action="{{ route('admin.classify.listings.feature', $listing->id) }}" method="post" class="mb-2">@csrf
                        <input type="hidden" name="status" value="{{ $listing->is_featured ? 0 : 1 }}">
                        <input type="hidden" name="days" value="7">
                        <button class="btn btn-info btn-block">{{ $listing->is_featured ? 'Unfeature' : 'Feature' }}</button>
                    </form>
                    <form action="{{ route('admin.classify.listings.premium', $listing->id) }}" method="post" class="mb-2">@csrf
                        <input type="hidden" name="status" value="{{ $listing->is_premium ? 0 : 1 }}">
                        <input type="hidden" name="days" value="7">
                        <button class="btn btn-warning btn-block">{{ $listing->is_premium ? 'Remove Premium' : 'Make Premium' }}</button>
                    </form>
                    <form action="{{ route('admin.classify.listings.destroy', $listing->id) }}" method="post" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-block">{{ translate('Delete') }}</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">{{ translate('Stats') }}</div>
                <div class="card-body">
                    <p>{{ translate('Views') }}: {{ $listing->views_count }}</p>
                    <p>{{ translate('Favorites') }}: {{ $listing->favorites_count }}</p>
                    <p>{{ translate('Chats') }}: {{ $listing->chats_count }}</p>
                    <p>{{ translate('Expires') }}: {{ $listing->expires_at }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

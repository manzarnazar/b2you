@extends('layouts.admin.app')

@section('title', translate('Edit listing') ?: 'Edit listing')

@section('content')
<div class="content container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1 class="page-header-title">{{ translate('Edit listing') ?: 'Edit listing' }}</h1>
        <a href="{{ route('admin.classify.listings.show', $listing->id) }}" class="btn btn-secondary">{{ translate('Back') }}</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.classify.listings.update', $listing->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('classify::admin.listings._form', ['listing' => $listing, 'categories' => $categories])
                <button type="submit" class="btn btn-primary">{{ translate('messages.update') ?: 'Update' }}</button>
            </form>
            <hr class="my-4">
            <form action="{{ route('admin.classify.listings.destroy', $listing->id) }}" method="post"
                  onsubmit="return confirm('{{ translate('Delete this listing permanently? This cannot be undone.') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="tio-delete"></i> {{ translate('Delete listing') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

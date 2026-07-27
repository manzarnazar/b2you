@extends('layouts.vendor.app')

@section('title', translate('Edit Listing') ?: 'Edit Listing')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">{{ translate('Edit Listing') ?: 'Edit Listing' }}</h1>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('vendor.classify.listings.update', $listing->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @include('classify::vendor.listings._form', ['listing' => $listing])
                <button class="btn btn-primary">{{ translate('Update') }}</button>
                <a href="{{ route('vendor.classify.listings.index') }}" class="btn btn-white">{{ translate('Cancel') }}</a>
            </form>
            <hr class="my-4">
            <form action="{{ route('vendor.classify.listings.destroy', $listing->id) }}" method="post"
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

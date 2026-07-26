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
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.vendor.app')

@section('title', translate('Add Listing') ?: 'Add Listing')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">{{ translate('Add Listing') ?: 'Add Listing' }}</h1>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('vendor.classify.listings.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                @include('classify::vendor.listings._form', ['listing' => null])
                <button class="btn btn-primary">{{ translate('Submit') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

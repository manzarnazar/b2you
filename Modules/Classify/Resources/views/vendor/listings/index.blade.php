@extends('layouts.vendor.app')

@section('title', translate('My Listings') ?: 'My Listings')

@section('content')
<div class="content container-fluid">
    <div class="page-header d-flex justify-content-between">
        <h1 class="page-header-title">{{ translate('My Listings') ?: 'My Listings' }}</h1>
        <a href="{{ route('vendor.classify.listings.create') }}" class="btn btn-primary">{{ translate('Add Listing') }}</a>
    </div>
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Title') }}</th>
                    <th>{{ translate('Price') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($listings as $key => $listing)
                    <tr>
                        <td>{{ $listings->firstItem() + $key }}</td>
                        <td><a href="{{ route('vendor.classify.listings.show', $listing->id) }}">{{ $listing->title }}</a></td>
                        <td>{{ \App\CentralLogics\Helpers::format_currency($listing->price) }}</td>
                        <td>{{ $listing->status }}</td>
                        <td>
                            <a href="{{ route('vendor.classify.listings.edit', $listing->id) }}" class="btn btn-sm btn-white"><i class="tio-edit"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">{{ translate('No data found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
            {!! $listings->links() !!}
        </div>
    </div>
</div>
@endsection

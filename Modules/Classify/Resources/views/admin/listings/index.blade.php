@extends('layouts.admin.app')

@section('title', translate('Classify Listings') ?: 'Classify Listings')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">{{ translate('Classify Listings') ?: 'Classify Listings' }}</h1>
    </div>

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap gap-2">
            @foreach(['all','pending','published','rejected','sold','expired'] as $st)
                <a href="{{ route('admin.classify.listings.index', $st === 'all' ? [] : ['status' => $st]) }}"
                   class="btn btn-sm {{ request('status', 'all') == $st || ($st==='all' && !request('status')) ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ ucfirst($st) }} ({{ $statusCounts[$st] ?? 0 }})
                </a>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form class="d-flex">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ translate('Search') }}">
                <button class="btn btn-primary ml-2">{{ translate('Search') }}</button>
            </form>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-borderless table-thead-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Title') }}</th>
                    <th>{{ translate('Store') }}</th>
                    <th>{{ translate('Price') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($listings as $key => $listing)
                    <tr>
                        <td>{{ $listings->firstItem() + $key }}</td>
                        <td>
                            <a href="{{ route('admin.classify.listings.show', $listing->id) }}">{{ $listing->title }}</a>
                            @if($listing->is_featured)<span class="badge badge-soft-info">Featured</span>@endif
                            @if($listing->is_premium)<span class="badge badge-soft-warning">Premium</span>@endif
                        </td>
                        <td>{{ $listing->store->name ?? '-' }}</td>
                        <td>{{ \App\CentralLogics\Helpers::format_currency($listing->price) }}</td>
                        <td><span class="badge badge-soft-secondary">{{ $listing->status }}</span></td>
                        <td>
                            <a class="btn btn-sm btn-white" href="{{ route('admin.classify.listings.show', $listing->id) }}"><i class="tio-visible"></i></a>
                            @if($listing->status === 'pending')
                                <form action="{{ route('admin.classify.listings.approve', $listing->id) }}" method="post" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-success"><i class="tio-checkmark"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">{{ translate('No data found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
            {!! $listings->links() !!}
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin.app')

@section('title', translate('Listing Reports') ?: 'Listing Reports')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">{{ translate('Listing Reports') ?: 'Listing Reports' }}</h1>
    </div>
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Listing') }}</th>
                    <th>{{ translate('User') }}</th>
                    <th>{{ translate('Reason') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($reports as $key => $report)
                    <tr>
                        <td>{{ $reports->firstItem() + $key }}</td>
                        <td>
                            @if($report->listing)
                                <a href="{{ route('admin.classify.listings.show', $report->listing_id) }}">{{ $report->listing->title }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $report->user->f_name ?? '-' }} {{ $report->user->l_name ?? '' }}</td>
                        <td>{{ $report->reason }}<br><small>{{ $report->note }}</small></td>
                        <td>{{ $report->status }}</td>
                        <td>
                            @if($report->status === 'pending')
                                <form action="{{ route('admin.classify.reports.resolve', $report->id) }}" method="post" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-success">{{ translate('Resolve') }}</button>
                                </form>
                                <form action="{{ route('admin.classify.reports.dismiss', $report->id) }}" method="post" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-secondary">{{ translate('Dismiss') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">{{ translate('No data found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
            {!! $reports->links() !!}
        </div>
    </div>
</div>
@endsection

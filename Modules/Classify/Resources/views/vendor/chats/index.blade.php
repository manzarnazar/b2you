@extends('layouts.vendor.app')

@section('title', translate('Classified chats') ?: 'Classified chats')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">{{ translate('Classified chats') ?: 'Classified chats' }}</h1>
        <p class="text-muted mb-0">{{ translate('Buyer messages about your classified listings') }}</p>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>{{ translate('Buyer') }}</th>
                        <th>{{ translate('About listing') ?: 'About listing' }}</th>
                        <th>{{ translate('Last message') }}</th>
                        <th>{{ translate('Unread') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conv)
                        <tr>
                            <td>{{ trim(($conv->customer?->f_name ?? '') . ' ' . ($conv->customer?->l_name ?? '')) ?: '-' }}</td>
                            <td>{{ $conv->listing?->title ?? '—' }}</td>
                            <td class="text-truncate" style="max-width:280px">{{ $conv->lastMessage?->message ?? '-' }}</td>
                            <td>
                                @if($conv->unread_vendor > 0)
                                    <span class="badge badge-danger">{{ $conv->unread_vendor }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('vendor.classify.chats.show', $conv->id) }}" class="btn btn-sm btn-primary">
                                    {{ translate('Open') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">{{ translate('No chats yet') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {!! $conversations->links() !!}
    </div>
</div>
@endsection

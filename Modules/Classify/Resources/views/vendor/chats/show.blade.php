@extends('layouts.vendor.app')

@section('title', translate('Chat') ?: 'Chat')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">{{ $conversation->listing?->title ?? translate('Chat') }}</h1>
                <p class="mb-0 text-muted">
                    {{ translate('Buyer') }}:
                    {{ trim(($conversation->customer?->f_name ?? '') . ' ' . ($conversation->customer?->l_name ?? '')) }}
                </p>
            </div>
            <div class="col-sm-auto">
                <a href="{{ route('vendor.classify.chats.index') }}" class="btn btn-secondary">{{ translate('Back') }}</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="max-height: 420px; overflow-y: auto;">
            @foreach($messages as $msg)
                <div class="mb-3 d-flex {{ $msg->sender_type === 'vendor' ? 'justify-content-end' : 'justify-content-start' }}">
                    <div class="p-2 rounded {{ $msg->sender_type === 'vendor' ? 'bg-primary text-white' : 'bg-light' }}" style="max-width: 75%;">
                        <div>{{ $msg->message }}</div>
                        <small class="d-block mt-1 opacity-75">{{ $msg->created_at?->format('M d, H:i') }}</small>
                    </div>
                </div>
            @endforeach
            {!! $messages->links() !!}
        </div>
        <div class="card-footer">
            <form method="post" action="{{ route('vendor.classify.chats.send', $conversation->id) }}">
                @csrf
                <div class="input-group">
                    <input type="text" name="message" class="form-control" placeholder="{{ translate('Type a message') }}" required maxlength="2000">
                    <button type="submit" class="btn btn-primary">{{ translate('Send') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

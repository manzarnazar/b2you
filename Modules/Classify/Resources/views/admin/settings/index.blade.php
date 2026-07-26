@extends('layouts.admin.app')

@section('title', translate('Classify Settings') ?: 'Classify Settings')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">{{ translate('Classify Settings') ?: 'Classify Settings' }}</h1>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.classify.settings.update') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>{{ translate('Listing Duration (days)') }}</label>
                        <input type="number" name="classify_listing_duration_days" class="form-control" value="{{ $settings['classify_listing_duration_days'] }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ translate('Listing Fee') }}</label>
                        <input type="number" step="0.01" name="classify_listing_fee" class="form-control" value="{{ $settings['classify_listing_fee'] }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ translate('Commission %') }}</label>
                        <input type="number" step="0.01" name="classify_commission_percent" class="form-control" value="{{ $settings['classify_commission_percent'] }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ translate('Max Images') }}</label>
                        <input type="number" name="classify_max_images" class="form-control" value="{{ $settings['classify_max_images'] }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ translate('Premium Fee') }}</label>
                        <input type="number" step="0.01" name="classify_premium_fee" class="form-control" value="{{ $settings['classify_premium_fee'] }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ translate('Premium Duration (days)') }}</label>
                        <input type="number" name="classify_premium_duration_days" class="form-control" value="{{ $settings['classify_premium_duration_days'] }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ translate('Featured Fee') }}</label>
                        <input type="number" step="0.01" name="classify_featured_fee" class="form-control" value="{{ $settings['classify_featured_fee'] }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ translate('Featured Duration (days)') }}</label>
                        <input type="number" name="classify_featured_duration_days" class="form-control" value="{{ $settings['classify_featured_duration_days'] }}">
                    </div>
                    <div class="col-md-12 form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="classify_approval_required" value="1" {{ $settings['classify_approval_required'] ? 'checked' : '' }}>
                            <label class="form-check-label">{{ translate('Approval Required') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="classify_auto_expiry" value="1" {{ $settings['classify_auto_expiry'] ? 'checked' : '' }}>
                            <label class="form-check-label">{{ translate('Auto Expiry') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="classify_premium_enabled" value="1" {{ $settings['classify_premium_enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label">{{ translate('Premium Listings Enabled') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="classify_featured_enabled" value="1" {{ $settings['classify_featured_enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label">{{ translate('Featured Listings Enabled') }}</label>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary">{{ translate('Save') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

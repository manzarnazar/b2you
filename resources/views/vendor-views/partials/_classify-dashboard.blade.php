@php
    $greeting = data_get($dashboard, 'greeting', 'Good Morning');
    $vendorName = data_get($dashboard, 'vendor_name', 'Seller');
    $activeListings = (int) data_get($dashboard, 'active_listings', 0);
    $overview = data_get($dashboard, 'overview', []);
    $performance = data_get($dashboard, 'performance', []);
    $status = data_get($dashboard, 'status', []);
    $recentListings = data_get($dashboard, 'recent_listings', collect());
    $topPerformers = data_get($dashboard, 'top_performers', collect());
    $recentChats = data_get($dashboard, 'recent_chats', collect());

    $statusBadgeClass = function ($state) {
        return match ($state) {
            'published' => 'badge-soft-success',
            'pending' => 'badge-soft-warning',
            'rejected' => 'badge-soft-danger',
            'expired' => 'badge-soft-secondary',
            'archived' => 'badge-soft-dark',
            'sold' => 'badge-soft-info',
            default => 'badge-soft-light',
        };
    };
@endphp

<style>
    .classify-dashboard-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    .classify-dashboard-card {
        border: 1px solid #e6edf3;
        border-radius: 10px;
        background: #fff;
        padding: 14px;
    }
    .classify-dashboard-number {
        font-size: 22px;
        font-weight: 700;
        color: #25396f;
    }
    .classify-dashboard-label {
        color: #6f7d95;
        font-size: 12px;
    }
    .classify-dashboard-list-item {
        border-bottom: 1px solid #edf2f7;
        padding: 12px 0;
    }
    .classify-dashboard-list-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-sm">
            <h1 class="page-header-title mb-1">{{ $greeting }}, {{ $vendorName }} <span aria-hidden="true">👋</span></h1>
            <p class="mb-0 text-muted">You have {{ number_format($activeListings) }} active listings</p>
        </div>
        <div class="col-sm-auto mt-3 mt-sm-0">
            <a href="{{ route('vendor.classify.listings.create') }}" class="btn btn-primary">
                <i class="tio-add"></i> + Create Listing
            </a>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h4 class="mb-3">Overview</h4>
        <div class="classify-dashboard-grid">
            <div class="classify-dashboard-card">
                <div class="classify-dashboard-number">{{ number_format((int) data_get($overview, 'active', 0)) }}</div>
                <div class="classify-dashboard-label">Active Listings</div>
            </div>
            <div class="classify-dashboard-card">
                <div class="classify-dashboard-number">{{ number_format((int) data_get($overview, 'pending', 0)) }}</div>
                <div class="classify-dashboard-label">Pending Approval</div>
            </div>
            <div class="classify-dashboard-card">
                <div class="classify-dashboard-number">{{ number_format((int) data_get($overview, 'sold', 0)) }}</div>
                <div class="classify-dashboard-label">Sold Listings</div>
            </div>
            <div class="classify-dashboard-card">
                <div class="classify-dashboard-number">{{ number_format((int) data_get($overview, 'expired', 0)) }}</div>
                <div class="classify-dashboard-label">Expired Listings</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h4 class="mb-3">Performance</h4>
        <div class="classify-dashboard-grid">
            <div class="classify-dashboard-card">
                <div class="classify-dashboard-number">{{ number_format((int) data_get($performance, 'views', 0)) }}</div>
                <div class="classify-dashboard-label">Total Views</div>
            </div>
            <div class="classify-dashboard-card">
                <div class="classify-dashboard-number">{{ number_format((int) data_get($performance, 'favorites', 0)) }}</div>
                <div class="classify-dashboard-label">Favorites</div>
            </div>
            <div class="classify-dashboard-card">
                <div class="classify-dashboard-number">{{ number_format((int) data_get($performance, 'chats_started', 0)) }}</div>
                <div class="classify-dashboard-label">Chats Started</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h4 class="mb-3">Listing Status</h4>
        <div class="classify-dashboard-grid">
            <div class="classify-dashboard-card"><div class="classify-dashboard-number">{{ number_format((int) data_get($status, 'published', 0)) }}</div><div class="classify-dashboard-label">Published</div></div>
            <div class="classify-dashboard-card"><div class="classify-dashboard-number">{{ number_format((int) data_get($status, 'pending', 0)) }}</div><div class="classify-dashboard-label">Pending</div></div>
            <div class="classify-dashboard-card"><div class="classify-dashboard-number">{{ number_format((int) data_get($status, 'rejected', 0)) }}</div><div class="classify-dashboard-label">Rejected</div></div>
            <div class="classify-dashboard-card"><div class="classify-dashboard-number">{{ number_format((int) data_get($status, 'expired', 0)) }}</div><div class="classify-dashboard-label">Expired</div></div>
            <div class="classify-dashboard-card"><div class="classify-dashboard-number">{{ number_format((int) data_get($status, 'archived', 0)) }}</div><div class="classify-dashboard-label">Archived</div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h4 class="mb-3">Recent Listings</h4>
                @forelse($recentListings as $listing)
                    <div class="classify-dashboard-list-item">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ $listing->title }}</h5>
                                <span class="badge {{ $statusBadgeClass($listing->status) }}">{{ ucfirst($listing->status) }}</span>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">
                            {{ number_format((int) $listing->views_count) }} views &nbsp;|&nbsp;
                            {{ number_format((int) $listing->favorites_count) }} favorites
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No listings yet</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h4 class="mb-3">Top Performing Listings</h4>
                @forelse($topPerformers as $index => $listing)
                    <div class="classify-dashboard-list-item d-flex align-items-center justify-content-between">
                        <div>
                            <strong>{{ $index + 1 }}.</strong> {{ $listing->title }}
                        </div>
                        <small class="text-muted">{{ number_format((int) $listing->views_count) }} views</small>
                    </div>
                @empty
                    <p class="text-muted mb-0">No listings yet</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">Recent Chats</h4>
                @forelse($recentChats as $chat)
                    <div class="classify-dashboard-list-item">
                        <h6 class="mb-1">{{ data_get($chat, 'customer_name', 'Customer') }}:</h6>
                        <p class="mb-0 text-muted">{{ data_get($chat, 'message', '') }}</p>
                    </div>
                @empty
                    <p class="text-muted mb-0">No recent chats</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

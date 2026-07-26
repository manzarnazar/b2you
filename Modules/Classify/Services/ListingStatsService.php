<?php

namespace Modules\Classify\Services;

use Modules\Classify\Entities\ClassifyListing;

class ListingStatsService
{
    public function forListing(ClassifyListing $listing): array
    {
        return [
            'views_count' => (int) $listing->views_count,
            'favorites_count' => (int) $listing->favorites_count,
            'chats_count' => (int) $listing->chats_count,
            'status' => $listing->status,
            'is_featured' => (bool) $listing->is_featured,
            'is_premium' => (bool) $listing->is_premium,
            'expires_at' => $listing->expires_at,
            'published_at' => $listing->published_at,
            'sold_at' => $listing->sold_at,
        ];
    }
}

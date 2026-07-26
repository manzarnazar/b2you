<?php

namespace Modules\Classify\Services;

use Modules\Classify\Entities\ClassifyListing;

class ListingExpiryService
{
    public function expireDueListings(): int
    {
        return ClassifyListing::query()
            ->where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }
}

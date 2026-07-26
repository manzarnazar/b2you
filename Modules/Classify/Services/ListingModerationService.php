<?php

namespace Modules\Classify\Services;

use App\CentralLogics\Helpers;
use App\Models\Store;
use Modules\Classify\Entities\ClassifyListing;

class ListingModerationService
{
    public function approve(ClassifyListing $listing): ClassifyListing
    {
        $listing->update([
            'status' => 'published',
            'is_approved' => 1,
            'published_at' => now(),
            'rejection_reason' => null,
        ]);
        $this->notifyStore($listing, 'listing_approved', translate('messages.your_listing_has_been_approved') ?: 'Your listing has been approved');
        return $listing;
    }

    public function reject(ClassifyListing $listing, ?string $reason = null): ClassifyListing
    {
        $listing->update([
            'status' => 'rejected',
            'is_approved' => 0,
            'rejection_reason' => $reason,
        ]);
        $this->notifyStore($listing, 'listing_rejected', $reason ?: (translate('messages.your_listing_has_been_rejected') ?: 'Your listing has been rejected'));
        return $listing;
    }

    public function setFeatured(ClassifyListing $listing, bool $featured, ?int $days = null): ClassifyListing
    {
        $listing->update([
            'is_featured' => $featured ? 1 : 0,
            'featured_until' => $featured ? now()->addDays($days ?: 7) : null,
        ]);
        return $listing;
    }

    public function setPremium(ClassifyListing $listing, bool $premium, ?int $days = null): ClassifyListing
    {
        $listing->update([
            'is_premium' => $premium ? 1 : 0,
            'premium_until' => $premium ? now()->addDays($days ?: 7) : null,
        ]);
        return $listing;
    }

    protected function notifyStore(ClassifyListing $listing, string $type, string $description): void
    {
        try {
            $store = Store::find($listing->store_id);
            if (!$store || !$store->vendor) {
                return;
            }
            $vendor = $store->vendor;
            $data = [
                'title' => translate('messages.listing_update') ?: 'Listing update',
                'description' => $description,
                'order_id' => '',
                'image' => '',
                'type' => $type,
                'module_id' => $listing->module_id,
                'listing_id' => $listing->id,
            ];
            if (!empty($vendor->firebase_token)) {
                Helpers::send_push_notif_to_device($vendor->firebase_token, $data);
            }
        } catch (\Throwable $e) {
            // non-blocking
        }
    }
}

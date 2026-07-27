<?php

namespace Modules\Classify\Services;

use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use Modules\Classify\Entities\ClassifyConversation;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyListingFavorite;
use Modules\Classify\Entities\ClassifyListingImage;
use Modules\Classify\Entities\ClassifyMessage;

class ListingService
{
    public function getSetting(string $key, $default = null)
    {
        $row = BusinessSetting::where('key', $key)->first();
        if (!$row) {
            return $default;
        }
        return $row->value;
    }

    public function approvalRequired(): bool
    {
        return (bool) $this->getSetting('classify_approval_required', 1);
    }

    public function listingDurationDays(): int
    {
        return (int) $this->getSetting('classify_listing_duration_days', config('classify.default_duration_days', 30));
    }

    public function maxImages(): int
    {
        return (int) $this->getSetting('classify_max_images', config('classify.default_max_images', 8));
    }

    public function autoExpiryEnabled(): bool
    {
        return (bool) $this->getSetting('classify_auto_expiry', 1);
    }

    public function create(array $data, $images = []): ClassifyListing
    {
        return DB::transaction(function () use ($data, $images) {
            $approvalRequired = $this->approvalRequired();
            $duration = $this->listingDurationDays();

            $data['status'] = $approvalRequired ? 'pending' : 'published';
            $data['is_approved'] = $approvalRequired ? 0 : 1;
            if (!$approvalRequired) {
                $data['published_at'] = now();
            }
            if ($this->autoExpiryEnabled()) {
                $data['expires_at'] = now()->addDays($duration);
            }

            $listing = ClassifyListing::create($data);
            $this->syncImages($listing, $images);
            return $listing->fresh(['images', 'category', 'subCategory', 'store']);
        });
    }

    public function update(ClassifyListing $listing, array $data, $images = null): ClassifyListing
    {
        return DB::transaction(function () use ($listing, $data, $images) {
            if ($this->approvalRequired() && $listing->status === 'published') {
                $data['status'] = 'pending';
                $data['is_approved'] = 0;
            }
            $listing->update($data);
            if ($images !== null) {
                $this->syncImages($listing, $images, true);
            }
            return $listing->fresh(['images', 'category', 'subCategory', 'store']);
        });
    }

    public function updateByAdmin(ClassifyListing $listing, array $data, $images = null): ClassifyListing
    {
        return DB::transaction(function () use ($listing, $data, $images) {
            $listing->update($data);
            if ($images !== null) {
                $this->syncImages($listing, $images, true);
            }
            return $listing->fresh(['images', 'category', 'subCategory', 'store']);
        });
    }

    public function syncImages(ClassifyListing $listing, $images, bool $append = false): void
    {
        if (!$append) {
            foreach ($listing->images as $img) {
                Helpers::check_and_delete('classify/', $img->image);
                $img->delete();
            }
        }

        $existingCount = $listing->images()->count();
        $max = $this->maxImages();
        $sort = $existingCount;

        foreach ($images as $index => $image) {
            if ($existingCount + $index >= $max) {
                break;
            }
            if (!$image) {
                continue;
            }
            $name = Helpers::upload('classify/', 'png', $image);
            ClassifyListingImage::create([
                'listing_id' => $listing->id,
                'image' => $name,
                'storage' => Helpers::getDisk(),
                'is_primary' => ($sort === 0 && $existingCount === 0),
                'sort_order' => $sort++,
            ]);
        }
    }

    public function markSold(ClassifyListing $listing): ClassifyListing
    {
        $listing->update([
            'status' => 'sold',
            'sold_at' => now(),
        ]);
        return $listing;
    }

    public function renew(ClassifyListing $listing): ClassifyListing
    {
        $duration = $this->listingDurationDays();
        $approvalRequired = $this->approvalRequired();
        $listing->update([
            'status' => $approvalRequired ? 'pending' : 'published',
            'is_approved' => $approvalRequired ? 0 : 1,
            'expires_at' => now()->addDays($duration),
            'published_at' => $approvalRequired ? $listing->published_at : now(),
            'rejection_reason' => null,
        ]);
        return $listing;
    }

    public function archive(ClassifyListing $listing): ClassifyListing
    {
        $listing->update(['status' => 'archived']);
        return $listing;
    }

    public function delete(ClassifyListing $listing): void
    {
        DB::transaction(function () use ($listing) {
            foreach ($listing->images as $img) {
                Helpers::check_and_delete('classify/', $img->image);
            }
            $listing->images()->delete();

            $conversationIds = ClassifyConversation::where('listing_id', $listing->id)->pluck('id');
            if ($conversationIds->isNotEmpty()) {
                ClassifyMessage::whereIn('conversation_id', $conversationIds)->delete();
                ClassifyConversation::whereIn('id', $conversationIds)->delete();
            }

            ClassifyListingFavorite::where('listing_id', $listing->id)->delete();

            $listing->delete();
        });
    }
}

<?php

namespace Modules\Classify\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Services\ListingModerationService;
use Modules\Classify\Services\ListingService;

class ListingController extends Controller
{
    public function __construct(
        protected ListingService $listingService,
        protected ListingModerationService $moderationService
    ) {}

    public function index(Request $request)
    {
        $listings = ClassifyListing::with(['store', 'category', 'images'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate($request->limit ?? 25);

        return response()->json($listings, 200);
    }

    public function show($id)
    {
        $listing = ClassifyListing::with(['store', 'category', 'subCategory', 'images', 'reports'])->findOrFail($id);
        return response()->json($listing, 200);
    }

    public function approve($id)
    {
        $listing = ClassifyListing::findOrFail($id);
        $this->moderationService->approve($listing);
        return response()->json(['message' => 'Listing approved', 'listing' => $listing->fresh()], 200);
    }

    public function reject(Request $request, $id)
    {
        $listing = ClassifyListing::findOrFail($id);
        $this->moderationService->reject($listing, $request->reason);
        return response()->json(['message' => 'Listing rejected', 'listing' => $listing->fresh()], 200);
    }

    public function feature(Request $request, $id)
    {
        $listing = ClassifyListing::findOrFail($id);
        $this->moderationService->setFeatured($listing, (bool) ($request->status ?? 1), (int) ($request->days ?: 7));
        return response()->json(['message' => 'Updated', 'listing' => $listing->fresh()], 200);
    }

    public function premium(Request $request, $id)
    {
        $listing = ClassifyListing::findOrFail($id);
        $this->moderationService->setPremium($listing, (bool) ($request->status ?? 1), (int) ($request->days ?: 7));
        return response()->json(['message' => 'Updated', 'listing' => $listing->fresh()], 200);
    }
}

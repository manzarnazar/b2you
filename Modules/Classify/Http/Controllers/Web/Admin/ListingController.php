<?php

namespace Modules\Classify\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Category;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyListingReport;
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
        $query = ClassifyListing::with(['store', 'category', 'images'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('title', 'like', "%{$request->search}%")
                        ->orWhere('id', $request->search);
                });
            })
            ->latest();

        $listings = $query->paginate(config('default_pagination'));
        $statusCounts = [
            'all' => ClassifyListing::count(),
            'pending' => ClassifyListing::where('status', 'pending')->count(),
            'published' => ClassifyListing::where('status', 'published')->count(),
            'rejected' => ClassifyListing::where('status', 'rejected')->count(),
            'sold' => ClassifyListing::where('status', 'sold')->count(),
            'expired' => ClassifyListing::where('status', 'expired')->count(),
        ];

        return view('classify::admin.listings.index', compact('listings', 'statusCounts'));
    }

    public function show($id)
    {
        $listing = ClassifyListing::with(['store', 'category', 'subCategory', 'images', 'reports'])->findOrFail($id);
        return view('classify::admin.listings.show', compact('listing'));
    }

    public function approve($id)
    {
        $listing = ClassifyListing::findOrFail($id);
        $this->moderationService->approve($listing);
        Toastr::success(translate('messages.listing_approved') ?: 'Listing approved');
        return back();
    }

    public function reject(Request $request, $id)
    {
        $listing = ClassifyListing::findOrFail($id);
        $this->moderationService->reject($listing, $request->reason);
        Toastr::success(translate('messages.listing_rejected') ?: 'Listing rejected');
        return back();
    }

    public function feature(Request $request, $id)
    {
        $listing = ClassifyListing::findOrFail($id);
        $this->moderationService->setFeatured($listing, (bool) $request->status, (int) ($request->days ?: 7));
        Toastr::success(translate('messages.updated_successfully') ?: 'Updated successfully');
        return back();
    }

    public function premium(Request $request, $id)
    {
        $listing = ClassifyListing::findOrFail($id);
        $this->moderationService->setPremium($listing, (bool) $request->status, (int) ($request->days ?: 7));
        Toastr::success(translate('messages.updated_successfully') ?: 'Updated successfully');
        return back();
    }

    public function destroy($id)
    {
        $listing = ClassifyListing::with('images')->findOrFail($id);
        $this->listingService->delete($listing);
        Toastr::success(translate('messages.deleted_successfully') ?: 'Deleted successfully');
        return redirect()->route('admin.classify.listings.index');
    }
}

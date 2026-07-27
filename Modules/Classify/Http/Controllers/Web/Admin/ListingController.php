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

        $listings = $query->paginate(config('default_pagination'))->withQueryString();
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

    public function edit($id)
    {
        $listing = ClassifyListing::with(['images', 'store'])->findOrFail($id);
        $moduleId = $listing->module_id ?? config('module.current_module_data')['id'];
        $categories = Category::where(['position' => 0, 'module_id' => $moduleId, 'status' => 1])
            ->with(['childes' => fn ($q) => $q->where('status', 1)->orderBy('name')])
            ->orderBy('name')
            ->get();
        return view('classify::admin.listings.edit', compact('listing', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $listing = ClassifyListing::with('images')->findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:new,used,refurbished',
            'category_id' => 'required|exists:categories,id',
            'status' => 'nullable|in:draft,pending,published,rejected,sold,expired,archived',
            'images.*' => 'nullable|image',
        ]);

        $data = $request->only([
            'title', 'description', 'price', 'condition', 'category_id', 'sub_category_id',
            'phone', 'address', 'latitude', 'longitude', 'status',
        ]);
        $data['is_negotiable'] = $request->boolean('is_negotiable');
        if ($request->filled('status') && $request->status === 'published') {
            $data['is_approved'] = 1;
            if (!$listing->published_at) {
                $data['published_at'] = now();
            }
        }
        $images = $request->hasFile('images') ? $request->file('images') : null;
        $this->listingService->updateByAdmin($listing, $data, $images);
        Toastr::success(translate('messages.updated_successfully') ?: 'Updated successfully');
        return redirect()->route('admin.classify.listings.show', $listing->id);
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

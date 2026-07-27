<?php

namespace Modules\Classify\Http\Controllers\Web\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Services\ListingService;
use Modules\Classify\Services\ListingStatsService;

class ListingController extends Controller
{
    public function __construct(
        protected ListingService $listingService,
        protected ListingStatsService $statsService
    ) {}

    protected function currentStore()
    {
        return Helpers::get_store_data();
    }

    public function index(Request $request)
    {
        $store = $this->currentStore();
        $baseQuery = ClassifyListing::ofStore($store->id);

        $statusCounts = [
            'all' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('status', 'published')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'sold' => (clone $baseQuery)->where('status', 'sold')->count(),
            'archived' => (clone $baseQuery)->where('status', 'archived')->count(),
        ];

        $listings = ClassifyListing::with(['category', 'images'])
            ->ofStore($store->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('updated_at')
            ->paginate(config('default_pagination'))
            ->withQueryString();

        return view('classify::vendor.listings.index', compact('listings', 'statusCounts'));
    }

    public function create()
    {
        $store = $this->currentStore();
        $categories = Category::where(['position' => 0, 'module_id' => $store->module_id, 'status' => 1])->get();
        return view('classify::vendor.listings.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:new,used,refurbished',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'nullable|image',
        ]);

        $store = $this->currentStore();
        $data = [
            'module_id' => $store->module_id,
            'store_id' => $store->id,
            'vendor_id' => $store->vendor_id,
            'zone_id' => $store->zone_id,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'is_negotiable' => $request->boolean('is_negotiable'),
            'condition' => $request->condition,
            'phone' => $request->phone ?: $store->phone,
            'address' => $request->address ?: $store->address,
            'latitude' => $request->latitude ?: $store->latitude,
            'longitude' => $request->longitude ?: $store->longitude,
        ];

        $this->listingService->create($data, $request->file('images', []));
        Toastr::success(translate('messages.listing_created') ?: 'Listing created');
        return redirect()->route('vendor.classify.listings.index');
    }

    public function show($id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::with(['category', 'subCategory', 'images'])->ofStore($store->id)->findOrFail($id);
        $stats = $this->statsService->forListing($listing);
        return view('classify::vendor.listings.show', compact('listing', 'stats'));
    }

    public function edit($id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::with('images')->ofStore($store->id)->findOrFail($id);
        $categories = Category::where(['position' => 0, 'module_id' => $store->module_id, 'status' => 1])->get();
        return view('classify::vendor.listings.edit', compact('listing', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::with('images')->ofStore($store->id)->findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:new,used,refurbished',
            'category_id' => 'required|exists:categories,id',
        ]);

        $data = $request->only([
            'title', 'description', 'price', 'condition', 'category_id', 'sub_category_id',
            'phone', 'address', 'latitude', 'longitude',
        ]);
        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $images = $request->hasFile('images') ? $request->file('images') : null;
        $this->listingService->update($listing, $data, $images);
        Toastr::success(translate('messages.updated_successfully') ?: 'Updated');
        return redirect()->route('vendor.classify.listings.index');
    }

    public function destroy($id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::with('images')->ofStore($store->id)->findOrFail($id);
        $this->listingService->delete($listing);
        Toastr::success(translate('messages.deleted_successfully') ?: 'Deleted');
        return redirect()->route('vendor.classify.listings.index');
    }

    public function sold($id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::ofStore($store->id)->findOrFail($id);
        $this->listingService->markSold($listing);
        Toastr::success(translate('messages.marked_as_sold') ?: 'Marked as sold');
        return back();
    }

    public function renew($id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::ofStore($store->id)->findOrFail($id);
        $this->listingService->renew($listing);
        Toastr::success(translate('messages.listing_renewed') ?: 'Listing renewed');
        return back();
    }

    public function archive($id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::ofStore($store->id)->findOrFail($id);
        $this->listingService->archive($listing);
        Toastr::success(translate('messages.listing_archived') ?: 'Listing archived');
        return back();
    }
}

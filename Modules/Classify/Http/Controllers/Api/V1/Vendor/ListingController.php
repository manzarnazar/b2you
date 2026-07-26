<?php

namespace Modules\Classify\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Services\ListingService;
use Modules\Classify\Services\ListingStatsService;

class ListingController extends Controller
{
    public function __construct(
        protected ListingService $listingService,
        protected ListingStatsService $statsService
    ) {}

    public function index(Request $request)
    {
        $store = $request->vendor->stores[0];
        $listings = ClassifyListing::with(['category', 'subCategory', 'images'])
            ->ofStore($store->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate($request->limit ?? 25);

        return response()->json($listings, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:new,used,refurbished',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'is_negotiable' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'image',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $store = $request->vendor->stores[0];
        if (($store->module->module_type ?? null) !== 'classify') {
            return response()->json(['errors' => [['code' => 'module', 'message' => 'Store is not a Classify module store']]], 403);
        }

        $data = [
            'module_id' => $store->module_id,
            'store_id' => $store->id,
            'vendor_id' => $request->vendor->id,
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

        $images = $request->file('images', []);
        $listing = $this->listingService->create($data, $images);

        return response()->json(['message' => 'Listing created', 'listing' => $listing], 200);
    }

    public function update(Request $request, $id)
    {
        $store = $request->vendor->stores[0];
        $listing = ClassifyListing::with('images')->ofStore($store->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'condition' => 'sometimes|required|in:new,used,refurbished',
            'category_id' => 'sometimes|required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'is_negotiable' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'image',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = $request->only([
            'title', 'description', 'price', 'condition', 'category_id', 'sub_category_id',
            'phone', 'address', 'latitude', 'longitude',
        ]);
        if ($request->has('is_negotiable')) {
            $data['is_negotiable'] = $request->boolean('is_negotiable');
        }

        $images = $request->hasFile('images') ? $request->file('images') : null;
        $listing = $this->listingService->update($listing, $data, $images);

        return response()->json(['message' => 'Listing updated', 'listing' => $listing], 200);
    }

    public function destroy(Request $request, $id)
    {
        $store = $request->vendor->stores[0];
        $listing = ClassifyListing::with('images')->ofStore($store->id)->findOrFail($id);
        $this->listingService->delete($listing);
        return response()->json(['message' => 'Listing deleted'], 200);
    }

    public function uploadImages(Request $request, $id)
    {
        $store = $request->vendor->stores[0];
        $listing = ClassifyListing::with('images')->ofStore($store->id)->findOrFail($id);
        $images = $request->file('images', []);
        $this->listingService->syncImages($listing, $images, true);
        return response()->json(['message' => 'Images uploaded', 'listing' => $listing->fresh('images')], 200);
    }

    public function sold(Request $request, $id)
    {
        $store = $request->vendor->stores[0];
        $listing = ClassifyListing::ofStore($store->id)->findOrFail($id);
        $this->listingService->markSold($listing);
        return response()->json(['message' => 'Marked as sold', 'listing' => $listing->fresh()], 200);
    }

    public function renew(Request $request, $id)
    {
        $store = $request->vendor->stores[0];
        $listing = ClassifyListing::ofStore($store->id)->findOrFail($id);
        $this->listingService->renew($listing);
        return response()->json(['message' => 'Listing renewed', 'listing' => $listing->fresh()], 200);
    }

    public function archive(Request $request, $id)
    {
        $store = $request->vendor->stores[0];
        $listing = ClassifyListing::ofStore($store->id)->findOrFail($id);
        $this->listingService->archive($listing);
        return response()->json(['message' => 'Listing archived', 'listing' => $listing->fresh()], 200);
    }

    public function stats(Request $request, $id)
    {
        $store = $request->vendor->stores[0];
        $listing = ClassifyListing::ofStore($store->id)->findOrFail($id);
        return response()->json($this->statsService->forListing($listing), 200);
    }

    public function show(Request $request, $id)
    {
        $store = $request->vendor->stores[0];
        $listing = ClassifyListing::with(['category', 'subCategory', 'images'])->ofStore($store->id)->findOrFail($id);
        return response()->json($listing, 200);
    }

    public function categories(Request $request)
    {
        $store = $request->vendor->stores[0];
        $categories = Category::where('module_id', $store->module_id)
            ->where('position', 0)
            ->where('status', 1)
            ->with(['childes' => fn ($q) => $q->where('status', 1)])
            ->get();
        return response()->json($categories, 200);
    }
}

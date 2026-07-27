<?php

namespace Modules\Classify\Http\Controllers\Web\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Services\CategoryFieldService;
use Modules\Classify\Services\ListingService;
use Modules\Classify\Services\ListingStatsService;

class ListingController extends Controller
{
    public function __construct(
        protected ListingService $listingService,
        protected ListingStatsService $statsService,
        protected CategoryFieldService $categoryFieldService
    ) {}

    protected function currentStore()
    {
        return Helpers::get_store_data();
    }

    protected function classifyParentCategories(int $moduleId)
    {
        return Category::where(['position' => 0, 'module_id' => $moduleId, 'status' => 1])
            ->with(['childes' => fn ($q) => $q->where('status', 1)->orderBy('name')])
            ->orderBy('name')
            ->get();
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
        $categories = $this->classifyParentCategories($store->module_id);
        $customFields = collect();
        $customFieldValues = [];
        return view('classify::vendor.listings.create', compact('categories', 'customFields', 'customFieldValues'));
    }

    public function categoryFields(Request $request)
    {
        $fields = $this->categoryFieldService->resolveFields(
            $request->filled('category_id') ? (int) $request->category_id : null,
            $request->filled('sub_category_id') ? (int) $request->sub_category_id : null
        );

        return response()->json([
            'fields' => $fields->map->toDefinitionArray()->values(),
        ]);
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

        $fields = $this->categoryFieldService->resolveFields(
            (int) $request->category_id,
            $request->filled('sub_category_id') ? (int) $request->sub_category_id : null
        );
        [$custom, $files] = $this->categoryFieldService->extractRequestValues($request);

        try {
            $normalized = $this->categoryFieldService->validateAndNormalize($fields, $custom, $files);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $store = $this->currentStore();
        $data = [
            'module_id' => $store->module_id,
            'store_id' => $store->id,
            'vendor_id' => $store->vendor_id,
            'zone_id' => $store->zone_id,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id ?: null,
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

        $this->listingService->create($data, $request->file('images', []), $fields, $normalized);
        Toastr::success(translate('messages.listing_created') ?: 'Listing created');
        return redirect()->route('vendor.classify.listings.index');
    }

    public function show($id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::with(['category', 'subCategory', 'images', 'fieldValues.field'])
            ->ofStore($store->id)
            ->findOrFail($id);
        $stats = $this->statsService->forListing($listing);
        $customFieldsDisplay = $this->categoryFieldService->displayArray($listing);
        return view('classify::vendor.listings.show', compact('listing', 'stats', 'customFieldsDisplay'));
    }

    public function edit($id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::with(['images', 'fieldValues'])->ofStore($store->id)->findOrFail($id);
        $categories = $this->classifyParentCategories($store->module_id);
        $customFields = $this->categoryFieldService->resolveFields(
            (int) $listing->category_id,
            $listing->sub_category_id ? (int) $listing->sub_category_id : null
        );
        $customFieldValues = $listing->fieldValues->mapWithKeys(function ($row) {
            return [$row->field_id => $row->decodedValue()];
        })->all();

        return view('classify::vendor.listings.edit', compact('listing', 'categories', 'customFields', 'customFieldValues'));
    }

    public function update(Request $request, $id)
    {
        $store = $this->currentStore();
        $listing = ClassifyListing::with(['images', 'fieldValues.field'])->ofStore($store->id)->findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:new,used,refurbished',
            'category_id' => 'required|exists:categories,id',
        ]);

        $fields = $this->categoryFieldService->resolveFields(
            (int) $request->category_id,
            $request->filled('sub_category_id') ? (int) $request->sub_category_id : null
        );
        [$custom, $files] = $this->categoryFieldService->extractRequestValues($request);

        try {
            $normalized = $this->categoryFieldService->validateAndNormalize($fields, $custom, $files, $listing->fieldValues->mapWithKeys(fn ($r) => [$r->field_id => $r->value])->all());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $data = $request->only([
            'title', 'description', 'price', 'condition', 'category_id', 'sub_category_id',
            'phone', 'address', 'latitude', 'longitude',
        ]);
        $data['sub_category_id'] = $request->sub_category_id ?: null;
        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $images = $request->hasFile('images') ? $request->file('images') : null;
        $this->listingService->update($listing, $data, $images, $fields, $normalized);
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

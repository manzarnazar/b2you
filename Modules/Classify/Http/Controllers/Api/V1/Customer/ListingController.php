<?php

namespace Modules\Classify\Http\Controllers\Api\V1\Customer;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyListingFavorite;
use Modules\Classify\Entities\ClassifyListingReport;
use Modules\Classify\Services\CategoryFieldService;

class ListingController extends Controller
{
    public function __construct(protected CategoryFieldService $categoryFieldService) {}

    /** Resolve logged-in customer on public classify routes (Bearer token, no auth middleware). */
    protected function customerUserId(Request $request): ?int
    {
        $user = $request->user('api');

        return $user ? (int) $user->id : null;
    }

    public function index(Request $request)
    {
        $moduleId = config('module.current_module_data')['id'] ?? null;
        $zoneIds = $request->header('zoneId') ? json_decode($request->header('zoneId'), true) : null;

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        // Only hard-filter by radius when the client explicitly sends radius_km.
        // Otherwise location is used for distance sorting so all published listings remain visible.
        $radiusKm = $request->filled('radius_km') ? $request->radius_km : null;
        $hasValidCoords = is_numeric($latitude) && is_numeric($longitude)
            && $latitude >= -90 && $latitude <= 90
            && $longitude >= -180 && $longitude <= 180;

        $query = ClassifyListing::query()
            ->with(['store', 'category', 'images'])
            ->published()
            ->when($moduleId, fn ($q) => $q->ofModule($moduleId))
            ->when($zoneIds, fn ($q) => $q->whereIn('zone_id', (array) $zoneIds))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->sub_category_id, fn ($q) => $q->where('sub_category_id', $request->sub_category_id))
            ->when($request->condition, fn ($q) => $q->where('condition', $request->condition))
            ->when($request->min_price, fn ($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn ($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->featured, fn ($q) => $q->where('is_featured', 1))
            ->when($request->premium, fn ($q) => $q->where('is_premium', 1))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('title', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%")
                        ->orWhere('address', 'like', "%{$request->search}%");
                });
            });

        if ($hasValidCoords) {
            $query->near($latitude, $longitude, $radiusKm)
                ->orderByDesc('is_premium')
                ->orderByDesc('is_featured')
                ->orderBy('distance')
                ->latest('published_at');
        } else {
            $query->orderByDesc('is_premium')
                ->orderByDesc('is_featured')
                ->latest('published_at');
        }

        $listings = $query->paginate($request->limit ?? 25);

        $favoriteIds = collect();
        $userId = $this->customerUserId($request);
        if ($userId) {
            $favoriteIds = ClassifyListingFavorite::where('user_id', $userId)
                ->whereIn('listing_id', $listings->getCollection()->pluck('id'))
                ->pluck('listing_id');
        }

        $listings->getCollection()->transform(function ($listing) use ($favoriteIds) {
            $arr = $listing->toArray();
            $arr['is_favorite'] = $favoriteIds->contains($listing->id);
            return $arr;
        });

        return response()->json($listings, 200);
    }

    public function show(Request $request, $id)
    {
        $listing = ClassifyListing::with(['store', 'category', 'subCategory', 'images', 'vendor', 'fieldValues.field'])
            ->published()
            ->findOrFail($id);

        $listing->increment('views_count');

        $userId = $this->customerUserId($request);
        $isFavorite = $userId
            ? ClassifyListingFavorite::where('user_id', $userId)
                ->where('listing_id', $listing->id)
                ->exists()
            : false;

        $data = $listing->toArray();
        $data['is_favorite'] = $isFavorite;
        $data['custom_fields'] = $this->categoryFieldService->displayArray($listing);

        return response()->json($data, 200);
    }

    public function similar(Request $request, $id)
    {
        $listing = ClassifyListing::published()->findOrFail($id);
        $similar = ClassifyListing::with(['store', 'images', 'category'])
            ->published()
            ->where('id', '!=', $listing->id)
            ->where(function ($q) use ($listing) {
                $q->where('category_id', $listing->category_id)
                    ->orWhere('store_id', $listing->store_id);
            })
            ->limit(12)
            ->get();

        $favoriteIds = collect();
        $userId = $this->customerUserId($request);
        if ($userId) {
            $favoriteIds = ClassifyListingFavorite::where('user_id', $userId)
                ->whereIn('listing_id', $similar->pluck('id'))
                ->pluck('listing_id');
        }

        $payload = $similar->map(function ($item) use ($favoriteIds) {
            $arr = $item->toArray();
            $arr['is_favorite'] = $favoriteIds->contains($item->id);
            return $arr;
        });

        return response()->json($payload, 200);
    }

    public function seller($storeId)
    {
        $store = Store::with('vendor')->findOrFail($storeId);
        $listings = ClassifyListing::with(['images', 'category'])
            ->published()
            ->ofStore($storeId)
            ->latest('published_at')
            ->paginate(25);

        return response()->json([
            'store' => $store,
            'listings' => $listings,
        ], 200);
    }

    public function addFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|exists:classify_listings,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $favorite = ClassifyListingFavorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'listing_id' => $request->listing_id,
        ]);

        if ($favorite->wasRecentlyCreated) {
            ClassifyListing::where('id', $request->listing_id)->increment('favorites_count');
        }

        return response()->json(['message' => 'Added to favorites'], 200);
    }

    public function removeFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|exists:classify_listings,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deleted = ClassifyListingFavorite::where('user_id', $request->user()->id)
            ->where('listing_id', $request->listing_id)
            ->delete();

        if ($deleted) {
            ClassifyListing::where('id', $request->listing_id)->where('favorites_count', '>', 0)->decrement('favorites_count');
        }

        return response()->json(['message' => 'Removed from favorites'], 200);
    }

    public function favorites(Request $request)
    {
        $ids = ClassifyListingFavorite::where('user_id', $request->user()->id)->pluck('listing_id');
        $listings = ClassifyListing::with(['store', 'images', 'category'])
            ->whereIn('id', $ids)
            ->published()
            ->latest()
            ->paginate($request->limit ?? 25);

        $listings->getCollection()->transform(function ($listing) {
            $arr = $listing->toArray();
            $arr['is_favorite'] = true;
            return $arr;
        });

        return response()->json($listings, 200);
    }

    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|exists:classify_listings,id',
            'reason' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $report = ClassifyListingReport::create([
            'listing_id' => $request->listing_id,
            'user_id' => $request->user()->id,
            'reason' => $request->reason,
            'note' => $request->note,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Report submitted', 'report' => $report], 200);
    }
}

<?php

namespace Modules\Classify\Http\Controllers\Api\V1\Customer;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Store;
use App\Models\UserInfo;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyListingFavorite;
use Modules\Classify\Entities\ClassifyListingReport;

class ListingController extends Controller
{
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

        return response()->json($listings, 200);
    }

    public function show(Request $request, $id)
    {
        $listing = ClassifyListing::with(['store', 'category', 'subCategory', 'images', 'vendor'])
            ->published()
            ->findOrFail($id);

        $listing->increment('views_count');

        $isFavorite = false;
        if ($request->user()) {
            $isFavorite = ClassifyListingFavorite::where('user_id', $request->user()->id)
                ->where('listing_id', $listing->id)
                ->exists();
        }

        $data = $listing->toArray();
        $data['is_favorite'] = $isFavorite;

        return response()->json($data, 200);
    }

    public function similar($id)
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

        return response()->json($similar, 200);
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

    public function chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|exists:classify_listings,id',
            'message' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            $listing = ClassifyListing::with('store')->findOrFail($request->listing_id);
            $vendorId = $listing->vendor_id ?: $listing->store?->vendor_id;
            $vendor = $vendorId ? Vendor::find($vendorId) : null;
            if (!$vendor) {
                return response()->json(['errors' => [['code' => 'vendor', 'message' => 'Seller not found']]], 404);
            }

            $user = $request->user();
            $sender = UserInfo::where('user_id', $user->id)->first();
            if (!$sender) {
                $sender = new UserInfo();
                $sender->user_id = $user->id;
                $sender->f_name = $user->f_name;
                $sender->l_name = $user->l_name;
                $sender->phone = $user->phone;
                $sender->email = $user->email;
                $sender->image = $user->image;
                $sender->save();
            }

            $receiver = UserInfo::where('vendor_id', $vendor->id)->first();
            if (!$receiver) {
                $receiver = new UserInfo();
                $receiver->vendor_id = $vendor->id;
                $receiver->f_name = $listing->store?->name ?: ($vendor->f_name ?: 'Seller');
                $receiver->l_name = $vendor->l_name ?: '';
                $receiver->phone = $vendor->phone;
                $receiver->email = $vendor->email;
                $receiver->image = $listing->store?->logo;
                $receiver->save();
            }

            $conversation = Conversation::WhereConversation($sender->id, $receiver->id)->first();
            if (!$conversation) {
                $conversation = new Conversation();
                $conversation->sender_id = $sender->id;
                $conversation->sender_type = 'customer';
                $conversation->receiver_id = $receiver->id;
                $conversation->receiver_type = 'vendor';
                $conversation->unread_message_count = 0;
                $conversation->last_message_time = now()->toDateTimeString();
                $conversation->save();
            }

            $body = $request->message ?: ('Interested in: ' . $listing->title);
            $message = new Message();
            $message->conversation_id = $conversation->id;
            $message->sender_id = $sender->id;
            $message->message = $body . "\n[listing_id:{$listing->id}]";
            $message->save();

            $conversation->unread_message_count = ($conversation->unread_message_count ?: 0) + 1;
            $conversation->last_message_id = $message->id;
            $conversation->last_message_time = now()->toDateTimeString();
            $conversation->save();

            $listing->increment('chats_count');

            try {
                $data = [
                    'title' => translate('messages.message') ?: 'New message',
                    'description' => $body,
                    'order_id' => '',
                    'image' => '',
                    'type' => 'message',
                    'conversation_id' => $conversation->id,
                    'listing_id' => $listing->id,
                    'sender_type' => 'user',
                ];
                if (!empty($vendor->firebase_token)) {
                    Helpers::send_push_notif_to_device($vendor->firebase_token, $data);
                }
                if ($listing->store_id) {
                    Helpers::send_push_notif_to_topic($data, "store_panel_{$listing->store_id}_message", 'message');
                }
            } catch (\Throwable $e) {
                // Push notification failures should not block chat.
            }

            return response()->json([
                'message' => 'Chat started',
                'conversation_id' => $conversation->id,
                'listing_id' => $listing->id,
                'vendor_id' => $vendor->id,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Classify chat failed: ' . $e->getMessage(), [
                'listing_id' => $request->listing_id,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'errors' => [['code' => 'chat', 'message' => 'Unable to start chat. Please try again.']],
            ], 500);
        }
    }
}

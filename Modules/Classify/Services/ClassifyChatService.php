<?php

namespace Modules\Classify\Services;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Modules\Classify\Entities\ClassifyConversation;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyMessage;

class ClassifyChatService
{
    public function findOrCreateConversation(ClassifyListing $listing, User $customer): ClassifyConversation
    {
        $vendorId = $listing->vendor_id ?: $listing->store?->vendor_id;
        if (!$vendorId || !$listing->store_id) {
            throw new \InvalidArgumentException('Listing has no seller');
        }

        return ClassifyConversation::firstOrCreate(
            [
                'listing_id' => $listing->id,
                'customer_id' => $customer->id,
            ],
            [
                'module_id' => $listing->module_id,
                'store_id' => $listing->store_id,
                'vendor_id' => $vendorId,
                'unread_customer' => 0,
                'unread_vendor' => 0,
            ]
        );
    }

    public function sendMessage(
        ClassifyConversation $conversation,
        string $senderType,
        string $body,
        ?User $customer = null,
        ?Vendor $vendor = null
    ): ClassifyMessage {
        return DB::transaction(function () use ($conversation, $senderType, $body, $customer, $vendor) {
            $message = ClassifyMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => $senderType,
                'customer_id' => $senderType === 'customer' ? $customer?->id : null,
                'vendor_id' => $senderType === 'vendor' ? $vendor?->id : null,
                'message' => $body,
                'is_seen' => false,
            ]);

            $conversation->last_message_id = $message->id;
            $conversation->last_message_at = now();
            if ($senderType === 'customer') {
                $conversation->unread_vendor = ($conversation->unread_vendor ?? 0) + 1;
            } else {
                $conversation->unread_customer = ($conversation->unread_customer ?? 0) + 1;
            }
            $conversation->save();

            $isFirst = ClassifyMessage::where('conversation_id', $conversation->id)->count() === 1;
            if ($isFirst) {
                ClassifyListing::where('id', $conversation->listing_id)->increment('chats_count');
            }

            return $message;
        });
    }

    public function markSeenForCustomer(ClassifyConversation $conversation): void
    {
        if ($conversation->unread_customer > 0) {
            $conversation->unread_customer = 0;
            $conversation->save();
        }
        ClassifyMessage::where('conversation_id', $conversation->id)
            ->where('sender_type', 'vendor')
            ->where('is_seen', false)
            ->update(['is_seen' => true]);
    }

    public function markSeenForVendor(ClassifyConversation $conversation): void
    {
        if ($conversation->unread_vendor > 0) {
            $conversation->unread_vendor = 0;
            $conversation->save();
        }
        ClassifyMessage::where('conversation_id', $conversation->id)
            ->where('sender_type', 'customer')
            ->where('is_seen', false)
            ->update(['is_seen' => true]);
    }

    public function formatConversation(ClassifyConversation $conversation, string $viewer): array
    {
        $conversation->loadMissing([
            'listing.images',
            'listing.category',
            'store',
            'customer',
            'lastMessage',
        ]);

        $listing = $conversation->listing;
        $data = $conversation->toArray();
        $data['listing'] = $listing ? [
            'id' => $listing->id,
            'title' => $listing->title,
            'price' => $listing->price,
            'primary_image_full_url' => $listing->primary_image_full_url,
            'status' => $listing->status,
        ] : null;
        $data['store'] = $conversation->store ? [
            'id' => $conversation->store->id,
            'name' => $conversation->store->name,
            'logo_full_url' => $conversation->store->logo_full_url ?? null,
        ] : null;
        $data['customer'] = $conversation->customer ? [
            'id' => $conversation->customer->id,
            'f_name' => $conversation->customer->f_name,
            'l_name' => $conversation->customer->l_name,
            'image_full_url' => $conversation->customer->image_full_url ?? null,
        ] : null;
        $data['last_message'] = $conversation->lastMessage ? [
            'id' => $conversation->lastMessage->id,
            'message' => $conversation->lastMessage->message,
            'sender_type' => $conversation->lastMessage->sender_type,
            'created_at' => $conversation->lastMessage->created_at,
        ] : null;
        $data['unread_count'] = $viewer === 'customer'
            ? (int) $conversation->unread_customer
            : (int) $conversation->unread_vendor;

        return $data;
    }
}

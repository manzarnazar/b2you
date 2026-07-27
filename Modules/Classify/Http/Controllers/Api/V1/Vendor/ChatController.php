<?php

namespace Modules\Classify\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Classify\Entities\ClassifyConversation;
use Modules\Classify\Entities\ClassifyMessage;
use Modules\Classify\Services\ClassifyChatService;

class ChatController extends Controller
{
    public function __construct(protected ClassifyChatService $chatService) {}

    protected function storeId(Request $request): int
    {
        return (int) $request->vendor->stores[0]->id;
    }

    public function conversations(Request $request)
    {
        $storeId = $this->storeId($request);

        $conversations = ClassifyConversation::query()
            ->where('store_id', $storeId)
            ->with(['listing.images', 'customer', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate($request->limit ?? 20);

        $conversations->getCollection()->transform(
            fn ($c) => $this->chatService->formatConversation($c, 'vendor')
        );

        return response()->json($conversations, 200);
    }

    public function messages(Request $request, $id)
    {
        $storeId = $this->storeId($request);
        $conversation = ClassifyConversation::where('store_id', $storeId)->findOrFail($id);

        $this->chatService->markSeenForVendor($conversation);

        $messages = ClassifyMessage::where('conversation_id', $conversation->id)
            ->orderBy('id')
            ->paginate($request->limit ?? 50);

        return response()->json([
            'conversation' => $this->chatService->formatConversation($conversation, 'vendor'),
            'messages' => $messages,
        ], 200);
    }

    public function send(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $storeId = $this->storeId($request);
        $conversation = ClassifyConversation::where('store_id', $storeId)->findOrFail($id);
        $vendor = $request->vendor;

        $message = $this->chatService->sendMessage(
            $conversation,
            'vendor',
            $request->message,
            null,
            $vendor
        );

        return response()->json([
            'message' => 'Sent',
            'data' => $message,
            'conversation_id' => $conversation->id,
        ], 200);
    }
}

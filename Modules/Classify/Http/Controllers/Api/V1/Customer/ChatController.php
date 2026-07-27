<?php

namespace Modules\Classify\Http\Controllers\Api\V1\Customer;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Classify\Entities\ClassifyConversation;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyMessage;
use Modules\Classify\Services\ClassifyChatService;

class ChatController extends Controller
{
    public function __construct(protected ClassifyChatService $chatService) {}

    public function conversations(Request $request)
    {
        $user = $request->user();
        $moduleId = config('module.current_module_data')['id'] ?? null;

        $query = ClassifyConversation::query()
            ->where('customer_id', $user->id)
            ->when($moduleId, fn ($q) => $q->where('module_id', $moduleId))
            ->with(['listing.images', 'store', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at');

        $conversations = $query->paginate($request->limit ?? 20);

        $conversations->getCollection()->transform(
            fn ($c) => $this->chatService->formatConversation($c, 'customer')
        );

        return response()->json($conversations, 200);
    }

    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|exists:classify_listings,id',
            'message' => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $listing = ClassifyListing::with('store')->published()->findOrFail($request->listing_id);
        $user = $request->user();

        $conversation = $this->chatService->findOrCreateConversation($listing, $user);
        $body = trim((string) $request->message);
        if ($body !== '') {
            $this->chatService->sendMessage($conversation, 'customer', $body, $user, null);
            $conversation->refresh();
        }

        return response()->json([
            'message' => 'Chat ready',
            'conversation' => $this->chatService->formatConversation($conversation, 'customer'),
            'conversation_id' => $conversation->id,
        ], 200);
    }

    public function messages(Request $request, $id)
    {
        $user = $request->user();
        $conversation = ClassifyConversation::where('customer_id', $user->id)->findOrFail($id);

        $this->chatService->markSeenForCustomer($conversation);

        $messages = ClassifyMessage::where('conversation_id', $conversation->id)
            ->orderBy('id')
            ->paginate($request->limit ?? 50);

        return response()->json([
            'conversation' => $this->chatService->formatConversation($conversation, 'customer'),
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

        $user = $request->user();
        $conversation = ClassifyConversation::where('customer_id', $user->id)->findOrFail($id);

        $message = $this->chatService->sendMessage(
            $conversation,
            'customer',
            $request->message,
            $user,
            null
        );

        return response()->json([
            'message' => 'Sent',
            'data' => $message,
            'conversation_id' => $conversation->id,
        ], 200);
    }
}

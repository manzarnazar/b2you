<?php

namespace Modules\Classify\Http\Controllers\Web\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Modules\Classify\Entities\ClassifyConversation;
use Modules\Classify\Entities\ClassifyMessage;
use Modules\Classify\Services\ClassifyChatService;

class ChatController extends Controller
{
    public function __construct(protected ClassifyChatService $chatService) {}

    protected function store()
    {
        return Helpers::get_store_data();
    }

    public function index(Request $request)
    {
        $store = $this->store();
        $conversations = ClassifyConversation::query()
            ->where('store_id', $store->id)
            ->with(['listing.images', 'customer', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('classify::vendor.chats.index', compact('conversations'));
    }

    public function show(Request $request, $id)
    {
        $store = $this->store();
        $conversation = ClassifyConversation::where('store_id', $store->id)
            ->with(['listing', 'customer'])
            ->findOrFail($id);

        $this->chatService->markSeenForVendor($conversation);

        $messages = ClassifyMessage::where('conversation_id', $conversation->id)
            ->orderBy('id')
            ->paginate(50);

        return view('classify::vendor.chats.show', compact('conversation', 'messages'));
    }

    public function send(Request $request, $id)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $store = $this->store();
        $conversation = ClassifyConversation::where('store_id', $store->id)->findOrFail($id);
        $vendor = $store->vendor;

        $this->chatService->sendMessage(
            $conversation,
            'vendor',
            $request->message,
            null,
            $vendor
        );

        Toastr::success(translate('Message sent') ?: 'Message sent');

        return redirect()->route('vendor.classify.chats.show', $id);
    }
}

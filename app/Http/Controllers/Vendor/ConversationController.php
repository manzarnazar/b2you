<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\CentralLogics\Helpers;
use App\Models\Conversation;
use App\Models\UserInfo;
use App\Models\Message;
use App\Models\User;
use App\Models\DeliveryMan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    public function list(Request $request)
    {
        $vendorData = Helpers::get_vendor_data();
        $store = Helpers::get_store_data();

        $vendor = UserInfo::where('vendor_id', $vendorData->id)->first();
        if (!$vendor) {
            $vendor = new UserInfo();
            $vendor->vendor_id = $vendorData->id;
            $vendor->f_name = $store->name ?? ($vendorData->f_name ?? 'Store');
            $vendor->l_name = '';
            $vendor->phone = $vendorData->phone ?? $store->phone ?? null;
            $vendor->email = $vendorData->email ?? $store->email ?? null;
            $vendor->image = $store->logo ?? null;
            $vendor->save();
        }

        $conversations = Conversation::with(['sender', 'receiver', 'last_message'])
            ->WhereUser($vendor->id)
            ->when($request->query('key'), function ($query) use ($request) {
                $key = explode(' ', $request->get('key'));
                $query->where(function ($qu) use ($key) {
                    $qu->whereHas('sender', function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%");
                        }
                    })->orWhereHas('receiver', function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%");
                        }
                    });
                });
            })
            ->orderBy('last_message_time', 'DESC')
            ->latest()
            ->paginate(8);

        if ($request->ajax()) {
            $view = view('vendor-views.messages.data', compact('conversations'))->render();
            return response()->json(['html' => $view]);
        }

        return view('vendor-views.messages.index', compact('conversations'));
    }

    public function view($conversation_id,$user_id)
    {
        $conversation = Conversation::find($conversation_id);
        $lastmessage = $conversation->last_message;
        if($lastmessage && $lastmessage->sender_id == $user_id ) {
            $conversation->unread_message_count = 0;
            $conversation->save();
        }
        Message::where(['conversation_id' => $conversation->id])->where('sender_id',$user_id)->update(['is_seen' => 1]);
        $convs = Message::where(['conversation_id' => $conversation_id])->get();
        // Message::where(['conversation_id' => $conversation_id])->update(['is_seen' => 1]);
        $conversation= Conversation::find($conversation_id);
        $receiver = $conversation->receiver;
        $sender = $conversation->sender;
        $vendor = Helpers::get_vendor_data();
        $vendor = UserInfo::where('vendor_id',$vendor->id)->first();

        if($receiver->user_id){
            $user = User::find($receiver->user_id);
            $user_type = 'user';
        }elseif($receiver->deliveryman_id){
            $user = DeliveryMan::find($receiver->deliveryman_id);
            $user_type = 'delivery_man';
        }elseif($sender->user_id){
            $user = User::find($sender->user_id);
            $user_type = 'user';
        }else{
            $user = DeliveryMan::find($sender->deliveryman_id);
            $user_type = 'delivery_man';
        }

        return response()->json([
            'view' => view('vendor-views.messages.partials._conversations', compact('convs', 'user', 'receiver','sender','user_type','vendor'))->render()
        ]);
    }

    public function store(Request $request, $user_id, $user_type)
    {
        if ($request->has('images')) {
            $image_name=[];
            foreach($request->images as $key=>$img)
            {
                $name = Helpers::upload('conversation/', 'png', $img);
                array_push($image_name,['img'=>$name, 'storage'=> Helpers::getDisk()]);
            }
        } else {
            $image_name = null;

            $validator = Validator::make($request->all(), [
                'reply' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => Helpers::error_processor($validator)]);
            }
        }

        $vendor = Helpers::get_vendor_data();
        $sender = UserInfo::where('vendor_id',$vendor->id)->first();
        if(!$sender){
            $sender = new UserInfo();
            $sender->vendor_id = $vendor->id;
            $sender->f_name = $vendor->stores[0]->name;
            $sender->l_name = '';
            $sender->phone = $vendor->phone;
            $sender->email = $vendor->email;
            $sender->image = $vendor->stores[0]->logo;
            $sender->save();
        }

        if($user_type == 'user'){

            $user = User::find($user_id);
            $fcm_token=$user->cm_firebase_token;
            $receiver = UserInfo::where('user_id', $user->id)->first();
            if(!$receiver){
                $receiver = new UserInfo();
                $receiver->user_id = $user->id;
                $receiver->f_name = $user->f_name;
                $receiver->l_name = $user->l_name;
                $receiver->phone = $user->phone;
                $receiver->email = $user->email;
                $receiver->image = $user->image;
                $receiver->save();
            }

        }elseif($user_type == 'delivery_man'){
            $dm = DeliveryMan::find($user_id);
            $fcm_token=$dm->fcm_token;
            $receiver = UserInfo::where('deliveryman_id', $dm->id)->first();
            if(!$receiver){
                $receiver = new UserInfo();
                $receiver->deliveryman_id = $dm->id;
                $receiver->f_name = $dm->f_name;
                $receiver->l_name = $dm->l_name;
                $receiver->phone = $dm->phone;
                $receiver->email = $dm->email;
                $receiver->image = $dm->image;
                $receiver->save();
            }
            $user = DeliveryMan::find($user_id);
        }



        $conversation = Conversation::WhereConversation($sender->id,$receiver->id)->first();


        if(!$conversation){
            $conversation = new Conversation;
            $conversation->sender_id = $sender->id;
            $conversation->sender_type = 'vendor';
            $conversation->receiver_id = $receiver->id;
            $conversation->receiver_type = $user_type;
            $conversation->last_message_time = Carbon::now()->toDateTimeString();
            $conversation->save();

            $conversation= Conversation::find($conversation->id);
        }

        $message = new Message();
        $message->conversation_id = $conversation->id;
        $message->sender_id = $sender->id;
        $message->message = $request->reply;
        if($image_name && count($image_name)>0){
            $message->file = json_encode($image_name, JSON_UNESCAPED_SLASHES);
        }
        try {
            if($message->save())
            $conversation->unread_message_count = $conversation->unread_message_count? $conversation->unread_message_count+1:1;
            $conversation->last_message_id=$message->id;
            $conversation->last_message_time = Carbon::now()->toDateTimeString();
            $conversation->save();
            {
                $data = [
                    'title' =>translate('messages.message_from')." ".$sender->f_name,
                    'description' => $message->message ?? translate('attachment'),
                    'order_id' => '',
                    'image' => '',
                    'message' => $message,
                    'type'=> 'message',
                    'conversation_id'=> $conversation->id,
                    'sender_type'=> 'vendor'
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }

        } catch (\Exception $e) {
            info($e->getMessage());
        }
        $vendor = UserInfo::where('vendor_id',$vendor->id)->first();
        $convs = Message::where(['conversation_id' => $conversation->id])->get();
        return response()->json([
            'view' => view('vendor-views.messages.partials._conversations', compact('convs', 'user', 'receiver','user_type','vendor'))->render()
        ]);
    }
}

<?php

namespace App\Http\Controllers\Vendor;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\OrderTransaction;
use Illuminate\Support\Facades\DB;
use Modules\Rental\Entities\Trips;
use App\Http\Controllers\Controller;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyConversation;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $store = Helpers::get_store_data();
        if($store->module_type == 'rental'){
            return to_route('vendor.providerDashboard');

        }

        if ($this->isClassifyStore($store)) {
            $classifyDashboardData = $this->buildClassifyDashboardData($store);
            return view('vendor-views.dashboard', [
                'isClassifyDashboard' => true,
                'classifyDashboardData' => $classifyDashboardData,
            ]);
        }
        $params = [
            'statistics_type' => $request['statistics_type'] ?? 'overall'
        ];
        session()->put('dash_params', $params);

        $data = self::dashboard_order_stats_data();
        $earning = [];
        $commission = [];
        $from = Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');
        $store_earnings = OrderTransaction::NotRefunded()->where(['vendor_id' => Helpers::get_store_data()->vendor_id])->select(
            DB::raw('IFNULL(sum(store_amount),0) as earning'),
            DB::raw('IFNULL(sum(admin_commission + admin_expense - delivery_fee_comission),0) as commission'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month')
        )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();
        for ($inc = 1; $inc <= 12; $inc++) {
            $earning[$inc] = 0;
            $commission[$inc] = 0;
            foreach ($store_earnings as $match) {
                if ($match['month'] == $inc) {
                    $earning[$inc] = $match['earning'];
                    $commission[$inc] = $match['commission'];
                }
            }
        }

        $top_sell = Item::orderBy("order_count", 'desc')
            ->take(6)
            ->get();
        $most_rated_items = Item::where('avg_rating' ,'>' ,0)
        ->orderBy('avg_rating','desc')
        ->take(6)
        ->get();
        $data['top_sell'] = $top_sell;
        $data['most_rated_items'] = $most_rated_items;

        if( Helpers::get_store_data()?->storeConfig?->show_low_stock_count && Helpers::get_store_data()?->storeConfig?->minimum_stock_for_warning > 0){
            $items=  Item::where('stock' ,'<=' , Helpers::get_store_data()->storeConfig->minimum_stock_for_warning );
        } else{
            $items=  Item::whereRaw('1 = 0');
        }

        $out_of_stock_count=  Helpers::get_store_data()->module->module_type != 'food' ?  $items->orderby('stock')->latest()->count() : null;

        $item = null;
        if($out_of_stock_count == 1 ){
            $item= $items->orderby('stock')->latest()->first();
        }

        return view('vendor-views.dashboard', compact('data', 'earning', 'commission', 'params','out_of_stock_count','item'));
    }

    protected function isClassifyStore($store): bool
    {
        return data_get($store, 'module.module_type') === 'classify'
            || data_get($store, 'module_type') === 'classify';
    }

    protected function buildClassifyDashboardData($store): array
    {
        $storeId = (int) $store->id;
        $vendor = auth('vendor')->user();
        $vendorName = trim((string) ($vendor?->f_name ?? ''));
        $hour = (int) now()->format('H');
        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');

        $statusCounts = ClassifyListing::query()
            ->where('store_id', $storeId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $metrics = ClassifyListing::query()
            ->where('store_id', $storeId)
            ->selectRaw('COALESCE(SUM(views_count),0) as total_views, COALESCE(SUM(favorites_count),0) as total_favorites, COALESCE(SUM(chats_count),0) as total_chats')
            ->first();

        $activeListingsCount = (int) ($statusCounts->get('published', 0));

        $recentListings = ClassifyListing::query()
            ->with(['images'])
            ->where('store_id', $storeId)
            ->latest()
            ->take(3)
            ->get();

        $topPerformers = ClassifyListing::query()
            ->where('store_id', $storeId)
            ->orderByDesc('views_count')
            ->orderByDesc('favorites_count')
            ->orderByDesc('chats_count')
            ->take(5)
            ->get(['id', 'title', 'views_count', 'favorites_count', 'chats_count', 'status']);

        $recentConversations = ClassifyConversation::query()
            ->with(['customer:id,f_name,l_name', 'lastMessage:id,conversation_id,message,created_at'])
            ->where('store_id', $storeId)
            ->orderByDesc('last_message_at')
            ->take(2)
            ->get();

        $recentChats = $recentConversations->map(function ($conversation) {
            $customerName = trim((string) (($conversation->customer?->f_name ?? '') . ' ' . ($conversation->customer?->l_name ?? '')));
            return [
                'customer_name' => $customerName ?: 'Customer',
                'message' => $conversation->lastMessage?->message ?: 'New conversation started',
            ];
        });

        return [
            'greeting' => $greeting,
            'vendor_name' => $vendorName ?: 'Seller',
            'active_listings' => $activeListingsCount,
            'overview' => [
                'active' => $activeListingsCount,
                'pending' => (int) ($statusCounts->get('pending', 0)),
                'sold' => (int) ($statusCounts->get('sold', 0)),
                'expired' => (int) ($statusCounts->get('expired', 0)),
            ],
            'performance' => [
                'views' => (int) ($metrics?->total_views ?? 0),
                'favorites' => (int) ($metrics?->total_favorites ?? 0),
                'chats_started' => (int) ($metrics?->total_chats ?? 0),
            ],
            'status' => [
                'published' => $activeListingsCount,
                'pending' => (int) ($statusCounts->get('pending', 0)),
                'rejected' => (int) ($statusCounts->get('rejected', 0)),
                'expired' => (int) ($statusCounts->get('expired', 0)),
                'archived' => (int) ($statusCounts->get('archived', 0)),
            ],
            'recent_listings' => $recentListings,
            'top_performers' => $topPerformers,
            'recent_chats' => $recentChats,
        ];
    }

    public function store_data()
    {

        $store= Helpers::get_store_data();
        if($store->module_type == 'rental'){
            $type='trip';
            $new_pending_order=Trips::where(['checked' => 0])->where('provider_id', $store->id)->count();

        } else{
            $new_pending_order = DB::table('orders')->where(['checked' => 0])->where('store_id', $store->id)->where('order_status','pending');
            if(config('order_confirmation_model') != 'store' && !$store->sub_self_delivery)
            {
                $new_pending_order = $new_pending_order->where('order_type', 'take_away');
            }
            $new_pending_order = $new_pending_order->count();
            $new_confirmed_order = DB::table('orders')->where(['checked' => 0])->where('store_id', $store->id)->whereIn('order_status',['confirmed', 'accepted'])->whereNotNull('confirmed')->count();
            $type= 'store_order';
        }

        return response()->json([
            'success' => 1,
            'data' => ['new_pending_order' => $new_pending_order, 'new_confirmed_order' => $new_confirmed_order?? 0, 'order_type' =>$type]
        ]);
    }

    public function order_stats(Request $request)
    {
        $params = session('dash_params');
        foreach ($params as $key => $value) {
            if ($key == 'statistics_type') {
                $params['statistics_type'] = $request['statistics_type'];
            }
        }
        session()->put('dash_params', $params);

        $data = self::dashboard_order_stats_data();
        return response()->json([
            'view' => view('vendor-views.partials._dashboard-order-stats', compact('data'))->render()
        ], 200);
    }

    public function dashboard_order_stats_data()
    {
        $params = session('dash_params');
        $today = $params['statistics_type'] == 'today' ? 1 : 0;
        $this_month = $params['statistics_type'] == 'this_month' ? 1 : 0;

        $confirmed = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['store_id' => Helpers::get_store_id()])->whereIn('order_status',['confirmed', 'accepted'])->whereNotNull('confirmed')->StoreOrder()->NotDigitalOrder()->OrderScheduledIn(30)->count();

        $cooking = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['order_status' => 'processing', 'store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $ready_for_delivery = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['order_status' => 'handover', 'store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $item_on_the_way = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->ItemOnTheWay()->where(['store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $delivered = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['order_status' => 'delivered', 'store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $refunded = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['order_status' => 'refunded', 'store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $scheduled = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->Scheduled()->where(['store_id' => Helpers::get_store_id()])->where(function($q){
            if(config('order_confirmation_model') == 'store')
            {
                $q->whereNotIn('order_status',['failed','canceled', 'refund_requested', 'refunded']);
            }
            else
            {
                $q->whereNotIn('order_status',['pending','failed','canceled', 'refund_requested', 'refunded'])->orWhere(function($query){
                    $query->where('order_status','pending')->where('order_type', 'take_away');
                });
            }

        })->StoreOrder()->NotDigitalOrder()->count();

        $all = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['store_id' => Helpers::get_store_id()])
        ->where(function($query){
            return $query->whereNotIn('order_status',(config('order_confirmation_model') == 'store'|| \App\CentralLogics\Helpers::get_store_data()->sub_self_delivery)?['failed','canceled', 'refund_requested', 'refunded']:['pending','failed','canceled', 'refund_requested', 'refunded'])
            ->orWhere(function($query){
                return $query->where('order_status','pending')->where('order_type', 'take_away');
            });
        })
        ->StoreOrder()->NotDigitalOrder()->count();

        $data = [
            'confirmed' => $confirmed,
            'cooking' => $cooking,
            'ready_for_delivery' => $ready_for_delivery,
            'item_on_the_way' => $item_on_the_way,
            'delivered' => $delivered,
            'refunded' => $refunded,
            'scheduled' => $scheduled,
            'all' => $all,
        ];

        return $data;
    }

    public function updateDeviceToken(Request $request)
    {
        $vendor = Vendor::find(Helpers::get_vendor_id());
        $vendor->firebase_token =  $request->token;

        $vendor->save();

        return response()->json(['Token successfully stored.']);
    }

    public function verifiedBadgePopupSeen(Request $request)
    {
        $store = Helpers::get_store_data();
        Helpers::mark_verified_badge_popup_seen($store);

        return response()->json(['success' => 1]);
    }
}

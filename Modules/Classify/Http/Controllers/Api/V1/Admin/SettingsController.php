<?php

namespace Modules\Classify\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show()
    {
        $keys = [
            'classify_listing_duration_days', 'classify_listing_fee', 'classify_commission_percent',
            'classify_approval_required', 'classify_max_images', 'classify_premium_enabled',
            'classify_premium_fee', 'classify_premium_duration_days', 'classify_featured_enabled',
            'classify_featured_fee', 'classify_featured_duration_days', 'classify_auto_expiry',
        ];
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = BusinessSetting::where('key', $key)->first()?->value;
        }
        return response()->json($settings, 200);
    }

    public function update(Request $request)
    {
        foreach ($request->except(['_token']) as $key => $value) {
            if (str_starts_with($key, 'classify_')) {
                BusinessSetting::updateOrInsert(['key' => $key], ['value' => $value]);
            }
        }
        return response()->json(['message' => 'Settings updated'], 200);
    }
}

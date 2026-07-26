<?php

namespace Modules\Classify\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $keys = [
            'classify_listing_duration_days',
            'classify_listing_fee',
            'classify_commission_percent',
            'classify_approval_required',
            'classify_max_images',
            'classify_premium_enabled',
            'classify_premium_fee',
            'classify_premium_duration_days',
            'classify_featured_enabled',
            'classify_featured_fee',
            'classify_featured_duration_days',
            'classify_auto_expiry',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = BusinessSetting::where('key', $key)->first()?->value;
        }

        $defaults = [
            'classify_listing_duration_days' => 30,
            'classify_listing_fee' => 0,
            'classify_commission_percent' => 0,
            'classify_approval_required' => 1,
            'classify_max_images' => 8,
            'classify_premium_enabled' => 1,
            'classify_premium_fee' => 0,
            'classify_premium_duration_days' => 7,
            'classify_featured_enabled' => 1,
            'classify_featured_fee' => 0,
            'classify_featured_duration_days' => 7,
            'classify_auto_expiry' => 1,
        ];

        foreach ($defaults as $key => $value) {
            if ($settings[$key] === null) {
                $settings[$key] = $value;
            }
        }

        return view('classify::admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'classify_listing_duration_days' => 'required|integer|min:1',
            'classify_listing_fee' => 'nullable|numeric|min:0',
            'classify_commission_percent' => 'nullable|numeric|min:0|max:100',
            'classify_approval_required' => 'nullable|boolean',
            'classify_max_images' => 'required|integer|min:1|max:20',
            'classify_premium_enabled' => 'nullable|boolean',
            'classify_premium_fee' => 'nullable|numeric|min:0',
            'classify_premium_duration_days' => 'nullable|integer|min:1',
            'classify_featured_enabled' => 'nullable|boolean',
            'classify_featured_fee' => 'nullable|numeric|min:0',
            'classify_featured_duration_days' => 'nullable|integer|min:1',
            'classify_auto_expiry' => 'nullable|boolean',
        ]);

        $boolKeys = [
            'classify_approval_required',
            'classify_premium_enabled',
            'classify_featured_enabled',
            'classify_auto_expiry',
        ];

        foreach ($boolKeys as $key) {
            $data[$key] = $request->boolean($key) ? 1 : 0;
        }

        foreach ($data as $key => $value) {
            BusinessSetting::updateOrInsert(['key' => $key], ['value' => $value]);
        }

        Toastr::success(translate('messages.settings_updated') ?: 'Settings updated');
        return back();
    }
}

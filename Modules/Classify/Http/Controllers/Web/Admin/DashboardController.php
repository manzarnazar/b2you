<?php

namespace Modules\Classify\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Store;
use Illuminate\Support\Facades\Config;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyListingReport;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $moduleId = Config::get('module.current_module_id');
        $mod = Module::find($moduleId);

        $stats = [
            'total_listings' => ClassifyListing::when($moduleId, fn ($q) => $q->where('module_id', $moduleId))->count(),
            'pending' => ClassifyListing::when($moduleId, fn ($q) => $q->where('module_id', $moduleId))->where('status', 'pending')->count(),
            'published' => ClassifyListing::when($moduleId, fn ($q) => $q->where('module_id', $moduleId))->where('status', 'published')->count(),
            'sold' => ClassifyListing::when($moduleId, fn ($q) => $q->where('module_id', $moduleId))->where('status', 'sold')->count(),
            'expired' => ClassifyListing::when($moduleId, fn ($q) => $q->where('module_id', $moduleId))->where('status', 'expired')->count(),
            'rejected' => ClassifyListing::when($moduleId, fn ($q) => $q->where('module_id', $moduleId))->where('status', 'rejected')->count(),
            'featured' => ClassifyListing::when($moduleId, fn ($q) => $q->where('module_id', $moduleId))->where('is_featured', 1)->count(),
            'premium' => ClassifyListing::when($moduleId, fn ($q) => $q->where('module_id', $moduleId))->where('is_premium', 1)->count(),
            'reports' => ClassifyListingReport::where('status', 'pending')->count(),
            'sellers' => Store::when($moduleId, fn ($q) => $q->where('module_id', $moduleId))->count(),
        ];

        $recent = ClassifyListing::with(['store', 'category', 'images'])
            ->when($moduleId, fn ($q) => $q->where('module_id', $moduleId))
            ->latest()
            ->limit(10)
            ->get();

        return view('classify::admin.dashboard', compact('stats', 'recent', 'mod'));
    }
}

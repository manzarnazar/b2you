<?php

namespace Modules\Classify\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Modules\Classify\Entities\ClassifyListingReport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = ClassifyListingReport::with(['listing.store', 'user', 'handler'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(config('default_pagination'));

        return view('classify::admin.reports.index', compact('reports'));
    }

    public function resolve(Request $request, $id)
    {
        $report = ClassifyListingReport::findOrFail($id);
        $report->update([
            'status' => 'resolved',
            'handled_by' => auth('admin')->id(),
            'handled_at' => now(),
        ]);
        Toastr::success(translate('messages.report_resolved') ?: 'Report resolved');
        return back();
    }

    public function dismiss($id)
    {
        $report = ClassifyListingReport::findOrFail($id);
        $report->update([
            'status' => 'dismissed',
            'handled_by' => auth('admin')->id(),
            'handled_at' => now(),
        ]);
        Toastr::success(translate('messages.report_dismissed') ?: 'Report dismissed');
        return back();
    }
}

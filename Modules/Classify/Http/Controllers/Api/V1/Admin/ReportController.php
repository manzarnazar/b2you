<?php

namespace Modules\Classify\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Classify\Entities\ClassifyListingReport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = ClassifyListingReport::with(['listing', 'user'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->limit ?? 25);
        return response()->json($reports, 200);
    }

    public function resolve($id)
    {
        $report = ClassifyListingReport::findOrFail($id);
        $report->update(['status' => 'resolved', 'handled_at' => now()]);
        return response()->json(['message' => 'Resolved', 'report' => $report], 200);
    }

    public function dismiss($id)
    {
        $report = ClassifyListingReport::findOrFail($id);
        $report->update(['status' => 'dismissed', 'handled_at' => now()]);
        return response()->json(['message' => 'Dismissed', 'report' => $report], 200);
    }
}

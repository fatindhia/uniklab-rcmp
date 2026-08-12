<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Activity Log';

        $area = in_array($request->query('area'), ['labs', 'staff'], true)
            ? $request->query('area')
            : 'all';

        $counts = [
            'all' => ActivityLog::count(),
            'labs' => ActivityLog::where('area', 'labs')->count(),
            'staff' => ActivityLog::where('area', 'staff')->count(),
        ];

        $entries = ActivityLog::query()
            ->with('performedBy')
            ->when($area !== 'all', fn ($q) => $q->where('area', $area))
            ->latest('created_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity-log', compact('pageTitle', 'entries', 'area', 'counts'));
    }
}

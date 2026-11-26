<?php

namespace App\Http\Controllers;

use App\Support\ActivityLogQuery;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        [$query, $scope] = ActivityLogQuery::build($request, auth()->user());

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        $activities = $query->latest('id')->paginate(20);

        return view('activity_logs.index', compact('activities', 'scope'));
    }
}

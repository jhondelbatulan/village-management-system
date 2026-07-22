<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\ActivityLogService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only([
            'user_id', 'role', 'village_id', 'action', 'module', 'date_from', 'date_to',
        ]);

        $activityLogs = ActivityLogService::getFiltered($filters, 25);
        $users = User::where('status', 'active')->orderBy('username')->get();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $actions = ActivityLogService::getDistinctActions();
        $stats = ActivityLogService::getStats();

        return view('system-admin.activity-logs', compact(
            'activityLogs', 'users', 'villages', 'actions', 'filters', 'stats'
        ));
    }
}

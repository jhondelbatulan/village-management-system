<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Village;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user.role', 'village');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('village_id')) {
            $query->where('village_id', $request->village_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $auditLogs = $query->latest()->paginate(25)->withQueryString();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $distinctActions = AuditLog::distinct()->pluck('action')->sort()->values();
        $distinctModules = AuditLog::whereNotNull('module')->distinct()->pluck('module')->sort()->values();
        $stats = AuditLogService::getStats();

        return view('system-admin.audit-logs', compact(
            'auditLogs', 'villages', 'distinctActions', 'distinctModules', 'stats'
        ));
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user.role', 'village');
        return view('system-admin.audit-log-detail', compact('auditLog'));
    }
}

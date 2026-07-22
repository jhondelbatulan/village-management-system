<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        $grouped = SystemSettingService::getGrouped();
        return view('system-admin.settings', compact('grouped'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->input('settings', []) as $key => $value) {
            SystemSettingService::set($key, $value);
        }

        return back()->with('success', 'System settings updated successfully.');
    }

    public function initDefaults(): RedirectResponse
    {
        SystemSettingService::initDefaults();
        return back()->with('success', 'Default settings initialized.');
    }
}

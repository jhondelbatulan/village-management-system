<?php

namespace App\Http\Controllers;

use App\Services\UserPreferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserPreferenceController extends Controller
{
    public function __construct(
        private UserPreferenceService $preferenceService
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $preferences = $this->preferenceService->getForUser($user->id);

        return view('settings.personal', compact('user', 'preferences'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'theme' => 'sometimes|in:light,dark,auto',
            'accent_color' => 'sometimes|in:blue,green,purple,orange,red,gray',
            'font_size' => 'sometimes|in:small,medium,large,xlarge',
            'sidebar_style' => 'sometimes|in:expanded,collapsed,auto',
            'dashboard_layout' => 'sometimes|in:default,compact,spacious',
            'animation_level' => 'sometimes|in:full,reduced,none',
            'compact_mode' => 'sometimes|boolean',
            'notification_sound' => 'sometimes|boolean',
            'browser_notifications' => 'sometimes|boolean',
            'display_name' => 'sometimes|nullable|string|max:255',
        ]);

        if ($request->has('dashboard_widgets')) {
            $validated['dashboard_widgets'] = $request->input('dashboard_widgets');
        }

        $this->preferenceService->updateForUser($user->id, $validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'preferences' => $this->preferenceService->getForUser($user->id)]);
        }

        return redirect()->route('settings.personal')->with('success', 'Preferences saved successfully.');
    }

    public function updateTheme(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate(['theme' => 'required|in:light,dark,auto']);

        $this->preferenceService->updateForUser($user->id, $validated);

        return response()->json(['success' => true, 'theme' => $validated['theme']]);
    }

    public function updateAccent(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate(['accent_color' => 'required|in:blue,green,purple,orange,red,gray']);

        $this->preferenceService->updateForUser($user->id, $validated);

        return response()->json(['success' => true, 'accent_color' => $validated['accent_color']]);
    }

    public function getPreferences(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $preferences = $this->preferenceService->getForUser($user->id);

        return response()->json([
            'theme' => $preferences->theme,
            'accent_color' => $preferences->accent_color,
            'font_size' => $preferences->font_size,
            'sidebar_style' => $preferences->sidebar_style,
            'animation_level' => $preferences->animation_level,
            'compact_mode' => $preferences->compact_mode,
            'dashboard_widgets' => $preferences->dashboard_widgets ?? $preferences->getDefaultDashboardWidgets(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\BrandingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SystemBrandingController extends Controller
{
    public function __construct(
        private BrandingService $brandingService
    ) {}

    public function index(): View
    {
        $branding = $this->brandingService->getAll();
        $groups = $this->brandingService->getBrandingGroup();

        return view('system-admin.branding', compact('branding', 'groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'system_name' => 'required|string|max:255',
            'system_tagline' => 'nullable|string|max:255',
            'organization_name' => 'nullable|string|max:255',
            'cpdo_office_name' => 'nullable|string|max:255',
            'system_address' => 'nullable|string|max:500',
            'system_phone' => 'nullable|string|max:50',
            'system_email' => 'nullable|email|max:255',
            'footer_copyright' => 'nullable|string|max:500',
            'system_version' => 'nullable|string|max:20',
            'developer_name' => 'nullable|string|max:255',
            'default_theme' => 'sometimes|in:light,dark,auto',
            'default_accent_color' => 'sometimes|in:blue,green,purple,orange,red,gray',
            'default_font_size' => 'sometimes|in:small,medium,large,xlarge',
            'default_sidebar_style' => 'sometimes|in:expanded,collapsed,auto',
            'default_animation_level' => 'sometimes|in:full,reduced,none',
            'default_dashboard_layout' => 'sometimes|in:default,compact,spacious',
        ]);

        $this->brandingService->setMany($validated);

        return redirect()->route('system-admin.branding.index')->with('success', 'Branding settings updated successfully.');
    }

    public function uploadLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => 'required|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $this->brandingService->uploadLogo($request->file('logo'));

        $redirect = $request->headers->get('referer');
        if ($redirect && str_contains($redirect, 'system-admin/settings')) {
            return redirect()->route('system-admin.settings.index')->with('success', 'Logo uploaded successfully.');
        }

        return redirect()->route('system-admin.branding.index')->with('success', 'Logo uploaded successfully.');
    }

    public function uploadFavicon(Request $request): RedirectResponse
    {
        $request->validate([
            'favicon' => 'required|mimes:png,ico,svg|max:1024',
        ]);

        $this->brandingService->uploadFavicon($request->file('favicon'));

        return redirect()->route('system-admin.branding.index')->with('success', 'Favicon uploaded successfully.');
    }
}

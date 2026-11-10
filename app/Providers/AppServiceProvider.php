<?php

namespace App\Providers;

use App\Helpers\SystemHelper;
use App\Http\View\Composers\PaymentPermissionsComposer;
use App\Http\View\Composers\VillageBackgroundComposer;
use App\Models\Household;
use App\Models\Resident;
use App\Models\HouseholdPayment;
use App\Models\Billing;
use App\Observers\HouseholdObserver;
use App\Observers\ResidentObserver;
use App\Observers\HouseholdPaymentObserver;
use App\Observers\BillingObserver;
use App\Models\VillagePhoto;
use App\Services\MenuService;
use App\Services\SystemSettingService;
use App\Services\BrandingService;
use App\Services\UserPreferenceService;
use App\Models\UserPreference;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        SystemSettingService::initDefaults();

        Resident::observe(ResidentObserver::class);
        Household::observe(HouseholdObserver::class);
        HouseholdPayment::observe(HouseholdPaymentObserver::class);
        Billing::observe(BillingObserver::class);

        View::composer(['layouts.app', 'partials.sidebar', 'partials.navbar', 'dashboard.*', 'settings.*', 'participation.*'], function ($view): void {
            $user = auth()->user();
            $brandingService = app(BrandingService::class);

            if ($user) {
                $prefs = UserPreference::forUser($user->id);
                $authUser = [
                    'name' => $user->full_name,
                    'username' => $user->username,
                    'role' => $user->role?->role_slug ?? 'resident',
                    'profile_image' => $user->profile_picture_url,
                ];
                $menuItems = app(MenuService::class)->getMenuForRole($user->role?->role_slug ?? 'resident', $user);
            } else {
                $prefs = null;
                $authUser = [
                    'name' => 'Guest',
                    'username' => 'guest',
                    'role' => config('app_settings.default_role', 'super_admin'),
                    'profile_image' => asset(config('app_settings.assets.default_profile')),
                ];
                $menuItems = [];
            }

            $menuService = app(MenuService::class);

            $roleSlug = $user?->role?->role_slug;
            $villageName = $user?->village?->village_name;

            if ($roleSlug === 'secretary' && $villageName) {
                $navRoleLabel = 'Village Secretary';
                $navDisplayName = $villageName . ' Secretary';
            } elseif ($roleSlug === 'treasurer' && $villageName) {
                $navRoleLabel = 'Village Treasurer';
                $navDisplayName = $villageName . ' Treasurer';
            } else {
                $navRoleLabel = $user?->role?->role_name ?? 'Guest';
                $navDisplayName = $authUser['name'];
            }

            $bodyClasses = 'dashboard-body';
            if ($prefs) {
                $bodyClasses .= ' theme-' . $prefs->theme;
                $bodyClasses .= ' accent-' . $prefs->accent_color;
                $bodyClasses .= ' font-' . $prefs->font_size;
                if ($prefs->compact_mode) $bodyClasses .= ' compact-mode';
                if ($prefs->sidebar_style === 'collapsed') $bodyClasses .= ' sidebar-collapsed';
                if ($prefs->animation_level === 'none') $bodyClasses .= ' animation-none';
                if ($prefs->animation_level === 'reduced') $bodyClasses .= ' animation-reduced';
            } else {
                $defaultTheme = $brandingService->get('default_theme', 'light');
                $bodyClasses .= ' theme-' . $defaultTheme;
                $bodyClasses .= ' accent-' . $brandingService->get('default_accent_color', 'blue');
                $bodyClasses .= ' font-' . $brandingService->get('default_font_size', 'medium');
            }

            $villagePhotos = collect();
            $viewName = $view->getName();
            if ($user && $viewName === 'dashboard.super-admin') {
                $villagePhotos = VillagePhoto::with('village')->orderBy('sort_order')->get();
            } elseif ($user && Str::startsWith($viewName, 'dashboard.')) {
                $villageId = $user->village_id ?? $user->household?->village_id;
                if ($villageId) {
                    $villagePhotos = VillagePhoto::where('village_id', $villageId)->orderBy('sort_order')->get();
                }
            }

            $view->with([
                'authUser' => $authUser,
                'menuItems' => $menuItems,
                'menuService' => $menuService,
                'systemName' => $brandingService->get('system_name', SystemHelper::systemName()),
                'roleLabel' => $user?->role?->role_name ?? 'Guest',
                'navRoleLabel' => $navRoleLabel,
                'navDisplayName' => $navDisplayName,
                'branding' => $brandingService,
                'userPrefs' => $prefs,
                'bodyClasses' => $bodyClasses,
                'dashboardWidgets' => $prefs ? ($prefs->dashboard_widgets ?? $prefs->getDefaultDashboardWidgets()) : [],
                'villagePhotos' => $villagePhotos,
            ]);
        });

        $paymentViews = [
            'billings.*',
            'payments.*',
        ];
        View::composer($paymentViews, PaymentPermissionsComposer::class);

        // Village cover photos rotate behind the login and recovery screens.
        View::composer([
            'auth.login',
            'auth.forgot-password',
            'auth.verify-otp',
            'auth.reset-password',
        ], VillageBackgroundComposer::class);

        Blade::component('stat-card', 'components.stat-card');
        Blade::component('menu-item', 'components.menu-item');
    }
}

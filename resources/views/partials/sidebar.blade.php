<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-profile text-center">
        <div class="profile-avatar mx-auto mb-3">
            <img src="{{ $authUser['profile_image'] ?? asset(config('app_settings.assets.default_profile')) }}"
                 alt="Profile Picture"
                 class="rounded-circle sidebar-logo">
        </div>
        <p class="profile-welcome mb-1">Welcome,</p>
        <h6 class="profile-name mb-1">{{ $navDisplayName ?? ($authUser['name'] ?? 'User') }}</h6>
        <span class="badge profile-role">{{ $navRoleLabel ?? $roleLabel }}</span>
        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-light mt-3 w-100">
            <i class="bi bi-pencil-square me-1"></i> Edit Profile
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            @foreach($menuItems as $item)
                <x-menu-item :item="$item" :menuService="$menuService" />
            @endforeach
        </ul>
    </nav>
</aside>

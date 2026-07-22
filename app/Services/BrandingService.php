<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BrandingService
{
    protected const BRANDING_KEYS = [
        'system_name' => 'Multi-Village Management System',
        'system_tagline' => 'GIS-Enabled Village Management',
        'organization_name' => 'Barangay Management Office',
        'cpdo_office_name' => 'CPDO Office',
        'system_address' => '',
        'system_phone' => '',
        'system_email' => '',
        'footer_copyright' => '',
        'system_version' => '1.0.0',
        'developer_name' => '',
        'system_logo' => '',
        'system_favicon' => '',
        'default_theme' => 'light',
        'default_accent_color' => 'blue',
        'default_font_size' => 'medium',
        'default_sidebar_style' => 'expanded',
        'default_animation_level' => 'full',
        'default_dashboard_layout' => 'default',
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        return SystemSetting::getValue($key, $default ?? (self::BRANDING_KEYS[$key] ?? null));
    }

    public function getAll(): array
    {
        $branding = [];
        foreach (self::BRANDING_KEYS as $key => $default) {
            $branding[$key] = $this->get($key, $default);
        }
        return $branding;
    }

    public function set(string $key, mixed $value): void
    {
        SystemSetting::setValue($key, $value);
    }

    public function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            if ($key === 'system_logo' || $key === 'system_favicon') {
                continue;
            }
            $this->set($key, $value);
        }
    }

    public function getLogoUrl(): string
    {
        $logo = $this->get('system_logo');
        if ($logo && Storage::disk('public')->exists($logo)) {
            return asset('storage/' . $logo);
        }
        return asset('assets/images/logos/system-logo.svg');
    }

    public function getFaviconUrl(): string
    {
        $favicon = $this->get('system_favicon');
        if ($favicon && Storage::disk('public')->exists($favicon)) {
            return asset('storage/' . $favicon);
        }
        return asset('favicon.ico');
    }

    public function uploadLogo(UploadedFile $file): string
    {
        $oldLogo = $this->get('system_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }
        $path = $file->store('branding', 'public');
        $this->set('system_logo', $path);
        return $path;
    }

    public function uploadFavicon(UploadedFile $file): string
    {
        $oldFavicon = $this->get('system_favicon');
        if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
            Storage::disk('public')->delete($oldFavicon);
        }
        $path = $file->store('branding', 'public');
        $this->set('system_favicon', $path);
        return $path;
    }

    public function getBrandingGroup(): array
    {
        return [
            'Basic Information' => [
                'system_name' => ['type' => 'text', 'label' => 'System Name', 'description' => 'The name displayed throughout the application'],
                'system_tagline' => ['type' => 'text', 'label' => 'System Tagline', 'description' => 'Short description shown on login page'],
                'system_version' => ['type' => 'text', 'label' => 'System Version', 'description' => 'Current version number'],
            ],
            'Organization' => [
                'organization_name' => ['type' => 'text', 'label' => 'Organization Name', 'description' => 'Main organization name'],
                'cpdo_office_name' => ['type' => 'text', 'label' => 'CPDO Office Name', 'description' => 'CPDO office designation'],
                'system_address' => ['type' => 'text', 'label' => 'Address', 'description' => 'Office address'],
                'system_phone' => ['type' => 'text', 'label' => 'Telephone Number', 'description' => 'Contact telephone number'],
                'system_email' => ['type' => 'email', 'label' => 'Email Address', 'description' => 'Contact email address'],
            ],
            'Footer & Credits' => [
                'footer_copyright' => ['type' => 'text', 'label' => 'Footer Copyright', 'description' => 'Copyright text in footer'],
                'developer_name' => ['type' => 'text', 'label' => 'Developer Information', 'description' => 'Developer or team name'],
            ],
            'Appearance Defaults' => [
                'default_theme' => ['type' => 'select', 'label' => 'Default Theme', 'options' => ['light' => 'Light', 'dark' => 'Dark', 'auto' => 'Automatic'], 'description' => 'Default theme for new users'],
                'default_accent_color' => ['type' => 'select', 'label' => 'Default Accent Color', 'options' => ['blue' => 'Blue', 'green' => 'Green', 'purple' => 'Purple', 'orange' => 'Orange', 'red' => 'Red', 'gray' => 'Gray'], 'description' => 'Default accent color for new users'],
                'default_font_size' => ['type' => 'select', 'label' => 'Default Font Size', 'options' => ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'xlarge' => 'Extra Large'], 'description' => 'Default font size for new users'],
                'default_sidebar_style' => ['type' => 'select', 'label' => 'Default Sidebar Style', 'options' => ['expanded' => 'Expanded', 'collapsed' => 'Collapsed', 'auto' => 'Auto Collapse'], 'description' => 'Default sidebar behavior'],
                'default_animation_level' => ['type' => 'select', 'label' => 'Default Animation Level', 'options' => ['full' => 'Full', 'reduced' => 'Reduced', 'none' => 'None'], 'description' => 'Default animation level'],
                'default_dashboard_layout' => ['type' => 'select', 'label' => 'Default Dashboard Layout', 'options' => ['default' => 'Default', 'compact' => 'Compact', 'spacious' => 'Spacious'], 'description' => 'Default dashboard card layout'],
            ],
        ];
    }
}

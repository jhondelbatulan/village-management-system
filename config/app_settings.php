<?php

return [

    'name' => 'Multi-Village Management System',
    'short_name' => 'MVMS',
    'tagline' => 'GIS-Enabled Village Governance Platform',
    'version' => '1.0.0',
    'footer_text' => '© ' . date('Y') . ' Multi-Village Management System. All rights reserved.',

    'roles' => [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'secretary' => 'Secretary',
        'treasurer' => 'Treasurer',
        'resident' => 'Resident',
    ],

    'default_role' => 'super_admin',

    'assets' => [
        'logo' => 'assets/images/logos/system-logo.svg',
        'login_background' => 'assets/images/backgrounds/login-bg.svg',
        'default_profile' => 'assets/images/profile/default-avatar.svg',
        'default_village_logo' => 'assets/images/profile/default-avatar.svg',
        'default_village_cover' => 'assets/images/profile/default-avatar.svg',
    ],

];

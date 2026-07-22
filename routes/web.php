<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VillageController;
use App\Http\Controllers\VillageDocumentController;
use App\Http\Controllers\VillageOfficerController;
use App\Http\Controllers\GisController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\VillagePaymentConfigController;
use App\Http\Controllers\HouseholdPaymentAmountController;
use App\Http\Controllers\SwitchController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ParticipationController;
use App\Http\Controllers\PredictivePaymentController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\SystemBrandingController;
use App\Http\Controllers\PopulationAnalyticsController;
use App\Http\Controllers\HouseholdAnalyticsController;
use App\Http\Controllers\DemographicDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Multi-Village Management System
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function (): void {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/users/online-status', function () {
        $threshold = now()->subMinutes(5);
        return \App\Models\User::where('last_activity_at', '>=', $threshold)
            ->pluck('id');
    })->name('users.online-status');

    /*
    | Role-Based Dashboards
    */
    Route::get('/dashboard/super-admin', [DashboardController::class, 'superAdmin'])
        ->middleware('role:super_admin')
        ->name('dashboard.super_admin');

    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('dashboard.admin');

    Route::get('/dashboard/secretary', [DashboardController::class, 'secretary'])
        ->middleware('role:secretary')
        ->name('dashboard.secretary');

    Route::get('/dashboard/treasurer', [DashboardController::class, 'treasurer'])
        ->middleware('role:treasurer')
        ->name('dashboard.treasurer');

    Route::get('/dashboard/resident', [DashboardController::class, 'resident'])
        ->middleware('role:resident')
        ->name('dashboard.resident');

    /*
    | Personal Settings (all authenticated users)
    */
    Route::prefix('settings')->name('settings.')->group(function (): void {
        Route::get('/personal', [UserPreferenceController::class, 'index'])->name('personal');
        Route::post('/personal', [UserPreferenceController::class, 'update'])->name('personal.update');
        Route::post('/personal/theme', [UserPreferenceController::class, 'updateTheme'])->name('personal.theme');
        Route::post('/personal/accent', [UserPreferenceController::class, 'updateAccent'])->name('personal.accent');
        Route::get('/personal/preferences', [UserPreferenceController::class, 'getPreferences'])->name('personal.preferences');
    });

    /*
    | Profile Management
    */
    Route::prefix('profile')->name('profile.')->group(function (): void {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::get('/change-password', [ProfileController::class, 'changePasswordForm'])->name('change-password');
        Route::put('/change-password', [ProfileController::class, 'changePassword'])->name('change-password.update');
        Route::post('/picture', [ProfileController::class, 'updatePicture'])->name('picture.update');
    });

    /*
    | Multi-Session / Account Switcher
    | Allows pinned accounts for quick role switching
    */
    Route::prefix('sessions')->name('sessions.')->group(function (): void {
        Route::get('/', [SwitchController::class, 'index'])->name('index');
        Route::post('/pin', [SwitchController::class, 'pin'])->name('pin');
        Route::post('/unpin/{userId}', [SwitchController::class, 'unpin'])->name('unpin');
        Route::post('/switch/{userId}', [SwitchController::class, 'switch'])->name('switch');
        Route::post('/back', [SwitchController::class, 'back'])->name('back');
        Route::get('/quick-switch', [SwitchController::class, 'quickSwitch'])->name('quick-switch');
    });

    /*
    | User Management (Super Admin & Admin only)
    */
    Route::middleware('role:super_admin,admin')->prefix('users')->name('users.')->group(function (): void {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

    /*
    | Approvals — Super Admin, Admin & Secretary
    | Super Admin: view all, final approve/reject
    | Admin: view pending from their village, approve → admin_approved
    | Secretary: view pending resident requests from their village, approve directly
    */
    Route::middleware('role:super_admin,admin,secretary')->prefix('approvals')->name('approvals.')->group(function (): void {
        Route::get('/households', [ApprovalController::class, 'households'])->name('households');
        Route::post('/households/{household}/approve', [ApprovalController::class, 'approveHousehold'])->name('approve-household');
        Route::post('/households/{household}/reject', [ApprovalController::class, 'rejectHousehold'])->name('reject-household');
        Route::get('/residents', [ApprovalController::class, 'residents'])->name('residents');

        Route::post('/residents/{resident}/approve', [ApprovalController::class, 'approveResident'])->name('approve-resident')->middleware('role:super_admin,admin');
        Route::post('/residents/{resident}/reject', [ApprovalController::class, 'rejectResident'])->name('reject-resident')->middleware('role:super_admin,admin');
        Route::post('/residents/{resident}/secretary-approve', [ApprovalController::class, 'approveResidentBySecretary'])->name('approve-resident-secretary')->middleware('role:secretary');
        Route::post('/residents/{resident}/secretary-reject', [ApprovalController::class, 'rejectResidentBySecretary'])->name('reject-resident-secretary')->middleware('role:secretary');
    });

    /*
    | Village Management
    | Super Admin: full CRUD
    | Secretary: view + edit their village
    */
    Route::middleware('role:super_admin,secretary')->prefix('villages')->name('villages.')->group(function (): void {
        Route::get('/', [VillageController::class, 'index'])->name('index')->middleware('role:super_admin');
        Route::get('/create', [VillageController::class, 'create'])->name('create')->middleware('role:super_admin');
        Route::post('/', [VillageController::class, 'store'])->name('store')->middleware('role:super_admin');
        Route::get('/export', [ResidentController::class, 'exportVillages'])->name('export')->middleware('role:super_admin');
        Route::get('/{village}/edit', [VillageController::class, 'edit'])->name('edit');
        Route::put('/{village}', [VillageController::class, 'update'])->name('update');
        Route::delete('/{village}', [VillageController::class, 'destroy'])->name('destroy')->middleware('role:super_admin');
        Route::post('/{id}/restore', [VillageController::class, 'restore'])->name('restore')->middleware('role:super_admin');
        Route::post('/{village}/photos', [VillageController::class, 'uploadPhoto'])->name('photos.upload');
        Route::delete('/{village}/photos/{photo}', [VillageController::class, 'deletePhoto'])->name('photos.delete');
    });

    Route::middleware('role:super_admin,secretary,treasurer,resident')->prefix('villages')->name('villages.')->group(function (): void {
        Route::get('/{village}', [VillageController::class, 'show'])->name('show');
    });

    /*
    | Village Officers
    */
    Route::middleware('role:super_admin,admin,secretary')->prefix('villages/{village}/officers')->name('villages.officers.')->group(function (): void {
        Route::get('/', [VillageOfficerController::class, 'index'])->name('index');
        Route::post('/', [VillageOfficerController::class, 'store'])->name('store');
        Route::put('/{officer}', [VillageOfficerController::class, 'update'])->name('update');
        Route::delete('/{officer}', [VillageOfficerController::class, 'destroy'])->name('destroy');
    });

    /*
    | Village Documents
    */
    Route::middleware('role:super_admin,admin,secretary')->prefix('villages/{village}/documents')->name('villages.documents.')->group(function (): void {
        Route::get('/', [VillageDocumentController::class, 'index'])->name('index');
        Route::post('/', [VillageDocumentController::class, 'store'])->name('store');
        Route::get('/{document}/download', [VillageDocumentController::class, 'download'])->name('download');
        Route::delete('/{document}', [VillageDocumentController::class, 'destroy'])->name('destroy');
    });

    /*
    | Household Management — Secretary creates/edits, Admin/Super Admin approve, all view
    | IMPORTANT: literal routes MUST come before wildcard {household} routes
    */
    Route::middleware('role:super_admin,admin,secretary')->prefix('households')->name('households.')->group(function (): void {
        Route::get('/', [HouseholdController::class, 'index'])->name('index');
        Route::get('/export', [ResidentController::class, 'exportHouseholds'])->name('export');

        Route::middleware('role:secretary')->group(function (): void {
            Route::get('/create', [HouseholdController::class, 'create'])->name('create');
            Route::post('/', [HouseholdController::class, 'store'])->name('store');
        });

        Route::middleware('role:super_admin,admin')->group(function (): void {
            Route::post('/{household}/approve', [HouseholdController::class, 'approve'])->name('approve');
        });

        Route::get('/{household}', [HouseholdController::class, 'show'])->name('show');

        Route::middleware('role:secretary')->group(function (): void {
            Route::get('/{household}/edit', [HouseholdController::class, 'edit'])->name('edit');
            Route::put('/{household}', [HouseholdController::class, 'update'])->name('update');
            Route::delete('/{household}', [HouseholdController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [HouseholdController::class, 'restore'])->name('restore');
            Route::post('/{household}/submit', [HouseholdController::class, 'submit'])->name('submit');
        });
    });

    /*
    | Resident Management — Secretary creates/edits, Admin/Super Admin approve, all view
    | IMPORTANT: literal routes MUST come before wildcard {resident} routes
    */
    Route::middleware('role:super_admin,admin,secretary')->prefix('residents')->name('residents.')->group(function (): void {
        Route::get('/', [ResidentController::class, 'index'])->name('index');
        Route::get('/import', [ResidentController::class, 'import'])->name('import');
        Route::post('/import', [ResidentController::class, 'processImport'])->name('process-import');
        Route::get('/download-template', [ResidentController::class, 'downloadTemplate'])->name('download-template');
        Route::get('/export', [ResidentController::class, 'export'])->name('export');

        Route::middleware('role:secretary')->group(function (): void {
            Route::get('/create', [ResidentController::class, 'create'])->name('create');
            Route::post('/', [ResidentController::class, 'store'])->name('store');
        });

        Route::middleware('role:super_admin,admin')->group(function (): void {
            Route::post('/{resident}/approve', [ResidentController::class, 'approve'])->name('approve');
        });

        Route::get('/{resident}', [ResidentController::class, 'show'])->name('show');

        Route::middleware('role:secretary')->group(function (): void {
            Route::get('/{resident}/edit', [ResidentController::class, 'edit'])->name('edit');
            Route::put('/{resident}', [ResidentController::class, 'update'])->name('update');
            Route::delete('/{resident}', [ResidentController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [ResidentController::class, 'restore'])->name('restore');
            Route::post('/{resident}/submit', [ResidentController::class, 'submit'])->name('submit');
        });
    });

    /*
    | GIS Module — Role-based map views
    | Super Admin: system-wide map
    | Admin/Secretary/Treasurer: village map
    | Resident: own household location
    */
    Route::prefix('gis')->name('gis.')->group(function (): void {
        Route::get('/', [GisController::class, 'index'])->name('index');
        Route::get('/search', [GisController::class, 'search'])->name('search');
        Route::get('/filter', [GisController::class, 'filter'])->name('filter');
        Route::get('/households', [GisController::class, 'households'])->name('households');
        Route::get('/export', [GisController::class, 'export'])->name('export');
        Route::get('/village/{villageId}', [GisController::class, 'villageApi'])->name('village-api');
        Route::get('/stats', [GisController::class, 'statsApi'])->name('stats');
        Route::get('/villages', [GisController::class, 'villagesApi'])->name('villages-api');
        Route::get('/village/{villageId}/analytics', [GisController::class, 'villageAnalyticsApi'])->name('village-analytics-api');
        Route::get('/village/{villageId}/dashboard-data', [GisController::class, 'dashboardDataApi'])->name('dashboard-data-api');
        Route::get('/system-overview', [GisController::class, 'systemOverviewApi'])->name('system-overview-api');
    });

    /*
    | Payment Management
    | Treasurer/Secretary: view + record payments for their village
    | Admin: view + approve payments
    | Super Admin: full access + payment config
    */
    Route::middleware('role:super_admin,admin,treasurer,secretary')->prefix('payments')->name('payments.')->group(function (): void {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/create', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
    });

    Route::middleware('role:super_admin,admin,treasurer,secretary,resident')->prefix('payments')->name('payments.')->group(function (): void {
        Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
    });

    /*
    | Billing Management
    | Treasurer/Secretary: view + generate billings for their village
    | Admin: view billings
    | Super Admin: full access
    */
    Route::middleware('role:super_admin,admin,treasurer,secretary,resident')->prefix('billings')->name('billings.')->group(function (): void {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/generate', [BillingController::class, 'generateForm'])->name('generate');
        Route::post('/generate', [BillingController::class, 'generate'])->name('generate.store');
        Route::get('/{billing}', [BillingController::class, 'show'])->name('show');
    });

    /*
    | Household Payments — residents can view their own household payments
    */
    Route::prefix('household-payments')->name('household-payments.')->group(function (): void {
        Route::get('/{household}', [PaymentController::class, 'householdPayments'])->name('show');
    });

    /*
    | My Household — Resident access to own household profile
    */
    Route::get('/my-household', [HouseholdController::class, 'myHousehold'])->name('households.my-household');
    Route::get('/my-household/request-member', [HouseholdController::class, 'requestMemberForm'])->name('households.request-member-form');
    Route::post('/my-household/request-member', [HouseholdController::class, 'requestMember'])->name('households.request-member');

    /*
    | Household Status Toggle — Secretary can toggle active/inactive
    */
    Route::middleware('role:super_admin,admin,secretary')->patch('/households/{household}/status', [HouseholdController::class, 'toggleStatus'])->name('households.toggle-status');

    /*
    | Payment Configuration — Super Admin only
    */
    Route::middleware('role:super_admin')->prefix('payment-config')->name('payment-config.')->group(function (): void {
        Route::get('/', [VillagePaymentConfigController::class, 'index'])->name('index');
        Route::get('/{village}', [VillagePaymentConfigController::class, 'show'])->name('show');
        Route::get('/{village}/edit', [VillagePaymentConfigController::class, 'edit'])->name('edit');
        Route::put('/{village}', [VillagePaymentConfigController::class, 'update'])->name('update');
        Route::post('/payment-type', [VillagePaymentConfigController::class, 'storePaymentType'])->name('store-type');
        Route::put('/payment-type/{paymentType}', [VillagePaymentConfigController::class, 'updatePaymentType'])->name('update-type');
        Route::delete('/payment-type/{paymentType}', [VillagePaymentConfigController::class, 'destroyPaymentType'])->name('destroy-type');
    });

    /*
    | Household Payment Amounts — Super Admin only
    | Per-household custom billing amounts (e.g. La Union building permits)
    */
    Route::middleware('role:super_admin')->prefix('household-amounts')->name('household-amounts.')->group(function (): void {
        Route::get('/{village}', [HouseholdPaymentAmountController::class, 'index'])->name('index');
        Route::get('/{village}/{household}/edit', [HouseholdPaymentAmountController::class, 'edit'])->name('edit');
        Route::put('/{village}/{household}', [HouseholdPaymentAmountController::class, 'update'])->name('update');
        Route::delete('/{village}/{household}/{amount}', [HouseholdPaymentAmountController::class, 'destroy'])->name('destroy');
    });

    /*
    | Announcement Management
    | Super Admin: full CRUD across all villages
    | Admin: create for their village
    | Secretary: full CRUD for their village
    | Treasurer: payment-related only for their village
    | Resident: view + respond to attendance-required
    */
    Route::middleware('role:super_admin,admin,secretary,treasurer,resident')->prefix('announcements')->name('announcements.')->group(function (): void {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');

        Route::middleware('role:super_admin,admin,secretary,treasurer')->group(function (): void {
            Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
            Route::post('/', [AnnouncementController::class, 'store'])->name('store');
            Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
            Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
            Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
            Route::post('/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('publish');
            Route::post('/{announcement}/archive', [AnnouncementController::class, 'archive'])->name('archive');
        });

        Route::get('/{announcement}', [AnnouncementController::class, 'show'])->name('show');

        Route::middleware('role:resident')->group(function (): void {
            Route::post('/{announcement}/respond', [AnnouncementController::class, 'respond'])->name('respond');
        });
    });

    /*
    | Event Management
    | Super Admin: full CRUD across all villages
    | Admin: create for their village
    | Secretary: full CRUD for their village
    | Resident: view + respond
    */
    Route::middleware('role:super_admin,admin,secretary,treasurer,resident')->prefix('events')->name('events.')->group(function (): void {
        Route::get('/', [EventController::class, 'index'])->name('index');

        Route::middleware('role:super_admin,admin,secretary')->group(function (): void {
            Route::get('/create', [EventController::class, 'create'])->name('create');
            Route::post('/', [EventController::class, 'store'])->name('store');
            Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
            Route::put('/{event}', [EventController::class, 'update'])->name('update');
            Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
            Route::post('/{event}/participations', [EventController::class, 'recordParticipation'])->name('participations.store');
            Route::delete('/{event}/participations/{participation}', [EventController::class, 'removeParticipation'])->name('participations.destroy');
            Route::post('/{event}/auto-generate', [EventController::class, 'autoGenerate'])->name('auto-generate');
        });

        Route::get('/{event}', [EventController::class, 'show'])->name('show');

        Route::middleware('role:resident')->group(function (): void {
            Route::post('/{event}/respond', [EventController::class, 'respond'])->name('respond');
        });
    });

    /*
    | Participation Monitoring
    | Super Admin: village comparison, all stats
    | Admin/Secretary: village participation stats
    */
    Route::middleware('role:super_admin,admin,secretary')->prefix('participation')->name('participation.')->group(function (): void {
        Route::get('/', [ParticipationController::class, 'index'])->name('index');
        Route::get('/household/{household}', [ParticipationController::class, 'household'])->name('household');
        Route::get('/resident/{resident}', [ParticipationController::class, 'residentHistory'])->name('resident-history');
    });

    /*
    | Population Analytics
    | Super Admin: system-wide analytics
    | Admin/Secretary/Treasurer: village-level analytics
    */
    Route::middleware('role:super_admin,admin,secretary,treasurer')->prefix('analytics')->name('analytics.')->group(function (): void {
        Route::get('/population', [PopulationAnalyticsController::class, 'index'])->name('population.index');
        Route::get('/population/village/{villageId}', [PopulationAnalyticsController::class, 'village'])->name('population.village');

        Route::get('/household', [HouseholdAnalyticsController::class, 'index'])->name('household.index');
        Route::get('/household/village/{villageId}', [HouseholdAnalyticsController::class, 'village'])->name('household.village');

        Route::get('/demographic', [DemographicDashboardController::class, 'index'])->name('demographic.index');
        Route::get('/demographic/village/{villageId}', [DemographicDashboardController::class, 'village'])->name('demographic.village');

        Route::get('/education', [\App\Http\Controllers\EducationAnalyticsController::class, 'index'])->name('education.index');
        Route::get('/education/village/{villageId}', [\App\Http\Controllers\EducationAnalyticsController::class, 'village'])->name('education.village');

        Route::get('/employment', [\App\Http\Controllers\EmploymentAnalyticsController::class, 'index'])->name('employment.index');
        Route::get('/employment/village/{villageId}', [\App\Http\Controllers\EmploymentAnalyticsController::class, 'village'])->name('employment.village');

        Route::middleware('role:super_admin,admin,treasurer')->group(function (): void {
            Route::get('/occupation', [\App\Http\Controllers\OccupationAnalyticsController::class, 'index'])->name('occupation.index');
            Route::get('/occupation/village/{villageId}', [\App\Http\Controllers\OccupationAnalyticsController::class, 'village'])->name('occupation.village');
            Route::get('/occupation/export/{format}', [\App\Http\Controllers\OccupationAnalyticsController::class, 'export'])->name('occupation.export');

            Route::get('/skills', [\App\Http\Controllers\SkillsAnalyticsController::class, 'index'])->name('skills.index');
            Route::get('/skills/village/{villageId}', [\App\Http\Controllers\SkillsAnalyticsController::class, 'village'])->name('skills.village');
            Route::get('/skills/export/{format}', [\App\Http\Controllers\SkillsAnalyticsController::class, 'export'])->name('skills.export');
        });

        Route::get('/socio-economic', [\App\Http\Controllers\SocioEconomicDashboardController::class, 'index'])->name('socio-economic.index');
        Route::get('/socio-economic/village/{villageId}', [\App\Http\Controllers\SocioEconomicDashboardController::class, 'village'])->name('socio-economic.village');
        Route::get('/socio-economic/export/{format}', [\App\Http\Controllers\SocioEconomicDashboardController::class, 'export'])->name('socio-economic.export');

        Route::get('/government-assistance', [\App\Http\Controllers\GovernmentAssistanceController::class, 'index'])->name('government-assistance.index');
        Route::get('/government-assistance/village/{villageId}', [\App\Http\Controllers\GovernmentAssistanceController::class, 'village'])->name('government-assistance.village');
        Route::get('/government-assistance/export/{format}', [\App\Http\Controllers\GovernmentAssistanceController::class, 'export'])->name('government-assistance.export');

        Route::get('/government-membership', [\App\Http\Controllers\GovernmentMembershipController::class, 'index'])->name('government-membership.index');
        Route::get('/government-membership/village/{villageId}', [\App\Http\Controllers\GovernmentMembershipController::class, 'village'])->name('government-membership.village');
        Route::get('/government-membership/export/{format}', [\App\Http\Controllers\GovernmentMembershipController::class, 'export'])->name('government-membership.export');

        Route::get('/healthcare', [\App\Http\Controllers\HealthcareController::class, 'index'])->name('healthcare.index');
        Route::get('/healthcare/village/{villageId}', [\App\Http\Controllers\HealthcareController::class, 'village'])->name('healthcare.village');
        Route::get('/healthcare/export/{format}', [\App\Http\Controllers\HealthcareController::class, 'export'])->name('healthcare.export');

        Route::get('/voter-registration', [\App\Http\Controllers\VoterRegistrationController::class, 'index'])->name('voter-registration.index');
        Route::get('/voter-registration/village/{villageId}', [\App\Http\Controllers\VoterRegistrationController::class, 'village'])->name('voter-registration.village');
        Route::get('/voter-registration/export/{format}', [\App\Http\Controllers\VoterRegistrationController::class, 'export'])->name('voter-registration.export');

        Route::get('/social-welfare', [\App\Http\Controllers\SocialWelfareController::class, 'index'])->name('social-welfare.index');
        Route::get('/social-welfare/village/{villageId}', [\App\Http\Controllers\SocialWelfareController::class, 'village'])->name('social-welfare.village');
        Route::get('/social-welfare/export/{format}', [\App\Http\Controllers\SocialWelfareController::class, 'export'])->name('social-welfare.export');

        Route::get('/community-development', [\App\Http\Controllers\CommunityDevelopmentController::class, 'index'])->name('community-development.index');
        Route::get('/community-development/village/{villageId}', [\App\Http\Controllers\CommunityDevelopmentController::class, 'village'])->name('community-development.village');
        Route::get('/community-development/export/{format}', [\App\Http\Controllers\CommunityDevelopmentController::class, 'export'])->name('community-development.export');

        Route::get('/executive-intelligence', [\App\Http\Controllers\ExecutiveIntelligenceController::class, 'index'])->name('executive-intelligence.index');
        Route::get('/executive-intelligence/export/{format}', [\App\Http\Controllers\ExecutiveIntelligenceController::class, 'export'])->name('executive-intelligence.export');

        Route::get('/search', [\App\Http\Controllers\AnalyticsSearchController::class, 'search'])->name('search');
        Route::get('/filter', [\App\Http\Controllers\AnalyticsSearchController::class, 'advancedFilter'])->name('filter');

        Route::get('/advanced-demographics', [\App\Http\Controllers\AdvancedDemographicsController::class, 'index'])->name('advanced-demographics.index');
        Route::get('/advanced-demographics/village/{villageId}', [\App\Http\Controllers\AdvancedDemographicsController::class, 'village'])->name('advanced-demographics.village');
        Route::get('/advanced-demographics/export/{format}', [\App\Http\Controllers\AdvancedDemographicsController::class, 'export'])->name('advanced-demographics.export');

        Route::get('/planning', [\App\Http\Controllers\PlanningAnalyticsController::class, 'index'])->name('planning.index');
        Route::get('/planning/village/{villageId}', [\App\Http\Controllers\PlanningAnalyticsController::class, 'village'])->name('planning.village');
        Route::get('/planning/search', [\App\Http\Controllers\PlanningAnalyticsController::class, 'search'])->name('planning.search');
        Route::get('/planning/filter', [\App\Http\Controllers\PlanningAnalyticsController::class, 'filter'])->name('planning.filter');
        Route::get('/planning/export/{format}', [\App\Http\Controllers\PlanningAnalyticsController::class, 'export'])->name('planning.export');

        Route::middleware('role:super_admin,admin,treasurer')->prefix('predictive-payment')->name('predictive-payment.')->group(function (): void {
            Route::get('/', [PredictivePaymentController::class, 'index'])->name('index');
            Route::get('/village/{villageId}', [PredictivePaymentController::class, 'village'])->name('village');
            Route::get('/household/{household}', [PredictivePaymentController::class, 'household'])->name('household');
            Route::get('/export/{format}', [PredictivePaymentController::class, 'export'])->name('export');
            Route::post('/recalculate', [PredictivePaymentController::class, 'recalculate'])->name('recalculate');
        });
    });

    Route::middleware('role:super_admin,admin,secretary,treasurer')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::post('/generate', [\App\Http\Controllers\ReportController::class, 'generate'])->name('generate');
        Route::get('/history', [\App\Http\Controllers\ReportController::class, 'history'])->name('history');
        Route::get('/print', [\App\Http\Controllers\ReportController::class, 'printView'])->name('print');
        Route::middleware('role:super_admin,admin')->group(function (): void {
            Route::get('/schedule', [\App\Http\Controllers\ReportController::class, 'schedule'])->name('schedule');
            Route::post('/schedule', [\App\Http\Controllers\ReportController::class, 'storeSchedule'])->name('schedule.store');
            Route::delete('/schedule/{report}', [\App\Http\Controllers\ReportController::class, 'destroySchedule'])->name('schedule.destroy');
        });
    });

    // ====== SYSTEM ADMINISTRATION (Super Admin Only) ======
    Route::middleware('role:super_admin')->prefix('system-admin')->name('system-admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SystemAdministrationController::class, 'dashboard'])->name('dashboard');
        Route::get('/health', [\App\Http\Controllers\SystemAdministrationController::class, 'health'])->name('health');

        Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [\App\Http\Controllers\AuditLogController::class, 'show'])->name('audit-logs.show');

        Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('/login-history', [\App\Http\Controllers\LoginHistoryController::class, 'index'])->name('login-history.index');
        Route::get('/failed-logins', [\App\Http\Controllers\LoginHistoryController::class, 'failedLogins'])->name('failed-logins.index');

        Route::get('/sessions', [\App\Http\Controllers\SessionController::class, 'index'])->name('sessions.index');
        Route::post('/sessions/force-logout-all', [\App\Http\Controllers\SessionController::class, 'forceLogoutAll'])->name('sessions.force-logout-all');
        Route::delete('/sessions/{sessionId}', [\App\Http\Controllers\SessionController::class, 'terminate'])->name('sessions.terminate');
        Route::post('/sessions/terminate-all/{userId}', [\App\Http\Controllers\SessionController::class, 'terminateAll'])->name('sessions.terminate-all');

        Route::get('/backups', [\App\Http\Controllers\BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/create', [\App\Http\Controllers\BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{backupRecord}/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{backupRecord}/restore', [\App\Http\Controllers\BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/backups/{backupRecord}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('backups.destroy');
        Route::post('/reset-application-data', [\App\Http\Controllers\BackupController::class, 'resetApplicationData'])->name('reset-application-data');

        Route::get('/settings', [\App\Http\Controllers\SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SystemSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/init-defaults', [\App\Http\Controllers\SystemSettingController::class, 'initDefaults'])->name('settings.init-defaults');

        Route::get('/branding', [SystemBrandingController::class, 'index'])->name('branding.index');
        Route::post('/branding', [SystemBrandingController::class, 'update'])->name('branding.update');
        Route::post('/branding/logo', [SystemBrandingController::class, 'uploadLogo'])->name('branding.upload-logo');
        Route::post('/branding/favicon', [SystemBrandingController::class, 'uploadFavicon'])->name('branding.upload-favicon');

        Route::get('/password-security', [\App\Http\Controllers\PasswordSecurityController::class, 'index'])->name('password-security.index');
        Route::put('/password-security', [\App\Http\Controllers\PasswordSecurityController::class, 'updateSettings'])->name('password-security.update');
        Route::post('/password-security/unlock/{user}', [\App\Http\Controllers\PasswordSecurityController::class, 'unlock'])->name('password-security.unlock');
    });

    // ====== ABOUT SYSTEM (All Authenticated Roles) ======
    Route::get('/about', [\App\Http\Controllers\SystemAdministrationController::class, 'about'])->name('about');

    // ====== NOTIFICATIONS (All Authenticated Roles) ======
    Route::prefix('notifications')->name('notifications.')->group(function (): void {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'fetchUnread'])->name('unread');
        Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });
});

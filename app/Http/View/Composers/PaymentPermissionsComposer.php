<?php

namespace App\Http\View\Composers;

use App\Services\PaymentPermissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentPermissionsComposer
{
    public function __construct(
        private PaymentPermissionService $permissionService
    ) {}

    public function compose(View $view): void
    {
        $user = Auth::user();

        if (!$user) {
            $view->with('paymentPermissions', [
                'can_view_payments' => false,
                'can_record_payment' => false,
                'can_generate_billings' => false,
                'can_manage_config' => false,
                'read_only_villages' => [],
                'payment_manager_village_ids' => [],
                'viewable_village_ids' => [],
            ]);
            return;
        }

        $view->with('paymentPermissions', [
            'can_view_payments' => $this->permissionService->canViewPayments($user),
            'can_record_payment_global' => !empty($this->permissionService->getPaymentManagerVillageIds($user)),
            'can_generate_billings_global' => !empty($this->permissionService->getPaymentManagerVillageIds($user)),
            'can_manage_config' => $this->permissionService->canManagePaymentConfig($user),
            'payment_manager_village_ids' => $this->permissionService->getPaymentManagerVillageIds($user),
            'viewable_village_ids' => $this->permissionService->getViewableVillageIds($user),
            'user_role' => $user->role?->role_slug,
            'user_village_id' => $user->village_id,
        ]);
    }
}

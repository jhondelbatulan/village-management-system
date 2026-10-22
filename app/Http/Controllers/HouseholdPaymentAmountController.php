<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdPaymentAmount;
use App\Models\PaymentType;
use App\Models\Village;
use App\Services\AuditLogService;
use App\Services\PaymentPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HouseholdPaymentAmountController extends Controller
{
    public function __construct(
        private PaymentPermissionService $permissionService
    ) {}

    public function index(Village $village): View
    {
        $this->permissionService->authorizePaymentConfig(auth()->user(), $village->id);

        $households = Household::where('village_id', $village->id)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->with(['paymentAmounts.paymentType'])
            ->orderBy('block_number')
            ->orderBy('lot_number')
            ->get();

        $paymentTypes = PaymentType::where('village_id', $village->id)
            ->where('is_active', true)
            ->get();

        return view('payments.household-amounts.index', compact('village', 'households', 'paymentTypes'));
    }

    public function edit(Village $village, Household $household): View
    {
        $this->permissionService->authorizePaymentConfig(auth()->user(), $village->id);

        if ($household->village_id !== $village->id) {
            abort(404);
        }

        $paymentTypes = PaymentType::where('village_id', $village->id)
            ->where('is_active', true)
            ->get();

        $existingAmounts = HouseholdPaymentAmount::where('village_id', $village->id)
            ->where('household_id', $household->id)
            ->get()
            ->keyBy('payment_type_id');

        return view('payments.household-amounts.edit', compact('village', 'household', 'paymentTypes', 'existingAmounts'));
    }

    public function update(Request $request, Village $village, Household $household): RedirectResponse
    {
        $this->permissionService->authorizePaymentConfig(auth()->user(), $village->id);

        if ($household->village_id !== $village->id) {
            abort(404);
        }

        $request->validate([
            'amounts' => 'required|array',
            'amounts.*.payment_type_id' => 'required|exists:payment_types,id',
            'amounts.*.custom_amount' => 'required|numeric|min:0',
        ]);

        foreach ($request->amounts as $item) {
            if ((float) $item['custom_amount'] > 0) {
                HouseholdPaymentAmount::updateOrCreate(
                    [
                        'village_id' => $village->id,
                        'household_id' => $household->id,
                        'payment_type_id' => $item['payment_type_id'],
                    ],
                    ['custom_amount' => $item['custom_amount']]
                );
            } else {
                HouseholdPaymentAmount::where('village_id', $village->id)
                    ->where('household_id', $household->id)
                    ->where('payment_type_id', $item['payment_type_id'])
                    ->delete();
            }
        }

        AuditLogService::log(
            'Update Household Payment Amounts',
            'Payment Configuration',
            "Updated custom amounts for household {$household->household_number} in {$village->village_name}"
        );

        return redirect()->route('household-amounts.index', $village)
            ->with('success', "Payment amounts updated for {$household->household_number}.");
    }

    public function destroy(Village $village, Household $household, HouseholdPaymentAmount $amount): RedirectResponse
    {
        $this->permissionService->authorizePaymentConfig(auth()->user(), $village->id);

        if ($amount->village_id !== $village->id || $amount->household_id !== $household->id) {
            abort(404);
        }

        $amount->delete();

        AuditLogService::log(
            'Delete Household Payment Amount',
            'Payment Configuration',
            "Deleted custom amount for household {$household->household_number} in {$village->village_name}"
        );

        return redirect()->route('household-amounts.edit', [$village, $household])
            ->with('success', 'Custom amount removed.');
    }
}

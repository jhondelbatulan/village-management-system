<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest\StoreVillagePaymentSettingRequest;
use App\Models\PaymentConfiguration;
use App\Models\PaymentType;
use App\Models\Village;
use App\Services\AuditLogService;
use App\Services\PaymentPermissionService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VillagePaymentConfigController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private PaymentPermissionService $permissionService
    ) {}

    public function index(): View
    {
        $this->permissionService->authorizePaymentConfig(auth()->user());

        $settings = PaymentConfiguration::with('village')->get();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        return view('payments.config.index', compact('settings', 'villages'));
    }

    public function show(Village $village): View
    {
        $this->permissionService->authorizePaymentConfig(auth()->user());

        $setting = PaymentConfiguration::where('village_id', $village->id)->first();
        $paymentTypes = PaymentType::where('village_id', $village->id)->get();

        return view('payments.config.show', compact('village', 'setting', 'paymentTypes'));
    }

    public function edit(Village $village): View
    {
        $this->permissionService->authorizePaymentConfig(auth()->user());

        $setting = PaymentConfiguration::where('village_id', $village->id)->first();
        $paymentTypes = PaymentType::where('village_id', $village->id)->get();

        return view('payments.config.edit', compact('village', 'setting', 'paymentTypes'));
    }

    public function update(StoreVillagePaymentSettingRequest $request, Village $village): RedirectResponse
    {
        $this->permissionService->authorizePaymentConfig(auth()->user());

        $validated = $request->validated();

        $data = [
            'village_id' => $village->id,
            'payment_enabled' => $request->input('payment_enabled') ? true : false,
            'payment_manager_role' => $validated['payment_manager_role'] ?? null,
            'penalty_rate' => $validated['penalty_rate'] ?? 0,
            'grace_period_days' => $validated['grace_period_days'] ?? 0,
            'billing_prefix' => $validated['billing_prefix'] ?? 'BILL',
            'receipt_prefix' => $validated['receipt_prefix'] ?? 'RCPT',
            'permit_prefix' => $validated['permit_prefix'] ?? 'PERMIT',
            'payment_notes' => $validated['payment_notes'] ?? null,
            'monthly_due_day' => $validated['monthly_due_day'] ?? null,
            'annual_due_month' => $validated['annual_due_month'] ?? null,
            'annual_due_day' => $validated['annual_due_day'] ?? null,
        ];

        try {
            PaymentConfiguration::updateOrCreate(
                ['village_id' => $village->id],
                $data
            );

            AuditLogService::log(
                'Update Village Payment Config',
                'Payment Configuration',
                "Updated payment settings for {$village->village_name}"
            );

            return redirect()->route('payment-config.show', $village)
                ->with('success', 'Village payment configuration updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save configuration: ' . $e->getMessage());
        }
    }

    public function storePaymentType(Request $request): RedirectResponse
    {
        $this->permissionService->authorizePaymentConfig(auth()->user());

        $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'payment_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'frequency' => ['required', 'in:monthly,annual,one_time'],
            'default_amount' => ['required', 'numeric', 'min:0'],
        ]);

        PaymentType::create([
            'village_id' => $request->village_id,
            'payment_name' => $request->payment_name,
            'description' => $request->description,
            'frequency' => $request->frequency,
            'default_amount' => $request->default_amount,
            'is_active' => true,
        ]);

        AuditLogService::log(
            'Create Payment Type',
            'Payment Configuration',
            "Created payment type: {$request->payment_name}"
        );

        return redirect()->route('payment-config.show', $request->village_id)
            ->with('success', 'Payment type added successfully.');
    }

    public function destroyPaymentType(PaymentType $paymentType): RedirectResponse
    {
        $this->permissionService->authorizePaymentConfig(auth()->user());

        $villageId = $paymentType->village_id;
        $name = $paymentType->payment_name;

        $paymentType->update(['is_active' => false]);

        AuditLogService::log(
            'Deactivate Payment Type',
            'Payment Configuration',
            "Deactivated payment type: {$name}"
        );

        return redirect()->route('payment-config.edit', $villageId)
            ->with('success', "Payment type \"{$name}\" has been deactivated.");
    }

    public function updatePaymentType(Request $request, PaymentType $paymentType): RedirectResponse
    {
        $this->permissionService->authorizePaymentConfig(auth()->user());

        $request->validate([
            'payment_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'frequency' => ['required', 'in:monthly,annual,one_time'],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $villageId = $paymentType->village_id;

        $paymentType->update([
            'payment_name' => $request->payment_name,
            'description' => $request->description,
            'frequency' => $request->frequency,
            'default_amount' => $request->default_amount,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogService::log(
            'Update Payment Type',
            'Payment Configuration',
            "Updated payment type: {$request->payment_name}"
        );

        return redirect()->route('payment-config.edit', $villageId)
            ->with('success', 'Payment type updated successfully.');
    }

    public function storePaymentTypeFromEdit(Request $request): RedirectResponse
    {
        $this->permissionService->authorizePaymentConfig(auth()->user());

        $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'payment_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'frequency' => ['required', 'in:monthly,annual,one_time'],
            'default_amount' => ['required', 'numeric', 'min:0'],
        ]);

        PaymentType::create([
            'village_id' => $request->village_id,
            'payment_name' => $request->payment_name,
            'description' => $request->description,
            'frequency' => $request->frequency,
            'default_amount' => $request->default_amount,
            'is_active' => true,
        ]);

        AuditLogService::log(
            'Create Payment Type',
            'Payment Configuration',
            "Created payment type: {$request->payment_name}"
        );

        return redirect()->route('payment-config.edit', $request->village_id)
            ->with('success', 'Payment type added successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\PaymentType;
use App\Models\Village;
use App\Services\PaymentPermissionService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private PaymentPermissionService $permissionService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = HouseholdPayment::with(['billing', 'household', 'village', 'paymentType', 'receivedByUser']);

        if ($user->role?->role_slug === 'treasurer') {
            $query->where('village_id', $user->village_id);
        } elseif ($user->role?->role_slug === 'secretary') {
            $query->where('village_id', $user->village_id);
        } elseif ($user->role?->role_slug === 'admin') {
            $query->where('village_id', $user->village_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('billing', function ($q2) use ($search) {
                      $q2->where('billing_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('household', function ($q2) use ($search) {
                      $q2->where('household_number', 'like', "%{$search}%")
                         ->orWhere('household_head_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->village_id) {
            $query->where('village_id', $request->village_id);
        }

        if ($request->payment_type_id) {
            $query->where('payment_type_id', $request->payment_type_id);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->month && $request->year) {
            $query->whereMonth('payment_date', $request->month)
                  ->whereYear('payment_date', $request->year);
        } elseif ($request->year) {
            $query->whereYear('payment_date', $request->year);
        }

        $payments = $query->latest()->paginate(15)->withQueryString();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $paymentTypes = PaymentType::where('is_active', true)->orderBy('payment_name')->get();

        $canRecordPayment = !empty($this->permissionService->getPaymentManagerVillageIds($user));

        return view('payments.index', compact('payments', 'villages', 'paymentTypes', 'canRecordPayment'));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();

        $managerVillageIds = $this->permissionService->getPaymentManagerVillageIds($user);
        if (empty($managerVillageIds)) {
            abort(403, 'You are not authorized to record payments.');
        }

        $billings = Billing::with(['household', 'paymentType', 'village', 'payments'])
            ->whereIn('status', ['pending', 'overdue', 'partially_paid'])
            ->whereIn('village_id', $managerVillageIds)
            ->orderBy('due_date')
            ->get();

        $billings = $billings->filter(function ($billing) {
            $totalPaid = (float) $billing->payments->sum('total_paid');
            return $totalPaid < (float) $billing->total_amount;
        });

        return view('payments.create', compact('billings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'billing_id' => 'required|exists:household_billings,id',
            'amount' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,gcash,maya,bank_transfer',
            'reference_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500',
        ], [
            'billing_id.required' => 'Please select a billing record.',
            'billing_id.exists' => 'The selected billing record does not exist.',
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be at least ₱0.01.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
        ]);

        $billing = Billing::findOrFail($request->billing_id);
        $this->permissionService->authorizeRecordPayment($user, $billing->village_id);

        try {
            $this->paymentService->recordPayment($request->only([
                'billing_id',
                'amount',
                'discount',
                'payment_method',
                'reference_number',
                'remarks',
            ]));

            return redirect()->route('billings.show', $billing)
                ->with('success', 'Payment recorded successfully. Receipt has been generated.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(HouseholdPayment $payment): View
    {
        $user = auth()->user();

        if (!$this->permissionService->canViewPayment($user, $payment)) {
            abort(403, 'You are not authorized to view this payment.');
        }

        $payment->load(['billing', 'household', 'village', 'paymentType', 'receivedByUser', 'receipt']);

        $totalPaidOnBilling = (float) $payment->billing->payments()
            ->where('id', '<=', $payment->id)
            ->sum('total_paid');

        $remainingBalance = max(0, (float) $payment->billing->total_amount - (float) $payment->billing->payments()
            ->sum('total_paid'));

        $canPrintReceipt = $this->permissionService->canPrintReceipt($user, $payment);

        return view('payments.show', compact('payment', 'canPrintReceipt', 'remainingBalance'));
    }

    public function receipt(HouseholdPayment $payment): View
    {
        $user = auth()->user();

        if (!$this->permissionService->canViewPayment($user, $payment)) {
            abort(403, 'You are not authorized to view this receipt.');
        }

        $payment->load(['billing', 'household.village', 'paymentType', 'receivedByUser']);

        $remainingBalance = max(0, (float) $payment->billing->total_amount - (float) $payment->billing->payments()
            ->sum('total_paid'));

        return view('payments.receipt', compact('payment', 'remainingBalance'));
    }

    public function householdPayments(Household $household): View
    {
        $user = auth()->user();

        if ($user->role?->role_slug === 'resident' && $household->id !== $user->household_id) {
            abort(403, 'You are not authorized to view payments for this household.');
        }

        $billings = $this->paymentService->getHouseholdBillings($household->id);
        $payments = $this->paymentService->getHouseholdPayments($household->id);
        $balance = $this->paymentService->getHouseholdBalance($household->id);

        return view('payments.household', compact('household', 'billings', 'payments', 'balance'));
    }
}

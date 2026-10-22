<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\GcashConfig;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\OnlinePayment;
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

        // Approved online payment requests prefill the record-payment form
        // with the resident's submitted details.
        $onlinePayment = null;
        if ($request->filled('online_payment')) {
            $onlinePayment = OnlinePayment::find($request->input('online_payment'));

            if ($onlinePayment) {
                abort_unless(
                    in_array($onlinePayment->village_id, $managerVillageIds),
                    403,
                    'This online payment belongs to another village.'
                );
                abort_unless(
                    $onlinePayment->status === 'approved',
                    403,
                    'This online payment request has not been approved yet.'
                );
                abort_unless($onlinePayment->payment_id === null, 403, 'This online payment has already been recorded.');
            }
        }

        return view('payments.create', compact('billings', 'onlinePayment'));
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
            'online_payment_id' => 'nullable|exists:online_payments,id',
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

        // When recording an approved online (GCash) payment, the request must
        // belong to this billing and must not have been recorded already.
        $onlinePayment = null;
        if ($request->filled('online_payment_id')) {
            $onlinePayment = OnlinePayment::findOrFail($request->input('online_payment_id'));

            if ($onlinePayment->status !== 'approved') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This online payment request has not been approved yet.');
            }

            if ($onlinePayment->billing_id !== $billing->id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'The online payment request does not match the selected billing.');
            }

            if ($onlinePayment->payment_id !== null) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This online payment has already been recorded.');
            }

            if (abs((float) $request->input('amount') - (float) $onlinePayment->amount) > 0.01) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'The recorded amount must match the approved online payment amount of ₱'
                        . number_format($onlinePayment->amount, 2) . '.');
            }
        }

        try {
            $payment = $this->paymentService->recordPayment($request->only([
                'billing_id',
                'amount',
                'discount',
                'payment_method',
                'reference_number',
                'remarks',
            ]));

            if ($onlinePayment) {
                $onlinePayment->update(['payment_id' => $payment->id]);
            }

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

        $gcashConfig = GcashConfig::where('village_id', $household->village_id)->first();
        $onlinePayments = OnlinePayment::with('billing')
            ->where('household_id', $household->id)
            ->latest()
            ->get();

        $activeRequestBillingIds = OnlinePayment::whereIn('billing_id', $billings->pluck('id'))
            ->where(function ($query): void {
                $query->where('status', 'pending')
                    ->orWhere(function ($q): void {
                        $q->where('status', 'approved')->whereNull('payment_id');
                    });
            })
            ->pluck('billing_id')
            ->all();

        $canPayOnline = $user->role?->role_slug === 'resident'
            && (int) $user->household_id === (int) $household->id;

        return view('payments.household', compact(
            'household',
            'billings',
            'payments',
            'balance',
            'gcashConfig',
            'onlinePayments',
            'activeRequestBillingIds',
            'canPayOnline'
        ));
    }
}

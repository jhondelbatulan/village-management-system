<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\GcashConfig;
use App\Models\OnlinePayment;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OnlinePaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Resident side — submit a GCash payment request for a bill
    |--------------------------------------------------------------------------
    */

    public function create(Billing $billing): View|RedirectResponse
    {
        $user = auth()->user();

        abort_unless($user->role?->role_slug === 'resident', 403, 'Only residents can pay bills online.');
        abort_unless((int) $billing->household_id === (int) $user->household_id, 403, 'You are not authorized to pay this billing.');

        if ($redirect = $this->blockInactiveBilling($billing)) {
            return $redirect;
        }

        $gcashConfig = GcashConfig::where('village_id', $billing->village_id)->first();

        if (! $gcashConfig?->hasCredentials()) {
            return redirect()->route('billings.show', $billing)
                ->with('error', 'GCash payment is not yet configured for this village. Please contact your treasurer.');
        }

        $balance = $this->billingBalance($billing);

        return view('billings.pay-online', compact('billing', 'gcashConfig', 'balance'));
    }

    public function store(Request $request, Billing $billing): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($user->role?->role_slug === 'resident', 403, 'Only residents can pay bills online.');
        abort_unless((int) $billing->household_id === (int) $user->household_id, 403, 'You are not authorized to pay this billing.');

        if ($redirect = $this->blockInactiveBilling($billing)) {
            return $redirect;
        }

        $gcashConfig = GcashConfig::where('village_id', $billing->village_id)->first();

        if (! $gcashConfig?->hasCredentials()) {
            return redirect()->route('billings.show', $billing)
                ->with('error', 'GCash payment is not yet configured for this village. Please contact your treasurer.');
        }

        $balance = $this->billingBalance($billing);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $balance],
            'transaction_number' => ['required', 'string', 'max:100'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a number.',
            'amount.min' => 'Payment amount must be at least ₱0.01.',
            'amount.max' => 'Payment amount must not exceed the remaining balance of ₱' . number_format($balance, 2) . '.',
            'transaction_number.required' => 'GCash transaction number is required.',
            'transaction_number.max' => 'Transaction number must not exceed 100 characters.',
            'proof.required' => 'Please attach a screenshot of your GCash payment.',
            'proof.image' => 'The proof must be an image file.',
            'proof.mimes' => 'Only JPG, PNG, and WebP images are allowed.',
            'proof.max' => 'Proof screenshot size must not exceed 2MB.',
        ]);

        $file = $request->file('proof');
        $filename = 'gcash_' . time() . '_' . $user->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('payment_proofs', $filename, 'public');

        $onlinePayment = OnlinePayment::create([
            'village_id' => $billing->village_id,
            'billing_id' => $billing->id,
            'household_id' => $billing->household_id,
            'resident_id' => $user->id,
            'amount' => $request->input('amount'),
            'transaction_number' => $request->input('transaction_number'),
            'proof_path' => 'payment_proofs/' . $filename,
            'status' => 'pending',
        ]);

        AuditLogService::log(
            'Submit Online Payment',
            'Payment Management',
            'Submitted GCash payment of ₱' . number_format($onlinePayment->amount, 2)
                . ' (txn ' . $onlinePayment->transaction_number . ') for billing ' . $billing->billing_number
        );

        $notifications = app(NotificationService::class);
        $requestMessage = 'A GCash payment of ₱' . number_format($onlinePayment->amount, 2)
            . ' was submitted for ' . $billing->billing_number
            . ' (txn ' . $onlinePayment->transaction_number . '). Awaiting your approval.';
        $notifications->notifyTreasurers(
            $billing->village_id,
            'payment',
            'Online Payment Request',
            $requestMessage,
            route('online-payments.show', $onlinePayment)
        );
        $notifications->notifyUser(
            $onlinePayment->resident_id,
            'payment',
            'GCash Request Submitted',
            'Your GCash payment request for ' . $billing->billing_number . ' was submitted and is awaiting treasurer approval.',
            route('billings.show', $billing)
        );

        return redirect()->route('billings.show', $billing)
            ->with('success', 'Your GCash payment has been submitted and is now awaiting treasurer approval.');
    }

    /*
    |--------------------------------------------------------------------------
    | Treasurer side — review, approve or decline requests
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $user = auth()->user();
        $this->authorizeTreasurerOfVillage($user);

        $query = OnlinePayment::with(['billing', 'household', 'resident'])
            ->where('village_id', $user->village_id);

        if (in_array($request->query('status'), OnlinePayment::STATUSES, true)) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search): void {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('billing', fn ($q2) => $q2->where('billing_number', 'like', "%{$search}%"))
                    ->orWhereHas('household', function ($q2) use ($search): void {
                        $q2->where('household_number', 'like', "%{$search}%")
                            ->orWhere('household_head_name', 'like', "%{$search}%");
                    });
            });
        }

        $onlinePayments = $query->latest()->paginate(15)->withQueryString();
        $pendingCount = OnlinePayment::where('village_id', $user->village_id)->where('status', 'pending')->count();

        return view('online-payments.index', compact('onlinePayments', 'pendingCount'));
    }

    public function show(OnlinePayment $onlinePayment): View
    {
        $this->authorizeTreasurerOfVillage(auth()->user(), $onlinePayment);

        $onlinePayment->load(['billing.paymentType', 'household', 'resident', 'reviewer', 'payment']);
        $currentBalance = max(
            0,
            (float) ($onlinePayment->billing?->total_amount ?? 0)
                - (float) ($onlinePayment->billing?->payments()->sum('total_paid') ?? 0)
        );

        return view('online-payments.show', compact('onlinePayment', 'currentBalance'));
    }

    public function approve(OnlinePayment $onlinePayment): RedirectResponse
    {
        $this->authorizeTreasurerOfVillage(auth()->user(), $onlinePayment);

        if ($onlinePayment->status !== 'pending' || $onlinePayment->payment_id !== null) {
            return redirect()->route('online-payments.show', $onlinePayment)
                ->with('error', 'This online payment request has already been reviewed.');
        }

        try {
            $payment = DB::transaction(function () use ($onlinePayment): \App\Models\HouseholdPayment {
                $request = OnlinePayment::query()->lockForUpdate()->findOrFail($onlinePayment->id);

                if ($request->status !== 'pending' || $request->payment_id !== null) {
                    throw new \RuntimeException('This online payment request has already been reviewed.');
                }

                $billing = Billing::query()->lockForUpdate()->findOrFail($request->billing_id);
                abort_unless(
                    (int) $billing->household_id === (int) $request->household_id
                        && (int) $billing->village_id === (int) $request->village_id,
                    422,
                    'This payment request no longer matches its billing.'
                );

                $payment = app(PaymentService::class)->recordPayment([
                    'billing_id' => $billing->id,
                    'amount' => (float) $request->amount,
                    'payment_method' => 'gcash',
                    'reference_number' => $request->transaction_number,
                    'remarks' => 'Approved GCash request #' . $request->id,
                ], false);

                $request->update([
                    'status' => 'approved',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'payment_id' => $payment->id,
                ]);

                return $payment;
            });
        } catch (\RuntimeException $exception) {
            return redirect()->route('online-payments.show', $onlinePayment)
                ->with('error', $exception->getMessage());
        } catch (\Exception $exception) {
            return redirect()->route('online-payments.show', $onlinePayment)
                ->with('error', $exception->getMessage());
        }

        AuditLogService::log(
            'Approve Online Payment',
            'Payment Management',
            'Approved GCash payment ' . $onlinePayment->transaction_number
                . ' and recorded receipt ' . $payment->receipt_number
                . ' for billing ' . $onlinePayment->billing?->billing_number
        );

        app(NotificationService::class)->notifyUser(
            $onlinePayment->resident_id,
            'payment',
            'Online Payment Approved',
            'Your GCash payment of ₱' . number_format($onlinePayment->amount, 2)
                . ' for ' . $onlinePayment->billing?->billing_number
                . ' has been approved and recorded.',
            route('payments.show', $payment)
        );

        return redirect()->route('online-payments.show', $onlinePayment)
            ->with('success', 'Online payment approved and recorded.');
    }

    public function decline(Request $request, OnlinePayment $onlinePayment): RedirectResponse
    {
        $this->authorizeTreasurerOfVillage(auth()->user(), $onlinePayment);

        if ($onlinePayment->status !== 'pending') {
            return redirect()->route('online-payments.show', $onlinePayment)
                ->with('error', 'This online payment request has already been reviewed.');
        }

        $data = $request->validate([
            'declined_reason' => ['required', 'string', 'max:500'],
        ], [
            'declined_reason.required' => 'A reason is required to decline this payment request.',
            'declined_reason.max' => 'Reason must not exceed 500 characters.',
        ]);

        $onlinePayment->update([
            'status' => 'declined',
            'declined_reason' => $data['declined_reason'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLogService::log(
            'Decline Online Payment',
            'Payment Management',
            'Declined GCash payment ' . $onlinePayment->transaction_number
                . ' for billing ' . $onlinePayment->billing?->billing_number
                . ($onlinePayment->declined_reason ? ' — ' . $onlinePayment->declined_reason : '')
        );

        $message = 'Your GCash payment of ₱' . number_format($onlinePayment->amount, 2)
            . ' for ' . $onlinePayment->billing?->billing_number . ' was declined.';
        if ($onlinePayment->declined_reason) {
            $message .= ' Reason: ' . $onlinePayment->declined_reason;
        }

        app(NotificationService::class)->notifyUser(
            $onlinePayment->resident_id,
            'payment',
            'Online Payment Declined',
            $message,
            route('billings.show', $onlinePayment->billing_id)
        );

        return redirect()->route('online-payments.index')
            ->with('success', 'Online payment request declined. The resident has been notified.');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function authorizeTreasurerOfVillage($user, ?OnlinePayment $onlinePayment = null): void
    {
        abort_unless($user?->role?->role_slug === 'treasurer', 403, 'Only treasurers can manage online payments.');
        abort_unless($user->village_id, 403, 'Your account is not linked to a village.');

        if ($onlinePayment) {
            abort_unless(
                (int) $onlinePayment->village_id === (int) $user->village_id,
                403,
                'This online payment belongs to another village.'
            );
        }
    }

    private function blockInactiveBilling(Billing $billing): ?RedirectResponse
    {
        if (OnlinePayment::hasActiveFor($billing->id)) {
            return redirect()->route('billings.show', $billing)
                ->with('error', 'You already have an online payment request awaiting approval for this billing.');
        }

        if ($this->billingBalance($billing) <= 0) {
            return redirect()->route('billings.show', $billing)
                ->with('error', 'This billing is already fully paid.');
        }

        return null;
    }

    private function billingBalance(Billing $billing): float
    {
        $totalPaid = (float) $billing->payments()->sum('total_paid');

        return round((float) $billing->total_amount - $totalPaid, 2);
    }
}

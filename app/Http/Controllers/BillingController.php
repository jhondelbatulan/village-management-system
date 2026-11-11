<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\OnlinePayment;
use App\Models\PaymentType;
use App\Models\Village;
use App\Services\PaymentPermissionService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private PaymentPermissionService $permissionService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Billing::with(['household', 'village', 'paymentType', 'creator']);

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
                $q->where('billing_number', 'like', "%{$search}%")
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

        if ($request->status) {
            if ($request->status === 'unpaid') {
                $query->whereIn('status', ['pending', 'partially_paid'])->where('due_date', '>=', now()->toDateString());
            } elseif ($request->status === 'fully_paid') {
                $query->where('status', 'paid');
            } elseif ($request->status === 'overdue') {
                $query->where(function ($q): void {
                    $q->where('status', 'overdue')
                        ->orWhere(function ($pending): void {
                            $pending->whereIn('status', ['pending', 'partially_paid'])
                                ->where('due_date', '<', now()->toDateString());
                        });
                });
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->billing_period) {
            $query->where('billing_period', $request->billing_period);
        }

        $billings = $query->latest()->paginate(15)->withQueryString();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $paymentTypes = PaymentType::where('is_active', true)->orderBy('payment_name')->get();

        $canGenerate = $this->permissionService->canGenerateBillings($user);

        return view('billings.index', compact('billings', 'villages', 'paymentTypes', 'canGenerate'));
    }

    public function show(Billing $billing): View
    {
        $user = auth()->user();

        $billing->load([
            'household',
            'village',
            'paymentType',
            'creator',
            'payments.receivedByUser',
            'penalties.imposedByUser',
            'receipts',
        ]);

        $totalPaid = (float) $billing->payments()
            ->sum('total_paid');

        $balance = $billing->total_amount - $totalPaid;

        $canRecordPayment = $this->permissionService->canRecordPayment($user, $billing->village_id);
        $isReadOnly = $this->permissionService->isReadOnlyVillage($user, $billing->village_id);

        $activeOnlinePayment = OnlinePayment::where('billing_id', $billing->id)
            ->where(function ($query): void {
                $query->where('status', 'pending')
                    ->orWhere(function ($q): void {
                        $q->where('status', 'approved')->whereNull('payment_id');
                    });
            })
            ->latest()
            ->first();

        $canPayOnline = $user->role?->role_slug === 'resident'
            && (int) $user->household_id === (int) $billing->household_id
            && $balance > 0
            && $activeOnlinePayment === null;

        return view('billings.show', compact(
            'billing',
            'totalPaid',
            'balance',
            'canRecordPayment',
            'isReadOnly',
            'canPayOnline',
            'activeOnlinePayment'
        ));
    }

    public function generateForm(): View
    {
        $user = auth()->user();

        $managerVillageIds = $this->permissionService->getPaymentManagerVillageIds($user);
        if (empty($managerVillageIds)) {
            abort(403, 'You are not authorized to generate billings.');
        }

        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $villages = $villages->filter(fn($v) => in_array($v->id, $managerVillageIds));

        return view('billings.generate', compact('villages'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'village_id' => 'required|exists:villages,id',
            'billing_period' => 'required|string',
            'payment_type_ids' => 'nullable|array',
            'payment_type_ids.*' => 'exists:payment_types,id',
        ]);

        $this->permissionService->authorizeGenerateBillings($user, $request->village_id);

        try {
            $result = $this->paymentService->generateBillings(
                $request->village_id,
                $request->billing_period,
                $request->payment_type_ids
            );

            return redirect()->route('billings.index')
                ->with('success', "Generated {$result['created']} billings. {$result['skipped']} duplicates skipped.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}

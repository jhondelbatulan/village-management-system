# Appendix — Selected Source Code

Source code excerpts from the **Web-Based GIS-Enabled Multi-Village Management System** (Laravel 12), taken verbatim from the `main` branch. Prepared for the manuscript appendices.

| # | Topic | Files |
|---|---|---|
| A | RBAC + village scoping | `app/Http/Middleware/RoleMiddleware.php`, `routes/web.php`, `HouseholdController::index()` |
| B | Approval workflow | `HouseholdController::submit()`, `ApprovalController::approveHousehold()`, `PendingChangeController::approve()`, `PendingChangeService` |
| C | Billing generation | `PaymentService::generateBillings()`, `calculateDueDate()`, `recordPenalty()`, `updateBillingStatus()` |
| D | Payments + GCash | `PaymentService::recordPayment()`, `OnlinePaymentController::store/approve/decline()` |
| E | Behavior scoring | `app/Services/Analytics/PaymentBehaviorService.php` |
| F | GIS map | `GisController::index()`, `GisService`, `resources/views/gis/index.blade.php` |
| G | Schema | `create_households_table`, `restructure_payment_tables`, `add_payment_behavior_to_households` |
| H | Reports | `ReportController::generate/exportPdf/exportCsv` |

> Lines marked with `// ...` are elided for length; all other code is verbatim.

---

## A. Role and Village Access Control

### A.1 `app/Http/Middleware/RoleMiddleware.php` (complete)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (! $user) {
            $this->clearSession($request);

            return redirect()->route('login');
        }

        if ($user->status !== 'active') {
            $this->clearSession($request);

            return redirect()->route('login')->withErrors([
                'username' => 'Your account has been deactivated. Please contact an administrator.',
            ]);
        }

        $userRole = $user->role?->role_slug;

        if (! in_array($userRole, $roles)) {
            abort(403, 'Unauthorized access. You do not have permission to view this page.');
        }

        return $next($request);
    }

    /**
     * Drop the whole session (not just the user id) so a stale cookie cannot
     * keep the browser bouncing back into a broken login state.
     */
    private function clearSession(Request $request): void
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
```

### A.2 Route groups using the middleware — `routes/web.php` (lines 174–202)

```php
/*
| Approvals — Two-Stage Workflow: Secretary → Admin → Super Admin
| Secretary: creates/submits household & resident records for review
| Admin: reviews pending submissions from their village (Stage 1)
| Super Admin: final approval of admin-approved records (Stage 2)
*/
Route::middleware('role:super_admin,admin,secretary')->prefix('approvals')->name('approvals.')->group(function (): void {
    Route::get('/households', [ApprovalController::class, 'households'])->name('households');
    Route::post('/households/{household}/approve', [ApprovalController::class, 'approveHousehold'])->name('approve-household');
    Route::post('/households/{household}/reject', [ApprovalController::class, 'rejectHousehold'])->name('reject-household');
    Route::get('/residents', [ApprovalController::class, 'residents'])->name('residents');
    Route::post('/residents/{resident}/approve', [ApprovalController::class, 'approveResident'])->name('approve-resident')->middleware('role:super_admin,admin');
    Route::post('/residents/{resident}/reject', [ApprovalController::class, 'rejectResident'])->name('reject-resident')->middleware('role:super_admin,admin');
});

/*
| Update & Delete Request Approvals — staged household/resident changes
| Resident/secretary edits and deletes are queued, never saved directly.
| Admin: stage 1 review of requests from their village
| Super Admin: final approval — the only step that saves/deletes
*/
Route::middleware('role:super_admin,admin')->prefix('approvals')->name('approvals.')->group(function (): void {
    Route::get('/updates', [PendingChangeController::class, 'updates'])->name('updates');
    Route::post('/updates/{change}/approve', [PendingChangeController::class, 'approve'])->name('approve-update');
    Route::post('/updates/{change}/reject', [PendingChangeController::class, 'reject'])->name('reject-update');
    Route::get('/deletes', [PendingChangeController::class, 'deletes'])->name('deletes');
    Route::post('/deletes/{change}/approve', [PendingChangeController::class, 'approve'])->name('approve-delete');
    Route::post('/deletes/{change}/reject', [PendingChangeController::class, 'reject'])->name('reject-delete');
});
```

### A.3 Village scoping — `app/Http/Controllers/HouseholdController.php::index()` (lines 27–37)

```php
public function index(): View
{
    $user = auth()->user();
    $query = Household::with('village', 'householdHead', 'submittedBy', 'approvedBy', 'adminApprovedBy');

    // Only households finally approved by the super admin are part of the official list.
    $query->where('approval_status', 'approved');

    if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
        $query->where('village_id', $user->village_id);
    }
    // ... filters/search follow
```

---

## B. Approval Workflow

### B.1 Submitting a record — `app/Http/Controllers/HouseholdController.php::submit()` (lines 439–465)

```php
public function submit(Household $household): RedirectResponse
{
    if (! in_array($household->approval_status, ['draft', 'rejected'])) {
        return back()->with('error', 'Only draft or rejected records can be submitted for approval.');
    }

    $data = [
        'approval_status' => 'pending',
        'submitted_by' => auth()->id(),
    ];

    if ($household->approval_status === 'rejected') {
        $data['approved_by'] = null;
        $data['approved_at'] = null;
        $data['admin_approved_by'] = null;
        $data['admin_approved_at'] = null;
        $data['remarks'] = null;
    }

    $household->update($data);

    AuditLogService::log('Submit Household', 'Household Management', "Submitted household for approval: {$household->household_number}");

    $this->notifyAdmins("New household submitted for approval ({$household->notification_label}).", 'approval', $household->village_id);

    return redirect()->route('households.show', $household)->with('success', 'Household submitted for approval successfully.');
}
```

### B.2 Admin review + Super Admin final approval — `app/Http/Controllers/ApprovalController.php::approveHousehold()` (lines 90–145)

```php
public function approveHousehold(Household $household): RedirectResponse
{
    return DB::transaction(function () use ($household): RedirectResponse {
        // Lock the row so only one approval action can be processed at a time.
        $household = Household::query()->lockForUpdate()->findOrFail($household->id);

        $user = auth()->user();
        $role = $user->role?->role_slug;

        // Stage 1 — Admin (President) reviews pending submissions from their village.
        if ($role === 'admin') {
            if ($household->village_id !== $user->village_id) {
                abort(403, 'You can only approve households in your village.');
            }

            if ($household->approval_status !== 'pending') {
                return back()->with('error', 'This household request has already been processed.');
            }

            $household->update([
                'approval_status' => 'admin_approved',
                'admin_approved_by' => auth()->id(),
                'admin_approved_at' => now(),
            ]);

            AuditLogService::log('Admin Approve Household', 'Approvals', "Admin approved household: {$household->household_number} (pending Super Admin final approval)");
            app(NotificationService::class)->notifySuperAdmins('Approval Update', "Household approved by Admin ({$household->notification_label}). Pending your final approval.", route('approvals.households'));
            app(NotificationService::class)->notifyUser($household->submitted_by, 'approval', 'Household Update', "Household approved by Admin ({$household->notification_label}). Awaiting Super Admin final approval.", route('households.show', $household));

            return redirect()->route('approvals.households')->with('success', "Household {$household->household_number} approved. Awaiting Super Admin final approval.");
        }

        // Stage 2 — Super Admin gives the final approval of admin-approved records only.
        if ($role === 'super_admin') {
            if ($household->approval_status !== 'admin_approved') {
                return back()->with('error', $household->approval_status === 'pending'
                    ? 'Only admin-approved households can be approved at this stage.'
                    : 'This household request has already been processed.');
            }

            $household->update([
                'approval_status' => 'approved',
                'status' => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            AuditLogService::log('Final Approve Household', 'Approvals', "Super Admin approved household: {$household->household_number}");
            app(NotificationService::class)->notifyUser($household->submitted_by, 'approval', 'Household Update', "Household has been approved by Super Admin ({$household->notification_label}).", route('households.show', $household));

            return redirect()->route('approvals.households')->with('success', "Household {$household->household_number} approved successfully.");
        }

        return back()->with('error', 'You cannot approve this household at this stage.');
    });
}
```

### B.3 Two-stage approval of staged updates/deletes — `app/Http/Controllers/PendingChangeController.php::approve()` (lines 32–133)

```php
public function approve(PendingChange $change): RedirectResponse
{
    return DB::transaction(function () use ($change): RedirectResponse {
        $change = PendingChange::query()->lockForUpdate()->findOrFail($change->id);

        $user = auth()->user();
        $role = $user?->role?->role_slug;
        $label = $change->target_label ?? ('#' . $change->target_id);
        $noun = $change->target_type === 'household' ? 'Household' : 'Resident';
        $actionNoun = $change->action === PendingChange::ACTION_DELETE ? 'Delete' : 'Update';
        $queue = $change->action === PendingChange::ACTION_DELETE
            ? route('approvals.deletes')
            : route('approvals.updates');

        // Stage 1 — Admin reviews requests from their village.
        if ($role === 'admin') {
            if ($change->village_id !== $user->village_id) {
                abort(403, 'You can only approve requests in your village.');
            }

            if ($change->approval_status !== 'pending') {
                return back()->with('error', 'This request has already been processed.');
            }

            $change->update([
                'approval_status' => 'admin_approved',
                'admin_approved_by' => $user->id,
                'admin_approved_at' => now(),
            ]);

            AuditLogService::log(
                'Admin Approve Change Request',
                'Approvals',
                "Admin approved {$actionNoun} request for {$noun} \"{$label}\" (pending Super Admin final approval)"
            );

            app(NotificationService::class)->notifySuperAdmins(
                'Approval Update',
                "{$actionNoun} request for {$noun} \"{$label}\" approved by Admin. Pending your final approval.",
                $queue
            );

            if ($change->submitted_by) {
                app(NotificationService::class)->notifyUser(
                    $change->submitted_by,
                    'approval',
                    "{$actionNoun} Request",
                    "Your {$actionNoun} request for {$noun} \"{$label}\" was approved by Admin. Awaiting Super Admin final approval.",
                    $queue
                );
            }

            return redirect()->back()->with('success', "{$actionNoun} request for \"{$label}\" approved. Awaiting Super Admin final approval.");
        }

        // Stage 2 — Super Admin final approval: the change is saved now.
        if ($role === 'super_admin') {
            if ($change->approval_status !== 'admin_approved') {
                return back()->with('error', $change->approval_status === 'pending'
                    ? 'Only admin-approved requests can be approved at this stage.'
                    : 'This request has already been processed.');
            }

            $target = $change->resolveTarget();
            if (! $target) {
                return back()->with('error', 'The record linked to this request no longer exists.');
            }

            $change->update([
                'approval_status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            app(PendingChangeService::class)->apply($change, $target);

            AuditLogService::log(
                'Final Approve Change Request',
                'Approvals',
                "Super Admin approved {$actionNoun} request for {$noun} \"{$label}\""
            );

            if ($change->submitted_by) {
                app(NotificationService::class)->notifyUser(
                    $change->submitted_by,
                    'approval',
                    "{$actionNoun} Request Approved",
                    $change->action === PendingChange::ACTION_DELETE
                        ? "Your delete request for {$noun} \"{$label}\" was approved. The record has been archived."
                        : "Your changes to {$noun} \"{$label}\" have been approved and saved.",
                    $queue
                );
            }

            return redirect()->back()->with('success', $change->action === PendingChange::ACTION_DELETE
                ? "\"{$label}\" has been deleted after final approval."
                : "Changes to \"{$label}\" have been saved after final approval.");
        }

        return back()->with('error', 'You cannot approve this request at this stage.');
    });
}
```

### B.4 Staging a change (nothing is written until final approval) — `app/Services/PendingChangeService.php` (lines 71–116, 127–190)

```php
private function stage(Model $target, string $targetType, string $action, array $payload): PendingChange
{
    $user = auth()->user();

    // Keep every submission immutable for audit. A newer proposal
    // supersedes prior open proposals from the same submitter.
    $openChanges = PendingChange::where('target_type', $targetType)
        ->where('target_id', $target->getKey())
        ->where('action', $action)
        ->where('submitted_by', $user?->id ?? 0)
        ->whereIn('approval_status', ['pending', 'admin_approved'])
        ->get();

    foreach ($openChanges as $openChange) {
        $this->discardStagedPicture($openChange);
        $openChange->update([
            'approval_status' => 'superseded',
            'remarks' => 'Superseded by a newer submission.',
        ]);
    }

    $change = PendingChange::create([
        'target_type' => $targetType,
        'target_id' => $target->getKey(),
        'target_label' => $this->labelFor($target, $targetType),
        'village_id' => $target->village_id,
        'action' => $action,
        'payload' => $action === PendingChange::ACTION_UPDATE ? $payload : [],
        'submitted_by' => $user?->id ?? 0,
        'submitter_role' => $user?->role?->role_slug,
        'approval_status' => 'pending',
    ]);

    // Stage 1: the village Admin reviews first.
    app(NotificationService::class)->notifyAdmins(
        $change->village_id,
        'Approval Required',
        $this->notificationMessage($change, $user?->full_name ?? 'A staff member'),
        $change->action === PendingChange::ACTION_DELETE
            ? route('approvals.deletes')
            : route('approvals.updates'),
        'warning'
    );

    return $change;
}

private function applyUpdate(Model $target, array $data): void
{
    if ($target instanceof Resident) {
        if ($target->approval_status === 'rejected') {
            $data = array_merge([
                'approval_status' => 'draft',
                'submitted_by' => null,
                'approved_by' => null,
                'approved_at' => null,
                'admin_approved_by' => null,
                'admin_approved_at' => null,
                'remarks' => null,
            ], $data);
        }
        // ... picture swap, village re-sync, then:
        $target->update($data);
        // ...
    }

    if ($target instanceof Household) {
        if ($target->approval_status === 'rejected') {
            $data = array_merge([
                'approval_status' => 'draft',
                'submitted_by' => null,
                'approved_by' => null,
                'approved_at' => null,
                'admin_approved_by' => null,
                'admin_approved_at' => null,
                'remarks' => null,
            ], $data);
        }

        $target->update($data);
        // ...
    }
}
```

---

## C. Billing Generation

### C.1 `app/Services/PaymentService.php::generateBillings()` (lines 36–139)

```php
public function generateBillings(int $villageId, string $billingPeriod, null|int|array $paymentTypeIds = null): array
{
    $config = $this->getVillageSetting($villageId);
    if (! $config || ! $config->payment_enabled) {
        throw new \Exception('Payment module is not enabled for this village.');
    }

    $types = match (true) {
        is_array($paymentTypeIds) => PaymentType::whereIn('id', $paymentTypeIds)
            ->where('village_id', $villageId)->get(),
        is_int($paymentTypeIds) || is_string($paymentTypeIds) => PaymentType::where('id', $paymentTypeIds)
            ->where('village_id', $villageId)->get(),
        default => $this->getActivePaymentTypes($villageId),
    };

    if ($types->isEmpty()) {
        throw new \Exception('No active payment types found for this village.');
    }

    $households = Household::where('village_id', $villageId)
        ->where('status', 'active')
        ->where('approval_status', 'approved')
        ->get();

    $created = 0;
    $skipped = 0;
    $billedTotals = [];
    $billedDueDates = [];

    foreach ($households as $household) {
        foreach ($types as $type) {
            $outstandingBilling = Billing::where('household_id', $household->id)
                ->where('payment_type_id', $type->id)
                ->whereIn('status', ['pending', 'overdue', 'partially_paid'])
                ->whereNull('deleted_at')
                ->latest('id')
                ->first();

            if ($outstandingBilling && $outstandingBilling->billing_period === $billingPeriod) {
                $skipped++;

                continue;
            }

            $customAmount = HouseholdPaymentAmount::where('household_id', $household->id)
                ->where('payment_type_id', $type->id)
                ->where('village_id', $villageId)
                ->value('custom_amount');

            $amount = $customAmount !== null ? (float) $customAmount : (float) $type->default_amount;
            $dueDate = $this->calculateDueDate($billingPeriod, $config, $type->frequency);

            if ($outstandingBilling) {
                $outstandingBilling->update([
                    'billing_number' => Billing::generateBillingNumber($config->billing_prefix),
                    'billing_period' => $billingPeriod,
                    'due_date' => $dueDate,
                    'amount' => (float) $outstandingBilling->amount + $amount,
                    'total_amount' => (float) $outstandingBilling->total_amount + $amount,
                    'status' => \Illuminate\Support\Carbon::parse($dueDate)->lt(now()->startOfDay()) ? 'overdue' : 'pending',
                ]);
                $created++;
            } else {
                Billing::create([
                    'billing_number' => Billing::generateBillingNumber($config->billing_prefix),
                    'household_id' => $household->id,
                    'village_id' => $villageId,
                    'payment_type_id' => $type->id,
                    'billing_period' => $billingPeriod,
                    'due_date' => $dueDate,
                    'amount' => $amount,
                    'penalty' => 0,
                    'discount' => 0,
                    'total_amount' => $amount,
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
                $created++;
            }

            $billedTotals[$household->id] = ($billedTotals[$household->id] ?? 0) + $amount;
            $billedDueDates[$household->id] = isset($billedDueDates[$household->id])
                ? min($billedDueDates[$household->id], $dueDate)
                : $dueDate;
        }
    }

    AuditLogService::log(
        'Generate Billings',
        'Payment Management',
        "Generated {$created} billings for village {$villageId} (period: {$billingPeriod})"
    );

    if ($billedTotals) {
        $this->notifyBilledHouseholds(
            $households->whereIn('id', array_keys($billedTotals)),
            $billingPeriod,
            $billedTotals,
            $billedDueDates
        );
    }

    return ['created' => $created, 'skipped' => $skipped];
}
```

### C.2 Due-date computation — `PaymentService::calculateDueDate()` (lines 486–516)

```php
private function calculateDueDate(string $billingPeriod, PaymentConfiguration $config, string $frequency = 'monthly'): string
{
    $parts = explode('-', $billingPeriod);
    $year = (int) $parts[0];
    $month = isset($parts[1]) ? (int) $parts[1] : 1;

    if ($frequency === 'annual') {
        $dueMonth = $config->annual_due_month ?: 1;
        $dueDay = $config->annual_due_day ?: 15;
        $dueYear = $year;
        if ($month > $dueMonth || ($month === $dueMonth && now()->day > $dueDay)) {
            $dueYear = $year + 1;
        }
        $day = min($dueDay, Carbon::create($dueYear, $dueMonth, 1)->daysInMonth);

        return Carbon::create($dueYear, $dueMonth, $day)->toDateString();
    }

    $dueDay = $config->monthly_due_day ?: 15;

    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear = $year + 1;
    }

    $day = min($dueDay, Carbon::create($nextYear, $nextMonth, 1)->daysInMonth);

    return Carbon::create($nextYear, $nextMonth, $day)->toDateString();
}
```

### C.3 Penalty and total computation — `PaymentService::recordPenalty()` (lines 257–281) and `updateBillingStatus()` (lines 466–484)

```php
public function recordPenalty(Billing $billing, float $amount, ?string $reason = null): PaymentPenalty
{
    return DB::transaction(function () use ($billing, $amount, $reason) {
        $penalty = PaymentPenalty::create([
            'billing_id' => $billing->id,
            'amount' => $amount,
            'reason' => $reason,
            'imposed_date' => now()->toDateString(),
            'imposed_by' => auth()->id(),
            'status' => 'active',
        ]);

        $billing->penalty = (float) $billing->penalty + $amount;
        $billing->total_amount = (float) $billing->amount + (float) $billing->penalty - (float) $billing->discount;
        $billing->save();

        AuditLogService::log(
            'Record Penalty',
            'Payment Management',
            'Penalty of ₱'.number_format($amount, 2)." imposed on billing {$billing->billing_number}"
        );

        return $penalty;
    });
}

private function updateBillingStatus(Billing $billing): void
{
    $totalPaid = (float) $billing->payments()->sum('total_paid');

    $totalDue = (float) $billing->total_amount;

    if ($totalPaid >= $totalDue - 0.01) {
        $billing->status = 'paid';
    } elseif ($totalPaid > 0) {
        // A partially settled bill stays "partially_paid" — even past its
        // due date — until it is fully paid or carried forward into the
        // next billing period as "unpaid".
        $billing->status = 'partially_paid';
    } else {
        $billing->status = $billing->due_date->lt(now()->startOfDay()) ? 'overdue' : 'pending';
    }

    $billing->save();
}
```

---

## D. Payment Recording and GCash Review

### D.1 Recording a payment and issuing a receipt — `PaymentService::recordPayment()` (lines 161–255)

```php
public function recordPayment(array $data, bool $notifyHousehold = true): HouseholdPayment
{
    return DB::transaction(function () use ($data, $notifyHousehold) {
        $billing = Billing::findOrFail($data['billing_id']);

        $config = $this->getVillageSetting($billing->village_id);
        $receiptPrefix = $config?->receipt_prefix ?? 'RCPT';

        $paymentAmount = (float) ($data['amount'] ?? 0);
        $discount = 0.0;

        if ($paymentAmount <= 0) {
            throw new \Exception('Payment amount must be greater than zero.');
        }

        $existingTotalPaid = (float) $billing->payments()->sum('total_paid');
        $remainingBalance = (float) $billing->total_amount - $existingTotalPaid;

        if ($remainingBalance <= 0) {
            throw new \Exception('This billing is already fully paid. No further payments can be recorded.');
        }

        $netAmount = $paymentAmount;
        if ($netAmount <= 0) {
            throw new \Exception('Payment amount must be greater than zero.');
        }

        if ($netAmount > $remainingBalance + 0.01) {
            throw new \Exception(
                'Payment amount of ₱'.number_format($netAmount, 2).
                ' exceeds the remaining balance of ₱'.number_format($remainingBalance, 2).'.'
            );
        }

        $totalPaid = $netAmount;

        $payment = HouseholdPayment::create([
            'receipt_number' => HouseholdPayment::generateReceiptNumber($receiptPrefix),
            'billing_id' => $billing->id,
            'village_id' => $billing->village_id,
            'household_id' => $billing->household_id,
            'payment_type_id' => $billing->payment_type_id,
            'amount' => $paymentAmount,
            'penalty' => 0,
            'discount' => $discount,
            'total_paid' => $totalPaid,
            'billing_number_snapshot' => $billing->billing_number,
            'billing_amount_snapshot' => $billing->total_amount,
            'billing_period_snapshot' => $billing->billing_period,
            'billing_balance_snapshot' => max(0, (float) $billing->total_amount - $existingTotalPaid - $totalPaid),
            'payment_date' => $data['payment_date'] ?? now()->toDateString(),
            'due_date' => $billing->due_date,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'reference_number' => $data['reference_number'] ?? null,
            'payment_status' => $existingTotalPaid + $netAmount >= (float) $billing->total_amount - 0.01 ? 'paid' : 'partially_paid',
            'received_by' => auth()->id(),
            'remarks' => $data['remarks'] ?? null,
        ]);

        PaymentReceipt::create([
            'receipt_number' => $payment->receipt_number,
            'payment_id' => $payment->id,
            'billing_id' => $billing->id,
            'household_id' => $billing->household_id,
            'village_id' => $billing->village_id,
            'receipt_type' => 'payment',
            'amount_paid' => $totalPaid,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'received_from' => $billing->household?->household_head_name ?? 'Household',
            'received_by' => auth()->id(),
            'receipt_date' => now()->toDateString(),
            'notes' => $data['remarks'] ?? null,
        ]);

        $this->updateBillingStatus($billing);

        AuditLogService::log(
            'Record Payment',
            'Payment Management',
            "Recorded payment {$payment->receipt_number} (₱".number_format($totalPaid, 2).") for billing {$billing->billing_number}"
        );

        if ($notifyHousehold) {
            app(NotificationService::class)->notifyHouseholdResidents(
                [$billing->household_id],
                'payment',
                'Payment Recorded',
                "A payment of ₱".number_format($totalPaid, 2)." ({$payment->receipt_number}) was recorded for {$billing->billing_number}.",
                route('household-payments.show', $billing->household_id)
            );
        }

        return $payment;
    });
}
```

### D.2 Resident submits GCash request (transaction number + proof) — `app/Http/Controllers/OnlinePaymentController.php::store()` (lines 47–127)

```php
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
```

### D.3 Treasurer approves a GCash request — `OnlinePaymentController::approve()` (lines 179–249)

```php
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
```

### D.4 Treasurer declines a GCash request — `OnlinePaymentController::decline()` (lines 251–298)

```php
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
```

---

## E. Payment Behavior Scoring (Predictive Analytics)

### E.1 `app/Services/Analytics/PaymentBehaviorService.php` — weights, scoring, classification (lines 44–173, 284–291)

```php
public const SCORING_WEIGHTS = [
    'on_time_bonus' => 15,
    'late_bonus' => 8,
    'missed_penalty' => 20,
    'partial_bonus' => 5,
    'consecutive_on_time_bonus' => 3,
    'consecutive_missed_penalty' => 5,
    'completion_weight' => 30,
    'consistency_weight' => 20,
    'recency_weight' => 10,
];

public function calculateScore(Household $household): array
{
    $villageId = $household->village_id;

    if (!$this->isPaymentEnabled($villageId)) {
        return $this->getDefaultResult();
    }

    $payments = HouseholdPayment::where('household_id', $household->id)
        ->where('village_id', $villageId)
        ->where('payment_status', '!=', 'cancelled')
        ->orderBy('payment_date')
        ->get();

    $billings = Billing::where('household_id', $household->id)
        ->where('village_id', $villageId)
        ->where('status', '!=', 'cancelled')
        ->orderBy('due_date')
        ->get();

    if ($payments->isEmpty() && $billings->isEmpty()) {
        return $this->getDefaultResult();
    }

    $totalBillings = $billings->count();
    $totalPayments = $payments->count();

    $onTimePayments = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date <= $p->due_date)->count();
    $latePayments = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date > $p->due_date && in_array($p->payment_status, ['paid']))->count();
    $missedPayments = $billings->filter(fn($b) => in_array($b->status, ['overdue']) || ($b->status === 'pending' && Carbon::parse($b->due_date)->isPast()))->count();
    $partialPayments = $payments->filter(fn($p) => $p->payment_status === 'partially_paid')->count();

    $paidBillings = $billings->filter(fn($b) => $b->status === 'paid')->count();
    $completionPercentage = $totalBillings > 0 ? ($paidBillings / $totalBillings) * 100 : 0;

    $consecutiveOnTime = $this->getConsecutiveOnTime($payments);
    $consecutiveMissed = $this->getConsecutiveMissed($billings);

    $consistencyScore = $this->calculateConsistency($payments, $billings);

    $score = 50.0;
    $reasons = [];

    if ($totalBillings > 0) {
        $score += ($onTimePayments * self::SCORING_WEIGHTS['on_time_bonus']);
        if ($onTimePayments > 0) {
            $reasons[] = "{$onTimePayments} on-time payment" . ($onTimePayments > 1 ? 's' : '');
        }

        $score -= ($latePayments * self::SCORING_WEIGHTS['late_bonus']);
        if ($latePayments > 0) {
            $reasons[] = "{$latePayments} late payment" . ($latePayments > 1 ? 's' : '');
        }

        $score -= ($missedPayments * self::SCORING_WEIGHTS['missed_penalty']);
        if ($missedPayments > 0) {
            $reasons[] = "{$missedPayments} missed/overdue payment" . ($missedPayments > 1 ? 's' : '');
        }

        $score += ($partialPayments * self::SCORING_WEIGHTS['partial_bonus']);
        if ($partialPayments > 0) {
            $reasons[] = "{$partialPayments} partial payment" . ($partialPayments > 1 ? 's' : '');
        }
    }

    if ($consecutiveOnTime >= 3) {
        $bonus = $consecutiveOnTime * self::SCORING_WEIGHTS['consecutive_on_time_bonus'];
        $score += $bonus;
        $reasons[] = "{$consecutiveOnTime} consecutive on-time payments";
    }

    if ($consecutiveMissed >= 2) {
        $penalty = $consecutiveMissed * self::SCORING_WEIGHTS['consecutive_missed_penalty'];
        $score -= $penalty;
        $reasons[] = "{$consecutiveMissed} consecutive missed payments";
    }

    $completionBonus = ($completionPercentage / 100) * self::SCORING_WEIGHTS['completion_weight'];
    $score += $completionBonus;

    $consistencyBonus = ($consistencyScore / 100) * self::SCORING_WEIGHTS['consistency_weight'];
    $score += $consistencyBonus;

    $recencyBonus = $this->calculateRecencyBonus($payments) * self::SCORING_WEIGHTS['recency_weight'];
    $score += $recencyBonus;

    $score = max(0, min(100, $score));

    $behavior = $this->classifyBehavior($score);

    $confidence = $this->calculateConfidence($totalBillings, $totalPayments);

    if (empty($reasons)) {
        $reasons[] = 'New household with limited payment history';
    }

    return [
        'score' => round($score, 2),
        'behavior' => $behavior,
        'behavior_label' => self::BEHAVIOR_LABELS[$behavior],
        'behavior_color' => self::BEHAVIOR_COLORS[$behavior],
        'behavior_icon' => self::BEHAVIOR_ICONS[$behavior],
        'confidence' => round($confidence, 2),
        'reasons' => $reasons,
        'stats' => [
            'total_billings' => $totalBillings,
            'total_payments' => $totalPayments,
            'on_time' => $onTimePayments,
            'late' => $latePayments,
            'missed' => $missedPayments,
            'partial' => $partialPayments,
            'completion_percentage' => round($completionPercentage, 2),
            'consecutive_on_time' => $consecutiveOnTime,
            'consecutive_missed' => $consecutiveMissed,
            'consistency_score' => round($consistencyScore, 2),
        ],
    ];
}

protected function classifyBehavior(float $score): string
{
    if ($score >= 85) return self::BEHAVIOR_EXCELLENT;
    if ($score >= 70) return self::BEHAVIOR_GOOD;
    if ($score >= 50) return self::BEHAVIOR_MODERATE;
    if ($score >= 30) return self::BEHAVIOR_AT_RISK;
    return self::BEHAVIOR_HIGH_RISK;
}
```

### E.2 Persisting the classification — `PaymentBehaviorService::recalculateHousehold()` (lines 175–186)

```php
public function recalculateHousehold(Household $household): void
{
    $result = $this->calculateScore($household);

    $household->update([
        'payment_behavior' => $result['behavior'],
        'payment_reliability_score' => $result['score'],
        'prediction_confidence' => $result['confidence'],
        'prediction_reasons' => json_encode($result['reasons']),
        'last_prediction_at' => Carbon::now(),
    ]);
}
```

---

## F. GIS Map

### F.1 Controller returning village/household coordinates — `app/Http/Controllers/GisController.php::index()` (lines 23–62)

```php
public function index(): View
{
    $user = auth()->user();
    $role = $user->role?->role_slug;

    if ($role === 'resident') {
        return $this->residentView($user);
    }

    if (in_array($role, ['admin', 'secretary', 'treasurer']) && $user->village_id) {
        return $this->villageView($user->village_id);
    }

    return $this->superAdminView();
}

private function superAdminView(): View
{
    $villages = $this->gis->getAllVillages();
    $villageMarkers = $this->gis->getVillagesWithCoords();
    $households = $this->gis->getAllHouseholdsWithCoords();
    $center = $this->gis->getSystemCenter();
    $stats = $this->gis->getSystemStatistics();
    $overviewData = $this->dashboard->getSystemOverviewData();

    return view('gis.index', compact('villages', 'villageMarkers', 'households', 'center', 'stats', 'overviewData'));
}

private function villageView(int $villageId): View
{
    $village = Village::findOrFail($villageId);
    $households = $this->gis->getVillageHouseholds($villageId);
    $stats = $this->gis->getVillageStatistics($villageId);
    $center = $this->gis->getVillageCenter($villageId);
    $dashboardData = $this->dashboard->getComprehensiveVillageData($villageId);
    // Every mapped village gets a big marker; household pins stay scoped to this village.
    $villageMarkers = $this->gis->getVillagesWithCoords();

    return view('gis.village', compact('village', 'households', 'stats', 'center', 'dashboardData', 'villageMarkers'));
}
```

### F.2 Coordinate queries — `app/Services/GisService.php` (lines 12–88)

```php
public function getVillageHouseholds(int $villageId)
{
    return Household::where('village_id', $villageId)
        ->where('approval_status', 'approved')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->with('village:id,village_name,village_code,latitude,longitude')
        ->select([
            'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
            'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
            'address', 'purok', 'village_id', 'created_at', 'approved_at',
        ])
        ->withCount(['residents as member_count' => function ($q) {
            $q->where('status', 'active');
        }])
        ->orderBy('household_number')
        ->get();
}

public function getAllHouseholdsWithCoords()
{
    return Household::where('approval_status', 'approved')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->with('village:id,village_name,village_code,latitude,longitude')
        ->select([
            'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
            'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
            'village_id',
        ])
        ->withCount(['residents as member_count' => function ($q) {
            $q->where('status', 'active');
        }])
        ->orderBy('village_id')
        ->orderBy('household_number')
        ->get();
}

public function getVillagesWithCoords()
{
    return Village::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->select([
            'id', 'village_name', 'village_code', 'latitude', 'longitude',
            'address', 'total_households', 'total_population',
        ])
        ->withCount([
            'households as household_count',
            'residents as resident_count' => function ($q) {
                $q->where('status', 'active');
            },
        ])
        ->orderBy('village_name')
        ->get();
}
```

### F.3 Leaflet JavaScript — `resources/views/gis/index.blade.php` (lines 205–256, 303–402)

```javascript
var villageMarkersData = @json($villageMarkers);
var allVillages = @json($villages);
var systemOverview = @json($overviewData ?? null);
var currentRole = '{{ auth()->user()->role?->role_slug ?? "super_admin" }}';

var map = L.map('gis-map', {
    fullscreenControl: true,
    fullscreenControlOptions: { position: 'topleft' },
    zoomControl: true,
    updateWhenIdle: false,
    preferCanvas: true,
}).setView([center.lat, center.lng], 12);

// Satellite imagery (default) and the original street map, switchable
// live through the layer control below — no page reload, position kept.
//
// Uses Esri World Imagery Wayback (release WB_2026_R04): a fixed, dated
// capture that is cloud-free over Comagascas, La Union and Calibunan.
// Wayback serves real imagery through z18 — z19 returns 404, which is
// what produced the "map data not available" message when zooming in.
var satelliteLayer = L.tileLayer('https://wayback.maptiles.arcgis.com/arcgis/rest/services/World_Imagery/WMTS/1.0.0/GoogleMapsCompatible/MapServer/tile/49059/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community',
    maxZoom: 18,
});

var streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
});

satelliteLayer.addTo(map);

L.control.layers(
    { 'Satellite View': satelliteLayer, 'Street Map': streetLayer },
    null,
    { position: 'topleft', collapsed: window.innerWidth < 768 }
).addTo(map);

var householdLayer = L.layerGroup().addTo(map);
var villageLayer = L.layerGroup().addTo(map);
var geocodeLayer = L.layerGroup().addTo(map);
var latestLayer = L.layerGroup().addTo(map);
var allMarkers = [];
var selectedHouseholdMarker = null;

function getHouseholdColor(h) {
    if (h.payment_status === 'paid') return '#28a745';
    if (h.payment_status === 'partial') return '#ffc107';
    if (h.payment_status === 'unpaid') return '#dc3545';
    if (h.payment_status === 'disabled' || h.payment_status === 'no_billing') return '#6c757d';
    if (h.status === 'active') return '#28a745';
    if (h.status === 'inactive') return '#ffc107';
    return '#6c757d';
}

function createVillageMarker(v) {
    var icon = L.divIcon({
        className: 'gis-marker',
        html: '<div style="width:34px;height:34px;background:#1a73e8;border:4px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;"><i class="bi bi-building" style="color:#fff;font-size:15px;"></i></div>',
        iconSize: [42, 42], iconAnchor: [21, 21],
    });
    var m = L.marker([v.latitude, v.longitude], { icon: icon });
    var hhCount = v.household_count || v.total_households || 0;
    var resCount = v.resident_count || v.total_population || 0;
    m.bindPopup(
        '<div class="gis-popup">' +
        '<strong style="font-size:1rem;color:#1a73e8;">' + v.village_name + '</strong><br>' +
        '<small class="text-muted">' + (v.address || '') + '</small><hr class="my-1">' +
        '<div class="d-flex justify-content-between mb-1"><small>Households:</small><strong>' + hhCount + '</strong></div>' +
        '<div class="d-flex justify-content-between mb-1"><small>Residents:</small><strong>' + resCount + '</strong></div>' +
        '<button onclick="selectVillage(' + v.id + ')" class="btn btn-primary btn-sm mt-2 w-100"><i class="bi bi-bar-chart-line me-1"></i>View Dashboard</button>' +
        '</div>'
    );
    return m;
}

function createHouseholdMarker(h) {
    var color = getHouseholdColor(h);
    var icon = L.divIcon({
        className: 'gis-marker',
        html: '<div style="width:12px;height:12px;background:' + color + ';border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>',
        iconSize: [16, 16], iconAnchor: [8, 8],
    });
    var m = L.marker([h.latitude, h.longitude], { icon: icon });
    var villageName = h.village ? h.village.village_name : '';
    var paymentBadge = '';
    if (h.payment_status && h.payment_status !== 'disabled' && h.payment_status !== 'no_billing') {
        var pClass = h.payment_status === 'paid' ? 'success' : (h.payment_status === 'partial' ? 'warning' : 'danger');
        paymentBadge = '<br><span class="badge bg-' + pClass + ' payment-badge">' + h.payment_status.charAt(0).toUpperCase() + h.payment_status.slice(1) + '</span>';
    }
    var memberCount = h.member_count || 0;
    var predBadge = '';
    if (h.payment_behavior) {
        var bClass = h.payment_behavior === 'excellent' ? 'success' : (h.payment_behavior === 'good' ? 'info' : (h.payment_behavior === 'moderate' ? 'warning' : 'danger'));
        predBadge = '<br><span class="badge bg-' + bClass + ' payment-badge"><i class="bi bi-graph-up-arrow me-1"></i>' + h.payment_behavior + '</span>';
    }
    m.bindPopup(
        '<div class="gis-popup">' +
        '<strong style="color:#1a73e8;">' + h.household_number + '</strong><br>' +
        '<small class="text-muted">' + villageName + '</small><hr class="my-1">' +
        '<div class="d-flex justify-content-between mb-1"><small>Head:</small><strong>' + (h.household_head_name || '') + '</strong></div>' +
        '<div class="d-flex justify-content-between mb-1"><small>Block/Lot:</small><strong>' + (h.block_number || '-') + '/' + (h.lot_number || '-') + '</strong></div>' +
        '<div class="d-flex justify-content-between mb-1"><small>Members:</small><strong>' + memberCount + '</strong></div>' +
        '<div class="d-flex justify-content-between mb-1"><small>Status:</small><span class="badge bg-' + (h.status === 'active' ? 'success' : (h.status === 'inactive' ? 'warning' : 'secondary')) + '">' + h.status + '</span></div>' +
        '<div class="d-flex justify-content-between mb-1"><small>Approval:</small><span class="badge bg-' + (h.approval_status === 'approved' ? 'success' : (h.approval_status === 'pending' ? 'warning' : 'danger')) + '">' + (h.approval_status || 'N/A') + '</span></div>' +
        paymentBadge + predBadge +
        '<a href="/households/' + h.id + '" class="btn btn-primary btn-sm mt-2 w-100"><i class="bi bi-eye me-1"></i>View Details</a>' +
        '</div>'
    );
    m.on('click', function() {
        if (selectedHouseholdMarker) {
            var prev = selectedHouseholdMarker._prevIcon;
            if (prev) selectedHouseholdMarker.setIcon(prev);
        }
        selectedHouseholdMarker = m;
    });
    return m;
}

function renderAll() {
    householdLayer.clearLayers();
    villageLayer.clearLayers();
    geocodeLayer.clearLayers();
    latestLayer.clearLayers();
    allMarkers = [];

    villageMarkersData.forEach(function(v) {
        if (v.latitude && v.longitude) {
            var m = createVillageMarker(v);
            m.addTo(villageLayer);
            allMarkers.push({ type: 'village', marker: m, data: v });
        }
    });

    allHouseholds.forEach(function(h) {
        if (h.latitude && h.longitude) {
            var m = createHouseholdMarker(h);
            m.addTo(householdLayer);
            allMarkers.push({ type: 'household', marker: m, data: h });
        }
    });
}

setTimeout(function() {
    renderAll();
    renderSystemOverview();
    var allItems = allMarkers;
    if (allItems.length > 0) fitToMarkers(allItems);
}, 80);

function fitToMarkers(markers) {
    if (markers.length === 0) return;
    var group = L.featureGroup(markers.map(function(item) { return item.marker; }));
    map.fitBounds(group.getBounds().pad(0.1));
}
```

---

## G. Database Structure

### G.1 `database/migrations/2026_07_14_000009_create_households_table.php` (complete)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->string('household_number');
            $table->unsignedBigInteger('household_head_id')->nullable();
            $table->string('purok')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('contact_number')->nullable();
            $table->string('household_type')->nullable();
            $table->string('house_ownership')->nullable();
            $table->string('house_status')->default('active');
            $table->date('registration_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['village_id', 'household_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
```

### G.2 Billing + payment schema — `database/migrations/2026_07_16_000005_restructure_payment_tables.php` (lines 48–95)

```php
Schema::create('household_billings', function (Blueprint $table) {
    $table->id();
    $table->string('billing_number')->unique();
    $table->foreignId('household_id')->constrained()->cascadeOnDelete();
    $table->foreignId('village_id')->constrained()->cascadeOnDelete();
    $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
    $table->string('billing_period')->comment('e.g. 2026-07 or 2026');
    $table->date('due_date');
    $table->decimal('amount', 10, 2);
    $table->decimal('penalty', 10, 2)->default(0);
    $table->decimal('discount', 10, 2)->default(0);
    $table->decimal('total_amount', 10, 2);
    $table->enum('status', ['draft', 'pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('draft');
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->text('remarks')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['village_id', 'status']);
    $table->index(['household_id', 'status']);
    $table->index(['billing_period', 'village_id']);
});

Schema::create('household_payments', function (Blueprint $table) {
    $table->id();
    $table->string('receipt_number')->unique();
    $table->foreignId('billing_id')->constrained('household_billings')->cascadeOnDelete();
    $table->foreignId('household_id')->constrained()->cascadeOnDelete();
    $table->foreignId('village_id')->constrained()->cascadeOnDelete();
    $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
    $table->decimal('amount', 10, 2)->comment('Amount being paid in this transaction');
    $table->decimal('penalty', 10, 2)->default(0);
    $table->decimal('discount', 10, 2)->default(0);
    $table->decimal('total_paid', 10, 2)->comment('Amount actually paid');
    $table->date('payment_date');
    $table->date('due_date');
    $table->enum('payment_method', ['cash', 'gcash', 'maya', 'bank_transfer'])->default('cash');
    $table->string('reference_number')->nullable()->comment('For non-cash payments');
    $table->enum('payment_status', ['pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('pending');
    $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
    $table->text('remarks')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['village_id', 'payment_status']);
    $table->index(['household_id', 'payment_status']);
    $table->index(['billing_id', 'payment_status']);
});
```

### G.3 Predictive-analytics columns — `database/migrations/2026_07_17_090000_add_payment_behavior_to_households_table.php` (complete)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->string('payment_behavior')->nullable()->after('status');
            $table->decimal('payment_reliability_score', 5, 2)->default(50.00)->after('payment_behavior');
            $table->decimal('prediction_confidence', 5, 2)->default(0.00)->after('payment_reliability_score');
            $table->text('prediction_reasons')->nullable()->after('prediction_confidence');
            $table->timestamp('last_prediction_at')->nullable()->after('prediction_reasons');

            $table->index('payment_behavior');
            $table->index('payment_reliability_score');
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropIndex(['payment_behavior']);
            $table->dropIndex(['payment_reliability_score']);
            $table->dropColumn([
                'payment_behavior',
                'payment_reliability_score',
                'prediction_confidence',
                'prediction_reasons',
                'last_prediction_at',
            ]);
        });
    }
};
```

---

## H. Report Generation (PDF/CSV/Excel)

### H.1 Entry point — `app/Http/Controllers/ReportController.php::generate()` (lines 53–87)

```php
public function generate(Request $request): \Symfony\Component\HttpFoundation\Response
{
    $request->validate([
        'report_type' => 'required|string',
        'format' => 'required|in:pdf,csv,excel',
        'village_id' => 'nullable|exists:villages,id',
    ]);

    $reportType = $request->input('report_type');
    $format = $request->input('format');
    $role = auth()->user()->role?->role_slug ?? 'resident';

    if (!$this->reportService->canAccessReport($role, $reportType)) {
        abort(403, 'You do not have permission to generate this report.');
    }

    $villageId = $this->villageScope($this->requestedVillageId($request))['villageId'];

    $filters = $request->only([
        'date_from', 'date_to', 'gender', 'civil_status', 'age_group',
        'occupation', 'employment_status', 'education', 'income_range',
        'block', 'lot', 'household_id',
    ]);

    $data = $this->reportService->getReportData($reportType, $villageId, $filters);

    $record = $this->reportService->recordGeneration($reportType, $villageId, $format, $filters);

    return match ($format) {
        'pdf' => $this->exportPdf($reportType, $data, $villageId, $record),
        'csv' => $this->exportCsv($reportType, $data, $record),
        'excel' => $this->exportExcel($reportType, $data, $record),
        default => redirect()->route('reports.index')->with('error', 'Unsupported format.'),
    };
}
```

### H.2 PDF export (Dompdf) — `ReportController::exportPdf()` (lines 219–246)

```php
protected function exportPdf(string $reportType, array $data, ?int $villageId, GeneratedReport $record): \Symfony\Component\HttpFoundation\Response
{
    $view = "reports.exports.{$reportType}";
    if (!view()->exists($view)) {
        $view = 'reports.exports.generic-pdf';
    }

    $pdf = Pdf::loadView($view, $data)
        ->setPaper('letter', 'portrait')
        ->setOptions([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

    $village = $villageId ? Village::find($villageId) : null;
    $filename = $reportType . ($village ? "-{$village->village_code}" : "-all") . "-" . now()->format('Y-m-d') . ".pdf";
    $path = storage_path("app/reports/{$filename}");

    if (!is_dir(storage_path('app/reports'))) {
        mkdir(storage_path('app/reports'), 0755, true);
    }

    $pdf->save($path);
    $record->update(['file_path' => $path, 'file_size' => filesize($path)]);

    return $pdf->download($filename);
}
```

### H.3 CSV export — `ReportController::exportCsv()` (lines 248–275)

```php
protected function exportCsv(string $reportType, array $data, GeneratedReport $record): \Symfony\Component\HttpFoundation\Response
{
    $filename = $reportType . "-" . now()->format('Y-m-d-His') . ".csv";
    $rows = $data['rows'] ?? [];

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    return response()->stream(function () use ($data, $rows) {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, [$data['reportTitle'] ?? 'Report']);
        fputcsv($handle, ['Village: ' . ($data['villageName'] ?? 'All')]);
        fputcsv($handle, ['Generated: ' . ($data['generatedAt'] ?? '')]);
        fputcsv($handle, ['By: ' . ($data['generatedBy'] ?? '')]);
        fputcsv($handle, []);

        if (!empty($rows)) {
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($handle, array_values($row));
            }
        }

        fclose($handle);
    }, 200, $headers);
}
```

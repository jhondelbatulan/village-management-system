<?php

namespace App\Http\Controllers;

use App\Models\PendingChange;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PendingChangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Two-stage approval queue for staged household/resident update and delete
 * requests: Admin reviews first (stage 1), Super Admin gives the final
 * approval (stage 2) which is the only moment changes are actually saved
 * (or a delete is actually carried out).
 */
class PendingChangeController extends Controller
{
    public function updates(): View
    {
        return $this->index(PendingChange::ACTION_UPDATE);
    }

    public function deletes(): View
    {
        return $this->index(PendingChange::ACTION_DELETE);
    }

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

    public function reject(Request $request, PendingChange $change): RedirectResponse
    {
        $user = $request->user();
        $role = $user?->role?->role_slug;

        if (! in_array($role, ['admin', 'super_admin'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:2000'],
        ]);
        $remarks = trim($validated['remarks']);
        if ($remarks === '') {
            return back()->withErrors(['remarks' => 'Remarks are required when rejecting.']);
        }

        return DB::transaction(function () use ($change, $user, $role, $remarks): RedirectResponse {
            $change = PendingChange::query()->lockForUpdate()->findOrFail($change->id);

            // Admin rejects pending requests in their village (Stage 1).
            if ($role === 'admin') {
                if ($change->village_id !== $user->village_id) {
                    abort(403, 'You can only reject requests in your village.');
                }

                if ($change->approval_status !== 'pending') {
                    return back()->with('error', 'This request has already been processed.');
                }
            }

            // Super Admin rejects admin-approved requests (Stage 2).
            if ($role === 'super_admin' && $change->approval_status !== 'admin_approved') {
                return back()->with('error', $change->approval_status === 'pending'
                    ? 'Only admin-approved requests can be rejected at this stage.'
                    : 'This request has already been processed.');
            }

            $label = $change->target_label ?? ('#' . $change->target_id);
            $noun = $change->target_type === 'household' ? 'Household' : 'Resident';
            $actionNoun = $change->action === PendingChange::ACTION_DELETE ? 'Delete' : 'Update';
            $queue = $change->action === PendingChange::ACTION_DELETE
                ? route('approvals.deletes')
                : route('approvals.updates');

            // Nothing was ever written to the record — only the staged
            // payload (and any staged picture) is thrown away.
            if ($change->action === PendingChange::ACTION_UPDATE) {
                app(PendingChangeService::class)->discardStagedPicture($change);
            }

            $change->update([
                'approval_status' => 'rejected',
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'remarks' => $remarks,
            ]);

            AuditLogService::log(
                'Reject Change Request',
                'Approvals',
                "Rejected {$actionNoun} request for {$noun} \"{$label}\". Reason: {$remarks}"
            );

            if ($change->submitted_by) {
                app(NotificationService::class)->notifyUser(
                    $change->submitted_by,
                    'approval',
                    "{$actionNoun} Request Rejected",
                    "Your {$actionNoun} request for {$noun} \"{$label}\" was rejected. Reason: {$remarks}",
                    $queue
                );
            }

            if ($role === 'super_admin') {
                app(NotificationService::class)->notifyAdmins(
                    $change->village_id,
                    'Approval Required',
                    "{$actionNoun} request for {$noun} \"{$label}\" was rejected by Super Admin. Reason: {$remarks}",
                    $queue,
                    'warning'
                );
            }

            return redirect()->back()->with('success', "Request for \"{$label}\" has been rejected. No changes were saved.");
        });
    }

    private function index(string $action): View
    {
        $user = auth()->user();
        $role = $user?->role?->role_slug;

        $isHistory = request('view') === 'history';

        $query = PendingChange::with(['submittedBy', 'adminApprovedBy', 'approvedBy', 'rejectedBy'])
            ->where('action', $action);

        if ($isHistory) {
            $query->whereIn('approval_status', ['approved', 'rejected', 'superseded']);
        } elseif ($role === 'admin') {
            $query->where('village_id', $user->village_id)
                ->where('approval_status', 'pending');
        } elseif ($role === 'super_admin') {
            $query->where('approval_status', 'admin_approved');
        }

        if (! $isHistory && request('status')) {
            $query->where('approval_status', request('status'));
        }

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search): void {
                $q->where('target_label', 'like', "%{$search}%");
            });
        }

        $changes = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'pendingCount' => $this->countFor($action, ['pending'], $role, $user),
            'adminApprovedCount' => $this->countFor($action, ['admin_approved'], $role, $user),
            'approvedCount' => $this->countFor($action, ['approved'], $role, $user),
            'rejectedCount' => $this->countFor($action, ['rejected'], $role, $user),
        ];

        return view('approvals.requests', [
            'changes' => $changes,
            'counts' => $counts,
            'role' => $role,
            'isHistory' => $isHistory,
            'action' => $action,
        ]);
    }

    private function countFor(string $action, array $statuses, ?string $role, $user): int
    {
        return PendingChange::where('action', $action)
            ->whereIn('approval_status', $statuses)
            ->when($role === 'admin', fn ($q) => $q->where('village_id', $user->village_id))
            ->count();
    }
}

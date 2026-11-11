<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest\StoreUserRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use App\Services\AuditLogService;
use App\Services\PasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(protected PasswordService $passwordService) {}

    public function index(): View
    {
        $user = auth()->user();

        $query = User::with(['role', 'village', 'household'])
            ->when($user->role?->role_slug === 'admin', function ($q) use ($user) {
                $q->where('village_id', $user->village_id);
            });

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (request('role')) {
            $query->whereHas('role', fn($q) => $q->where('role_slug', request('role')));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('sort') === 'az') {
            $query->orderBy('full_name');
        } elseif (request('sort') === 'za') {
            $query->orderByDesc('full_name');
        } else {
            $query->latest();
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::orderBy('role_name')->get();

        return view('user-management.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $roles = $this->getAssignableRoles($user);
        $isAdmin = $user->role?->role_slug === 'admin';

        if ($isAdmin) {
            $villages = Village::where('id', $user->village_id)->get();
        } else {
            $villages = Village::orderBy('village_name')->get();
        }

        $householdsQuery = Household::query()
            ->where('approval_status', 'approved')
            ->whereDoesntHave('users', fn ($query) => $query->whereHas('role', fn ($role) => $role->where('role_slug', 'resident')))
            ->orderBy('block_number')
            ->orderBy('lot_number');

        if ($isAdmin) {
            $householdsQuery->where('village_id', $user->village_id);
        }

        $households = $householdsQuery->get();

        $residentRoleId = Role::where('role_slug', 'resident')->first()?->id;

        return view('user-management.create', compact('roles', 'villages', 'isAdmin', 'households', 'residentRoleId'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Apply the configured password policy to the account being created.
        $this->passwordService->enforcePolicy($data['password']);

        $data['password'] = bcrypt($data['password']);

        $currentUser = auth()->user();
        $isAdmin = $currentUser->role?->role_slug === 'admin';

        if ($isAdmin) {
            $data['village_id'] = $currentUser->village_id;
        }

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = 'profile_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_pictures', $filename, 'public');
            $data['profile_picture'] = 'profile_pictures/' . $filename;
        }

        unset($data['role_name']);

        // New accounts are always created active; status changes happen afterwards.
        $data['status'] = 'active';

        // Fresh accounts must finish the one-time account setup (personal
        // information, email, contact number and their own password) the first
        // time they sign in, before reaching their dashboard.
        $data['must_setup'] = true;

        $user = User::create($data);

        // Seed the password history so the initial password cannot be reused.
        $this->passwordService->recordPassword($user, $request->password);

        AuditLogService::log(
            'Create User',
            'User Management',
            "Created user account: {$user->username} ({$user->role?->role_name})"
        );

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }

    public function edit(User $user): View
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser->role?->role_slug === 'admin';

        if ($isAdmin && $currentUser->village_id !== $user->village_id) {
            abort(403, 'You can only manage users in your village.');
        }

        $roles = $this->getAssignableRoles($currentUser);

        if ($isAdmin) {
            $villages = Village::where('id', $currentUser->village_id)->get();
        } else {
            $villages = Village::orderBy('village_name')->get();
        }

        $householdsQuery = Household::query()
            ->where(fn ($query) => $query
                ->where('approval_status', 'approved')
                ->orWhere('id', $user->household_id ?? 0))
            ->orderBy('block_number')
            ->orderBy('lot_number');

        if ($isAdmin) {
            $householdsQuery->where('village_id', $currentUser->village_id);
        }

        $households = $householdsQuery->get();

        $residentRoleId = Role::where('role_slug', 'resident')->first()?->id;

        return view('user-management.edit', compact('user', 'roles', 'villages', 'isAdmin', 'households', 'residentRoleId'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser->role?->role_slug === 'admin';

        if ($isAdmin && $currentUser->village_id !== $user->village_id) {
            abort(403, 'You can only manage users in your village.');
        }

        $data = $request->validated();

        if ($isAdmin) {
            $data['village_id'] = $currentUser->village_id;
        }

        $plainPassword = null;
        if (! empty($data['password'])) {
            // Apply the configured password policy to the user being edited.
            $this->passwordService->enforcePolicy($data['password'], $user);
            $plainPassword = $data['password'];
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                $oldPath = Storage::disk('public')->path($user->profile_picture);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('profile_picture');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_pictures', $filename, 'public');
            $data['profile_picture'] = 'profile_pictures/' . $filename;
        }

        $user->update($data);

        if ($plainPassword !== null) {
            $this->passwordService->recordPassword($user, $plainPassword);
        }

        AuditLogService::log(
            'Update User',
            'User Management',
            "Updated user account: {$user->username}"
        );

        return redirect()->route('users.index')->with('success', 'User account updated successfully.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot change your own account status.');
        }

        if ($user->role?->role_slug === 'super_admin' && $currentUser->role?->role_slug !== 'super_admin') {
            abort(403, 'Only a Super Admin can change a Super Admin\'s status.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        if ($newStatus === 'inactive') {
            $this->invalidateUserAccess($user);
        }

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';

        AuditLogService::log(
            ucfirst($label) . ' User',
            'User Management',
            "User \"{$user->username}\" has been {$label}."
        );

        return redirect()->route('users.index')->with('success', "User \"{$user->username}\" has been {$label}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->role?->role_slug === 'super_admin' && $currentUser->role?->role_slug !== 'super_admin') {
            abort(403, 'Only a Super Admin can delete another Super Admin.');
        }

        $username = $user->username;

        try {
            DB::transaction(function () use ($user): void {
                $this->invalidateUserAccess($user);
                $user->forceDelete();
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', "User \"{$username}\" could not be deleted because related records still reference it.");
        }

        AuditLogService::log(
            'Delete User',
            'User Management',
            "Deleted user account: {$username}"
        );

        return redirect()->route('users.index')->with('success', "User \"{$username}\" has been deleted.");
    }

    private function invalidateUserAccess(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $user->forceFill(['remember_token' => Str::random(60)])->save();
    }

    private function getAssignableRoles(User $currentUser): \Illuminate\Support\Collection
    {
        return match ($currentUser->role?->role_slug) {
            'super_admin' => Role::whereIn('role_slug', ['admin'])->get(),
            'admin' => Role::whereIn('role_slug', ['secretary', 'treasurer', 'resident'])->get(),
            default => Role::whereRaw('0')->get(),
        };
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest\StoreUserRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserManagementController extends Controller
{
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

        $households = Household::where('status', 'active')
            ->orderBy('block_number')
            ->orderBy('lot_number')
            ->get();

        if ($isAdmin) {
            $households = $households->where('village_id', $user->village_id);
        }

        $residentRoleId = Role::where('role_slug', 'resident')->first()?->id;

        return view('user-management.create', compact('roles', 'villages', 'isAdmin', 'households', 'residentRoleId'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
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

        $user = User::create($data);

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

        $households = Household::where('status', 'active')
            ->orderBy('block_number')
            ->orderBy('lot_number')
            ->get();

        if ($isAdmin) {
            $households = $households->where('village_id', $currentUser->village_id);
        }

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

        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                $oldPath = storage_path('app/public/' . $user->profile_picture);
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
            \Illuminate\Support\Facades\Session::where('user_id', $user->id)->update(['user_id' => null]);
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
        $user->update(['status' => 'inactive']);
        \Illuminate\Support\Facades\Session::where('user_id', $user->id)->update(['user_id' => null]);
        $user->delete();

        AuditLogService::log(
            'Delete User',
            'User Management',
            "Deleted user account: {$username}"
        );

        return redirect()->route('users.index')->with('success', "User \"{$username}\" has been deleted.");
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

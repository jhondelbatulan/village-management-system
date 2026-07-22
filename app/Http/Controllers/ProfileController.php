<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest\ChangePasswordRequest;
use App\Http\Requests\ProfileRequest\UpdateProfileRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        if (!$user) abort(401);
        $user->load('role', 'village');

        return view('profile.index', compact('user'));
    }

    public function edit(): View
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();

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
        } else {
            unset($data['profile_picture']);
        }

        $user->update($data);

        AuditLogService::log('Update Profile', 'Profile', 'User updated their profile information.');

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully.');
    }

    public function changePasswordForm(): View
    {
        return view('profile.change-password');
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update(['password' => $request->password]);

        AuditLogService::log('Change Password', 'Profile', 'User changed their password.');

        return redirect()->route('profile.index')->with('success', 'Password changed successfully.');
    }

    public function updatePicture(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();

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
            $user->update(['profile_picture' => 'profile_pictures/' . $filename]);

            AuditLogService::log('Update Picture', 'Profile', 'User updated their profile picture.');
        }

        return redirect()->route('profile.index')->with('success', 'Profile picture updated successfully.');
    }
}

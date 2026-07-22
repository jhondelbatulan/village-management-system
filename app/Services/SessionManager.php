<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Facades\Auth;

class SessionManager
{
    public function getPinnedSessions(int $ownerId)
    {
        return UserSession::where('owner_id', $ownerId)
            ->where('is_active', true)
            ->whereHas('targetUser')
            ->with('targetUser.role', 'targetUser.village')
            ->orderBy('last_used_at', 'desc')
            ->get();
    }

    public function pinAccount(int $ownerId, int $targetUserId, ?string $name = null): UserSession
    {
        $target = User::findOrFail($targetUserId);

        $existing = UserSession::where('owner_id', $ownerId)
            ->where('target_user_id', $targetUserId)
            ->first();

        if ($existing) {
            $existing->update([
                'is_active' => true,
                'session_name' => $name ?? $existing->session_name,
                'last_used_at' => now(),
            ]);
            return $existing;
        }

        return UserSession::create([
            'owner_id' => $ownerId,
            'target_user_id' => $targetUserId,
            'session_name' => $name,
            'is_active' => true,
            'last_used_at' => now(),
        ]);
    }

    public function unpinAccount(int $ownerId, int $targetUserId): bool
    {
        return UserSession::where('owner_id', $ownerId)
            ->where('target_user_id', $targetUserId)
            ->update(['is_active' => false]) > 0;
    }

    public function switchToUser(int $targetUserId): bool
    {
        $user = User::find($targetUserId);
        if (!$user || $user->status !== 'active') {
            return false;
        }

        Auth::login($user);

        session(['switched_from' => session()->get('switched_from', auth('web')->user()?->id ?? $targetUserId)]);
        session(['switched_to' => $targetUserId]);
        session(['is_switched' => true]);

        $ownerId = session('switched_from');
        if ($ownerId && $ownerId !== $targetUserId) {
            $pinned = UserSession::where('owner_id', $ownerId)
                ->where('target_user_id', $targetUserId)
                ->first();
            if ($pinned) {
                $pinned->touchLastUsed();
            } else {
                $this->pinAccount($ownerId, $targetUserId);
            }
        }

        return true;
    }

    public function switchBack(): bool
    {
        $originalUserId = session('switched_from');
        if (!$originalUserId) {
            return false;
        }

        $user = User::find($originalUserId);
        if (!$user) {
            return false;
        }

        Auth::login($user);

        session()->forget('switched_from');
        session()->forget('switched_to');
        session()->forget('is_switched');

        return true;
    }

    public function isSwitched(): bool
    {
        return session('is_switched', false);
    }

    public function getOriginalUserId(): ?int
    {
        return session('switched_from');
    }

    public function getCurrentUserId(): int
    {
        return session('switched_to', auth()->id());
    }

    public function getAvailableAccounts(int $ownerId)
    {
        return User::where('id', '!=', $ownerId)
            ->where('status', 'active')
            ->with('role', 'village')
            ->orderBy('username')
            ->get();
    }

    public function removeSession(int $sessionId, int $ownerId): bool
    {
        return UserSession::where('id', $sessionId)
            ->where('owner_id', $ownerId)
            ->delete() > 0;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\GcashConfig;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GcashConfigController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $this->authorizeTreasurer($user);

        $village = $user->village;
        $config = GcashConfig::where('village_id', $user->village_id)->first();

        return view('gcash.edit', compact('village', 'config'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeTreasurer($user);

        $data = $request->validate([
            'gcash_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+ ]{6,20}$/'],
            'gcash_name' => ['required', 'string', 'max:255'],
        ], [
            'gcash_number.required' => 'GCash number is required.',
            'gcash_number.max' => 'GCash number must not exceed 20 characters.',
            'gcash_number.regex' => 'Enter a valid GCash number (digits, + or spaces only).',
            'gcash_name.required' => 'GCash account name is required.',
            'gcash_name.max' => 'GCash account name must not exceed 255 characters.',
        ]);

        GcashConfig::updateOrCreate(['village_id' => $user->village_id], $data);

        AuditLogService::log(
            'Update GCash Details',
            'Payment Management',
            "Updated GCash payment details for {$user->village?->village_name}"
        );

        return redirect()->route('gcash-config.edit')
            ->with('success', 'GCash details updated successfully. Residents will see these as their payment guide.');
    }

    private function authorizeTreasurer($user): void
    {
        abort_unless(
            $user->role?->role_slug === 'treasurer' && $user->village_id,
            403,
            'Only village treasurers can manage GCash details.'
        );
    }
}

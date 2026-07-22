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
            auth()->logout();
            return redirect()->route('login');
        }

        if ($user->status !== 'active') {
            auth()->logout();
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
}

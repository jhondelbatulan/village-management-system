<?php

namespace App\Http\Middleware;

use App\Support\RoleRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $this->redirectToDashboard($request, $guard);
            }
        }

        return $next($request);
    }

    /**
     * Send an already-signed-in user to their own dashboard. When the account
     * cannot be resolved to a dashboard the session is destroyed, otherwise the
     * browser would loop forever between "/" and "/login".
     */
    private function redirectToDashboard(Request $request, ?string $guard): Response
    {
        $auth = Auth::guard($guard);
        $routeName = RoleRedirect::routeName($auth->user()?->role?->role_slug);

        if ($routeName !== null) {
            return redirect()->route($routeName);
        }

        $auth->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'username' => 'Your session is no longer valid. Please sign in again.',
        ]);
    }
}

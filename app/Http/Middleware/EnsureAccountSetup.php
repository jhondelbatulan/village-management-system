<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountSetup
{
    /**
     * Newly created accounts must finish the one-time account setup (personal
     * information, email, contact number and their own password) before they
     * are allowed anywhere else in the application.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_setup && ! $request->routeIs('account.setup.show', 'account.setup.store', 'logout')) {
            return redirect()->route('account.setup.show');
        }

        return $next($request);
    }
}

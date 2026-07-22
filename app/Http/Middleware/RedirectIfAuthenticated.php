<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $role = auth()->user()->role?->role_slug;

            return match ($role) {
                'super_admin' => redirect()->route('dashboard.super_admin'),
                'admin' => redirect()->route('dashboard.admin'),
                'secretary' => redirect()->route('dashboard.secretary'),
                'treasurer' => redirect()->route('dashboard.treasurer'),
                'resident' => redirect()->route('dashboard.resident'),
                default => redirect()->route('login'),
            };
        }

        return $next($request);
    }
}

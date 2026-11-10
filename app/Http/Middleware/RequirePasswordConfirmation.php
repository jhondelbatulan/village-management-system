<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordConfirmation
{
    /**
     * How long a password confirmation stays valid, in seconds. This only has
     * to cover the gap between the confirmation prompt and the action itself —
     * the UI asks for the password on every protected action.
     */
    private const CONFIRMATION_TTL = 300;

    /**
     * Gate sensitive actions behind a fresh password confirmation.
     *
     * Usage: ->middleware('confirm.password')            (everyone on the route)
     *        ->middleware('confirm.password:super_admin,admin')  (only these roles)
     */
    public function handle(Request $request, Closure $next, string ...$roleSlugs): Response
    {
        // When the route is shared with roles that are not required to confirm
        // (e.g. reports are only gated for super admin and admin), everyone
        // else passes straight through.
        if (! empty($roleSlugs) && ! in_array($request->user()?->role?->role_slug, $roleSlugs, true)) {
            return $next($request);
        }

        if ($this->recentlyConfirmed($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Password confirmation required.'], 419);
        }

        if ($request->isMethod('GET')) {
            // Downloads are safe to replay: remember the URL, confirm, then
            // redirect straight back to it.
            $request->session()->put('url.intended', $request->fullUrl());
        } else {
            // Mutations cannot be replayed by a redirect, so send the user back
            // to the page that holds the form and let them resubmit it there.
            $request->session()->put('url.intended', $request->headers->get('referer') ?: url('/'));
        }

        return redirect()->route('password.confirm.show');
    }

    private function recentlyConfirmed(Request $request): bool
    {
        $confirmedAt = $request->session()->get('auth.password_confirmed_at');

        return is_numeric($confirmedAt)
            && (time() - (int) $confirmedAt) < self::CONFIRMATION_TTL;
    }
}

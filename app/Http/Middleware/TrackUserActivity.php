<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $user->forceFill(['last_activity_at' => now()])->saveQuietly();
        }

        return $next($request);
    }
}

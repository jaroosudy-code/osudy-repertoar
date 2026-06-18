<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentBand
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $user = $request->user();

        // Bypass pre system routes
        if ($request->is('switch-band/*', 'no-band', 'select-band', 'logout', 'settings*')) {
            return $next($request);
        }

        // Ak je kapela už v session a user ju naozaj má, pokračuj
        $currentId = session('current_band_id');
        if ($currentId && $user->bands()->where('band_id', $currentId)->exists()) {
            return $next($request);
        }

        $bands = $user->bands()->get();

        if ($bands->isEmpty()) {
            if ($user->isAdmin()) {
                return $next($request);
            }
            return redirect('/no-band');
        }

        if ($bands->count() === 1) {
            session(['current_band_id' => $bands->first()->id]);
            return $next($request);
        }

        // Viac kapiel bez aktívnej — vyber kapelu
        return redirect('/select-band');
    }
}

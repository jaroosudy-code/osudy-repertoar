<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role?->slug, $roles)) {
            abort(403, 'Nemáš oprávnenie na túto akciu.');
        }
        return $next($request);
    }
}

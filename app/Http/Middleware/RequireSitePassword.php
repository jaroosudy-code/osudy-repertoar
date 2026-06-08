<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSitePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('site_authenticated')) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}

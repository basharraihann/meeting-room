<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            abort(403, 'USER DOES NOT HAVE THE RIGHT ROLES.');
        }

        // Cek pakai Spatie
        if (!$request->user()->hasRole($role)) {
            abort(403, 'USER DOES NOT HAVE THE RIGHT ROLES.');
        }

        return $next($request);
    }
}
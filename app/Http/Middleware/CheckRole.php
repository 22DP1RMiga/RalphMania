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
            return redirect()->route('login');
        }

        $user = $request->user()->load('role');

        if (!$user->role || $user->role->name !== $role) {
            abort(403, 'Jums nav piekļuves tiesību');
        }

        return $next($request);
    }
}

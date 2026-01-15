<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only allows super administrators to access the route.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nepieciešama autentifikācija.'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user is a super admin
        if (!$user->is_super_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Piekļuve liegta. Nepieciešamas super administratora tiesības.'], 403);
            }
            abort(403, 'Piekļuve liegta. Nepieciešamas super administratora tiesības.');
        }

        return $next($request);
    }
}

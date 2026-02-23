<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CourierMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nepieciešama autentifikācija.'], 401);
            }
            return redirect()->route('login');
        }

        // Load role if not loaded
        $user->loadMissing('role');

        // Check if user has courier role (role_id = 4, name = 'courier')
        if (!$user->role || $user->role->name !== 'courier') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Piekļuve liegta. Jums nav kurjera tiesību.'], 403);
            }
            abort(403, 'Piekļuve liegta. Jums nav kurjera tiesību.');
        }

        // Check if courier account is active
        if (!$user->is_active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Jūsu konts ir deaktivizēts.'], 403);
            }
            abort(403, 'Jūsu konts ir deaktivizēts.');
        }

        return $next($request);
    }
}

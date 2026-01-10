<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nepieciešama autentifikācija.'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user is an administrator
        if (!$user->isAdministrator()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Piekļuve liegta. Jums nav administratora tiesību.'], 403);
            }
            abort(403, 'Piekļuve liegta. Jums nav administratora tiesību.');
        }

        // If specific permission is required, check it
        if ($permission && !$user->hasAdminPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Piekļuve liegta. Jums nav nepieciešamo tiesību.'], 403);
            }
            abort(403, 'Piekļuve liegta. Jums nav nepieciešamo tiesību.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        // Nav autentificēts
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nepieciešama autentifikācija.'], 401);
            }
            return redirect()->route('login');
        }

        // Nav administratora loma
        if (!$user->isAdministrator()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Piekļuve liegta. Jums nav administratora tiesību.'], 403);
            }

            return Inertia::render('Admin/Unauthorized', [
                'requiredPermission' => null,
                'context'            => 'admin',
                'returnUrl'          => url()->previous() ?: '/dashboard',
            ])->toResponse($request)->setStatusCode(403);
        }

        // Konkrēta atļauja nepieciešama
        if ($permission && !$user->hasAdminPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message'    => 'Piekļuve liegta. Jums nav nepieciešamo tiesību.',
                    'permission' => $permission,
                ], 403);
            }

            return Inertia::render('Admin/Unauthorized', [
                'requiredPermission' => $permission,
                'context'            => 'admin',
                'returnUrl'          => url()->previous() ?: '/admin/dashboard',
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}

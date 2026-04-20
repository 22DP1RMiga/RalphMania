<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

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

        $user->loadMissing('role');

        // Nav kurjera loma
        if (!$user->role || $user->role->name !== 'courier') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Piekļuve liegta. Jums nav kurjera tiesību.'], 403);
            }

            return Inertia::render('Courier/Unauthorized', [
                'context'   => 'courier',
                'returnUrl' => url()->previous() ?: '/login',
            ])->toResponse($request)->setStatusCode(403);
        }

        // Konts deaktivizēts
        if (!$user->is_active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Jūsu konts ir deaktivizēts.'], 403);
            }

            return Inertia::render('Courier/Unauthorized', [
                'context'   => 'courier',
                'returnUrl' => url()->previous() ?: '/login',
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}

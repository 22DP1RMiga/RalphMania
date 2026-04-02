<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class CheckPermission
{
    /**
     * Ja lietotājam nav vajadzīgās atļaujas, atgriež Inertia lapu
     * Admin/Unauthorized — nevis abort(403).
     * Lapa pielāgojas pie locale un izskatās kā UnauthorizedModal.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin vienmēr cauri
        if ($user->is_super_admin) {
            return $next($request);
        }

        // Pārbauda atļauju
        if ($user->administrator) {
            $permissions = $user->administrator->permissions ?? [];
            if (in_array($permission, $permissions)) {
                return $next($request);
            }
        }

        // JSON / API pieprasījumi
        if ($request->expectsJson()) {
            return response()->json([
                'message'    => 'Forbidden',
                'permission' => $permission,
            ], 403);
        }

        // Inertia lapa ar 403 HTTP statusu
        return Inertia::render('Admin/Unauthorized', [
            'requiredPermission' => $permission,
            'returnUrl'          => url()->previous() ?: '/admin/dashboard',
        ])->toResponse($request)->setStatusCode(403);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login');
        }

        // Super admins have all permissions
        if ($user->is_super_admin) {
            return $next($request);
        }

        // Check if user is admin and has specific permission
        if ($user->administrator) {
            $permissions = $user->administrator->permissions ?? [];

            if (in_array($permission, $permissions)) {
                return $next($request);
            }
        }

        // Permission denied
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}

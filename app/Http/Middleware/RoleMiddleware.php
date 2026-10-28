<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  array  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Admin bypass
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (empty($roles)) {
            return $next($request);
        }

        $userRoleName = $user->role ? trim($user->role->name) : null;
        $allowedRoles = array_map(function ($r) {
            return strtolower(trim($r));
        }, $roles);

        if ($userRoleName && in_array(strtolower($userRoleName), $allowedRoles)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk fitur ini.',
            ], 403);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized access.');
        }

        // Check for role switch (admin only feature)
        $effectiveRole = $request->session()->get('admin_role_switch');

        if ($effectiveRole && $request->user()->role === 'admin') {
            // Admin is viewing as another role
            // Allow admins to always access admin routes regardless of role switch
            if (in_array('admin', $roles)) {
                return $next($request);
            }
            // For non-admin routes, check the switched role
            if (!in_array($effectiveRole, $roles)) {
                abort(403, 'Unauthorized access.');
            }
        } else {
            // Normal role check
            if (!in_array($request->user()->role, $roles)) {
                abort(403, 'Unauthorized access.');
            }
        }

        return $next($request);
    }
}

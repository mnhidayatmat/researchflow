<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireProPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isPro()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'AI Assistant is available for Pro users only. Please contact your administrator to upgrade.',
                ], 403);
            }

            return redirect()->route('ai.upgrade');
        }

        return $next($request);
    }
}

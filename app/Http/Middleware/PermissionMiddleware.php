<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => 401,
                'errors' => 'Unauthenticated',
                'message' => 'Unauthorized',
                'success' => false,
            ], 401);
        }

        if (!auth()->user()->can($permission)) {
            return response()->json([
                'errors' => 'Insufficient permissions.',
                'status' => 403,
                'message' => 'Forbidden',
                'success' => false,
            ], 403);
        }

        return $next($request);
    }
}

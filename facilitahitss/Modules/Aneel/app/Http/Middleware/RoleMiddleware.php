<?php

namespace Modules\Aneel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $allowedRoles = ['admin', 'editor'];

        if (!$user || !in_array($user->role, $allowedRoles)) {
            return redirect()->back()->with('error', 'Acesso não autorizado.');
        }

        return $next($request);
    }
}

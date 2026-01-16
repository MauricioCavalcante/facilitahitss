<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $allowedRoles = ['admin'];

        if (!$user || !in_array($user->role, $allowedRoles)) {
            return redirect()->back()->with('error', 'Acesso não autorizado.');
        }

        return $next($request);
    }
}

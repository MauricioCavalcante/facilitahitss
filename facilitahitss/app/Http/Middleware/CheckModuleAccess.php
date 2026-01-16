<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ModuleUser;
use App\Models\Module;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $moduleName = $request->segment(1);

        $module = Module::where('name', $moduleName)->first();

        if (!$module) {
            return redirect("/")->with('error', 'Módulo não encontrado ou não existe!');
        }

        $hasPermission = ModuleUser::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->exists();

        if (!$hasPermission) {
            return redirect("/")->with('error', 'Você não tem permissão para acessar o módulo!');
        }

        return $next($request);
    }
}

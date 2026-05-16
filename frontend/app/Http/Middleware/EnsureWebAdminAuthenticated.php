<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWebAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('web_admin')) {
            return redirect()->route('web.admin.login')
                ->with('error', 'Debes iniciar sesion como administrador.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWebUserAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('web_user')) {
            return redirect()->route('web.login')
                ->with('error', 'Debes iniciar sesion para continuar.');
        }

        return $next($request);
    }
}

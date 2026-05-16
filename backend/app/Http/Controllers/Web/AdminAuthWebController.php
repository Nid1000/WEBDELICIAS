<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\JwtService;
use App\Services\PasswordVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAuthWebController extends Controller
{
    public function __construct(
        private readonly JwtService $jwtService,
        private readonly PasswordVerifier $passwordVerifier,
    ) {
    }

    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('web_admin')) {
            return redirect()->route('web.admin.dashboard');
        }

        return view('admin.auth.login', ['title' => 'Login administrador']);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = DB::table('administradores')->where('email', $data['email'])->first();
        if (!$admin || !(bool) $admin->activo || !$this->passwordVerifier->verify($data['password'], (string) $admin->password)) {
            return back()->withInput($request->except('password'))->withErrors([
                'email' => 'Email o contrasena incorrectos.',
            ]);
        }

        $request->session()->put([
            'web_admin' => [
                'id' => (int) $admin->id,
                'nombre' => (string) $admin->nombre,
                'email' => (string) $admin->email,
                'rol' => (string) $admin->rol,
            ],
            'auth_token' => $this->jwtService->sign([
                'id' => (int) $admin->id,
                'email' => (string) $admin->email,
                'tipo' => 'admin',
            ]),
            'auth_tipo' => 'admin',
        ]);
        $request->session()->regenerate();

        return redirect()->route('web.admin.dashboard')->with('success', 'Bienvenido al panel administrativo.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['web_admin', 'auth_token', 'auth_tipo']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('web.admin.login')->with('success', 'Sesion de administrador cerrada.');
    }
}

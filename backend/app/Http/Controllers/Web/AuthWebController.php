<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\JwtService;
use App\Services\PasswordVerifier;
use App\Support\PasswordRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthWebController extends Controller
{
    public function __construct(
        private readonly JwtService $jwtService,
        private readonly PasswordVerifier $passwordVerifier,
    ) {
    }

    public function home(Request $request): View
    {
        return view('web.home', [
            'user' => $request->session()->get('web_user'),
        ]);
    }

    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('web_user')) {
            return redirect()->route('web.home');
        }

        return view('web.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:191'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Ingresa un email valido.',
            'password.required' => 'La contrasena es obligatoria.',
        ]);

        $user = DB::table('usuarios')->where('email', $credentials['email'])->first();
        if (!$user || !(bool) $user->activo || !$this->passwordVerifier->verify($credentials['password'], (string) $user->password)) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['email' => 'Email o contrasena incorrectos.']);
        }

        $sessionUser = [
            'id' => (int) $user->id,
            'nombre' => (string) $user->nombre,
            'apellido' => (string) $user->apellido,
            'email' => (string) $user->email,
            'telefono' => $user->telefono,
            'direccion' => $user->direccion,
            'distrito' => $user->distrito,
            'numero_casa' => $user->numero_casa,
        ];

        $request->session()->put([
            'web_user' => $sessionUser,
            'auth_token' => $this->jwtService->sign([
                'id' => (int) $user->id,
                'email' => (string) $user->email,
                'tipo' => 'usuario',
            ]),
            'auth_tipo' => 'usuario',
        ]);

        $request->session()->regenerate();

        return redirect()->route('web.home')->with('success', 'Bienvenido de nuevo.');
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('web_user')) {
            return redirect()->route('web.home');
        }

        return view('web.auth.register', [
            'distritos' => DB::table('catalogo_distritos_huancayo')
                ->select(['id', 'nombre'])
                ->where('activo', 1)
                ->orderBy('orden_lista', 'asc')
                ->orderBy('nombre', 'asc')
                ->get(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:191'],
            'apellido' => ['required', 'string', 'min:2', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'password' => ['required', 'confirmed', PasswordRules::userPassword()],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['required', 'string'],
            'distrito' => ['required', 'string', 'min:2', 'max:120'],
            'numero_casa' => ['required', 'string', 'max:20'],
        ], [
            'password.confirmed' => 'La confirmacion de contrasena no coincide.',
        ]);

        $exists = DB::table('usuarios')->where('email', $data['email'])->exists();
        if ($exists) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Ya existe una cuenta con este email.']);
        }

        $id = DB::table('usuarios')->insertGetId([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'],
            'distrito' => $data['distrito'],
            'numero_casa' => $data['numero_casa'],
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sessionUser = [
            'id' => $id,
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'email' => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'],
            'distrito' => $data['distrito'],
            'numero_casa' => $data['numero_casa'],
        ];

        $request->session()->put([
            'web_user' => $sessionUser,
            'auth_token' => $this->jwtService->sign([
                'id' => $id,
                'email' => $data['email'],
                'tipo' => 'usuario',
            ]),
            'auth_tipo' => 'usuario',
        ]);

        $request->session()->regenerate();

        return redirect()->route('web.home')->with('success', 'Tu cuenta fue creada correctamente.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['web_user', 'auth_token', 'auth_tipo']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('web.login')->with('success', 'Sesion cerrada correctamente.');
    }
}

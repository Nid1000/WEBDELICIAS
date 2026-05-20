<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PasswordVerifier;
use App\Support\PasswordRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileWebController extends Controller
{
    public function __construct(
        private readonly PasswordVerifier $passwordVerifier,
    ) {
    }

    public function show(Request $request): View
    {
        $sessionUser = $request->session()->get('web_user');

        $user = DB::table('usuarios')
            ->select(['id', 'nombre', 'apellido', 'email', 'telefono', 'direccion', 'distrito', 'numero_casa', 'created_at'])
            ->where('id', (int) $sessionUser['id'])
            ->where('activo', 1)
            ->first();

        abort_unless($user, 404);

        $stats = [
            'total_pedidos' => (int) DB::table('pedidos')->where('usuario_id', (int) $user->id)->count(),
            'total_gastado' => (float) (DB::table('pedidos')
                ->where('usuario_id', (int) $user->id)
                ->where('estado', '<>', 'cancelado')
                ->sum('total') ?? 0),
        ];

        return view('web.profile.show', [
            'user' => $user,
            'stats' => $stats,
            'distritos' => DB::table('catalogo_distritos_huancayo')
                ->select(['id', 'nombre'])
                ->where('activo', 1)
                ->orderBy('orden_lista')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $sessionUser = $request->session()->get('web_user');
        $userId = (int) ($sessionUser['id'] ?? 0);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2'],
            'apellido' => ['required', 'string', 'min:2'],
            'telefono' => ['nullable', 'regex:/^9\d{8}$/'],
            'direccion' => ['required', 'string', 'min:5'],
            'distrito' => ['required', 'string', 'min:2'],
            'numero_casa' => ['required', 'string', 'min:1'],
        ]);

        DB::table('usuarios')->where('id', $userId)->update([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'],
            'distrito' => $data['distrito'],
            'numero_casa' => $data['numero_casa'],
            'updated_at' => now(),
        ]);

        $updated = DB::table('usuarios')
            ->select(['id', 'nombre', 'apellido', 'email', 'telefono', 'direccion', 'distrito', 'numero_casa'])
            ->where('id', $userId)
            ->first();

        $request->session()->put('web_user', [
            'id' => (int) $updated->id,
            'nombre' => (string) $updated->nombre,
            'apellido' => (string) $updated->apellido,
            'email' => (string) $updated->email,
            'telefono' => $updated->telefono,
            'direccion' => $updated->direccion,
            'distrito' => $updated->distrito,
            'numero_casa' => $updated->numero_casa,
        ]);

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password_actual' => ['required', 'string'],
            'password_nueva' => ['required', 'confirmed', 'different:password_actual', PasswordRules::userPassword()],
        ], [
            'password_nueva.confirmed' => 'La confirmacion de contrasena no coincide.',
            'password_nueva.different' => 'La nueva contrasena debe ser diferente a la actual.',
        ]);

        $sessionUser = $request->session()->get('web_user');
        $user = DB::table('usuarios')->where('id', (int) $sessionUser['id'])->first();

        if (!$user || !(bool) $user->activo) {
            return redirect()->route('web.login')->with('error', 'Tu sesion ya no es valida.');
        }

        if (!$this->passwordVerifier->verify($data['password_actual'], (string) $user->password)) {
            return back()->withErrors(['password_actual' => 'La contrasena actual es incorrecta.']);
        }

        DB::table('usuarios')->where('id', (int) $user->id)->update([
            'password' => Hash::make($data['password_nueva']),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Contrasena actualizada correctamente.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function passwordLooksHashed(string $hash): bool
    {
        $h = trim($hash);
        return str_starts_with($h, '$2y$') || str_starts_with($h, '$2a$') || str_starts_with($h, '$2b$');
    }

    private function verifyPassword(string $plain, string $stored): bool
    {
        $stored = (string) $stored;
        if ($stored === '') {
            return false;
        }

        // Primero intenta con el hasher de Laravel (si el hash es compatible).
        try {
            return Hash::check($plain, $stored);
        } catch (\Throwable) {
            // Compatibilidad: evita 500 si el hash no coincide con el algoritmo esperado.
        }

        // Fallback: verifica hashes bcrypt legacy ($2a$, $2b$) con password_verify.
        if ($this->passwordLooksHashed($stored)) {
            return password_verify($plain, $stored);
        }

        // Último recurso (migración): si en la BD quedó texto plano, permite login y luego se debería rehashear.
        return hash_equals($stored, $plain);
    }

    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'nombre' => ['required', 'string', 'min:2', 'max:191'],
                'apellido' => ['required', 'string', 'min:2', 'max:191'],
                'email' => ['required', 'email', 'max:191'],
                'password' => [
                    'required',
                    'string',
                    'min:6',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                ],
                'telefono' => ['nullable', 'string', 'max:20'],
                'direccion' => ['nullable', 'string'],
                'distrito' => ['nullable', 'string', 'min:2', 'max:120'],
                'numero_casa' => ['nullable', 'string', 'max:20'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'statusCode' => 400,
                'error' => 'Datos inválidos',
                'message' => 'Validación fallida',
                'details' => $e->errors(),
            ], 400);
        }

        $existing = DB::table('usuarios')->where('email', $data['email'])->first();
        if ($existing) {
            return response()->json([
                'statusCode' => 400,
                'error' => 'Email ya registrado',
                'message' => 'Ya existe una cuenta con este email',
            ], 400);
        }

        $id = DB::table('usuarios')->insertGetId([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'distrito' => $data['distrito'] ?? null,
            'numero_casa' => $data['numero_casa'] ?? null,
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = DB::table('usuarios')->select([
            'id', 'nombre', 'apellido', 'email', 'telefono', 'direccion', 'distrito', 'numero_casa',
        ])->where('id', $id)->first();

        $token = app(JwtService::class)->sign([
            'id' => $id,
            'email' => $data['email'],
            'tipo' => 'usuario',
        ]);

        return response()->json([
            'statusCode' => 201,
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email', 'max:191'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'statusCode' => 400,
                'error' => 'Datos inválidos',
                'message' => 'Validación fallida',
                'details' => $e->errors(),
            ], 400);
        }

        $user = DB::table('usuarios')->where('email', $data['email'])->first();
        if (!$user) {
            return response()->json([
                'statusCode' => 401,
                'error' => 'Credenciales inválidas',
                'message' => 'Email o contraseña incorrectos',
            ], 401);
        }
        if (!(bool) $user->activo) {
            return response()->json([
                'statusCode' => 401,
                'error' => 'Cuenta inactiva',
                'message' => 'Tu cuenta ha sido desactivada',
            ], 401);
        }
        if (!$this->verifyPassword((string) $data['password'], (string) $user->password)) {
            return response()->json([
                'statusCode' => 401,
                'error' => 'Credenciales inválidas',
                'message' => 'Email o contraseña incorrectos',
            ], 401);
        }

        $token = app(JwtService::class)->sign([
            'id' => (int) $user->id,
            'email' => (string) $user->email,
            'tipo' => 'usuario',
        ]);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Login exitoso',
            'user' => [
                'id' => (int) $user->id,
                'nombre' => (string) $user->nombre,
                'apellido' => (string) $user->apellido,
                'email' => (string) $user->email,
                'telefono' => $user->telefono,
                'direccion' => $user->direccion,
                'distrito' => $user->distrito,
                'numero_casa' => $user->numero_casa,
            ],
            'token' => $token,
        ], 200);
    }

    public function adminLogin(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email', 'max:191'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'statusCode' => 400,
                'error' => 'Datos inválidos',
                'message' => 'Validación fallida',
                'details' => $e->errors(),
            ], 400);
        }

        $admin = DB::table('administradores')->where('email', $data['email'])->first();
        if (!$admin) {
            return response()->json([
                'statusCode' => 401,
                'error' => 'Credenciales inválidas',
                'message' => 'Email o contraseña incorrectos',
            ], 401);
        }
        if (!(bool) $admin->activo) {
            return response()->json([
                'statusCode' => 401,
                'error' => 'Cuenta inactiva',
                'message' => 'Tu cuenta de administrador ha sido desactivada',
            ], 401);
        }
        if (!$this->verifyPassword((string) $data['password'], (string) $admin->password)) {
            return response()->json([
                'statusCode' => 401,
                'error' => 'Credenciales inválidas',
                'message' => 'Email o contraseña incorrectos',
            ], 401);
        }

        $token = app(JwtService::class)->sign([
            'id' => (int) $admin->id,
            'email' => (string) $admin->email,
            'tipo' => 'admin',
        ]);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Login de administrador exitoso',
            'admin' => [
                'id' => (int) $admin->id,
                'nombre' => (string) $admin->nombre,
                'email' => (string) $admin->email,
                'rol' => (string) $admin->rol,
            ],
            'token' => $token,
        ], 200);
    }

    public function verify(Request $request)
    {
        $payload = $request->attributes->get('user');
        if (!is_array($payload)) {
            return response()->json([
                'statusCode' => 401,
                'error' => 'Token inválido',
                'message' => 'Token inválido o expirado',
            ], 401);
        }

        $tipo = $payload['tipo'] ?? null;
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;
        if ($tipo === 'admin') {
            $admin = DB::table('administradores')
                ->select(['id', 'nombre', 'email', 'rol', 'activo'])
                ->where('id', $id)
                ->first();

            if (!$admin || !(bool) $admin->activo) {
                return response()->json([
                    'statusCode' => 401,
                    'error' => 'Token inválido',
                    'message' => 'Administrador no encontrado o inactivo',
                ], 401);
            }

            return response()->json([
                'statusCode' => 200,
                'message' => 'Token válido',
                'tipo' => 'admin',
                'admin' => [
                    'id' => (int) $admin->id,
                    'nombre' => (string) $admin->nombre,
                    'email' => (string) $admin->email,
                    'rol' => (string) $admin->rol,
                ],
            ], 200);
        }

        $user = DB::table('usuarios')
            ->select([
                'id', 'nombre', 'apellido', 'email', 'telefono', 'direccion', 'distrito', 'numero_casa', 'activo',
            ])
            ->where('id', $id)
            ->first();

        if (!$user || !(bool) $user->activo) {
            return response()->json([
                'statusCode' => 401,
                'error' => 'Token inválido',
                'message' => 'Usuario no encontrado o inactivo',
            ], 401);
        }

        return response()->json([
            'statusCode' => 200,
            'message' => 'Token válido',
            'tipo' => 'usuario',
            'user' => [
                'id' => (int) $user->id,
                'nombre' => (string) $user->nombre,
                'apellido' => (string) $user->apellido,
                'email' => (string) $user->email,
                'telefono' => $user->telefono,
                'direccion' => $user->direccion,
                'distrito' => $user->distrito,
                'numero_casa' => $user->numero_casa,
            ],
        ], 200);
    }
}

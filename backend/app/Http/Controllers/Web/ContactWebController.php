<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\ContactoRecibidoAdmin;
use App\Mail\ContactoRecibidoUsuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactWebController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email'],
            'telefono' => ['required', 'regex:/^9\d{8}$/'],
            'mensaje' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'telefono.required' => 'Ingresa tu numero de celular.',
            'telefono.regex' => 'El numero debe tener 9 digitos y empezar con 9.',
        ]);

        Log::info('Nuevo contacto web', $data);

        try {
            $toAddress = (string) env('CONTACT_TO_ADDRESS', env('MAIL_FROM_ADDRESS'));
            $toName = (string) env('CONTACT_TO_NAME', 'Soporte');

            if ($toAddress !== '') {
                Mail::to($toAddress, $toName)->send(new ContactoRecibidoAdmin($data));
            }

            Mail::to($data['email'])->send(new ContactoRecibidoUsuario($data));
        } catch (\Throwable $exception) {
            Log::warning('No se pudo enviar correo de contacto web', ['error' => $exception->getMessage()]);
        }

        return redirect()->to(route('web.home') . '#contacto')
            ->with('success', 'Tu mensaje fue enviado y te contactaremos pronto.');
    }
}

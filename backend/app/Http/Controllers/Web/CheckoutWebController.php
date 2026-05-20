<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\PedidosController;
use App\Support\StorefrontCart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutWebController extends Controller
{
    public function __construct(
        private readonly PedidosController $pedidosController,
        private readonly FacturacionController $facturacionController,
    ) {
    }

    public function show(Request $request): View
    {
        $user = $request->session()->get('web_user');
        $cartItems = StorefrontCart::items($request);

        return view('web.checkout', [
            'cartItems' => $cartItems,
            'cartTotal' => StorefrontCart::total($request),
            'distritos' => DB::table('catalogo_distritos_huancayo')
                ->select(['id', 'nombre'])
                ->where('activo', 1)
                ->orderBy('orden_lista')
                ->orderBy('nombre')
                ->get(),
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cartItems = StorefrontCart::items($request);
        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Tu carrito esta vacio.');
        }

        $data = $request->validate([
            'fecha_entrega' => ['required', 'date_format:Y-m-d', 'after:today'],
            'direccion_entrega' => ['required', 'string', 'min:5'],
            'distrito_entrega' => ['required', 'string', 'min:2'],
            'numero_casa_entrega' => ['required', 'string', 'min:1'],
            'telefono_contacto' => ['required', 'regex:/^9\d{8}$/'],
            'notas' => ['nullable', 'string', 'max:500'],
            'comprobante_tipo' => ['required', 'in:boleta,factura'],
            'tipo_documento' => ['required', 'in:DNI,RUC'],
            'numero_documento' => ['required', 'string'],
        ], [
            'fecha_entrega.after' => 'La fecha de entrega debe ser desde manana en adelante.',
            'telefono_contacto.regex' => 'El telefono debe tener 9 digitos y empezar con 9.',
        ]);

        if ($data['comprobante_tipo'] === 'factura' && $data['tipo_documento'] !== 'RUC') {
            return back()->withInput()->with('error', 'Para emitir factura, el documento debe ser RUC.');
        }

        if ($data['tipo_documento'] === 'DNI' && !preg_match('/^\d{8}$/', $data['numero_documento'])) {
            return back()->withInput()->with('error', 'El DNI debe tener 8 digitos.');
        }

        if ($data['tipo_documento'] === 'RUC' && !preg_match('/^\d{11}$/', $data['numero_documento'])) {
            return back()->withInput()->with('error', 'El RUC debe tener 11 digitos.');
        }

        $user = $request->session()->get('web_user');
        $payload = [
            'id' => (int) ($user['id'] ?? 0),
            'email' => (string) ($user['email'] ?? ''),
            'tipo' => 'usuario',
        ];

        $orderRequest = Request::create('/api/pedidos', 'POST', [
            'productos' => $cartItems->map(fn ($item) => ['id' => $item->id, 'cantidad' => $item->cantidad])->values()->all(),
            'fecha_entrega' => $data['fecha_entrega'],
            'direccion_entrega' => $data['direccion_entrega'],
            'distrito_entrega' => $data['distrito_entrega'],
            'numero_casa_entrega' => $data['numero_casa_entrega'],
            'telefono_contacto' => $data['telefono_contacto'],
            'notas' => $data['notas'] ?? null,
        ]);
        $orderRequest->setLaravelSession($request->session());
        $orderRequest->attributes->set('user', $payload);

        $orderResponse = $this->pedidosController->store($orderRequest);
        $orderPayload = $orderResponse->getData(true);

        if ($orderResponse->getStatusCode() >= 400) {
            return back()->withInput()->with('error', (string) ($orderPayload['message'] ?? 'No se pudo crear el pedido.'));
        }

        $pedidoId = (int) ($orderPayload['pedido']['id'] ?? 0);

        $invoiceRequest = Request::create('/api/facturacion/emitir', 'POST', [
            'pedido_id' => $pedidoId,
            'comprobante_tipo' => $data['comprobante_tipo'],
            'tipo_documento' => $data['tipo_documento'],
            'numero_documento' => $data['numero_documento'],
        ]);
        $invoiceRequest->setLaravelSession($request->session());
        $invoiceRequest->attributes->set('user', $payload);
        if (env('DECOLECTA_TOKEN')) {
            $invoiceRequest->headers->set('X-Decolecta-Token', (string) env('DECOLECTA_TOKEN'));
        }

        $invoiceResponse = $this->facturacionController->emitir($invoiceRequest);
        $invoicePayload = $invoiceResponse->getData(true);

        StorefrontCart::clear($request);

        if ($invoiceResponse->getStatusCode() >= 400) {
            return redirect()->route('web.orders.show', $pedidoId)
                ->with('success', 'Pedido creado correctamente. El comprobante podra emitirse despues.')
                ->with('error', (string) ($invoicePayload['message'] ?? 'No se pudo emitir el comprobante.'));
        }

        return redirect()->route('web.orders.show', $pedidoId)
            ->with('success', 'Pedido creado y comprobante emitido correctamente.');
    }
}

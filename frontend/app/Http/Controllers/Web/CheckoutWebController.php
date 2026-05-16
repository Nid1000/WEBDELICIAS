<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BackendApiClient;
use App\Support\StorefrontCart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutWebController extends Controller
{
    public function __construct(
        private readonly BackendApiClient $api,
    ) {
    }

    public function show(Request $request): View
    {
        $cartItems = StorefrontCart::items($request);
        $districtsResponse = $this->api->get('usuarios/distritos-huancayo');

        return view('web.checkout', [
            'cartItems' => $cartItems,
            'cartTotal' => StorefrontCart::total($request),
            'distritos' => collect($this->api->okData($districtsResponse, 'distritos', [])),
            'user' => $request->session()->get('web_user'),
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

        $orderResponse = $this->api->post('pedidos', [
            'productos' => $cartItems->map(fn ($item) => ['id' => $item->id, 'cantidad' => $item->cantidad])->values()->all(),
            'fecha_entrega' => $data['fecha_entrega'],
            'direccion_entrega' => $data['direccion_entrega'],
            'distrito_entrega' => $data['distrito_entrega'],
            'numero_casa_entrega' => $data['numero_casa_entrega'],
            'telefono_contacto' => $data['telefono_contacto'],
            'notas' => $data['notas'] ?? null,
        ]);
        $orderPayload = $orderResponse->getData(true);

        if ($orderResponse->getStatusCode() >= 400) {
            return back()->withInput()->with('error', $this->api->errorMessage($orderResponse, 'No se pudo crear el pedido.'));
        }

        $pedidoId = (int) data_get($orderResponse->json(), 'pedido.id', 0);

        $invoiceResponse = $this->api->post('facturacion/emitir', [
            'pedido_id' => $pedidoId,
            'comprobante_tipo' => $data['comprobante_tipo'],
            'tipo_documento' => $data['tipo_documento'],
            'numero_documento' => $data['numero_documento'],
        ]);

        StorefrontCart::clear($request);

        if ($invoiceResponse->getStatusCode() >= 400) {
            return redirect()->route('web.orders.show', $pedidoId)
                ->with('success', 'Pedido creado correctamente. El comprobante podra emitirse despues.')
                ->with('error', $this->api->errorMessage($invoiceResponse, 'No se pudo emitir el comprobante.'));
        }

        return redirect()->route('web.orders.show', $pedidoId)
            ->with('success', 'Pedido creado y comprobante emitido correctamente.');
    }
}

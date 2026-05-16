<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\StorefrontCart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersWebController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->session()->get('web_user');
        $userId = (int) ($user['id'] ?? 0);

        $orders = DB::table('pedidos')
            ->where('usuario_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($order) {
                $order->total = (float) $order->total;
                return $order;
            });

        $orderIds = $orders->pluck('id')->all();
        $details = count($orderIds) > 0
            ? DB::table('pedido_detalles')->whereIn('pedido_id', $orderIds)->get()->groupBy('pedido_id')
            : collect();

        $receipts = DB::table('comprobantes')
            ->whereIn('pedido_id', $orderIds ?: [0])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($receipt) {
                $fileBase = "pedido-{$receipt->pedido_id}-{$receipt->serie}-" . str_pad((string) $receipt->numero, 8, '0', STR_PAD_LEFT);
                $receipt->pdf_url = asset('uploads/' . str_replace('\\', '/', (string) $receipt->archivo_ruta));
                $receipt->xml_url = asset("uploads/comprobantes/{$fileBase}.xml");
                $receipt->img_url = asset("uploads/comprobantes/{$fileBase}.svg");
                return $receipt;
            });

        return view('web.orders.index', [
            'orders' => $orders->map(function ($order) use ($details) {
                $order->lineas = $details[(int) $order->id] ?? collect();
                $order->total_productos = $order->lineas->sum('cantidad');
                return $order;
            }),
            'receipts' => $receipts,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $user = $request->session()->get('web_user');
        $userId = (int) ($user['id'] ?? 0);

        $order = DB::table('pedidos')->where('id', $id)->where('usuario_id', $userId)->first();
        abort_unless($order, 404);

        $details = DB::table('pedido_detalles')
            ->leftJoin('productos', 'productos.id', '=', 'pedido_detalles.producto_id')
            ->select([
                'pedido_detalles.*',
                'productos.nombre as producto_nombre',
                'productos.imagen as producto_imagen',
            ])
            ->where('pedido_detalles.pedido_id', $id)
            ->get()
            ->map(function ($detail) {
                $detail->precio_unitario = (float) $detail->precio_unitario;
                $detail->subtotal = (float) $detail->subtotal;
                $detail->producto_imagen_url = StorefrontCart::imageUrl($detail->producto_imagen);
                return $detail;
            });

        $receipt = DB::table('comprobantes')->where('pedido_id', $id)->orderByDesc('created_at')->first();
        if ($receipt) {
            $fileBase = "pedido-{$receipt->pedido_id}-{$receipt->serie}-" . str_pad((string) $receipt->numero, 8, '0', STR_PAD_LEFT);
            $receipt->pdf_url = asset('uploads/' . str_replace('\\', '/', (string) $receipt->archivo_ruta));
            $receipt->xml_url = asset("uploads/comprobantes/{$fileBase}.xml");
            $receipt->img_url = asset("uploads/comprobantes/{$fileBase}.svg");
        }

        return view('web.orders.show', [
            'order' => $order,
            'details' => $details,
            'receipt' => $receipt,
        ]);
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $user = $request->session()->get('web_user');
        $userId = (int) ($user['id'] ?? 0);

        $order = DB::table('pedidos')->where('id', $id)->where('usuario_id', $userId)->first();
        if (!$order) {
            return back()->with('error', 'No se encontro el pedido.');
        }

        if (in_array((string) $order->estado, ['entregado', 'cancelado', 'listo'], true)) {
            return back()->with('error', 'El pedido no puede cancelarse en su estado actual.');
        }

        DB::table('pedidos')->where('id', $id)->update([
            'estado' => 'cancelado',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Pedido cancelado correctamente.');
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\StorefrontCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartWebController extends Controller
{
    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'producto_id' => ['required', 'integer', 'min:1'],
            'cantidad' => ['nullable', 'integer', 'min:1'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $product = DB::table('productos')
            ->select(['id', 'nombre', 'stock', 'activo'])
            ->where('id', (int) $data['producto_id'])
            ->first();

        if (!$product || !(bool) $product->activo) {
            return back()->with('error', 'El producto seleccionado no existe.');
        }

        if ((int) $product->stock <= 0) {
            return back()->with('error', 'Este producto no tiene stock disponible.');
        }

        StorefrontCart::add($request, (int) $product->id, (int) ($data['cantidad'] ?? 1));

        $target = !empty($data['redirect_to']) ? $data['redirect_to'] : url()->previous();

        return redirect()->to($target)->with('success', $product->nombre . ' fue agregado al carrito.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'cantidad' => ['required', 'integer', 'min:0'],
        ]);

        StorefrontCart::update($request, $id, (int) $data['cantidad']);

        return back()->with('success', 'Carrito actualizado.');
    }

    public function clear(Request $request): RedirectResponse
    {
        StorefrontCart::clear($request);

        return back()->with('success', 'Carrito vaciado.');
    }
}

<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StorefrontCart
{
    public static function imageUrl(null|string $image, string $fallback = '/images/products/alfajores.jpg'): string
    {
        $value = trim((string) $image);
        if ($value === '') {
            return asset(ltrim($fallback, '/'));
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $value = str_replace('\\', '/', $value);
        if (str_starts_with($value, '/')) {
            return asset(ltrim($value, '/'));
        }

        return asset('uploads/' . ltrim($value, '/'));
    }

    public static function items(Request $request): Collection
    {
        $raw = collect($request->session()->get('storefront_cart', []));
        if ($raw->isEmpty()) {
            return collect();
        }

        $productIds = $raw->keys()->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->values()->all();
        if (count($productIds) === 0) {
            return collect();
        }

        $products = DB::table('productos')
            ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->select([
                'productos.id',
                'productos.nombre',
                'productos.precio',
                'productos.imagen',
                'productos.stock',
                'productos.destacado',
                'productos.categoria_id',
                'categorias.nombre as categoria_nombre',
            ])
            ->whereIn('productos.id', $productIds)
            ->where('productos.activo', 1)
            ->get()
            ->keyBy('id');

        $items = $raw->map(function ($item, $productId) use ($products) {
            $product = $products[(int) $productId] ?? null;
            if (!$product) {
                return null;
            }

            $quantity = max(1, min((int) ($item['cantidad'] ?? 1), max(1, (int) $product->stock)));
            $price = (float) $product->precio;

            return (object) [
                'id' => (int) $product->id,
                'nombre' => (string) $product->nombre,
                'precio' => $price,
                'cantidad' => $quantity,
                'stock' => (int) $product->stock,
                'imagen' => $product->imagen,
                'imagen_url' => self::imageUrl($product->imagen),
                'categoria_id' => $product->categoria_id ? (int) $product->categoria_id : null,
                'categoria_nombre' => $product->categoria_nombre ?: null,
                'subtotal' => $price * $quantity,
            ];
        })->filter()->values();

        self::persistNormalized($request, $items);

        return $items;
    }

    public static function count(Request $request): int
    {
        return self::items($request)->sum('cantidad');
    }

    public static function total(Request $request): float
    {
        return (float) self::items($request)->sum('subtotal');
    }

    public static function add(Request $request, int $productId, int $quantity = 1): void
    {
        $cart = $request->session()->get('storefront_cart', []);
        $current = (int) (($cart[$productId]['cantidad'] ?? 0));
        $cart[$productId] = ['cantidad' => max(1, $current + $quantity)];
        $request->session()->put('storefront_cart', $cart);
        self::items($request);
    }

    public static function update(Request $request, int $productId, int $quantity): void
    {
        $cart = $request->session()->get('storefront_cart', []);
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = ['cantidad' => $quantity];
        }
        $request->session()->put('storefront_cart', $cart);
        self::items($request);
    }

    public static function clear(Request $request): void
    {
        $request->session()->forget('storefront_cart');
    }

    private static function persistNormalized(Request $request, Collection $items): void
    {
        $normalized = $items->mapWithKeys(fn ($item) => [
            $item->id => ['cantidad' => $item->cantidad],
        ])->all();

        $request->session()->put('storefront_cart', $normalized);
    }
}

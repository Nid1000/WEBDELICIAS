<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StorefrontController extends Controller
{
    public function home(Request $request): View
    {
        $featuredProducts = $this->mapProducts(
            DB::table('productos')
                ->where('activo', 1)
                ->orderByDesc('destacado')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
        );

        $categories = DB::table('categorias')
            ->select(['id', 'nombre', 'descripcion', 'imagen'])
            ->where('activo', 1)
            ->orderBy('nombre')
            ->limit(3)
            ->get()
            ->map(function ($category) {
                $category->imagen_url = $this->resolvePublicImage($category->imagen, '/images/categories/pan.png');
                return $category;
            });

        return view('web.home', [
            'user' => $request->session()->get('web_user'),
            'featuredProducts' => $featuredProducts,
            'homeCategories' => $categories,
            'galleryImages' => [
                asset('images/products/alfajores.jpg'),
                asset('images/products/delikeik.jpg'),
                asset('images/products/karamanduka.jpg'),
                asset('images/products/pionono.jpg'),
                asset('images/products/tostadas.jpg'),
            ],
            'testimonials' => [
                [
                    'nombre' => 'Maria G.',
                    'texto' => 'Las tortas personalizadas son espectaculares. La decoracion y el sabor superaron mis expectativas.',
                ],
                [
                    'nombre' => 'Luis P.',
                    'texto' => 'El pan artesanal siempre esta fresco y crujiente. La atencion es amable y rapida.',
                ],
                [
                    'nombre' => 'Andrea R.',
                    'texto' => 'Los alfajores y piononos son mis favoritos. Perfectos para compartir en familia.',
                ],
            ],
        ]);
    }

    public function products(Request $request): View
    {
        $query = DB::table('productos')
            ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->select([
                'productos.*',
                'categorias.nombre as categoria_nombre',
            ])
            ->where('productos.activo', 1);

        $filters = [
            'buscar' => trim((string) $request->query('buscar', '')),
            'categoria' => (int) $request->query('categoria', 0),
            'precioMin' => $request->query('precioMin'),
            'precioMax' => $request->query('precioMax'),
            'destacado' => $request->boolean('destacado'),
            'disponible' => $request->query('disponible', '1') !== '0',
            'orden' => (string) $request->query('orden', 'nombre'),
        ];

        if ($filters['buscar'] !== '') {
            $query->where(function ($builder) use ($filters): void {
                $builder->where('productos.nombre', 'like', '%' . $filters['buscar'] . '%')
                    ->orWhere('productos.descripcion', 'like', '%' . $filters['buscar'] . '%');
            });
        }

        if ($filters['categoria'] > 0) {
            $query->where('productos.categoria_id', $filters['categoria']);
        }

        if ($filters['disponible']) {
            $query->where('productos.stock', '>', 0);
        }

        if ($filters['destacado']) {
            $query->where('productos.destacado', 1);
        }

        if (is_numeric($filters['precioMin'])) {
            $query->where('productos.precio', '>=', (float) $filters['precioMin']);
        }

        if (is_numeric($filters['precioMax'])) {
            $query->where('productos.precio', '<=', (float) $filters['precioMax']);
        }

        match ($filters['orden']) {
            'precio_asc' => $query->orderBy('productos.precio', 'asc'),
            'precio_desc' => $query->orderBy('productos.precio', 'desc'),
            'destacado' => $query->orderBy('productos.destacado', 'desc')->orderBy('productos.nombre', 'asc'),
            default => $query->orderBy('productos.nombre', 'asc'),
        };

        $perPage = (int) $request->query('limite', 12);
        if (!in_array($perPage, [12, 24, 36], true)) {
            $perPage = 12;
        }

        /** @var LengthAwarePaginator $products */
        $products = $query->paginate($perPage)->withQueryString();
        $products->setCollection($this->mapProducts($products->getCollection()));

        $categories = DB::table('categorias')
            ->select(['id', 'nombre'])
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view('web.products.index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'perPage' => $perPage,
        ]);
    }

    public function showProduct(int $id): View
    {
        $product = DB::table('productos')
            ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->select([
                'productos.*',
                'categorias.nombre as categoria_nombre',
                'categorias.descripcion as categoria_descripcion',
            ])
            ->where('productos.id', $id)
            ->where('productos.activo', 1)
            ->first();

        abort_unless($product, 404);

        $product = $this->mapProduct($product);

        $related = $this->mapProducts(
            DB::table('productos')
                ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
                ->select(['productos.*', 'categorias.nombre as categoria_nombre'])
                ->where('productos.activo', 1)
                ->where('productos.id', '!=', $id)
                ->when($product->categoria_id, fn ($query) => $query->where('productos.categoria_id', $product->categoria_id))
                ->orderByDesc('productos.destacado')
                ->orderByDesc('productos.created_at')
                ->limit(4)
                ->get()
        );

        return view('web.products.show', [
            'product' => $product,
            'relatedProducts' => $related,
        ]);
    }

    private function mapProducts(Collection $products): Collection
    {
        return $products->map(fn ($product) => $this->mapProduct($product));
    }

    private function mapProduct(object $product): object
    {
        $product->precio = (float) $product->precio;
        $product->imagen_url = $this->resolvePublicImage($product->imagen, '/images/products/alfajores.jpg');
        $product->agotado = ((int) ($product->stock ?? 0)) <= 0;
        $product->stock_bajo = ((int) ($product->stock ?? 0)) > 0 && ((int) $product->stock <= 5);

        return $product;
    }

    private function resolvePublicImage(null|string $image, string $fallback): string
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
}

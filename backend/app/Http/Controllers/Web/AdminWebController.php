<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\NotificacionesService;
use App\Support\SiteSettings;
use App\Support\StorefrontCart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminWebController extends Controller
{
    public function __construct(
        private readonly NotificacionesService $notificaciones,
    ) {
    }

    public function dashboard(): View
    {
        $receipts = DB::table('comprobantes')->orderByDesc('created_at')->limit(200)->get();

        $weeklySales = $receipts
            ->filter(fn ($item) => $item->created_at && now()->diffInDays($item->created_at) <= 7)
            ->reduce(fn ($carry, $item) => $carry + (float) ($item->total ?? 0), 0.0);

        $salesSeries = DB::table('pedidos')
            ->select([DB::raw("DATE(created_at) as fecha"), DB::raw("SUM(total) as total")])
            ->where('estado', '<>', 'cancelado')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy(DB::raw("DATE(created_at)"))
            ->get();

        return view('admin.dashboard', [
            'metrics' => [
                'productos' => (int) DB::table('productos')->where('activo', 1)->count(),
                'categorias' => (int) DB::table('categorias')->where('activo', 1)->count(),
                'usuarios' => (int) DB::table('usuarios')->count(),
                'ventasSemana' => $weeklySales,
            ],
            'salesSeries' => $salesSeries,
            'receiptTypes' => [
                'boleta' => (int) $receipts->where('tipo', 'boleta')->count(),
                'factura' => (int) $receipts->where('tipo', 'factura')->count(),
            ],
        ]);
    }

    public function categoriesIndex(Request $request): View
    {
        $query = DB::table('categorias')->orderBy('nombre');
        if ($search = trim((string) $request->query('buscar', ''))) {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        return view('admin.categories.index', [
            'categories' => $query->paginate(20)->withQueryString(),
            'search' => (string) $request->query('buscar', ''),
        ]);
    }

    public function categoriesCreate(): View
    {
        return view('admin.categories.create');
    }

    public function categoriesStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'imagen' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,jfif'],
            'imagen_url' => ['nullable', 'url', 'max:500'],
        ]);

        $duplicate = DB::table('categorias')->where('nombre', trim($data['nombre']))->exists();
        if ($duplicate) {
            return back()->withInput()->with('error', 'Ya existe una categoria con ese nombre.');
        }

        $image = $this->storeOptionalImage($request, 'imagen', 'categorias') ?: ($data['imagen_url'] ?? null);

        $id = DB::table('categorias')->insertGetId([
            'nombre' => trim($data['nombre']),
            'descripcion' => $data['descripcion'] ?? null,
            'imagen' => $image,
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('web.admin.categories.edit', $id)->with('success', 'Categoria creada correctamente.');
    }

    public function categoriesEdit(int $id): View
    {
        $category = DB::table('categorias')->where('id', $id)->first();
        abort_unless($category, 404);
        $category->imagen_url = StorefrontCart::imageUrl($category->imagen, '/images/categories/pan.png');

        return view('admin.categories.edit', ['category' => $category]);
    }

    public function categoriesUpdate(Request $request, int $id): RedirectResponse
    {
        $category = DB::table('categorias')->where('id', $id)->first();
        if (!$category) {
            return back()->with('error', 'Categoria no encontrada.');
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'imagen' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,jfif'],
            'imagen_url' => ['nullable', 'url', 'max:500'],
        ]);

        $duplicate = DB::table('categorias')
            ->where('nombre', trim($data['nombre']))
            ->where('id', '<>', $id)
            ->exists();
        if ($duplicate) {
            return back()->withInput()->with('error', 'Ya existe otra categoria con ese nombre.');
        }

        $image = $this->storeOptionalImage($request, 'imagen', 'categorias');

        DB::table('categorias')->where('id', $id)->update([
            'nombre' => trim($data['nombre']),
            'descripcion' => $data['descripcion'] ?? null,
            'imagen' => $image ?: ($data['imagen_url'] ?? $category->imagen),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Categoria actualizada.');
    }

    public function categoriesToggle(int $id): RedirectResponse
    {
        $category = DB::table('categorias')->where('id', $id)->first();
        if (!$category) {
            return back()->with('error', 'Categoria no encontrada.');
        }

        DB::table('categorias')->where('id', $id)->update([
            'activo' => $category->activo ? 0 : 1,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Estado de la categoria actualizado.');
    }

    public function categoriesDelete(int $id): RedirectResponse
    {
        DB::table('categorias')->where('id', $id)->update([
            'activo' => 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('web.admin.categories.index')->with('success', 'Categoria desactivada.');
    }

    public function productsIndex(Request $request): View
    {
        $query = DB::table('productos')
            ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->select(['productos.*', 'categorias.nombre as categoria_nombre'])
            ->where('productos.activo', 1)
            ->orderBy('productos.created_at', 'desc');

        if ($search = trim((string) $request->query('buscar', ''))) {
            $query->where('productos.nombre', 'like', '%' . $search . '%');
        }
        if ($categoryId = (int) $request->query('categoria', 0)) {
            $query->where('productos.categoria_id', $categoryId);
        }
        if ($request->boolean('stock_bajo')) {
            $query->where('productos.stock', '<=', 5);
        }

        $products = $query->paginate(20)->withQueryString();
        $products->getCollection()->transform(function ($product) {
            $product->precio = (float) $product->precio;
            $product->imagen_url = StorefrontCart::imageUrl($product->imagen);
            return $product;
        });

        return view('admin.products.index', [
            'products' => $products,
            'categories' => DB::table('categorias')->where('activo', 1)->orderBy('nombre')->get(),
            'filters' => $request->only(['buscar', 'categoria', 'stock_bajo']),
        ]);
    }

    public function productsCreate(): View
    {
        return view('admin.products.create', [
            'categories' => DB::table('categorias')->where('activo', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function productsStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'precio' => ['required', 'numeric', 'min:0'],
            'categoria_id' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'destacado' => ['nullable', 'boolean'],
            'imagen' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,jfif'],
            'imagen_url' => ['nullable', 'url', 'max:500'],
        ]);

        $category = DB::table('categorias')->where('id', (int) $data['categoria_id'])->where('activo', 1)->first();
        if (!$category) {
            return back()->withInput()->with('error', 'La categoria seleccionada no existe.');
        }

        $image = $this->storeOptionalImage($request, 'imagen', 'productos') ?: ($data['imagen_url'] ?? null);

        $id = DB::table('productos')->insertGetId([
            'nombre' => trim($data['nombre']),
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => (string) $data['precio'],
            'categoria_id' => (int) $data['categoria_id'],
            'imagen' => $image,
            'stock' => (int) $data['stock'],
            'destacado' => $request->boolean('destacado') ? 1 : 0,
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->notificaciones->broadcastNewProduct($id, trim($data['nombre']));
        } catch (\Throwable) {
        }

        return redirect()->route('web.admin.products.edit', $id)->with('success', 'Producto creado correctamente.');
    }

    public function productsEdit(int $id): View
    {
        $product = DB::table('productos')->where('id', $id)->first();
        abort_unless($product, 404);
        $product->precio = (float) $product->precio;
        $product->imagen_url = StorefrontCart::imageUrl($product->imagen);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => DB::table('categorias')->where('activo', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function productsUpdate(Request $request, int $id): RedirectResponse
    {
        $product = DB::table('productos')->where('id', $id)->first();
        if (!$product) {
            return back()->with('error', 'Producto no encontrado.');
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'precio' => ['required', 'numeric', 'min:0'],
            'categoria_id' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'destacado' => ['nullable', 'boolean'],
            'imagen' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,jfif'],
            'imagen_url' => ['nullable', 'url', 'max:500'],
        ]);

        $image = $this->storeOptionalImage($request, 'imagen', 'productos');

        DB::table('productos')->where('id', $id)->update([
            'nombre' => trim($data['nombre']),
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => (string) $data['precio'],
            'categoria_id' => (int) $data['categoria_id'],
            'imagen' => $image ?: ($data['imagen_url'] ?? $product->imagen),
            'stock' => (int) $data['stock'],
            'destacado' => $request->boolean('destacado') ? 1 : 0,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Producto actualizado.');
    }

    public function productsToggleFeatured(int $id): RedirectResponse
    {
        $product = DB::table('productos')->where('id', $id)->first();
        if (!$product) {
            return back()->with('error', 'Producto no encontrado.');
        }

        DB::table('productos')->where('id', $id)->update([
            'destacado' => $product->destacado ? 0 : 1,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Preferencia de destacado actualizada.');
    }

    public function productsDelete(int $id): RedirectResponse
    {
        DB::table('productos')->where('id', $id)->update([
            'activo' => 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('web.admin.products.index')->with('success', 'Producto desactivado.');
    }

    public function usersIndex(Request $request): View
    {
        $query = DB::table('usuarios')->orderByDesc('created_at');
        if ($search = trim((string) $request->query('buscar', ''))) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('apellido', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('telefono', 'like', '%' . $search . '%');
            });
        }
        if ($status = $request->query('estado')) {
            $query->where('activo', $status === 'activos' ? 1 : 0);
        }

        return view('admin.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['buscar', 'estado']),
        ]);
    }

    public function usersShow(int $id): View
    {
        $user = DB::table('usuarios')->where('id', $id)->first();
        abort_unless($user, 404);

        $stats = [
            'total_pedidos' => (int) DB::table('pedidos')->where('usuario_id', $id)->count(),
            'total_gastado' => (float) (DB::table('pedidos')->where('usuario_id', $id)->where('estado', '<>', 'cancelado')->sum('total') ?? 0),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function usersUpdate(Request $request, int $id): RedirectResponse
    {
        $user = DB::table('usuarios')->where('id', $id)->first();
        if (!$user) {
            return back()->with('error', 'Usuario no encontrado.');
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2'],
            'apellido' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'distrito' => ['nullable', 'string', 'max:120'],
            'numero_casa' => ['nullable', 'string', 'max:20'],
        ]);

        $duplicate = DB::table('usuarios')->where('email', $data['email'])->where('id', '<>', $id)->exists();
        if ($duplicate) {
            return back()->withInput()->with('error', 'Ya existe otro usuario con ese email.');
        }

        DB::table('usuarios')->where('id', $id)->update([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'email' => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'distrito' => $data['distrito'] ?? null,
            'numero_casa' => $data['numero_casa'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Usuario actualizado.');
    }

    public function usersToggle(int $id): RedirectResponse
    {
        $user = DB::table('usuarios')->where('id', $id)->first();
        if (!$user) {
            return back()->with('error', 'Usuario no encontrado.');
        }

        DB::table('usuarios')->where('id', $id)->update([
            'activo' => $user->activo ? 0 : 1,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function ordersIndex(Request $request): View
    {
        $query = DB::table('pedidos')
            ->leftJoin('usuarios', 'usuarios.id', '=', 'pedidos.usuario_id')
            ->select([
                'pedidos.*',
                'usuarios.nombre as usuario_nombre',
                'usuarios.apellido as usuario_apellido',
                'usuarios.email as usuario_email',
            ])
            ->orderByDesc('pedidos.created_at');

        if ($search = trim((string) $request->query('buscar', ''))) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('pedidos.notas', 'like', '%' . $search . '%')
                    ->orWhere('pedidos.direccion_entrega', 'like', '%' . $search . '%')
                    ->orWhere('pedidos.telefono_contacto', 'like', '%' . $search . '%')
                    ->orWhere('usuarios.nombre', 'like', '%' . $search . '%')
                    ->orWhere('usuarios.apellido', 'like', '%' . $search . '%')
                    ->orWhere('usuarios.email', 'like', '%' . $search . '%');
            });
        }
        if ($status = (string) $request->query('estado', '')) {
            $query->where('pedidos.estado', $status);
        }
        if ($from = $request->query('desde')) {
            $query->where('pedidos.created_at', '>=', $from);
        }
        if ($to = $request->query('hasta')) {
            $query->where('pedidos.created_at', '<=', $to);
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['buscar', 'estado', 'desde', 'hasta']),
        ]);
    }

    public function ordersShow(int $id): View
    {
        $order = DB::table('pedidos')
            ->leftJoin('usuarios', 'usuarios.id', '=', 'pedidos.usuario_id')
            ->select([
                'pedidos.*',
                'usuarios.nombre as usuario_nombre',
                'usuarios.apellido as usuario_apellido',
                'usuarios.email as usuario_email',
                'usuarios.telefono as usuario_telefono',
            ])
            ->where('pedidos.id', $id)
            ->first();
        abort_unless($order, 404);
        $order->total = (float) $order->total;

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

        return view('admin.orders.show', compact('order', 'details'));
    }

    public function ordersUpdateState(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,listo,entregado,cancelado'],
        ]);

        $order = DB::table('pedidos')->where('id', $id)->first();
        if (!$order) {
            return back()->with('error', 'Pedido no encontrado.');
        }

        DB::table('pedidos')->where('id', $id)->update([
            'estado' => $data['estado'],
            'updated_at' => now(),
        ]);

        if ($order->usuario_id && $data['estado'] === 'listo') {
            try {
                $this->notificaciones->createForUser([
                    'userId' => (int) $order->usuario_id,
                    'title' => 'Tu pedido esta listo',
                    'body' => "El pedido #{$id} ya esta listo para seguimiento.",
                    'type' => 'order_ready',
                    'audience' => 'both',
                    'route' => 'order',
                    'targetId' => $id,
                ]);
            } catch (\Throwable) {
            }
        }

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function ordersUpdateShipping(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'salida_reparto_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'conductor' => ['nullable', 'string', 'max:191'],
            'vehiculo' => ['nullable', 'string', 'max:191'],
        ]);

        DB::table('pedidos')->where('id', $id)->update([
            'salida_reparto_at' => !empty($data['salida_reparto_at']) ? str_replace('T', ' ', $data['salida_reparto_at']) . ':00' : null,
            'conductor' => $data['conductor'] ?? null,
            'vehiculo' => $data['vehiculo'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Datos de reparto actualizados.');
    }

    public function ordersUpdateDeliveryDate(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'fecha_entrega' => ['nullable', 'date_format:Y-m-d'],
        ]);

        DB::table('pedidos')->where('id', $id)->update([
            'fecha_entrega' => $data['fecha_entrega'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Fecha de entrega actualizada.');
    }

    public function receiptsIndex(Request $request): View
    {
        $query = DB::table('comprobantes as c')
            ->join('pedidos as p', 'p.id', '=', 'c.pedido_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 'p.usuario_id')
            ->select([
                'c.*',
                'p.total as pedido_total',
                'p.id as pedido_id',
                'u.nombre as usuario_nombre',
                'u.apellido as usuario_apellido',
            ])
            ->orderByDesc('c.created_at');

        if ($type = (string) $request->query('tipo', '')) {
            $query->where('c.tipo', $type);
        }
        if ($search = trim((string) $request->query('buscar', ''))) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('c.numero_formateado', 'like', '%' . $search . '%')
                    ->orWhere('u.nombre', 'like', '%' . $search . '%')
                    ->orWhere('u.apellido', 'like', '%' . $search . '%');
            });
        }

        $receipts = $query->paginate(20)->withQueryString();
        $receipts->getCollection()->transform(function ($receipt) {
            $fileBase = "pedido-{$receipt->pedido_id}-{$receipt->serie}-" . str_pad((string) $receipt->numero, 8, '0', STR_PAD_LEFT);
            $receipt->pedido_total = (float) $receipt->pedido_total;
            $receipt->pdf_url = asset('uploads/' . str_replace('\\', '/', (string) $receipt->archivo_ruta));
            $receipt->xml_url = asset("uploads/comprobantes/{$fileBase}.xml");
            $receipt->img_url = asset("uploads/comprobantes/{$fileBase}.svg");
            return $receipt;
        });

        return view('admin.receipts.index', [
            'receipts' => $receipts,
            'filters' => $request->only(['tipo', 'buscar']),
        ]);
    }

    public function reportsIndex(Request $request): View
    {
        $mode = (string) $request->query('modo', 'diario');
        $from = $request->query('desde');
        $to = $request->query('hasta');

        $seriesQuery = DB::table('pedidos')->where('estado', '<>', 'cancelado');
        $productQuery = DB::table('pedido_detalles as d')
            ->join('pedidos as p', 'p.id', '=', 'd.pedido_id')
            ->leftJoin('productos as pr', 'pr.id', '=', 'd.producto_id')
            ->where('p.estado', '<>', 'cancelado');
        $categoryQuery = DB::table('pedido_detalles as d')
            ->join('pedidos as p', 'p.id', '=', 'd.pedido_id')
            ->leftJoin('productos as pr', 'pr.id', '=', 'd.producto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'pr.categoria_id')
            ->where('p.estado', '<>', 'cancelado');

        if ($from) {
            $seriesQuery->where('created_at', '>=', $from);
            $productQuery->where('p.created_at', '>=', $from);
            $categoryQuery->where('p.created_at', '>=', $from);
        }
        if ($to) {
            $seriesQuery->where('created_at', '<=', $to);
            $productQuery->where('p.created_at', '<=', $to);
            $categoryQuery->where('p.created_at', '<=', $to);
        }

        if ($mode === 'semanal') {
            $series = $seriesQuery
                ->select([DB::raw("DATE_SUB(DATE(created_at), INTERVAL (WEEKDAY(created_at)) DAY) as label"), DB::raw("SUM(total) as total")])
                ->groupBy(DB::raw("DATE_SUB(DATE(created_at), INTERVAL (WEEKDAY(created_at)) DAY)"))
                ->orderBy(DB::raw("DATE_SUB(DATE(created_at), INTERVAL (WEEKDAY(created_at)) DAY)"))
                ->get();
        } elseif ($mode === 'mensual') {
            $series = $seriesQuery
                ->select([DB::raw("DATE_FORMAT(created_at, '%Y-%m') as label"), DB::raw("SUM(total) as total")])
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->get();
        } else {
            $series = $seriesQuery
                ->select([DB::raw("DATE(created_at) as label"), DB::raw("SUM(total) as total")])
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy(DB::raw("DATE(created_at)"))
                ->get();
        }

        $topProducts = $productQuery
            ->select([
                'd.producto_id',
                DB::raw('MAX(pr.nombre) as nombre'),
                DB::raw('MAX(pr.imagen) as imagen'),
                DB::raw('SUM(d.cantidad) as cantidad'),
                DB::raw('SUM(d.subtotal) as subtotal'),
            ])
            ->groupBy('d.producto_id')
            ->orderByDesc(DB::raw('SUM(d.cantidad)'))
            ->limit(8)
            ->get();

        $topCategories = $categoryQuery
            ->select([
                DB::raw('pr.categoria_id as categoria_id'),
                DB::raw('MAX(c.nombre) as nombre'),
                DB::raw('SUM(d.cantidad) as cantidad'),
                DB::raw('SUM(d.subtotal) as subtotal'),
            ])
            ->groupBy('pr.categoria_id')
            ->orderByDesc(DB::raw('SUM(d.cantidad)'))
            ->limit(8)
            ->get();

        return view('admin.reports.index', [
            'mode' => $mode,
            'from' => $from,
            'to' => $to,
            'series' => $series,
            'topProducts' => $topProducts,
            'topCategories' => $topCategories,
        ]);
    }

    public function settingsIndex(): View
    {
        return view('admin.settings.index', [
            'settings' => SiteSettings::get(),
        ]);
    }

    public function settingsUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'moneda' => ['required', 'string', 'max:10'],
            'prefijo' => ['required', 'string', 'max:20'],
            'branding' => ['required', 'string', 'max:120'],
        ]);

        SiteSettings::put($data);

        return back()->with('success', 'Configuracion guardada.');
    }

    public function sendNotification(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:600'],
            'audience' => ['required', 'in:web,mobile,both'],
            'route' => ['nullable', 'string', 'max:50'],
            'targetId' => ['nullable', 'string', 'max:50'],
            'userId' => ['nullable', 'integer'],
        ]);

        if (!empty($data['userId'])) {
            $this->notificaciones->createForUser([
                'userId' => (int) $data['userId'],
                'title' => $data['title'],
                'body' => $data['message'],
                'type' => 'admin_broadcast',
                'audience' => $data['audience'],
                'route' => $data['route'] ?? 'store',
                'targetId' => $data['targetId'] ?? null,
            ]);
        } else {
            $this->notificaciones->broadcastToUsers([
                'title' => $data['title'],
                'body' => $data['message'],
                'type' => 'admin_broadcast',
                'audience' => $data['audience'],
                'route' => $data['route'] ?? 'store',
                'targetId' => $data['targetId'] ?? null,
            ]);
        }

        return back()->with('success', 'Notificacion enviada correctamente.');
    }

    private function storeOptionalImage(Request $request, string $field, string $directory): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $dir = public_path('uploads/' . $directory);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $name = $directory . '-' . now()->timestamp . '-' . Str::random(8) . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($dir, $name);

        return $directory . '/' . $name;
    }
}

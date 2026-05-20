@extends('layouts.storefront', ['title' => 'Menu'])

@section('content')
    <section class="page-hero">
        <div class="max-w-3xl">
            <span class="eyebrow">Menu Delicias</span>
            <h2 class="headline mt-4">Nuestros productos ahora viven dentro de Laravel sin perder el estilo de la tienda.</h2>
            <p class="subheadline mt-4">
                Filtra por categoria, precio o destacados y navega el catalogo con datos reales desde tu base de datos.
            </p>
        </div>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-[320px_1fr]">
        <aside class="filter-card">
            <form method="GET" action="{{ route('web.products') }}" class="space-y-4">
                <div>
                    <label for="buscar" class="label">Buscar productos</label>
                    <input id="buscar" name="buscar" type="text" value="{{ $filters['buscar'] }}" class="input" placeholder="Nombre del producto...">
                </div>

                <div>
                    <label for="categoria" class="label">Categoria</label>
                    <select id="categoria" name="categoria" class="input">
                        <option value="0">Todas las categorias</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['categoria'] === (int) $category->id)>{{ $category->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="precioMin" class="label">Precio min.</label>
                        <input id="precioMin" name="precioMin" type="number" step="0.01" min="0" value="{{ $filters['precioMin'] }}" class="input" placeholder="0.00">
                    </div>
                    <div>
                        <label for="precioMax" class="label">Precio max.</label>
                        <input id="precioMax" name="precioMax" type="number" step="0.01" min="0" value="{{ $filters['precioMax'] }}" class="input" placeholder="100.00">
                    </div>
                </div>

                <label class="checkbox-row">
                    <input type="checkbox" name="disponible" value="1" @checked($filters['disponible'])>
                    <span>Solo productos disponibles</span>
                </label>

                <label class="checkbox-row">
                    <input type="checkbox" name="destacado" value="1" @checked($filters['destacado'])>
                    <span>Solo destacados</span>
                </label>

                <div>
                    <label for="orden" class="label">Ordenar por</label>
                    <select id="orden" name="orden" class="input">
                        <option value="nombre" @selected($filters['orden'] === 'nombre')>Nombre</option>
                        <option value="precio_asc" @selected($filters['orden'] === 'precio_asc')>Precio: menor a mayor</option>
                        <option value="precio_desc" @selected($filters['orden'] === 'precio_desc')>Precio: mayor a menor</option>
                        <option value="destacado" @selected($filters['orden'] === 'destacado')>Destacados primero</option>
                    </select>
                </div>

                <div>
                    <label for="limite" class="label">Por pagina</label>
                    <select id="limite" name="limite" class="input">
                        <option value="12" @selected($perPage === 12)>12 productos</option>
                        <option value="24" @selected($perPage === 24)>24 productos</option>
                        <option value="36" @selected($perPage === 36)>36 productos</option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary flex-1">Aplicar</button>
                    <a href="{{ route('web.products') }}" class="btn btn-outline-secondary flex-1 justify-center">Limpiar</a>
                </div>
            </form>
        </aside>

        <div>
            <div class="mb-5 flex flex-col gap-4 rounded-[2rem] border border-amber-200 bg-white/85 p-5 shadow-sm sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="eyebrow">Catalogo</p>
                    <h3 class="mt-2 text-2xl font-semibold text-stone-900">{{ $products->total() }} productos encontrados</h3>
                </div>
                <p class="text-sm text-stone-600">
                    Pagina {{ $products->currentPage() }} de {{ max(1, $products->lastPage()) }}
                </p>
            </div>

            @if ($products->count() === 0)
                <div class="empty-state">
                    <h4 class="text-xl font-semibold text-stone-900">No encontramos productos con esos filtros.</h4>
                    <p class="mt-2 text-sm text-stone-600">Puedes limpiar la busqueda o probar otra categoria.</p>
                </div>
            @else
                <div class="product-grid">
                    @foreach ($products as $product)
                        @include('web.products.partials.card', ['product' => $product])
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

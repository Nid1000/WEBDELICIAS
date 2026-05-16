@extends('layouts.storefront', ['title' => $product->nombre])

@section('content')
    <section class="grid gap-8 lg:grid-cols-[1fr_0.95fr] lg:items-start">
        <div class="overflow-hidden rounded-[2rem] border border-amber-200 bg-white/80 shadow-xl shadow-amber-100/40">
            <img src="{{ $product->imagen_url }}" alt="{{ $product->nombre }}" class="h-full min-h-[360px] w-full object-cover">
        </div>

        <div class="space-y-5">
            <a href="{{ route('web.products') }}" class="eyebrow">Volver al menu</a>
            <div>
                <h2 class="headline text-[var(--color-secondary)]">{{ $product->nombre }}</h2>
                @if ($product->categoria_nombre)
                    <p class="mt-2 text-sm font-medium uppercase tracking-[0.24em] text-stone-500">{{ $product->categoria_nombre }}</p>
                @endif
            </div>

            <div class="rounded-[2rem] border border-amber-200 bg-white/90 p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="text-3xl font-semibold text-stone-900">S/ {{ number_format($product->precio, 2) }}</span>
                    <div class="flex flex-wrap gap-2">
                        @if ($product->destacado)
                            <span class="badge badge-accent">Destacado</span>
                        @endif
                        @if ($product->agotado)
                            <span class="badge badge-danger">Agotado</span>
                        @else
                            <span class="badge badge-surface">Stock disponible: {{ $product->stock }}</span>
                        @endif
                    </div>
                </div>

                <p class="mt-4 text-base leading-7 text-stone-600">
                    {{ $product->descripcion ?: 'Producto artesanal de Delicias listo para pedidos y atencion personalizada.' }}
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if (!$product->agotado)
                        <form action="{{ route('web.cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="producto_id" value="{{ $product->id }}">
                            <input type="hidden" name="cantidad" value="1">
                            <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                            <button type="submit" class="btn btn-primary">Agregar al carrito</button>
                        </form>
                    @endif
                    <a href="{{ route('web.checkout') }}" class="btn btn-outline-secondary">Ir al checkout</a>
                    <a href="{{ route('web.products', ['categoria' => $product->categoria_id]) }}" class="btn btn-outline-secondary">
                        Ver mas de esta categoria
                    </a>
                </div>
            </div>

            <div class="rounded-[2rem] border border-dashed border-amber-300 bg-amber-50 p-6">
                <p class="text-sm font-semibold text-stone-900">Ideal para pedidos desde Laravel</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">
                    Esta vista ya forma parte del frontend Laravel, usando la misma linea visual de la tienda y datos reales desde la base.
                </p>
            </div>
        </div>
    </section>

    @if ($relatedProducts->count() > 0)
        <section class="mt-14">
            <div class="max-w-2xl">
                <p class="eyebrow">Sugerencias</p>
                <h3 class="mt-3 text-3xl font-semibold text-stone-900">Tambien podria gustarte</h3>
            </div>
            <div class="product-grid mt-6">
                @foreach ($relatedProducts as $product)
                    @include('web.products.partials.card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif
@endsection

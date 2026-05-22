<article class="product-card">
    <a href="{{ route('web.products.show', $product->id) }}" class="product-image-wrap">
        <img src="{{ $product->imagen_url }}" alt="{{ $product->nombre }}" class="product-image">
        <div class="product-badges">
            @if ($product->destacado)
                <span class="badge badge-accent">Destacado</span>
            @endif
            @if ($product->stock_bajo)
                <span class="badge badge-warning">Quedan {{ $product->stock }}</span>
            @endif
            @if ($product->agotado)
                <span class="badge badge-danger">Agotado</span>
            @endif
        </div>
        @if (!empty($product->categoria_nombre))
            <span class="badge badge-surface product-category">{{ $product->categoria_nombre }}</span>
        @endif
    </a>

    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-xl font-semibold leading-tight text-stone-900">{{ $product->nombre }}</h3>
            <span class="text-xl font-semibold text-[var(--color-secondary)]">S/ {{ number_format($product->precio, 2) }}</span>
        </div>
        @if ($product->descripcion)
            <p class="mt-2 line-clamp-2 text-sm text-stone-600">{{ $product->descripcion }}</p>
        @endif
        <div class="mt-5 flex gap-3">
            <a href="{{ route('web.products.show', $product->id) }}" class="btn btn-primary flex-1 justify-center">Ver detalle</a>
            @if (!$product->agotado)
                <form action="{{ route('web.cart.add') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="producto_id" value="{{ $product->id }}">
                    <input type="hidden" name="cantidad" value="1">
                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                    <button type="submit" class="btn btn-outline-secondary w-full justify-center">Agregar</button>
                </form>
            @else
                <span class="btn btn-outline-secondary flex-1 justify-center opacity-60">Sin stock</span>
            @endif
        </div>
    </div>
</article>

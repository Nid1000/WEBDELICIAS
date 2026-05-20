@extends('layouts.storefront', ['title' => 'Delicias'])

@section('content')
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="relative max-w-2xl">
            <span class="eyebrow">Recien horneado, hecho para ti</span>
            <h2 class="headline mt-5 text-[var(--color-secondary)]">Panaderia artesanal con el mismo diseno del frontend, ahora dentro de Laravel.</h2>
            <p class="subheadline mt-5">
                Descubre panes crujientes, dulces irresistibles y tortas personalizadas con una experiencia web servida desde Blade.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('web.products') }}" class="btn btn-primary">Ver menu</a>
                <a href="#contacto" class="btn btn-outline-secondary">Pedir ahora</a>
            </div>
        </div>
    </section>

    <section id="categorias" class="section-space">
        <div class="max-w-2xl">
            <span class="eyebrow">Categorias</span>
            <h3 class="section-title mt-3">Tres clasicos de la casa para empezar.</h3>
            <p class="subheadline mt-3">Panes artesanales, dulces irresistibles y tortas personalizadas con identidad propia.</p>
        </div>
        <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($homeCategories as $category)
                <article class="product-card">
                    <div class="relative aspect-[4/3] overflow-hidden">
                        <img src="{{ $category->imagen_url }}" alt="{{ $category->nombre }}" class="h-full w-full object-cover">
                    </div>
                    <div class="p-5">
                        <h4 class="text-xl font-semibold text-stone-900">{{ $category->nombre }}</h4>
                        <p class="mt-2 text-sm leading-6 text-stone-600">
                            {{ $category->descripcion ?: 'Productos artesanales preparados con ingredientes seleccionados y horneado diario.' }}
                        </p>
                        <a href="{{ route('web.products', ['categoria' => $category->id]) }}" class="btn btn-outline-secondary mt-4">Ver categoria</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section id="nosotros" class="section-space">
        <div class="grid gap-8 lg:grid-cols-[1fr_0.95fr] lg:items-center">
            <div>
                <span class="eyebrow">Nosotros</span>
                <h3 class="section-title mt-3">Sabores de siempre con una presentacion moderna.</h3>
                <p class="subheadline mt-4">
                    En Delicias horneamos cada dia con dedicacion y carino. Nuestros productos combinan recetas de familia,
                    ingredientes naturales y procesos artesanales para ofrecer sabores autenticos.
                </p>
                <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                    <li class="feature-item">Ingredientes seleccionados y de calidad</li>
                    <li class="feature-item">Recetas tradicionales con toques modernos</li>
                    <li class="feature-item">Horneado diario para asegurar frescura</li>
                    <li class="feature-item">Hecho con carino por nuestro equipo</li>
                </ul>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('web.products') }}" class="btn btn-outline-secondary">Ver el menu</a>
                    <a href="#contacto" class="btn btn-primary">Contacto</a>
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-amber-200 bg-white/80 shadow-xl shadow-amber-100/50">
                <img src="{{ asset('images/illustrations/illustrations.png') }}" alt="Panaderia Delicias" class="h-full w-full object-cover">
            </div>
        </div>
    </section>

    <section id="visitanos" class="section-space">
        <div class="grid gap-8 lg:grid-cols-[0.95fr_1fr] lg:items-start">
            <div>
                <span class="eyebrow">Visitanos</span>
                <h3 class="section-title mt-3">Ven por pan fresco, quedate por la experiencia.</h3>
                <p class="subheadline mt-4">
                    Panaderia Delicias, Jr. Parra del Riego #164, El Tambo, Huancayo. Atendemos todos los dias de 7:00 AM a 9:00 PM.
                </p>
                <div class="mt-4 space-y-2 text-sm text-stone-700">
                    <p><strong>Celular:</strong> 993560096</p>
                    <p><strong>Correo:</strong> contacto@delicias.com</p>
                </div>
                <div class="map-card mt-6">
                    <iframe
                        title="Ubicacion Delicias"
                        src="https://www.google.com/maps?q=Jr.+Parra+del+Riego+164,+El+Tambo,+Huancayo&output=embed"
                        width="100%"
                        height="280"
                        style="border:0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($galleryImages as $image)
                    <div class="overflow-hidden rounded-[1.5rem] border border-amber-100 shadow-sm">
                        <img src="{{ $image }}" alt="Producto Delicias" class="aspect-square w-full object-cover transition duration-300 hover:scale-105">
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="destacados" class="section-space">
        <div class="max-w-2xl">
            <span class="eyebrow">Favoritos</span>
            <h3 class="section-title mt-3">Productos destacados desde tu base de datos.</h3>
            <p class="subheadline mt-3">Una seleccion de favoritos que ya se renderiza desde Laravel.</p>
        </div>
        <div class="product-grid mt-8">
            @foreach ($featuredProducts as $product)
                @include('web.products.partials.card', ['product' => $product])
            @endforeach
        </div>
    </section>

    <section id="testimonios" class="section-space">
        <div class="max-w-2xl">
            <span class="eyebrow">Testimonios</span>
            <h3 class="section-title mt-3">Lo que dicen nuestros clientes.</h3>
        </div>
        <div class="mt-8 grid gap-5 lg:grid-cols-3">
            @foreach ($testimonials as $testimonial)
                <article class="rounded-[2rem] border border-amber-200 bg-white/90 p-6 shadow-sm">
                    <div class="text-[var(--color-primary)]">★★★★★</div>
                    <p class="mt-3 text-sm leading-7 text-stone-600">"{{ $testimonial['texto'] }}"</p>
                    <p class="mt-4 text-sm font-semibold text-stone-900">{{ $testimonial['nombre'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section id="contacto" class="section-space">
        <div class="mx-auto max-w-2xl rounded-[2rem] border border-amber-200 bg-white/92 p-8 shadow-xl shadow-amber-100/40">
            <div class="text-center">
                <span class="eyebrow">Contacto</span>
                <h3 class="section-title mt-3">Cuentanos tu pedido especial.</h3>
                <p class="subheadline mt-3">
                    El formulario tambien ya corre dentro de Laravel y mantiene la validacion principal del frontend.
                </p>
            </div>

            <form action="{{ route('web.contact.submit') }}" method="POST" class="mt-8 space-y-4">
                @csrf
                <div>
                    <label for="nombre" class="label">Nombre</label>
                    <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}" required class="input" placeholder="Tu nombre">
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input" placeholder="tu@email.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="telefono" class="label">Telefono</label>
                        <input id="telefono" name="telefono" type="tel" value="{{ old('telefono') }}" required class="input" placeholder="9XXXXXXXX">
                        @error('telefono')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="mensaje" class="label">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="5" required class="input min-h-36" placeholder="Cuéntanos qué necesitas">{{ old('mensaje') }}</textarea>
                    @error('mensaje')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full justify-center">Enviar mensaje</button>
            </form>
        </div>
    </section>
@endsection

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Delicias Bakery' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|poppins:400,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
    <div class="storefront-bg" aria-hidden="true">
        <div class="storefront-glow storefront-glow-left"></div>
        <div class="storefront-glow storefront-glow-right"></div>
    </div>

    <header class="navbar-shell">
        <div class="topbar">
            <div class="container flex h-9 items-center justify-between gap-4 text-xs text-stone-700">
                <div class="flex flex-wrap items-center gap-3">
                    <span>Envio gratis </span>
                    <a href="tel:993560096" class="hover:text-stone-950">993560096</a>
                </div>
                <div class="hidden items-center gap-3 sm:flex">
                    <a href="{{ route('web.home') }}#contacto" class="hover:text-stone-950">Contactanos</a>
                    <a href="{{ route('web.products') }}" class="hover:text-stone-950">Ver menu</a>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="navbar-main">
                <a href="{{ route('web.home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logos/logo 1.png') }}" alt="Delicias" class="h-10 w-10 rounded-full object-cover ring-1 ring-amber-200">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--color-secondary)]">Panaderia</p>
                        <h1 class="text-lg font-semibold text-stone-900">Delicias</h1>
                    </div>
                </a>

                <nav class="hidden items-center gap-6 text-sm md:flex">
                    <a href="{{ route('web.home') }}" class="hover:text-[var(--color-secondary)]">Inicio</a>
                    <a href="{{ route('web.products') }}" class="hover:text-[var(--color-secondary)]">Menu</a>
                    @if ($storefrontUser)
                        <a href="{{ route('web.orders') }}" class="hover:text-[var(--color-secondary)]">Historial</a>
                    @endif
                    <a href="{{ route('web.home') }}#nosotros" class="hover:text-[var(--color-secondary)]">Nosotros</a>
                    <a href="{{ route('web.home') }}#visitanos" class="hover:text-[var(--color-secondary)]">Visitanos</a>
                    <a href="{{ route('web.home') }}#contacto" class="hover:text-[var(--color-secondary)]">Contacto</a>
                </nav>

                <div class="flex items-center gap-3 text-sm">
                    <a href="{{ route('web.checkout') }}" class="cart-pill">
                        Carrito
                        @if (($storefrontCartCount ?? 0) > 0)
                            <span class="cart-pill-count">{{ $storefrontCartCount }}</span>
                        @endif
                    </a>
                    @if ($storefrontUser)
                        <a href="{{ route('web.profile') }}" class="hidden rounded-full border border-amber-200 bg-white/90 px-4 py-2 font-medium text-stone-700 shadow-sm sm:inline-flex">
                            {{ $storefrontUser['nombre'] }}
                        </a>
                        <form action="{{ route('web.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Salir</button>
                        </form>
                    @else
                        <a href="{{ route('web.login') }}" class="btn btn-outline-secondary">Entrar</a>
                        <a href="{{ route('web.register') }}" class="btn btn-primary hidden sm:inline-flex">Registro</a>
                    @endif
                </div>
            </div>
        </div>

        @if (($storefrontCategories ?? collect())->count() > 0)
            <div class="navbar-categories">
                <div class="container flex gap-3 overflow-x-auto py-3">
                    @foreach ($storefrontCategories as $category)
                        <a href="{{ route('web.products', ['categoria' => $category->id]) }}" class="category-pill">
                            {{ $category->nombre }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </header>

    <main class="container pb-16 pt-8">
        @if (session('success'))
            <div class="flash-success mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="flash-error mb-6">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div>
                <h3 class="text-lg font-semibold text-[var(--color-secondary)]">Panaderia Delicias</h3>
                <p class="mt-2 text-sm text-stone-700">
                    Pan artesanal, dulces y tortas con ingredientes de primera calidad.
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-stone-900">Contacto</h4>
                <ul class="mt-3 space-y-2 text-sm text-stone-700">
                    <li>Jr. Parra del Riego #164, El Tambo, Huancayo</li>
                    <li>993560096</li>
                    <li>contacto@delicias.com</li>
                    <li>Lunes a Domingo, 7:00 AM - 9:00 PM</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-stone-900">Explorar</h4>
                <div class="mt-3 flex flex-col gap-2 text-sm text-stone-700">
                    <a href="{{ route('web.products') }}" class="hover:text-stone-950">Menu completo</a>
                    <a href="{{ route('web.home') }}#testimonios" class="hover:text-stone-950">Testimonios</a>
                    <a href="{{ route('web.home') }}#contacto" class="hover:text-stone-950">Pedidos especiales</a>
                </div>
            </div>
        </div>
        <div class="border-t border-black/10">
            <div class="container flex flex-col gap-2 py-4 text-sm text-stone-600 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ now()->year }} Delicias. Todos los derechos reservados.</p>
                <p>Frontend publico migrado a Laravel Blade.</p>
            </div>
        </div>
    </footer>
</body>
</html>

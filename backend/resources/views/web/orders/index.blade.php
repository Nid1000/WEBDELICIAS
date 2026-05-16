@extends('layouts.storefront', ['title' => 'Historial de pedidos'])

@section('content')
    <section class="page-hero">
        <div class="max-w-3xl">
            <span class="eyebrow">Historial</span>
            <h2 class="headline mt-4">Tus pedidos y comprobantes ya pueden verse desde Laravel.</h2>
            <p class="subheadline mt-4">Revisa estados, productos pedidos y archivos emitidos sin depender del frontend Next.</p>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-4">
            @forelse ($orders as $order)
                <article class="rounded-[2rem] border border-amber-200 bg-white/90 p-6 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="eyebrow">Pedido #{{ $order->id }}</p>
                            <h3 class="mt-3 text-2xl font-semibold text-stone-900">Estado: {{ ucfirst($order->estado) }}</h3>
                            <p class="mt-2 text-sm text-stone-600">
                                Creado el {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
                            </p>
                            @if ($order->fecha_entrega)
                                <p class="mt-1 text-sm text-stone-600">Entrega: {{ \Illuminate\Support\Carbon::parse($order->fecha_entrega)->format('d/m/Y') }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-stone-500">Total</p>
                            <p class="text-3xl font-semibold text-[var(--color-secondary)]">S/ {{ number_format($order->total, 2) }}</p>
                            <p class="mt-1 text-sm text-stone-500">{{ $order->total_productos }} productos</p>
                        </div>
                    </div>

                    @if ($order->lineas->count() > 0)
                        <div class="mt-5 grid gap-3">
                            @foreach ($order->lineas->take(3) as $line)
                                <div class="flex items-center justify-between rounded-2xl border border-amber-100 bg-white px-4 py-3 text-sm">
                                    <span>Producto #{{ $line->producto_id }}</span>
                                    <span>{{ $line->cantidad }} unidad(es)</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('web.orders.show', $order->id) }}" class="btn btn-primary">Ver detalle</a>
                        @if (!in_array($order->estado, ['cancelado', 'listo', 'entregado'], true))
                            <form action="{{ route('web.orders.cancel', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">Cancelar pedido</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <h3 class="text-2xl font-semibold text-stone-900">Aun no tienes pedidos.</h3>
                    <p class="mt-2 text-sm text-stone-600">Cuando completes tu primera compra, aparecera aqui con su comprobante.</p>
                    <a href="{{ route('web.products') }}" class="btn btn-primary mt-5">Explorar menu</a>
                </div>
            @endforelse
        </div>

        <div class="rounded-[2rem] border border-amber-200 bg-white/90 p-8 shadow-sm">
            <p class="eyebrow">Comprobantes</p>
            <h3 class="mt-3 text-3xl font-semibold text-stone-900">Archivos emitidos</h3>

            <div class="mt-6 space-y-4">
                @forelse ($receipts as $receipt)
                    <article class="rounded-[1.5rem] border border-amber-100 bg-white p-4 shadow-sm">
                        <h4 class="text-lg font-semibold text-stone-900">{{ strtoupper($receipt->tipo) }} {{ $receipt->numero_formateado }}</h4>
                        <p class="mt-1 text-sm text-stone-500">Pedido #{{ $receipt->pedido_id }}</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ $receipt->pdf_url }}" target="_blank" class="btn btn-outline-secondary">PDF</a>
                            <a href="{{ $receipt->xml_url }}" target="_blank" class="btn btn-outline-secondary">XML</a>
                            <a href="{{ $receipt->img_url }}" target="_blank" class="btn btn-outline-secondary">Imagen</a>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-stone-600">Todavia no hay comprobantes emitidos.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

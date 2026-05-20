@extends('layouts.storefront', ['title' => 'Historial de compras'])

@section('content')
    <section class="orders-shell">
        <div class="max-w-4xl">
            <h2 class="orders-title">Historial de compras</h2>
        </div>

        <div class="orders-tabs">
            <a href="{{ route('web.orders', array_filter(['tab' => 'orders'] + $filters)) }}" class="orders-tab @if ($activeTab === 'orders') orders-tab-active @endif">
                Pedidos
            </a>
            <a href="{{ route('web.orders', array_filter(['tab' => 'receipts'] + $filters)) }}" class="orders-tab @if ($activeTab === 'receipts') orders-tab-active @endif">
                Comprobantes
            </a>
        </div>

        <form method="GET" action="{{ route('web.orders') }}" class="orders-filters">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <div>
                <label for="buscar" class="label">Busqueda</label>
                <input
                    id="buscar"
                    name="buscar"
                    type="text"
                    value="{{ $filters['buscar'] }}"
                    class="input"
                    placeholder="{{ $activeTab === 'orders' ? 'ID o estado' : 'Numero o pedido' }}"
                >
            </div>

            <div>
                <label for="estado" class="label">Estado</label>
                <select id="estado" name="estado" class="input" @disabled($activeTab !== 'orders')>
                    <option value="">Todos</option>
                    @foreach ($orderStates as $state)
                        <option value="{{ $state }}" @selected($filters['estado'] === $state)>{{ ucfirst($state) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="desde" class="label">Desde</label>
                <input id="desde" name="desde" type="date" value="{{ $filters['desde'] }}" class="input">
            </div>

            <div>
                <label for="hasta" class="label">Hasta</label>
                <input id="hasta" name="hasta" type="date" value="{{ $filters['hasta'] }}" class="input">
            </div>
        </form>

        @if ($activeTab === 'orders')
            <section class="orders-results">
                @forelse ($orders as $order)
                    <article class="history-card">
                        <div class="history-card-main">
                            <div>
                                <p class="history-card-kicker">Pedido #{{ $order->id }}</p>
                                <h3 class="history-card-title">{{ ucfirst($order->estado) }}</h3>
                                <p class="history-card-meta">
                                    {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
                                    @if ($order->fecha_entrega)
                                        · Entrega {{ \Illuminate\Support\Carbon::parse($order->fecha_entrega)->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>

                            <div class="history-card-side">
                                <p class="history-card-total">S/ {{ number_format($order->total, 2) }}</p>
                                <p class="history-card-meta">{{ $order->total_productos }} productos</p>
                            </div>
                        </div>

                        <div class="history-card-actions">
                            <a href="{{ route('web.orders.show', $order->id) }}" class="btn btn-primary">Ver detalle</a>
                            @if (!in_array($order->estado, ['cancelado', 'listo', 'entregado'], true))
                                <form action="{{ route('web.orders.cancel', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary">Cancelar</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="orders-empty">
                        <p class="orders-empty-title">Aun no tienes pedidos.</p>
                        <p class="orders-empty-copy">No hay pedidos disponibles.</p>
                    </div>
                @endforelse
            </section>
        @else
            <section class="orders-results">
                @forelse ($receipts as $receipt)
                    <article class="history-card">
                        <div class="history-card-main">
                            <div>
                                <p class="history-card-kicker">Pedido #{{ $receipt->pedido_id }}</p>
                                <h3 class="history-card-title">{{ strtoupper($receipt->tipo) }} {{ $receipt->numero_formateado }}</h3>
                                <p class="history-card-meta">Comprobante emitido</p>
                            </div>
                        </div>

                        <div class="history-card-actions">
                            @if ($receipt->pdf_url)
                                <a href="{{ $receipt->pdf_url }}" target="_blank" class="btn btn-primary">PDF</a>
                            @endif
                            @if ($receipt->xml_url)
                                <a href="{{ $receipt->xml_url }}" target="_blank" class="btn btn-outline-secondary">XML</a>
                            @endif
                            @if ($receipt->img_url)
                                <a href="{{ $receipt->img_url }}" target="_blank" class="btn btn-outline-secondary">Imagen</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="orders-empty">
                        <p class="orders-empty-title">Aun no tienes comprobantes.</p>
                        <p class="orders-empty-copy">No hay comprobantes disponibles.</p>
                    </div>
                @endforelse
            </section>
        @endif
    </section>
@endsection

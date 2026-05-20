@extends('layouts.storefront', ['title' => 'Checkout'])

@section('content')
    <section class="page-hero">
        <div class="max-w-3xl">
            <span class="eyebrow">Checkout</span>
            <h2 class="headline mt-4">Confirma tu pedido y revisa cada detalle antes de finalizar tu compra.</h2>
            <p class="subheadline mt-4">Revisamos tu carrito, generamos el pedido y emitimos el comprobante en el mismo flujo.</p>
        </div>
    </section>

    @if ($cartItems->isEmpty())
        <section class="empty-state mt-8">
            <h3 class="text-2xl font-semibold text-stone-900">Tu carrito esta vacio.</h3>
            <p class="mt-2 text-sm text-stone-600">Agrega algunos productos desde el menu para continuar con tu compra.</p>
            <a href="{{ route('web.products') }}" class="btn btn-primary mt-5">Explorar productos</a>
        </section>
    @else
        <section class="mt-8 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <div class="rounded-[2rem] border border-amber-200 bg-white/90 p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="eyebrow">Resumen</p>
                        <h3 class="mt-3 text-3xl font-semibold text-stone-900">Productos seleccionados</h3>
                    </div>
                    <form action="{{ route('web.cart.clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Vaciar carrito</button>
                    </form>
                </div>

                <div class="space-y-4">
                    @foreach ($cartItems as $item)
                        <div class="rounded-[1.5rem] border border-amber-100 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <img src="{{ $item->imagen_url }}" alt="{{ $item->nombre }}" class="h-20 w-20 rounded-2xl object-cover">
                                <div class="flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h4 class="text-lg font-semibold text-stone-900">{{ $item->nombre }}</h4>
                                            <p class="text-sm text-stone-500">{{ $item->categoria_nombre ?: 'Producto artesanal' }}</p>
                                        </div>
                                        <p class="text-lg font-semibold text-[var(--color-secondary)]">S/ {{ number_format($item->subtotal, 2) }}</p>
                                    </div>

                                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <form action="{{ route('web.cart.update', $item->id) }}" method="POST" class="flex items-center gap-3">
                                            @csrf
                                            @method('PATCH')
                                            <label class="text-sm text-stone-600">Cantidad</label>
                                            <input type="number" min="0" max="{{ max(1, $item->stock) }}" name="cantidad" value="{{ $item->cantidad }}" class="input w-24">
                                            <button type="submit" class="btn btn-outline-secondary">Actualizar</button>
                                        </form>
                                        <p class="text-sm text-stone-600">Precio unitario: S/ {{ number_format($item->precio, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 rounded-[1.5rem] border border-amber-200 bg-amber-50 p-5">
                    <div class="flex items-center justify-between text-sm text-stone-600">
                        <span>Total del carrito</span>
                        <strong class="text-2xl text-stone-900">S/ {{ number_format($cartTotal, 2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-amber-200 bg-white/90 p-8 shadow-sm">
                <p class="eyebrow">Entrega y comprobante</p>
                <h3 class="mt-3 text-3xl font-semibold text-stone-900">Datos del pedido</h3>

                <form action="{{ route('web.checkout.submit') }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="distrito_entrega" class="label">Distrito</label>
                            <select id="distrito_entrega" name="distrito_entrega" required class="input">
                                <option value="">Selecciona un distrito</option>
                                @foreach ($distritos as $distrito)
                                    <option value="{{ $distrito->nombre }}" @selected(old('distrito_entrega', $user['distrito'] ?? '') === $distrito->nombre)>{{ $distrito->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="numero_casa_entrega" class="label">Numero de casa</label>
                            <input id="numero_casa_entrega" name="numero_casa_entrega" type="text" required value="{{ old('numero_casa_entrega', $user['numero_casa'] ?? '') }}" class="input">
                        </div>
                    </div>

                    <div>
                        <label for="direccion_entrega" class="label">Direccion de entrega</label>
                        <input id="direccion_entrega" name="direccion_entrega" type="text" required value="{{ old('direccion_entrega', $user['direccion'] ?? '') }}" class="input">
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="telefono_contacto" class="label">Telefono</label>
                            <input id="telefono_contacto" name="telefono_contacto" type="tel" required value="{{ old('telefono_contacto', $user['telefono'] ?? '') }}" class="input" placeholder="9XXXXXXXX">
                        </div>
                        <div>
                            <label for="fecha_entrega" class="label">Fecha de entrega</label>
                            <input id="fecha_entrega" name="fecha_entrega" type="date" required min="{{ $minDeliveryDate }}" value="{{ old('fecha_entrega', $minDeliveryDate) }}" class="input">
                        </div>
                    </div>

                    <div>
                        <label for="notas" class="label">Notas</label>
                        <textarea id="notas" name="notas" rows="3" class="input min-h-28" placeholder="Instrucciones adicionales">{{ old('notas') }}</textarea>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3" data-document-validation data-document-url="{{ route('web.checkout.validate-document') }}">
                        <div>
                            <label for="comprobante_tipo" class="label">Comprobante</label>
                            <select id="comprobante_tipo" name="comprobante_tipo" class="input">
                                <option value="boleta" @selected(old('comprobante_tipo', 'boleta') === 'boleta')>Boleta</option>
                                <option value="factura" @selected(old('comprobante_tipo') === 'factura')>Factura</option>
                            </select>
                        </div>
                        <div>
                            <label for="tipo_documento" class="label">Documento</label>
                            <select id="tipo_documento" name="tipo_documento" class="input">
                                <option value="DNI" @selected(old('tipo_documento', 'DNI') === 'DNI')>DNI</option>
                                <option value="RUC" @selected(old('tipo_documento') === 'RUC')>RUC</option>
                            </select>
                        </div>
                        <div>
                            <label for="numero_documento" class="label">Numero</label>
                            <div class="flex gap-2">
                                <input id="numero_documento" name="numero_documento" type="text" required value="{{ old('numero_documento') }}" class="input" inputmode="numeric" autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary shrink-0" data-document-lookup>Validar</button>
                            </div>
                        </div>
                        <p class="md:col-span-3 text-sm text-stone-600" data-document-message>
                            Ingresa un DNI de 8 digitos o un RUC valido de 11 digitos.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary w-full justify-center">Confirmar pedido</button>
                </form>
            </div>
        </section>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deliveryDate = document.getElementById('fecha_entrega');
            if (deliveryDate) {
                const enforceMinDeliveryDate = () => {
                    if (deliveryDate.min && (!deliveryDate.value || deliveryDate.value < deliveryDate.min)) {
                        deliveryDate.value = deliveryDate.min;
                    }
                };
                deliveryDate.addEventListener('change', enforceMinDeliveryDate);
                enforceMinDeliveryDate();
            }

            const form = document.querySelector('[data-document-validation]');
            if (!form) return;

            const receipt = document.getElementById('comprobante_tipo');
            const type = document.getElementById('tipo_documento');
            const number = document.getElementById('numero_documento');
            const message = document.querySelector('[data-document-message]');
            const lookup = document.querySelector('[data-document-lookup]');
            const csrf = document.querySelector('input[name="_token"]')?.value || '';
            let lookupTimer = null;
            let lookupKey = '';

            const onlyDigits = (value) => value.replace(/\D+/g, '');
            const validRuc = (value) => {
                const ruc = onlyDigits(value);
                if (!/^\d{11}$/.test(ruc) || !['10', '15', '17', '20'].includes(ruc.slice(0, 2))) return false;
                const weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
                const sum = weights.reduce((acc, weight, index) => acc + Number(ruc[index]) * weight, 0);
                let check = 11 - (sum % 11);
                if (check === 10) check = 0;
                if (check === 11) check = 1;
                return check === Number(ruc[10]);
            };

            const setMessage = (text, state = 'neutral') => {
                message.textContent = text;
                message.classList.toggle('text-emerald-700', state === 'success');
                message.classList.toggle('text-red-600', state === 'error');
                message.classList.toggle('text-stone-600', state === 'neutral');
            };

            const hasValidFormat = () => type.value === 'DNI'
                ? /^\d{8}$/.test(number.value)
                : validRuc(number.value);

            const sync = () => {
                if (receipt.value === 'factura') {
                    type.value = 'RUC';
                }

                number.value = onlyDigits(number.value).slice(0, type.value === 'RUC' ? 11 : 8);
                const ok = hasValidFormat();
                lookupKey = '';

                setMessage(
                    ok
                        ? 'Formato correcto. Buscando validacion con RENIEC/SUNAT...'
                        : (type.value === 'DNI'
                        ? 'El DNI debe tener exactamente 8 digitos.'
                        : 'El RUC debe tener 11 digitos y digito verificador correcto.'),
                    ok ? 'neutral' : 'error'
                );

                if (lookupTimer) {
                    clearTimeout(lookupTimer);
                }
                if (ok) {
                    lookupTimer = setTimeout(() => validateWithProvider(), 550);
                }
            };

            const validateWithProvider = async () => {
                if (!hasValidFormat()) {
                    sync();
                    return;
                }

                const currentKey = `${type.value}:${number.value}`;
                if (currentKey === lookupKey) {
                    return;
                }

                lookupKey = currentKey;
                lookup.disabled = true;
                setMessage(`Consultando ${type.value === 'DNI' ? 'RENIEC' : 'SUNAT'}...`);

                try {
                    const response = await fetch(form.dataset.documentUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            tipo_documento: type.value,
                            numero_documento: number.value,
                        }),
                    });
                    const payload = await response.json();
                    setMessage(
                        payload.message || (response.ok ? 'Documento validado correctamente.' : 'No se pudo validar el documento.'),
                        response.ok ? 'success' : 'error'
                    );
                } catch (error) {
                    lookupKey = '';
                    setMessage('No se pudo conectar con el servicio de validacion.', 'error');
                } finally {
                    lookup.disabled = false;
                }
            };

            receipt.addEventListener('change', sync);
            type.addEventListener('change', sync);
            number.addEventListener('input', sync);
            lookup.addEventListener('click', validateWithProvider);
            sync();
        });
    </script>
@endsection

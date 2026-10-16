@extends('layouts.app')

@section('title', (isset($venta) ? 'Editar compra' : 'Registrar compra') . ' · Óptica Reyna')

@section('content')
    <p><a href="{{ route('pacientes.show', $paciente) }}">← Volver a {{ $paciente->nombre }}</a></p>
    <div class="card">
        <h2 style="margin-top:0">{{ isset($venta) ? 'Editar compra #' . $venta->id : 'Registrar compra' }}</h2>
        <p class="muted">Paciente: <strong>{{ $paciente->nombre }}</strong></p>

        <form method="POST" action="{{ isset($venta) ? route('ventas.update', $venta) : route('pacientes.ventas.store', $paciente) }}">
            @csrf
            @isset($venta)
                @method('PUT')
            @endisset

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem">
                <div>
                    <label for="fecha_venta">Fecha de {{ isset($venta) ? 'la compra' : 'compra' }}</label>
                    <input type="date" id="fecha_venta" name="fecha_venta" value="{{ old('fecha_venta', $fechaVenta) }}" max="{{ now()->toDateString() }}" required>
                    @error('fecha_venta') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
            @unless (isset($venta))
                <div style="margin-bottom:1rem">
                    <label for="pago_inicial">Anticipo recibido (opcional)</label>
                    <input type="number" id="pago_inicial" name="pago_inicial" value="{{ old('pago_inicial', '0.00') }}" min="0" step="0.01" inputmode="decimal" style="width:100%">
                    @error('pago_inicial') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div style="margin-bottom:1rem">
                    <label for="metodo_pago">Forma de pago del anticipo</label>
                    <select id="metodo_pago" name="metodo_pago">
                        <option value="">No se recibió anticipo</option>
                        @foreach (['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia', 'otro' => 'Otro'] as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(old('metodo_pago') === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    @error('metodo_pago') <p class="error">{{ $message }}</p> @enderror
                </div>
            @endunless

            @error('productos') <p class="error">{{ $message }}</p> @enderror
            <div style="display:flex; justify-content:space-between; align-items:center">
                <h3>Productos</h3>
                <button type="button" id="agregar-producto" class="btn-secundario">Agregar producto</button>
            </div>
            <div style="overflow-x:auto">
                <table>
                    <thead><tr><th>Descripción</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody id="partidas">
                        @php($productosIniciales = old('productos', isset($venta) ? $venta->partidas->map(fn ($partida) => ['descripcion' => $partida->descripcion, 'cantidad' => $partida->cantidad, 'precio_unitario' => $partida->precio_unitario])->all() : [['descripcion' => '', 'cantidad' => 1, 'precio_unitario' => '']]))
                        @foreach ($productosIniciales as $indice => $producto)
                            <tr class="partida">
                                <td>
                                    <input type="text" name="productos[{{ $indice }}][descripcion]" value="{{ $producto['descripcion'] ?? '' }}" maxlength="255" required style="width:100%" aria-label="Descripción del producto">
                                    @error("productos.$indice.descripcion") <p class="error">{{ $message }}</p> @enderror
                                </td>
                                <td>
                                    <input type="number" name="productos[{{ $indice }}][cantidad]" value="{{ $producto['cantidad'] ?? 1 }}" min="1" max="9999" step="1" required style="width:90px" aria-label="Cantidad">
                                    @error("productos.$indice.cantidad") <p class="error">{{ $message }}</p> @enderror
                                </td>
                                <td>
                                    <input type="number" name="productos[{{ $indice }}][precio_unitario]" value="{{ $producto['precio_unitario'] ?? '' }}" min="0.01" max="9999999.99" step="0.01" required style="width:150px" aria-label="Precio unitario">
                                    @error("productos.$indice.precio_unitario") <p class="error">{{ $message }}</p> @enderror
                                </td>
                                <td class="subtotal">$0.00</td>
                                <td><button type="button" class="btn-peligro quitar-producto" aria-label="Quitar producto">Quitar</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th colspan="3" style="text-align:right">Total de la compra</th><th id="total">$0.00</th><th></th></tr>
                        @unless (isset($venta))
                            <tr><th colspan="3" style="text-align:right">Saldo después del anticipo</th><th id="saldo">$0.00</th><th></th></tr>
                        @endunless
                    </tfoot>
                </table>
            </div>

            @isset($venta)
                <h3>Registrar abono con esta edición <span class="muted" style="font-size:.85rem; font-weight:400">(opcional)</span></h3>
                <p class="muted">El abono se agregará al historial de pagos. No se modifican los pagos ya registrados.</p>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:1rem">
                    <div>
                        <label for="abono_monto">Monto que se abona</label>
                        <input type="number" id="abono_monto" name="abono_monto" value="{{ old('abono_monto') }}" min="0" step="0.01" inputmode="decimal" style="width:100%">
                        @error('abono_monto') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="abono_fecha">Fecha del abono</label>
                        <input type="date" id="abono_fecha" name="abono_fecha" value="{{ old('abono_fecha', now()->toDateString()) }}" min="{{ $venta->fecha_venta->toDateString() }}" max="{{ now()->toDateString() }}">
                        @error('abono_fecha') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="abono_metodo_pago">Forma de pago del abono</label>
                        <select id="abono_metodo_pago" name="abono_metodo_pago">
                            <option value="">Selecciona una forma de pago</option>
                            @foreach (['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia', 'otro' => 'Otro'] as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('abono_metodo_pago') === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                        @error('abono_metodo_pago') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div style="margin-bottom:1rem">
                    <label for="abono_notas">Notas del abono</label>
                    <input type="text" id="abono_notas" name="abono_notas" maxlength="1000" value="{{ old('abono_notas') }}" style="width:100%">
                    @error('abono_notas') <p class="error">{{ $message }}</p> @enderror
                </div>
            @endisset

            <div style="margin:1rem 0">
                <label for="notas">Notas</label>
                <textarea id="notas" name="notas" rows="2" maxlength="2000" style="width:100%">{{ old('notas') }}</textarea>
                @error('notas') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="acciones">
                <button type="submit">{{ isset($venta) ? 'Guardar cambios' : 'Registrar compra' }}</button>
                <a class="boton btn-secundario" href="{{ isset($venta) ? route('ventas.show', $venta) : route('pacientes.show', $paciente) }}">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const tbody = document.getElementById('partidas');
            const template = (index) => {
                const row = document.createElement('tr');
                row.className = 'partida';
                row.innerHTML = `
                    <td><input type="text" name="productos[${index}][descripcion]" maxlength="255" required style="width:100%" aria-label="Descripción del producto"></td>
                    <td><input type="number" name="productos[${index}][cantidad]" value="1" min="1" max="9999" step="1" required style="width:90px" aria-label="Cantidad"></td>
                    <td><input type="number" name="productos[${index}][precio_unitario]" min="0.01" max="9999999.99" step="0.01" required style="width:150px" aria-label="Precio unitario"></td>
                    <td class="subtotal">$0.00</td>
                    <td><button type="button" class="btn-peligro quitar-producto" aria-label="Quitar producto">Quitar</button></td>`;
                return row;
            };
            const actualizarTotal = () => {
                let totalCentavos = 0;
                tbody.querySelectorAll('.partida').forEach((row) => {
                    const cantidad = Number(row.querySelector('[name$="[cantidad]"]').value) || 0;
                    const precio = Number(row.querySelector('[name$="[precio_unitario]"]').value) || 0;
                    const subtotalCentavos = Math.round(precio * 100) * cantidad;
                    totalCentavos += subtotalCentavos;
                    row.querySelector('.subtotal').textContent = '$' + (subtotalCentavos / 100).toFixed(2);
                });
                document.getElementById('total').textContent = '$' + (totalCentavos / 100).toFixed(2);
                const anticipoInput = document.getElementById('pago_inicial');
                if (anticipoInput) {
                    const anticipo = Number(anticipoInput.value) || 0;
                    document.getElementById('saldo').textContent = '$' + Math.max(0, totalCentavos / 100 - anticipo).toFixed(2);
                }
            };

            document.getElementById('agregar-producto').addEventListener('click', () => {
                const index = Math.max(-1, ...Array.from(tbody.querySelectorAll('.partida')).map((row) => {
                    const match = row.querySelector('[name]').name.match(/^productos\[(\d+)\]/);
                    return match ? Number(match[1]) : -1;
                })) + 1;
                tbody.appendChild(template(index));
                actualizarTotal();
            });
            tbody.addEventListener('input', actualizarTotal);
            document.getElementById('pago_inicial')?.addEventListener('input', actualizarTotal);
            tbody.addEventListener('click', (event) => {
                if (event.target.closest('.quitar-producto') && tbody.querySelectorAll('.partida').length > 1) {
                    event.target.closest('.partida').remove();
                    actualizarTotal();
                }
            });
            actualizarTotal();
        })();
    </script>
@endsection

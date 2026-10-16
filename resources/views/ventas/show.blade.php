@extends('layouts.app')

@section('title', 'Compra #' . $venta->id . ' · Óptica Reyna')

@section('content')
    <p><a href="{{ route('pacientes.show', $venta->paciente) }}">← Volver a {{ $venta->paciente->nombre }}</a></p>
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center">
            <h2 style="margin-top:0">Detalle de compra #{{ $venta->id }}</h2>
            <a class="boton" href="{{ route('ventas.edit', $venta) }}">Editar compra</a>
        </div>
        <p><strong>Paciente:</strong> <a href="{{ route('pacientes.show', $venta->paciente) }}">{{ $venta->paciente->nombre }}</a></p>
        <p><strong>Fecha:</strong> {{ $venta->fecha_venta->format('d/m/Y') }}</p>

        <table>
            <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead>
            <tbody>
                @foreach ($venta->partidas as $partida)
                    <tr>
                        <td>{{ $partida->descripcion }}</td>
                        <td>{{ $partida->cantidad }}</td>
                        <td>${{ number_format((float) $partida->precio_unitario, 2) }}</td>
                        <td>${{ number_format((float) $partida->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><th colspan="3" style="text-align:right">Total de la compra</th><th>${{ number_format((float) $venta->total, 2) }}</th></tr>
                <tr><th colspan="3" style="text-align:right">Total abonado</th><th>${{ number_format((float) $pagadoFormateado, 2) }}</th></tr>
                <tr><th colspan="3" style="text-align:right">Saldo pendiente</th><th>${{ number_format((float) $saldoFormateado, 2) }}</th></tr>
            </tfoot>
        </table>

        <h3>Anticipos y abonos</h3>
        <table>
            <thead><tr><th>Fecha</th><th>Forma de pago</th><th>Importe</th><th>Notas</th></tr></thead>
            <tbody>
                @forelse ($venta->pagos as $pago)
                    <tr>
                        <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($pago->metodo_pago) }}</td>
                        <td>${{ number_format((float) $pago->monto, 2) }}</td>
                        <td>{{ $pago->notas ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Aún no se han registrado pagos.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if ($saldoCentavos > 0)
            <h3>Registrar abono</h3>
            <form method="POST" action="{{ route('ventas.abonos.store', $venta) }}" class="card" style="background:#f9fafb">
                @csrf
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem">
                    <div>
                        <label for="fecha_pago">Fecha del abono</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->toDateString()) }}" min="{{ $venta->fecha_venta->toDateString() }}" max="{{ now()->toDateString() }}" required>
                        @error('fecha_pago') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="monto">Importe (saldo: ${{ number_format((float) $saldoFormateado, 2) }})</label>
                        <input type="number" id="monto" name="monto" min="0.01" max="{{ $saldoFormateado }}" step="0.01" value="{{ old('monto') }}" required>
                        @error('monto') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="metodo_pago">Forma de pago</label>
                        <select id="metodo_pago" name="metodo_pago" required>
                            @foreach (['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia', 'otro' => 'Otro'] as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('metodo_pago', 'efectivo') === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                        @error('metodo_pago') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div style="margin:1rem 0">
                    <label for="notas">Notas del abono</label>
                    <input type="text" id="notas" name="notas" maxlength="1000" value="{{ old('notas') }}" style="width:100%">
                    @error('notas') <p class="error">{{ $message }}</p> @enderror
                </div>
                <button type="submit">Registrar abono</button>
            </form>
        @else
            <p class="badge">Compra liquidada</p>
        @endif

        @if ($venta->notas)
            <p><strong>Notas:</strong> {{ $venta->notas }}</p>
        @endif
    </div>
@endsection

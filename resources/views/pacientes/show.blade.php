@extends('layouts.app')

@section('title', $paciente->nombre . ' · Óptica Reyna')

@section('content')
    <p><a href="{{ route('pacientes.index') }}">← Volver a pacientes</a></p>

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center">
            <h2 style="margin:0">{{ $paciente->nombre }}</h2>
            <div class="acciones">
                <a class="boton" href="{{ route('pacientes.edit', $paciente) }}">Editar</a>
                <form method="POST" action="{{ route('pacientes.destroy', $paciente) }}"
                      onsubmit="return confirm('¿Eliminar al paciente y todo su historial de consultas?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-peligro">Eliminar</button>
                </form>
            </div>
        </div>
        <p class="muted">
            {{ $paciente->edad }} años · Nac. {{ $paciente->fecha_nacimiento->format('d/m/Y') }}
            · Tel: {{ $paciente->telefono ?? '—' }}
        </p>
        <p>{{ $paciente->direccion ?? 'Sin dirección registrada' }}</p>
        <p>
            @forelse ($paciente->enfermedades as $enfermedad)
                <span class="badge">{{ $enfermedad->nombre }}</span>
            @empty
                <span class="muted">Sin enfermedades registradas</span>
            @endforelse
        </p>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center">
        <h3>Historial de compras</h3>
        <a class="boton" href="{{ route('pacientes.ventas.create', $paciente) }}">Registrar compra</a>
    </div>
    <div class="card">
        <table>
            <thead><tr><th>Fecha</th><th>Productos</th><th>Pago</th><th>Total</th><th></th></tr></thead>
            <tbody>
                @forelse ($paciente->ventas as $venta)
                    <tr>
                        <td>{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                        <td>{{ $venta->partidas->count() }}</td>
                        <td>{{ ucfirst($venta->metodo_pago) }}</td>
                        <td>${{ number_format((float) $venta->total, 2) }}</td>
                        <td><a href="{{ route('ventas.show', $venta) }}">Ver detalle</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Este paciente aún no tiene compras registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center">
        <h3>Historial de consultas</h3>
        <a class="boton" href="{{ route('pacientes.consultas.create', $paciente) }}">Nueva consulta</a>
    </div>
    @forelse ($paciente->consultas as $consulta)
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center">
                <div>
                    <strong>{{ $consulta->fecha_consulta->format('d/m/Y H:i') }}</strong>
                    @if ($consulta->proxima_cita)
                        <span class="muted"> · Próxima cita: {{ $consulta->proxima_cita->format('d/m/Y') }}</span>
                    @endif
                </div>
                <div class="acciones">
                    <a href="{{ route('consultas.edit', $consulta) }}">Editar</a>
                    <form method="POST" action="{{ route('consultas.destroy', $consulta) }}"
                          onsubmit="return confirm('¿Eliminar esta consulta?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-peligro">Eliminar</button>
                    </form>
                </div>
            </div>
            <p><strong>Motivo:</strong> {{ $consulta->motivo_consulta }}</p>
            @if ($consulta->observaciones_clinicas)
                <p><strong>Observaciones:</strong> {{ $consulta->observaciones_clinicas }}</p>
            @endif

            @if ($refraccion = $consulta->refraccion)
                <table>
                    <thead><tr><th>Ojo</th><th>Esférico</th><th>Cilindro</th><th>Eje</th><th>ADD</th><th>DIP</th><th>ALT</th></tr></thead>
                    <tbody>
                        <tr><td>OD</td><td>{{ $refraccion->od_esferico ?? '—' }}</td><td>{{ $refraccion->od_cilindro ?? '—' }}</td><td>{{ $refraccion->od_eje ?? '—' }}</td><td>{{ $refraccion->od_add ?? '—' }}</td><td>{{ $refraccion->od_dip ?? '—' }}</td><td>{{ $refraccion->od_alt ?? '—' }}</td></tr>
                        <tr><td>OI</td><td>{{ $refraccion->oi_esferico ?? '—' }}</td><td>{{ $refraccion->oi_cilindro ?? '—' }}</td><td>{{ $refraccion->oi_eje ?? '—' }}</td><td>{{ $refraccion->oi_add ?? '—' }}</td><td>—</td><td>{{ $refraccion->oi_alt ?? '—' }}</td></tr>
                    </tbody>
                </table>
                @foreach ($refraccion->padecimientos_refractivos ?? [] as $padecimiento)
                    <span class="badge">{{ $padecimiento }}</span>
                @endforeach
                @if ($refraccion->notas_opticas)
                    <p class="muted">{{ $refraccion->notas_opticas }}</p>
                @endif
            @endif
        </div>
    @empty
        <div class="card muted">Este paciente aún no tiene consultas.</div>
    @endforelse
@endsection

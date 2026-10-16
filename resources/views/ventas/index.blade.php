@extends('layouts.app')

@section('title', 'Compras · Óptica Reyna')

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center">
            <h2 style="margin:0">Historial de compras</h2>
            <a class="boton" href="{{ route('pacientes.index') }}">Buscar paciente</a>
        </div>
        <form method="GET" action="{{ route('ventas.index') }}" style="margin:1rem 0">
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por paciente">
            <button type="submit">Buscar</button>
        </form>

        <table>
            <thead><tr><th>Fecha</th><th>Paciente</th><th>Productos</th><th>Total</th><th>Abonado</th><th>Saldo</th><th></th></tr></thead>
            <tbody>
                @forelse ($ventas as $venta)
                    <tr>
                        <td>{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                        <td><a href="{{ route('pacientes.show', $venta->paciente) }}">{{ $venta->paciente->nombre }}</a></td>
                        <td>{{ $venta->partidas_count }}</td>
                        <td>${{ number_format((float) $venta->total, 2) }}</td>
                        <td>${{ number_format((float) ($venta->pagos_sum_monto ?? 0), 2) }}</td>
                        <td>${{ number_format(max(0, (float) $venta->total - (float) ($venta->pagos_sum_monto ?? 0)), 2) }}</td>
                        <td><a href="{{ route('ventas.show', $venta) }}">Ver detalle</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">No hay compras registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1rem">{{ $ventas->links() }}</div>
    </div>
@endsection

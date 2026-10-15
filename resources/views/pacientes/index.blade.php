@extends('layouts.app')

@section('title', 'Pacientes · Óptica Reyna')

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center">
            <h2 style="margin:0">Pacientes</h2>
            <a class="boton" href="{{ route('pacientes.create') }}">Nuevo paciente</a>
        </div>
        <form method="GET" action="{{ route('pacientes.index') }}" style="margin:1rem 0">
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por nombre">
            <button type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr><th>Nombre</th><th>Edad</th><th>Teléfono</th><th>Consultas</th><th>Última consulta</th></tr>
            </thead>
            <tbody>
                @forelse ($pacientes as $paciente)
                    <tr>
                        <td><a href="{{ route('pacientes.show', $paciente) }}">{{ $paciente->nombre }}</a></td>
                        <td>{{ $paciente->edad }} años</td>
                        <td>{{ $paciente->telefono ?? '—' }}</td>
                        <td>{{ $paciente->consultas_count }}</td>
                        <td>{{ $paciente->consultas_max_fecha_consulta ? \Illuminate\Support\Carbon::parse($paciente->consultas_max_fecha_consulta)->format('d/m/Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No hay pacientes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1rem">{{ $pacientes->links() }}</div>
    </div>
@endsection

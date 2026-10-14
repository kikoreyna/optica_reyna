@extends('layouts.app')

@section('title', 'Pacientes · Óptica Reyna')

@section('content')
    <div class="card">
        <h2 style="margin-top:0">Pacientes</h2>
        <form method="GET" action="{{ route('pacientes.index') }}" style="margin-bottom:1rem">
            <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nombre">
            <button type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr><th>Nombre</th><th>Edad</th><th>Teléfono</th><th>Consultas</th><th>Última consulta</th></tr>
            </thead>
            <tbody>
                @forelse ($patients as $patient)
                    <tr>
                        <td><a href="{{ route('pacientes.show', $patient) }}">{{ $patient->nombre }}</a></td>
                        <td>{{ $patient->edad }} años</td>
                        <td>{{ $patient->telefono ?? '—' }}</td>
                        <td>{{ $patient->consultations_count }}</td>
                        <td>{{ $patient->consultations_max_fecha_consulta ? \Illuminate\Support\Carbon::parse($patient->consultations_max_fecha_consulta)->format('d/m/Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No hay pacientes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1rem">{{ $patients->links() }}</div>
    </div>
@endsection

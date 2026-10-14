@extends('layouts.app')

@section('title', $patient->nombre . ' · Óptica Reyna')

@section('content')
    <p><a href="{{ route('pacientes.index') }}">← Volver a pacientes</a></p>

    <div class="card">
        <h2 style="margin-top:0">{{ $patient->nombre }}</h2>
        <p class="muted">
            {{ $patient->edad }} años · Nac. {{ $patient->fecha_nacimiento->format('d/m/Y') }}
            · Tel: {{ $patient->telefono ?? '—' }}
        </p>
        <p>{{ $patient->direccion ?? 'Sin dirección registrada' }}</p>
        <p>
            @forelse ($patient->diseases as $disease)
                <span class="badge">{{ $disease->nombre_enfermedad }}</span>
            @empty
                <span class="muted">Sin enfermedades registradas</span>
            @endforelse
        </p>
    </div>

    <h3>Historial de consultas</h3>
    @forelse ($patient->consultations as $consultation)
        <div class="card">
            <strong>{{ $consultation->fecha_consulta->format('d/m/Y H:i') }}</strong>
            @if ($consultation->proxima_cita)
                <span class="muted"> · Próxima cita: {{ $consultation->proxima_cita->format('d/m/Y') }}</span>
            @endif
            <p><strong>Motivo:</strong> {{ $consultation->motivo_consulta }}</p>
            @if ($consultation->observaciones_clinicas)
                <p><strong>Observaciones:</strong> {{ $consultation->observaciones_clinicas }}</p>
            @endif

            @if ($refraction = $consultation->refraction)
                <table>
                    <thead><tr><th>Ojo</th><th>Esférico</th><th>Cilindro</th><th>Eje</th><th>ADD</th><th>DIP</th><th>ALT</th></tr></thead>
                    <tbody>
                        <tr><td>OD</td><td>{{ $refraction->od_esferico ?? '—' }}</td><td>{{ $refraction->od_cilindro ?? '—' }}</td><td>{{ $refraction->od_eje ?? '—' }}</td><td>{{ $refraction->od_add ?? '—' }}</td><td>{{ $refraction->od_dip ?? '—' }}</td><td>{{ $refraction->od_alt ?? '—' }}</td></tr>
                        <tr><td>OI</td><td>{{ $refraction->oi_esferico ?? '—' }}</td><td>{{ $refraction->oi_cilindro ?? '—' }}</td><td>{{ $refraction->oi_eje ?? '—' }}</td><td>{{ $refraction->oi_add ?? '—' }}</td><td>—</td><td>{{ $refraction->oi_alt ?? '—' }}</td></tr>
                    </tbody>
                </table>
                @foreach ($refraction->padecimientos_refractivos ?? [] as $padecimiento)
                    <span class="badge">{{ $padecimiento }}</span>
                @endforeach
                @if ($refraction->notas_opticas)
                    <p class="muted">{{ $refraction->notas_opticas }}</p>
                @endif
            @endif
        </div>
    @empty
        <div class="card muted">Este paciente aún no tiene consultas.</div>
    @endforelse
@endsection

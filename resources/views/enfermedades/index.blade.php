@extends('layouts.app')

@section('title', 'Enfermedades · Óptica Reyna')

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center">
            <h2 style="margin:0">Catálogo de enfermedades</h2>
            <a class="boton" href="{{ route('enfermedades.create') }}">Nueva enfermedad</a>
        </div>

        <form method="GET" action="{{ route('enfermedades.index') }}" style="margin:1rem 0">
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por nombre">
            <button type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr><th>Nombre</th><th>Pacientes</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($enfermedades as $enfermedad)
                    <tr>
                        <td>{{ $enfermedad->nombre }}</td>
                        <td>{{ $enfermedad->pacientes_count }}</td>
                        <td class="acciones">
                            <a href="{{ route('enfermedades.edit', $enfermedad) }}">Editar</a>
                            <form method="POST" action="{{ route('enfermedades.destroy', $enfermedad) }}"
                                  onsubmit="return confirm('¿Eliminar &quot;{{ $enfermedad->nombre }}&quot;?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-peligro">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="muted">No hay enfermedades registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1rem">{{ $enfermedades->links() }}</div>
    </div>
@endsection

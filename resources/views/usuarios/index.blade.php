@extends('layouts.app')

@section('title', 'Usuarios · Óptica Reyna')

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center">
            <h2 style="margin:0">Usuarios</h2>
            <a class="boton" href="{{ route('usuarios.create') }}">Nuevo usuario</a>
        </div>

        <form method="GET" action="{{ route('usuarios.index') }}" style="margin:1rem 0">
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por nombre o correo">
            <button type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->name }} @if (auth()->user()->is($usuario)) <span class="muted">(tú)</span> @endif</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ ucfirst($usuario->role) }}</td>
                        <td class="acciones">
                            <a href="{{ route('usuarios.edit', $usuario) }}">Editar</a>
                            <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}"
                                  onsubmit="return confirm('¿Eliminar la cuenta de {{ $usuario->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-peligro">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">No hay usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1rem">{{ $usuarios->links() }}</div>
    </div>
@endsection

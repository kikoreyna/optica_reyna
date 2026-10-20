@extends('layouts.app')

@section('title', 'Editar usuario · Óptica Reyna')

@section('content')
    <div class="card">
        <h2>Editar usuario</h2>
        <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
            @csrf
            @method('PUT')
            @include('usuarios._formulario')
        </form>
    </div>
@endsection

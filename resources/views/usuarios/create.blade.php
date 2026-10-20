@extends('layouts.app')

@section('title', 'Nuevo usuario · Óptica Reyna')

@section('content')
    <div class="card">
        <h2>Crear usuario</h2>
        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf
            @include('usuarios._formulario')
        </form>
    </div>
@endsection

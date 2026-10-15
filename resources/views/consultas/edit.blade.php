@extends('layouts.app')

@section('title', 'Editar consulta · Óptica Reyna')

@section('content')
    <p><a href="{{ route('pacientes.show', $paciente) }}">← Volver a {{ $paciente->nombre }}</a></p>
    <div class="card">
        <h2 style="margin-top:0">Editar consulta · {{ $paciente->nombre }}</h2>
        <form method="POST" action="{{ route('consultas.update', $consulta) }}">
            @csrf
            @method('PUT')
            @include('consultas._formulario')
        </form>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Nueva consulta · Óptica Reyna')

@section('content')
    <p><a href="{{ route('pacientes.show', $paciente) }}">← Volver a {{ $paciente->nombre }}</a></p>
    <div class="card">
        <h2 style="margin-top:0">Nueva consulta · {{ $paciente->nombre }}</h2>
        <form method="POST" action="{{ route('pacientes.consultas.store', $paciente) }}">
            @csrf
            @include('consultas._formulario')
        </form>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Editar paciente · Óptica Reyna')

@section('content')
    <div class="card">
        <h2 style="margin-top:0">Editar paciente</h2>
        <form method="POST" action="{{ route('pacientes.update', $paciente) }}">
            @csrf
            @method('PUT')
            @include('pacientes._formulario')
        </form>
    </div>
@endsection

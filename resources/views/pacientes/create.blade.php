@extends('layouts.app')

@section('title', 'Nuevo paciente · Óptica Reyna')

@section('content')
    <div class="card">
        <h2 style="margin-top:0">Nuevo paciente</h2>
        <form method="POST" action="{{ route('pacientes.store') }}">
            @csrf
            @include('pacientes._formulario')
        </form>
    </div>
@endsection

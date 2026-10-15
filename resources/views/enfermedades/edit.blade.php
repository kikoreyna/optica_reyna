@extends('layouts.app')

@section('title', 'Editar enfermedad · Óptica Reyna')

@section('content')
    <div class="card">
        <h2 style="margin-top:0">Editar enfermedad</h2>
        <form method="POST" action="{{ route('enfermedades.update', $enfermedad) }}">
            @csrf
            @method('PUT')
            @include('enfermedades._formulario')
        </form>
    </div>
@endsection

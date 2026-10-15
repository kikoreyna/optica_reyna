@extends('layouts.app')

@section('title', 'Nueva enfermedad · Óptica Reyna')

@section('content')
    <div class="card">
        <h2 style="margin-top:0">Nueva enfermedad</h2>
        <form method="POST" action="{{ route('enfermedades.store') }}">
            @csrf
            @include('enfermedades._formulario')
        </form>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Iniciar sesión · Óptica Reyna')

@section('content')
    <div class="card" style="max-width:440px; margin:3rem auto">
        <h2>Iniciar sesión</h2>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div style="margin-bottom:1rem">
                <label for="email">Correo electrónico</label>
                <input class="campo" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div style="margin-bottom:1rem">
                <label for="password">Contraseña</label>
                <input class="campo" type="password" id="password" name="password" required autocomplete="current-password">
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <label style="font-weight:400; margin-bottom:1rem">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Mantener sesión iniciada
            </label>
            <button type="submit">Entrar</button>
        </form>
    </div>
@endsection

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Óptica Reyna')</title>
    <style>
        :root { --color-primario: #BFEAF2; --color-fondo: #EAF7FB; --color-texto: #173B45; --color-enlace: #145365; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; background: var(--color-fondo); color: var(--color-texto); }
        header { background: var(--color-primario); color: var(--color-texto); padding: 1rem 2rem; }
        header a { color: var(--color-texto); text-decoration: none; font-size: 1.25rem; font-weight: 600; }
        main { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: .6rem; border-bottom: 1px solid #e5e7eb; }
        th { font-size: .8rem; text-transform: uppercase; color: #6b7280; }
        a { color: var(--color-enlace); }
        input[type=text] { padding: .5rem; border: 1px solid #d1d5db; border-radius: 6px; width: 260px; }
        button { padding: .5rem 1rem; background: var(--color-primario); color: var(--color-texto); border: 0; border-radius: 6px; cursor: pointer; }
        .muted { color: #6b7280; }
        .badge { display: inline-block; background: var(--color-fondo); color: var(--color-enlace); padding: .1rem .6rem; border-radius: 999px; font-size: .8rem; margin-right: .3rem; }
        nav[role=navigation] svg { width: 1rem; height: 1rem; }
        header nav { display: inline-flex; gap: 1.5rem; align-items: center; }
        header nav a.activo { text-decoration: underline; }
        .alerta { padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alerta-exito { background: #dcfce7; color: #166534; }
        .error { color: #b91c1c; font-size: .85rem; margin: .25rem 0 0; }
        label { display: block; font-weight: 600; margin-bottom: .35rem; }
        .btn-secundario { background: var(--color-fondo); color: var(--color-texto); }
        .btn-peligro { background: #b91c1c; }
        .acciones { display: flex; gap: .5rem; align-items: center; }
        .acciones form { margin: 0; }
        a.boton { display: inline-block; padding: .5rem 1rem; background: var(--color-primario); color: var(--color-texto); border-radius: 6px; text-decoration: none; font-size: .9rem; }
        input, select { padding: .5rem; border: 1px solid #d1d5db; border-radius: 6px; }
        .campo { width: min(100%, 360px); }
        .nav-derecha { margin-left: auto; }
    </style>
</head>
<body>
    <header>
        <nav>
            @auth
                <a href="{{ route('pacientes.index') }}">Óptica Reyna</a>
                <a href="{{ route('pacientes.index') }}" class="{{ request()->routeIs('pacientes.*') ? 'activo' : '' }}" style="font-size:1rem">Pacientes</a>
                <a href="{{ route('ventas.index') }}" class="{{ request()->routeIs('ventas.*') ? 'activo' : '' }}" style="font-size:1rem">Compras</a>
                <a href="{{ route('enfermedades.index') }}" class="{{ request()->routeIs('enfermedades.*') ? 'activo' : '' }}" style="font-size:1rem">Enfermedades</a>
                @if (auth()->user()->isAdministrator())
                    <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'activo' : '' }}" style="font-size:1rem">Usuarios</a>
                @endif
                <span class="nav-derecha" style="font-size:.9rem">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secundario">Cerrar sesión</button>
                </form>
            @else
                <a href="{{ route('login') }}">Óptica Reyna</a>
            @endauth
        </nav>
    </header>
    <main>
        @if (session('exito'))
            <div class="alerta alerta-exito">{{ session('exito') }}</div>
        @endif
        @if (session('error'))
            <div class="alerta" style="background:#fee2e2; color:#991b1b">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>

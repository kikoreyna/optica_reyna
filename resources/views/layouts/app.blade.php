<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Óptica Reyna')</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; background: #f3f5f9; color: #1f2937; }
        header { background: #1e3a8a; color: #fff; padding: 1rem 2rem; }
        header a { color: #fff; text-decoration: none; font-size: 1.25rem; font-weight: 600; }
        main { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: .6rem; border-bottom: 1px solid #e5e7eb; }
        th { font-size: .8rem; text-transform: uppercase; color: #6b7280; }
        a { color: #1d4ed8; }
        input[type=text] { padding: .5rem; border: 1px solid #d1d5db; border-radius: 6px; width: 260px; }
        button { padding: .5rem 1rem; background: #1e3a8a; color: #fff; border: 0; border-radius: 6px; cursor: pointer; }
        .muted { color: #6b7280; }
        .badge { display: inline-block; background: #dbeafe; color: #1e40af; padding: .1rem .6rem; border-radius: 999px; font-size: .8rem; margin-right: .3rem; }
        nav[role=navigation] svg { width: 1rem; height: 1rem; }
    </style>
</head>
<body>
    <header><a href="{{ route('pacientes.index') }}">Óptica Reyna</a></header>
    <main>@yield('content')</main>
</body>
</html>

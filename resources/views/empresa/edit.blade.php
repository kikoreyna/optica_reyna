@extends('layouts.app')

@section('title', 'Datos de la empresa · '.($empresa->nombre ?: 'Óptica Reyna'))

@section('content')
    <div class="card">
        <h2>Datos de la empresa</h2>
        <p class="muted">El nombre y el logo se muestran en el encabezado y el logo también se utiliza como favicon.</p>

        @if ($empresa->logo_path)
            <div style="margin-bottom:1rem">
                <p><strong>Logo actual</strong></p>
                <img src="{{ route('empresa.logo') }}"
                     alt="Logo actual de {{ $empresa->nombre }}" style="max-width:220px; max-height:140px; object-fit:contain">
            </div>
        @endif

        <form method="POST" action="{{ route('empresa.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="margin-bottom:1rem">
                <label for="nombre">Nombre comercial</label>
                <input class="campo" type="text" id="nombre" name="nombre" value="{{ old('nombre', $empresa->nombre) }}" maxlength="255" required>
                @error('nombre') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div style="margin-bottom:1rem">
                <label for="razon_social">Razón social</label>
                <input class="campo" type="text" id="razon_social" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}" maxlength="255">
                @error('razon_social') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div style="margin-bottom:1rem">
                <label for="rfc">RFC</label>
                <input class="campo" type="text" id="rfc" name="rfc" value="{{ old('rfc', $empresa->rfc) }}" maxlength="13">
                @error('rfc') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div style="margin-bottom:1rem">
                <label for="telefono">Teléfono</label>
                <input class="campo" type="tel" id="telefono" name="telefono" value="{{ old('telefono', $empresa->telefono) }}" maxlength="30">
                @error('telefono') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div style="margin-bottom:1rem">
                <label for="correo">Correo electrónico</label>
                <input class="campo" type="email" id="correo" name="correo" value="{{ old('correo', $empresa->correo) }}" maxlength="255">
                @error('correo') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div style="margin-bottom:1rem">
                <label for="direccion">Dirección</label>
                <textarea class="campo" id="direccion" name="direccion" rows="3">{{ old('direccion', $empresa->direccion) }}</textarea>
                @error('direccion') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div style="margin-bottom:1rem">
                <label for="sitio_web">Sitio web</label>
                <input class="campo" type="url" id="sitio_web" name="sitio_web" value="{{ old('sitio_web', $empresa->sitio_web) }}" maxlength="255" placeholder="https://ejemplo.com">
                @error('sitio_web') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div style="margin-bottom:1rem">
                <label for="logo">Logo (JPG, PNG o WebP; máximo 2 MB)</label>
                <input class="campo" type="file" id="logo" name="logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                @error('logo') <p class="error">{{ $message }}</p> @enderror
            </div>

            @if ($empresa->logo_path)
                <label style="font-weight:400; margin-bottom:1rem">
                    <input type="checkbox" name="quitar_logo" value="1" @checked(old('quitar_logo'))>
                    Quitar logo actual
                </label>
            @endif

            <div class="acciones">
                <button type="submit">Guardar datos</button>
            </div>
        </form>
    </div>
@endsection

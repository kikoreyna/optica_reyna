<div style="margin-bottom:1rem">
    <label for="nombre">Nombre de la enfermedad</label>
    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $enfermedad->nombre) }}" maxlength="255" required autofocus>
    @error('nombre')
        <p class="error">{{ $message }}</p>
    @enderror
</div>

<div class="acciones">
    <button type="submit">Guardar</button>
    <a class="boton btn-secundario" href="{{ route('enfermedades.index') }}">Cancelar</a>
</div>

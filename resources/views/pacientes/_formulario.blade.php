<div style="margin-bottom:1rem">
    <label for="nombre">Nombre completo</label>
    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $paciente->nombre) }}" maxlength="255" required autofocus style="width:100%">
    @error('nombre') <p class="error">{{ $message }}</p> @enderror
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem">
    <div>
        <label for="telefono">Teléfono</label>
        <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $paciente->telefono) }}" maxlength="20" style="width:100%">
        @error('telefono') <p class="error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="fecha_nacimiento">Fecha de nacimiento</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
               value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento?->format('Y-m-d')) }}"
               max="{{ now()->format('Y-m-d') }}" required>
        @error('fecha_nacimiento') <p class="error">{{ $message }}</p> @enderror
    </div>
</div>

<div style="margin-bottom:1rem">
    <label for="direccion">Dirección</label>
    <textarea id="direccion" name="direccion" rows="2" maxlength="1000" style="width:100%">{{ old('direccion', $paciente->direccion) }}</textarea>
    @error('direccion') <p class="error">{{ $message }}</p> @enderror
</div>

<div style="margin-bottom:1rem">
    <label>Enfermedades</label>
    @php($seleccionadas = collect(old('enfermedades', $enfermedadesSeleccionadas))->map(fn ($id) => (int) $id)->all())
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:.4rem">
        @forelse ($enfermedades as $enfermedad)
            <label style="font-weight:400; display:flex; gap:.4rem; align-items:center; margin:0">
                <input type="checkbox" name="enfermedades[]" value="{{ $enfermedad->id }}" @checked(in_array($enfermedad->id, $seleccionadas, true))>
                {{ $enfermedad->nombre }}
            </label>
        @empty
            <span class="muted">No hay enfermedades en el catálogo. <a href="{{ route('enfermedades.create') }}">Agregar una</a></span>
        @endforelse
    </div>
    @error('enfermedades') <p class="error">{{ $message }}</p> @enderror
    @error('enfermedades.*') <p class="error">{{ $message }}</p> @enderror
</div>

<div class="acciones">
    <button type="submit">Guardar</button>
    <a class="boton btn-secundario" href="{{ $paciente->exists ? route('pacientes.show', $paciente) : route('pacientes.index') }}">Cancelar</a>
</div>

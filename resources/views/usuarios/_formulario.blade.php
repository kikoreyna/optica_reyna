<div style="margin-bottom:1rem">
    <label for="name">Nombre</label>
    <input class="campo" type="text" id="name" name="name" value="{{ old('name', $usuario->name) }}" maxlength="255" required autofocus>
    @error('name')
        <p class="error">{{ $message }}</p>
    @enderror
</div>

<div style="margin-bottom:1rem">
    <label for="email">Correo electrónico</label>
    <input class="campo" type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" maxlength="255" required>
    @error('email')
        <p class="error">{{ $message }}</p>
    @enderror
</div>

<div style="margin-bottom:1rem">
    <label for="role">Rol</label>
    <select class="campo" id="role" name="role" required>
        <option value="empleado" @selected(old('role', $usuario->role ?: 'empleado') === 'empleado')>Empleado</option>
        <option value="administrador" @selected(old('role', $usuario->role) === 'administrador')>Administrador</option>
    </select>
    @error('role')
        <p class="error">{{ $message }}</p>
    @enderror
</div>

<div style="margin-bottom:1rem">
    <label for="password">Contraseña {{ $usuario->exists ? '(deja en blanco para conservarla)' : '' }}</label>
    <input class="campo" type="password" id="password" name="password" @required(! $usuario->exists) autocomplete="new-password">
    @error('password')
        <p class="error">{{ $message }}</p>
    @enderror
</div>

<div style="margin-bottom:1rem">
    <label for="password_confirmation">Confirmar contraseña</label>
    <input class="campo" type="password" id="password_confirmation" name="password_confirmation" @required(! $usuario->exists) autocomplete="new-password">
</div>

<div class="acciones">
    <button type="submit">Guardar</button>
    <a class="boton btn-secundario" href="{{ route('usuarios.index') }}">Cancelar</a>
</div>

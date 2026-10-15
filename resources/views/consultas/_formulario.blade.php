<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem">
    <div>
        <label for="fecha_consulta">Fecha y hora de la consulta</label>
        <input type="datetime-local" id="fecha_consulta" name="fecha_consulta"
               value="{{ old('fecha_consulta', $consulta->fecha_consulta?->format('Y-m-d\TH:i')) }}" required>
        @error('fecha_consulta') <p class="error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="proxima_cita">Próxima cita</label>
        <input type="date" id="proxima_cita" name="proxima_cita"
               value="{{ old('proxima_cita', $consulta->proxima_cita?->format('Y-m-d')) }}">
        @error('proxima_cita') <p class="error">{{ $message }}</p> @enderror
    </div>
</div>

<div style="margin-bottom:1rem">
    <label for="motivo_consulta">Motivo de la consulta</label>
    <textarea id="motivo_consulta" name="motivo_consulta" rows="2" maxlength="2000" required style="width:100%">{{ old('motivo_consulta', $consulta->motivo_consulta) }}</textarea>
    @error('motivo_consulta') <p class="error">{{ $message }}</p> @enderror
</div>

<div style="margin-bottom:1rem">
    <label for="observaciones_clinicas">Observaciones clínicas</label>
    <textarea id="observaciones_clinicas" name="observaciones_clinicas" rows="3" maxlength="5000" style="width:100%">{{ old('observaciones_clinicas', $consulta->observaciones_clinicas) }}</textarea>
    @error('observaciones_clinicas') <p class="error">{{ $message }}</p> @enderror
</div>

<h3>Refracción <span class="muted" style="font-size:.85rem; font-weight:400">(opcional)</span></h3>

@php
    $campo = fn (string $nombre) => old($nombre, $refraccion->{$nombre});
    $celda = function (string $nombre, string $paso, string $min, string $max) use ($campo) {
        $valor = $campo($nombre);
        $valor = is_numeric($valor) && str_contains($paso, '.') ? number_format((float) $valor, 2, '.', '') : $valor;

        return '<input type="number" name="'.$nombre.'" value="'.e($valor).'" step="'.$paso.'" min="'.$min.'" max="'.$max.'" style="width:90px">';
    };
@endphp

<table style="margin-bottom:.5rem">
    <thead><tr><th>Ojo</th><th>Esférico</th><th>Cilindro</th><th>Eje</th><th>ADD</th><th>DIP</th><th>ALT</th></tr></thead>
    <tbody>
        <tr>
            <td>OD</td>
            <td>{!! $celda('od_esferico', '0.25', '-20', '20') !!}</td>
            <td>{!! $celda('od_cilindro', '0.25', '-10', '10') !!}</td>
            <td>{!! $celda('od_eje', '1', '0', '180') !!}</td>
            <td>{!! $celda('od_add', '0.25', '0', '5') !!}</td>
            <td>{!! $celda('od_dip', '1', '0', '99') !!}</td>
            <td>{!! $celda('od_alt', '1', '0', '99') !!}</td>
        </tr>
        <tr>
            <td>OI</td>
            <td>{!! $celda('oi_esferico', '0.25', '-20', '20') !!}</td>
            <td>{!! $celda('oi_cilindro', '0.25', '-10', '10') !!}</td>
            <td>{!! $celda('oi_eje', '1', '0', '180') !!}</td>
            <td>{!! $celda('oi_add', '0.25', '0', '5') !!}</td>
            <td class="muted">—</td>
            <td>{!! $celda('oi_alt', '1', '0', '99') !!}</td>
        </tr>
    </tbody>
</table>
@foreach (['od_esferico', 'od_cilindro', 'od_eje', 'od_add', 'od_dip', 'od_alt', 'oi_esferico', 'oi_cilindro', 'oi_eje', 'oi_add', 'oi_alt'] as $nombre)
    @error($nombre) <p class="error">{{ $nombre }}: {{ $message }}</p> @enderror
@endforeach

<div style="margin:1rem 0">
    <label>Padecimientos refractivos</label>
    @php($marcados = old('padecimientos_refractivos', $refraccion->padecimientos_refractivos ?? []))
    <div style="display:flex; gap:1.25rem; flex-wrap:wrap">
        @foreach (\App\Models\Refraccion::PADECIMIENTOS as $padecimiento)
            <label style="font-weight:400; display:flex; gap:.4rem; align-items:center; margin:0">
                <input type="checkbox" name="padecimientos_refractivos[]" value="{{ $padecimiento }}" @checked(in_array($padecimiento, $marcados, true))>
                {{ $padecimiento }}
            </label>
        @endforeach
    </div>
    @error('padecimientos_refractivos.*') <p class="error">{{ $message }}</p> @enderror
</div>

<div style="margin-bottom:1rem">
    <label for="notas_opticas">Notas ópticas</label>
    <textarea id="notas_opticas" name="notas_opticas" rows="2" maxlength="2000" style="width:100%">{{ old('notas_opticas', $refraccion->notas_opticas) }}</textarea>
    @error('notas_opticas') <p class="error">{{ $message }}</p> @enderror
</div>

<div class="acciones">
    <button type="submit">Guardar</button>
    <a class="boton btn-secundario" href="{{ route('pacientes.show', $paciente) }}">Cancelar</a>
</div>

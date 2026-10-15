<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Refraccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConsultaController extends Controller
{
    private const CAMPOS_REFRACCION = [
        'od_esferico', 'od_cilindro', 'od_eje', 'od_add', 'od_dip', 'od_alt',
        'oi_esferico', 'oi_cilindro', 'oi_eje', 'oi_add', 'oi_alt',
        'padecimientos_refractivos', 'notas_opticas',
    ];

    public function create(Paciente $paciente): View
    {
        return view('consultas.create', [
            'paciente' => $paciente,
            'consulta' => new Consulta(['fecha_consulta' => now()]),
            'refraccion' => new Refraccion(),
        ]);
    }

    public function store(Request $request, Paciente $paciente): RedirectResponse
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos, $paciente): void {
            $consulta = $paciente->consultas()->create(
                Arr::except($datos, self::CAMPOS_REFRACCION)
            );

            $this->guardarRefraccion($consulta, $datos);
        });

        return redirect()->route('pacientes.show', $paciente)->with('exito', 'Consulta registrada correctamente.');
    }

    public function edit(Consulta $consulta): View
    {
        $consulta->load(['paciente', 'refraccion']);

        return view('consultas.edit', [
            'paciente' => $consulta->paciente,
            'consulta' => $consulta,
            'refraccion' => $consulta->refraccion ?? new Refraccion(),
        ]);
    }

    public function update(Request $request, Consulta $consulta): RedirectResponse
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos, $consulta): void {
            $consulta->update(Arr::except($datos, self::CAMPOS_REFRACCION));
            $this->guardarRefraccion($consulta, $datos);
        });

        return redirect()->route('pacientes.show', $consulta->paciente_id)->with('exito', 'Consulta actualizada correctamente.');
    }

    public function destroy(Consulta $consulta): RedirectResponse
    {
        $consulta->delete();

        return redirect()->route('pacientes.show', $consulta->paciente_id)->with('exito', 'Consulta eliminada correctamente.');
    }

    private function guardarRefraccion(Consulta $consulta, array $datos): void
    {
        $refraccion = Arr::only($datos, self::CAMPOS_REFRACCION);
        $refraccion['padecimientos_refractivos'] = array_values($refraccion['padecimientos_refractivos'] ?? []);

        $conDatos = array_filter(
            $refraccion,
            fn ($valor) => $valor !== null && $valor !== [] && $valor !== ''
        );

        if ($conDatos === []) {
            $consulta->refraccion()->delete();

            return;
        }

        Refraccion::updateOrCreate(
            ['consulta_id' => $consulta->id],
            $refraccion + ['paciente_id' => $consulta->paciente_id]
        );
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'fecha_consulta' => ['required', 'date'],
            'motivo_consulta' => ['required', 'string', 'max:2000'],
            'observaciones_clinicas' => ['nullable', 'string', 'max:5000'],
            'proxima_cita' => ['nullable', 'date', 'after_or_equal:fecha_consulta'],

            'od_esferico' => ['nullable', 'numeric', 'between:-20,20'],
            'od_cilindro' => ['nullable', 'numeric', 'between:-10,10'],
            'od_eje' => ['nullable', 'integer', 'between:0,180'],
            'od_add' => ['nullable', 'numeric', 'between:0,5'],
            'od_dip' => ['nullable', 'integer', 'between:0,99'],
            'od_alt' => ['nullable', 'integer', 'between:0,99'],
            'oi_esferico' => ['nullable', 'numeric', 'between:-20,20'],
            'oi_cilindro' => ['nullable', 'numeric', 'between:-10,10'],
            'oi_eje' => ['nullable', 'integer', 'between:0,180'],
            'oi_add' => ['nullable', 'numeric', 'between:0,5'],
            'oi_alt' => ['nullable', 'integer', 'between:0,99'],
            'padecimientos_refractivos' => ['nullable', 'array'],
            'padecimientos_refractivos.*' => ['string', 'in:'.implode(',', Refraccion::PADECIMIENTOS)],
            'notas_opticas' => ['nullable', 'string', 'max:2000'],
        ], [
            'fecha_consulta.required' => 'La fecha de la consulta es obligatoria.',
            'fecha_consulta.date' => 'La fecha de la consulta no es válida.',
            'motivo_consulta.required' => 'El motivo de la consulta es obligatorio.',
            'motivo_consulta.max' => 'El motivo no puede superar los 2000 caracteres.',
            'observaciones_clinicas.max' => 'Las observaciones no pueden superar los 5000 caracteres.',
            'proxima_cita.date' => 'La fecha de la próxima cita no es válida.',
            'proxima_cita.after_or_equal' => 'La próxima cita no puede ser anterior a la consulta.',
            '*.numeric' => 'Debe ser un número.',
            '*.integer' => 'Debe ser un número entero.',
            '*.between' => 'El valor está fuera del rango permitido (:min a :max).',
            'padecimientos_refractivos.*.in' => 'Padecimiento no válido.',
            'notas_opticas.max' => 'Las notas no pueden superar los 2000 caracteres.',
        ]);
    }
}

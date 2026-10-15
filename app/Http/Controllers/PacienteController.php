<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Enfermedad;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PacienteController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q', ''));

        $pacientes = Paciente::query()
            ->withCount('consultas')
            ->withMax('consultas', 'fecha_consulta')
            ->when($busqueda !== '', fn ($consulta) => $consulta->where('nombre', 'like', "%{$busqueda}%"))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('pacientes.index', compact('pacientes', 'busqueda'));
    }

    public function create(): View
    {
        return view('pacientes.create', $this->datosFormulario(new Paciente()));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $paciente = Paciente::create($datos);
        $paciente->enfermedades()->sync($datos['enfermedades'] ?? []);

        return redirect()->route('pacientes.show', $paciente)->with('exito', 'Paciente registrado correctamente.');
    }

    public function show(Paciente $paciente): View
    {
        $paciente->load([
            'enfermedades',
            'consultas' => fn ($consulta) => $consulta->latest('fecha_consulta'),
            'consultas.refraccion',
        ]);

        return view('pacientes.show', compact('paciente'));
    }

    public function edit(Paciente $paciente): View
    {
        return view('pacientes.edit', $this->datosFormulario($paciente));
    }

    public function update(Request $request, Paciente $paciente): RedirectResponse
    {
        $datos = $this->validar($request);

        $paciente->update($datos);
        $paciente->enfermedades()->sync($datos['enfermedades'] ?? []);

        return redirect()->route('pacientes.show', $paciente)->with('exito', 'Paciente actualizado correctamente.');
    }

    public function destroy(Paciente $paciente): RedirectResponse
    {
        $paciente->delete();

        return redirect()->route('pacientes.index')->with('exito', 'Paciente eliminado correctamente.');
    }

    private function datosFormulario(Paciente $paciente): array
    {
        return [
            'paciente' => $paciente,
            'enfermedades' => Enfermedad::orderBy('nombre')->get(),
            'enfermedadesSeleccionadas' => $paciente->exists ? $paciente->enfermedades()->pluck('enfermedades.id')->all() : [],
        ];
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:1000'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'enfermedades' => ['nullable', 'array'],
            'enfermedades.*' => ['integer', 'exists:enfermedades,id'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',
            'direccion.max' => 'La dirección no puede superar los 1000 caracteres.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'enfermedades.*.exists' => 'Una de las enfermedades seleccionadas no existe.',
        ]);
    }
}

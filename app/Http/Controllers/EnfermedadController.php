<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Enfermedad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnfermedadController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q', ''));

        $enfermedades = Enfermedad::query()
            ->withCount('pacientes')
            ->when($busqueda !== '', fn ($consulta) => $consulta->where('nombre', 'like', "%{$busqueda}%"))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('enfermedades.index', compact('enfermedades', 'busqueda'));
    }

    public function create(): View
    {
        return view('enfermedades.create', ['enfermedad' => new Enfermedad()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Enfermedad::create($this->validar($request));

        return redirect()->route('enfermedades.index')->with('exito', 'Enfermedad registrada correctamente.');
    }

    public function edit(Enfermedad $enfermedad): View
    {
        return view('enfermedades.edit', compact('enfermedad'));
    }

    public function update(Request $request, Enfermedad $enfermedad): RedirectResponse
    {
        $enfermedad->update($this->validar($request, $enfermedad));

        return redirect()->route('enfermedades.index')->with('exito', 'Enfermedad actualizada correctamente.');
    }

    public function destroy(Enfermedad $enfermedad): RedirectResponse
    {
        $enfermedad->delete();

        return redirect()->route('enfermedades.index')->with('exito', 'Enfermedad eliminada correctamente.');
    }

    private function validar(Request $request, ?Enfermedad $enfermedad = null): array
    {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('enfermedades', 'nombre')->ignore($enfermedad?->id),
            ],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'nombre.unique' => 'Ya existe una enfermedad con ese nombre.',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmpresaController extends Controller
{
    public function logo(): BinaryFileResponse
    {
        $path = Empresa::query()->find(1)?->logo_path;

        abort_unless($path && Storage::disk('public')->exists($path), 404);

        $file = Storage::disk('public')->path($path);

        return response()->file($file, [
            'Content-Type' => mime_content_type($file),
            'Cache-Control' => 'public, max-age=0, must-revalidate',
        ]);
    }

    public function edit(): View
    {
        $empresa = Empresa::firstOrCreate(['id' => 1]);

        return view('empresa.edit', compact('empresa'));
    }

    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:13', 'regex:/^[A-Za-z&Ññ]{3,4}\d{6}[A-Za-z0-9]{3}$/'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:2000'],
            'sitio_web' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'quitar_logo' => ['nullable', 'boolean'],
        ], [
            'nombre.required' => 'El nombre comercial es obligatorio.',
            'rfc.regex' => 'Ingresa un RFC válido.',
            'correo.email' => 'Ingresa un correo válido.',
            'sitio_web.url' => 'Ingresa una dirección web válida, incluyendo https://.',
            'logo.image' => 'El archivo debe ser una imagen válida.',
            'logo.mimes' => 'El logo debe estar en formato JPG, PNG o WebP.',
            'logo.max' => 'El logo no puede superar los 2 MB.',
        ]);

        $empresa = Empresa::firstOrCreate(['id' => 1]);
        $logoAnterior = $empresa->logo_path;

        if ($request->hasFile('logo')) {
            $logoNuevo = $request->file('logo')->store('empresa', 'public');

            if ($logoNuevo === false) {
                throw new RuntimeException('No se pudo guardar el logo de la empresa.');
            }

            $datos['logo_path'] = $logoNuevo;
        } elseif ($request->boolean('quitar_logo')) {
            $datos['logo_path'] = null;
        }

        unset($datos['quitar_logo']);
        $empresa->update($datos);

        if ($logoAnterior && $logoAnterior !== $empresa->logo_path) {
            $disco = Storage::disk('public');

            if ($disco->exists($logoAnterior) && ! $disco->delete($logoAnterior)) {
                throw new RuntimeException('No se pudo eliminar el logo anterior de la empresa.');
            }
        }

        return redirect()->route('empresa.edit')->with('exito', 'Los datos de la empresa se guardaron correctamente.');
    }
}

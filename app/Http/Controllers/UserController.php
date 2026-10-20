<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q', ''));

        $usuarios = User::query()
            ->when($busqueda !== '', fn ($consulta) => $consulta->where(function ($consulta) use ($busqueda) {
                $consulta->where('name', 'like', "%{$busqueda}%")
                    ->orWhere('email', 'like', "%{$busqueda}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios', 'busqueda'));
    }

    public function create(): View
    {
        return view('usuarios.create', ['usuario' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validar($request, true));

        return redirect()->route('usuarios.index')->with('exito', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $datos = $this->validar($request, false, $usuario);

        if ($usuario->isAdministrator()
            && $datos['role'] !== User::ROLE_ADMINISTRADOR
            && User::where('role', User::ROLE_ADMINISTRADOR)->count() === 1) {
            return back()
                ->withErrors(['role' => 'No se puede quitar el rol al último administrador.'])
                ->withInput();
        }

        if ($request->user()->is($usuario) && $usuario->isAdministrator()
            && $datos['role'] !== User::ROLE_ADMINISTRADOR) {
            return back()
                ->withErrors(['role' => 'No puedes quitarte tu propio rol de administrador.'])
                ->withInput();
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('exito', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($request->user()->is($usuario)) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($usuario->isAdministrator()
            && User::where('role', User::ROLE_ADMINISTRADOR)->count() === 1) {
            return back()->with('error', 'No se puede eliminar al último administrador.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('exito', 'Usuario eliminado correctamente.');
    }

    private function validar(Request $request, bool $crear, ?User $usuario = null): array
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($usuario?->id),
            ],
            'role' => ['required', Rule::in([User::ROLE_ADMINISTRADOR, User::ROLE_EMPLEADO])],
            'password' => [$crear ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'role.required' => 'Selecciona un rol.',
            'role.in' => 'El rol seleccionado no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        }

        return $datos;
    }
}

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('redirige a iniciar sesión cuando un visitante solicita una ruta protegida', function () {
    $this->get(route('pacientes.index'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('url.intended', route('pacientes.index'));
});

it('permite iniciar y cerrar sesión con credenciales válidas', function () {
    $usuario = User::factory()->create(['password' => 'clave-segura']);

    $this->post(route('login.store'), [
        'email' => $usuario->email,
        'password' => 'clave-segura',
    ])->assertRedirect(route('pacientes.index'));

    $this->assertAuthenticatedAs($usuario);

    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();
});

it('rechaza credenciales incorrectas', function () {
    $usuario = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $usuario->email,
            'password' => 'incorrecta',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('permite al administrador crear usuarios con cualquiera de los roles', function () {
    $administrador = User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);
    $this->actingAs($administrador);

    $this->post(route('usuarios.store'), [
        'name' => 'Empleado Nuevo',
        'email' => 'empleado@example.com',
        'role' => User::ROLE_EMPLEADO,
        'password' => 'clave-segura',
        'password_confirmation' => 'clave-segura',
    ])->assertRedirect(route('usuarios.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'empleado@example.com',
        'role' => User::ROLE_EMPLEADO,
    ]);
    expect(Hash::check('clave-segura', User::where('email', 'empleado@example.com')->firstOrFail()->password))->toBeTrue();

    $this->post(route('usuarios.store'), [
        'name' => 'Administradora Nueva',
        'email' => 'administradora@example.com',
        'role' => User::ROLE_ADMINISTRADOR,
        'password' => 'clave-segura',
        'password_confirmation' => 'clave-segura',
    ])->assertRedirect(route('usuarios.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'administradora@example.com',
        'role' => User::ROLE_ADMINISTRADOR,
    ]);
});

it('permite al administrador editar, buscar y eliminar usuarios', function () {
    $administrador = User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);
    $usuario = User::factory()->create(['name' => 'Empleado Inicial']);
    $this->actingAs($administrador);

    $this->get(route('usuarios.index', ['q' => 'Empleado Inicial']))
        ->assertOk()
        ->assertSee('Empleado Inicial');

    $this->put(route('usuarios.update', $usuario), [
        'name' => 'Empleado Actualizado',
        'email' => $usuario->email,
        'role' => User::ROLE_ADMINISTRADOR,
        'password' => '',
        'password_confirmation' => '',
    ])->assertRedirect(route('usuarios.index'));

    expect($usuario->fresh()->role)->toBe(User::ROLE_ADMINISTRADOR);

    $this->delete(route('usuarios.destroy', $usuario->fresh()))
        ->assertRedirect(route('usuarios.index'));

    $this->assertDatabaseMissing('users', ['id' => $usuario->id]);
});

it('impide que empleados administren usuarios y permite sus rutas operativas', function () {
    $empleado = User::factory()->create(['role' => User::ROLE_EMPLEADO]);
    $this->actingAs($empleado);

    $this->get(route('usuarios.index'))->assertForbidden();
    $this->get(route('pacientes.index'))->assertOk();
});

it('impide que el último administrador se elimine o pierda su rol', function () {
    $administrador = User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);
    $this->actingAs($administrador);

    $this->delete(route('usuarios.destroy', $administrador))
        ->assertRedirect()
        ->assertSessionHas('error');
    $this->assertDatabaseHas('users', ['id' => $administrador->id]);

    $this->put(route('usuarios.update', $administrador), [
        'name' => $administrador->name,
        'email' => $administrador->email,
        'role' => User::ROLE_EMPLEADO,
        'password' => '',
        'password_confirmation' => '',
    ])->assertSessionHasErrors('role');

    expect($administrador->fresh()->role)->toBe(User::ROLE_ADMINISTRADOR);
});

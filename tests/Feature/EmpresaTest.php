<?php

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

function imagenPngDePrueba(string $nombre): UploadedFile
{
    $contenido = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC');

    return UploadedFile::fake()->createWithContent($nombre, $contenido);
}

it('permite al administrador consultar y guardar los datos de la empresa', function () {
    $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]));

    $this->get(route('empresa.edit'))
        ->assertOk()
        ->assertSee('Datos de la empresa');

    $this->put(route('empresa.update'), [
        'nombre' => 'Óptica del Centro',
        'razon_social' => 'Óptica del Centro SA de CV',
        'rfc' => 'OCE850101AB1',
        'telefono' => '5551234567',
        'correo' => 'contacto@example.com',
        'direccion' => 'Av. Principal 123',
        'sitio_web' => 'https://example.com',
    ])->assertRedirect(route('empresa.edit'));

    $this->assertDatabaseHas('empresa', [
        'id' => 1,
        'nombre' => 'Óptica del Centro',
        'rfc' => 'OCE850101AB1',
    ]);
});

it('guarda, muestra y permite quitar el logo usado como favicon', function () {
    $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]));

    $this->put(route('empresa.update'), [
        'nombre' => 'Óptica Reyna',
        'logo' => imagenPngDePrueba('logo.png'),
    ])->assertRedirect(route('empresa.edit'));

    $empresa = Empresa::findOrFail(1);
    Storage::disk('public')->assertExists($empresa->logo_path);

    $this->post(route('logout'));

    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('empresa.logo'), false)
        ->assertSee('Logo de Óptica Reyna');

    $this->get(route('empresa.logo'))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');

    $this->actingAs(User::where('role', User::ROLE_ADMINISTRADOR)->firstOrFail());

    $this->put(route('empresa.update'), [
        'nombre' => $empresa->nombre,
        'quitar_logo' => '1',
    ])->assertRedirect(route('empresa.edit'));

    Storage::disk('public')->assertMissing($empresa->logo_path);
    expect($empresa->fresh()->logo_path)->toBeNull();
});

it('reemplaza el logo y elimina el archivo anterior', function () {
    $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]));
    $empresa = Empresa::create(['id' => 1, 'nombre' => 'Óptica Reyna']);
    $logoAnterior = imagenPngDePrueba('anterior.png')->store('empresa', 'public');
    $empresa->update(['logo_path' => $logoAnterior]);

    $this->put(route('empresa.update'), [
        'nombre' => $empresa->nombre,
        'logo' => imagenPngDePrueba('nuevo.png'),
    ])->assertRedirect(route('empresa.edit'));

    Storage::disk('public')->assertMissing($logoAnterior);
    Storage::disk('public')->assertExists($empresa->fresh()->logo_path);
});

it('valida los datos de contacto y solo admite imágenes seguras como logo', function () {
    $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]));

    $this->put(route('empresa.update'), [
        'nombre' => '',
        'correo' => 'no-es-correo',
        'rfc' => 'INVALIDO',
        'sitio_web' => 'no-es-url',
        'logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
    ])->assertSessionHasErrors(['nombre', 'correo', 'rfc', 'sitio_web', 'logo']);
});

it('impide que los empleados modifiquen los datos de la empresa', function () {
    $this->actingAs(User::factory()->create(['role' => User::ROLE_EMPLEADO]));

    $this->get(route('empresa.edit'))->assertForbidden();
    $this->put(route('empresa.update'), ['nombre' => 'No autorizado'])->assertForbidden();
});

<?php

use App\Models\Enfermedad;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lista el catálogo de enfermedades', function () {
    Enfermedad::create(['nombre' => 'Glaucoma']);

    $this->get(route('enfermedades.index'))
        ->assertOk()
        ->assertSee('Glaucoma');
});

it('crea una enfermedad', function () {
    $this->post(route('enfermedades.store'), ['nombre' => 'Cataratas'])
        ->assertRedirect(route('enfermedades.index'));

    $this->assertDatabaseHas('enfermedades', ['nombre' => 'Cataratas']);
});

it('valida que el nombre sea obligatorio y único', function () {
    Enfermedad::create(['nombre' => 'Glaucoma']);

    $this->post(route('enfermedades.store'), ['nombre' => ''])->assertSessionHasErrors('nombre');
    $this->post(route('enfermedades.store'), ['nombre' => 'Glaucoma'])->assertSessionHasErrors('nombre');
});

it('edita una enfermedad conservando su propio nombre', function () {
    $enfermedad = Enfermedad::create(['nombre' => 'Glaucoma']);

    $this->put(route('enfermedades.update', $enfermedad), ['nombre' => 'Glaucoma'])
        ->assertRedirect(route('enfermedades.index'));

    $this->put(route('enfermedades.update', $enfermedad), ['nombre' => 'Glaucoma agudo'])
        ->assertRedirect(route('enfermedades.index'));

    expect($enfermedad->fresh()->nombre)->toBe('Glaucoma agudo');
});

it('elimina una enfermedad', function () {
    $enfermedad = Enfermedad::create(['nombre' => 'Glaucoma']);

    $this->delete(route('enfermedades.destroy', $enfermedad))
        ->assertRedirect(route('enfermedades.index'));

    $this->assertDatabaseMissing('enfermedades', ['nombre' => 'Glaucoma']);
});

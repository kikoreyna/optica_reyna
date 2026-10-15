<?php

use App\Models\Consulta;
use App\Models\Enfermedad;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function datosPaciente(array $extra = []): array
{
    return array_merge([
        'nombre' => 'Ana Torres',
        'telefono' => '5511223344',
        'direccion' => 'Calle 1',
        'fecha_nacimiento' => '1990-05-10',
    ], $extra);
}

function datosConsulta(array $extra = []): array
{
    return array_merge([
        'fecha_consulta' => '2026-10-01T10:30',
        'motivo_consulta' => 'Revisión general',
    ], $extra);
}

it('muestran los formularios de pacientes', function () {
    $paciente = Paciente::create(datosPaciente());

    $this->get(route('pacientes.index'))->assertOk()->assertSee('Nuevo paciente');
    $this->get(route('pacientes.create'))->assertOk();
    $this->get(route('pacientes.edit', $paciente))->assertOk()->assertSee('Ana Torres');
    $this->get(route('pacientes.show', $paciente))->assertOk()->assertSee('Nueva consulta');
});

it('crea un paciente con enfermedades', function () {
    $glaucoma = Enfermedad::create(['nombre' => 'Glaucoma']);

    $this->post(route('pacientes.store'), datosPaciente(['enfermedades' => [$glaucoma->id]]))
        ->assertRedirect();

    $paciente = Paciente::firstWhere('nombre', 'Ana Torres');
    expect($paciente->enfermedades->pluck('nombre')->all())->toBe(['Glaucoma']);
});

it('valida los datos del paciente', function () {
    $this->post(route('pacientes.store'), datosPaciente(['nombre' => '', 'fecha_nacimiento' => '2999-01-01']))
        ->assertSessionHasErrors(['nombre', 'fecha_nacimiento']);

    $this->post(route('pacientes.store'), datosPaciente(['enfermedades' => [999]]))
        ->assertSessionHasErrors('enfermedades.*');
});

it('actualiza un paciente y sincroniza sus enfermedades', function () {
    $a = Enfermedad::create(['nombre' => 'A']);
    $b = Enfermedad::create(['nombre' => 'B']);
    $paciente = Paciente::create(datosPaciente());
    $paciente->enfermedades()->attach($a);

    $this->put(route('pacientes.update', $paciente), datosPaciente(['nombre' => 'Ana Ruiz', 'enfermedades' => [$b->id]]))
        ->assertRedirect(route('pacientes.show', $paciente));

    expect($paciente->fresh()->nombre)->toBe('Ana Ruiz')
        ->and($paciente->enfermedades()->pluck('enfermedades.id')->all())->toBe([$b->id]);

    $this->put(route('pacientes.update', $paciente), datosPaciente());
    expect($paciente->enfermedades()->count())->toBe(0);
});

it('elimina un paciente con sus consultas', function () {
    $paciente = Paciente::create(datosPaciente());
    $paciente->consultas()->create(datosConsulta());

    $this->delete(route('pacientes.destroy', $paciente))->assertRedirect(route('pacientes.index'));

    $this->assertDatabaseCount('pacientes', 0);
    $this->assertDatabaseCount('consultas', 0);
});

it('muestra los formularios de consultas', function () {
    $paciente = Paciente::create(datosPaciente());
    $consulta = $paciente->consultas()->create(datosConsulta());

    $this->get(route('pacientes.consultas.create', $paciente))->assertOk();
    $this->get(route('consultas.edit', $consulta))->assertOk()->assertSee('Revisión general');
});

it('crea una consulta con refracción', function () {
    $paciente = Paciente::create(datosPaciente());

    $this->post(route('pacientes.consultas.store', $paciente), datosConsulta([
        'od_esferico' => '-1.25',
        'od_eje' => '90',
        'oi_alt' => '18',
        'padecimientos_refractivos' => ['Miopía'],
    ]))->assertRedirect(route('pacientes.show', $paciente));

    $consulta = $paciente->consultas()->first();
    expect($consulta->refraccion->od_esferico)->toBe('-1.25')
        ->and($consulta->refraccion->padecimientos_refractivos)->toBe(['Miopía'])
        ->and($consulta->refraccion->paciente_id)->toBe($paciente->id);
});

it('crea una consulta sin refracción si no se captura ningún dato', function () {
    $paciente = Paciente::create(datosPaciente());

    $this->post(route('pacientes.consultas.store', $paciente), datosConsulta())->assertRedirect();

    $this->assertDatabaseCount('consultas', 1);
    $this->assertDatabaseCount('refracciones', 0);
});

it('valida los datos de la consulta', function () {
    $paciente = Paciente::create(datosPaciente());

    $this->post(route('pacientes.consultas.store', $paciente), datosConsulta([
        'motivo_consulta' => '',
        'proxima_cita' => '2020-01-01',
        'od_eje' => '500',
        'od_esferico' => 'abc',
        'padecimientos_refractivos' => ['Inventado'],
    ]))->assertSessionHasErrors(['motivo_consulta', 'proxima_cita', 'od_eje', 'od_esferico', 'padecimientos_refractivos.0']);

    $this->assertDatabaseCount('consultas', 0);
});

it('actualiza una consulta y su refracción, y la elimina si se vacía', function () {
    $paciente = Paciente::create(datosPaciente());
    $consulta = $paciente->consultas()->create(datosConsulta());

    $this->put(route('consultas.update', $consulta), datosConsulta(['motivo_consulta' => 'Control', 'od_esferico' => '1.00']))
        ->assertRedirect(route('pacientes.show', $paciente));

    expect($consulta->fresh()->motivo_consulta)->toBe('Control')
        ->and($consulta->fresh()->refraccion->od_esferico)->toBe('1.00');

    $this->put(route('consultas.update', $consulta), datosConsulta());
    $this->assertDatabaseCount('refracciones', 0);
});

it('elimina una consulta', function () {
    $paciente = Paciente::create(datosPaciente());
    $consulta = $paciente->consultas()->create(datosConsulta());

    $this->delete(route('consultas.destroy', $consulta))->assertRedirect(route('pacientes.show', $paciente));

    expect(Consulta::count())->toBe(0);
});

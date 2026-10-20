<?php

use App\Models\Paciente;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function crearPacienteParaVenta(array $extra = []): Paciente
{
    return Paciente::create(array_merge([
        'nombre' => 'Ana Torres',
        'telefono' => '5511223344',
        'direccion' => 'Calle 1',
        'fecha_nacimiento' => '1990-05-10',
    ], $extra));
}

function datosVenta(array $extra = []): array
{
    return array_merge([
        'fecha_venta' => now()->toDateString(),
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['descripcion' => 'Armazón', 'cantidad' => 2, 'precio_unitario' => '12.50'],
            ['descripcion' => 'Estuche', 'cantidad' => 1, 'precio_unitario' => '15.50'],
        ],
    ], $extra);
}

it('registra compras con sus partidas y calcula el total en el servidor', function () {
    $paciente = crearPacienteParaVenta();

    $this->get(route('pacientes.ventas.create', $paciente))
        ->assertOk()
        ->assertSee('Agregar producto');

    $respuesta = $this->post(route('pacientes.ventas.store', $paciente), datosVenta([
        'total' => '0.01',
        'pago_inicial' => '10.00',
    ]))->assertRedirect();

    $venta = Venta::with(['partidas', 'pagos'])->firstOrFail();

    expect($venta->total)->toBe('40.50')
        ->and($venta->partidas)->toHaveCount(2)
        ->and($venta->partidas[0]->subtotal)->toBe('25.00')
        ->and($venta->partidas[1]->subtotal)->toBe('15.50')
        ->and($venta->pagos)->toHaveCount(1)
        ->and($venta->pagos[0]->monto)->toBe('10.00');

    $this->get($respuesta->headers->get('Location'))->assertOk()->assertSee('40.50')->assertSee('30.50');
    $this->get(route('pacientes.show', $paciente))->assertOk()->assertSee('Historial de compras');
    $this->get(route('ventas.index'))->assertOk()->assertSee('Ana Torres');
});

it('permite registrar varios abonos y liquida la compra al cubrir el saldo', function () {
    $paciente = crearPacienteParaVenta();
    $this->post(route('pacientes.ventas.store', $paciente), datosVenta([
        'pago_inicial' => '10.00',
    ]))->assertRedirect();
    $venta = Venta::firstOrFail();

    $this->post(route('ventas.abonos.store', $venta), [
        'fecha_pago' => now()->toDateString(),
        'monto' => '12.25',
        'metodo_pago' => 'tarjeta',
        'notas' => 'Primer abono',
    ])->assertRedirect(route('ventas.show', $venta));

    $this->post(route('ventas.abonos.store', $venta), [
        'fecha_pago' => now()->toDateString(),
        'monto' => '18.25',
        'metodo_pago' => 'transferencia',
    ])->assertRedirect(route('ventas.show', $venta));

    $this->assertDatabaseCount('pagos_venta', 3);
    $this->get(route('ventas.show', $venta))
        ->assertOk()
        ->assertSee('Compra liquidada')
        ->assertSee('Primer abono')
        ->assertSee('$40.50');
});

it('permite editar los datos y productos de una compra sin alterar sus abonos', function () {
    $paciente = crearPacienteParaVenta();
    $this->post(route('pacientes.ventas.store', $paciente), datosVenta([
        'pago_inicial' => '10.00',
    ]))->assertRedirect();
    $venta = Venta::firstOrFail();

    $this->get(route('ventas.edit', $venta))
        ->assertOk()
        ->assertSee('Editar compra')
        ->assertSee('Guardar cambios')
        ->assertSee('Monto que se abona')
        ->assertSee('Armazón');

    $this->put(route('ventas.update', $venta), [
        'fecha_venta' => now()->toDateString(),
        'notas' => 'Compra corregida',
        'abono_monto' => '5.00',
        'abono_fecha' => now()->toDateString(),
        'abono_metodo_pago' => 'tarjeta',
        'abono_notas' => 'Abono registrado al editar',
        'productos' => [
            ['descripcion' => 'Lentes graduados', 'cantidad' => 1, 'precio_unitario' => '35.00'],
        ],
    ])->assertRedirect(route('ventas.show', $venta));

    $venta->refresh()->load(['partidas', 'pagos']);
    expect($venta->total)->toBe('35.00')
        ->and($venta->notas)->toBe('Compra corregida')
        ->and($venta->partidas)->toHaveCount(1)
        ->and($venta->partidas[0]->descripcion)->toBe('Lentes graduados')
        ->and($venta->pagos)->toHaveCount(2)
        ->and($venta->pagos->sum('monto'))->toBe(15.0)
        ->and($venta->pagos[1]->monto)->toBe('5.00')
        ->and($venta->pagos[1]->notas)->toBe('Abono registrado al editar');
});

it('no permite editar la compra por debajo de lo abonado ni con partidas inválidas', function () {
    $paciente = crearPacienteParaVenta();
    $this->post(route('pacientes.ventas.store', $paciente), datosVenta([
        'pago_inicial' => '30.00',
    ]))->assertRedirect();
    $venta = Venta::firstOrFail();

    $this->put(route('ventas.update', $venta), [
        'fecha_venta' => now()->toDateString(),
        'productos' => [
            ['descripcion' => 'Producto rebajado', 'cantidad' => 1, 'precio_unitario' => '20.00'],
        ],
    ])->assertSessionHasErrors('productos');

    $this->put(route('ventas.update', $venta), [
        'fecha_venta' => now()->toDateString(),
        'productos' => [
            ['descripcion' => '', 'cantidad' => 0, 'precio_unitario' => '0'],
        ],
    ])->assertSessionHasErrors([
        'productos.0.descripcion',
        'productos.0.cantidad',
        'productos.0.precio_unitario',
    ]);

    expect($venta->fresh()->total)->toBe('40.50');
    $this->assertDatabaseCount('partidas_venta', 2);
    $this->assertDatabaseCount('pagos_venta', 1);
});

it('rechaza un abono desde la edición si excede el saldo y conserva la compra', function () {
    $paciente = crearPacienteParaVenta();
    $this->post(route('pacientes.ventas.store', $paciente), datosVenta([
        'pago_inicial' => '30.00',
    ]))->assertRedirect();
    $venta = Venta::firstOrFail();

    $this->put(route('ventas.update', $venta), [
        'fecha_venta' => now()->toDateString(),
        'notas' => 'No debe guardarse',
        'abono_monto' => '10.51',
        'abono_fecha' => now()->toDateString(),
        'abono_metodo_pago' => 'efectivo',
        'productos' => [
            ['descripcion' => 'Armazón', 'cantidad' => 2, 'precio_unitario' => '12.50'],
            ['descripcion' => 'Estuche', 'cantidad' => 1, 'precio_unitario' => '15.50'],
        ],
    ])->assertSessionHasErrors('abono_monto');

    expect($venta->fresh()->total)->toBe('40.50')
        ->and($venta->fresh()->notas)->toBeNull();
    $this->assertDatabaseCount('partidas_venta', 2);
    $this->assertDatabaseCount('pagos_venta', 1);
});

it('rechaza anticipos o abonos superiores al saldo pendiente', function () {
    $paciente = crearPacienteParaVenta();

    $this->post(route('pacientes.ventas.store', $paciente), datosVenta([
        'pago_inicial' => '40.51',
    ]))->assertSessionHasErrors('pago_inicial');

    $this->assertDatabaseCount('ventas', 0);

    $this->post(route('pacientes.ventas.store', $paciente), datosVenta([
        'pago_inicial' => '30.00',
    ]))->assertRedirect();
    $venta = Venta::firstOrFail();

    $this->post(route('ventas.abonos.store', $venta), [
        'fecha_pago' => now()->toDateString(),
        'monto' => '10.51',
        'metodo_pago' => 'efectivo',
    ])->assertSessionHasErrors('monto');

    $this->assertDatabaseCount('pagos_venta', 1);
});

it('valida la fecha, forma de pago y partidas de una compra', function () {
    $paciente = crearPacienteParaVenta();

    $this->post(route('pacientes.ventas.store', $paciente), datosVenta([
        'fecha_venta' => now()->addDay()->toDateString(),
        'metodo_pago' => 'credito',
        'productos' => [
            ['descripcion' => '', 'cantidad' => 0, 'precio_unitario' => '0'],
        ],
    ]))->assertSessionHasErrors([
        'fecha_venta',
        'metodo_pago',
        'productos.0.descripcion',
        'productos.0.cantidad',
        'productos.0.precio_unitario',
    ]);

    $this->assertDatabaseCount('ventas', 0);
});

it('permite crear compras sin anticipo para registrar abonos después', function () {
    $paciente = crearPacienteParaVenta();

    $this->post(route('pacientes.ventas.store', $paciente), datosVenta([
        'metodo_pago' => '',
    ]))->assertRedirect();

    $venta = Venta::with('pagos')->firstOrFail();
    expect($venta->pagos)->toBeEmpty();

    $this->get(route('ventas.show', $venta))
        ->assertOk()
        ->assertSee('Aún no se han registrado pagos.')
        ->assertSee('Registrar abono');
});

it('conserva los pacientes que tienen compras registradas', function () {
    $paciente = crearPacienteParaVenta();
    $this->post(route('pacientes.ventas.store', $paciente), datosVenta())->assertRedirect();

    $this->delete(route('pacientes.destroy', $paciente))
        ->assertRedirect(route('pacientes.show', $paciente))
        ->assertSessionHas('error');

    $this->assertDatabaseCount('pacientes', 1);
    $this->assertDatabaseCount('ventas', 1);
});

it('elimina la columna de núcleo familiar de la base de datos', function () {
    expect(Schema::hasColumn('pacientes', 'responsable_id'))->toBeFalse();
});

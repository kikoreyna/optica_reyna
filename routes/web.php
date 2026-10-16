<?php

use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\EnfermedadController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('pacientes.index'));

Route::resource('pacientes', PacienteController::class)
    ->parameters(['pacientes' => 'paciente']);

Route::resource('pacientes.consultas', ConsultaController::class)
    ->shallow()
    ->except(['index', 'show'])
    ->parameters(['pacientes' => 'paciente', 'consultas' => 'consulta']);

Route::get('ventas', [VentaController::class, 'index'])->name('ventas.index');
Route::get('pacientes/{paciente}/ventas/create', [VentaController::class, 'create'])->name('pacientes.ventas.create');
Route::post('pacientes/{paciente}/ventas', [VentaController::class, 'store'])->name('pacientes.ventas.store');
Route::get('ventas/{venta}/edit', [VentaController::class, 'edit'])->name('ventas.edit');
Route::put('ventas/{venta}', [VentaController::class, 'update'])->name('ventas.update');
Route::get('ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
Route::post('ventas/{venta}/abonos', [VentaController::class, 'storeAbono'])->name('ventas.abonos.store');

Route::resource('enfermedades', EnfermedadController::class)
    ->parameters(['enfermedades' => 'enfermedad'])
    ->except('show');

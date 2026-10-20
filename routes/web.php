<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EnfermedadController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::get('empresa/logo', [EmpresaController::class, 'logo'])->name('empresa.logo');

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', fn () => redirect()->route('pacientes.index'));

    Route::middleware('role:administrador')->group(function () {
        Route::resource('usuarios', UserController::class)
            ->parameters(['usuarios' => 'usuario'])
            ->except('show');
        Route::get('empresa', [EmpresaController::class, 'edit'])->name('empresa.edit');
        Route::put('empresa', [EmpresaController::class, 'update'])->name('empresa.update');
    });

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
});

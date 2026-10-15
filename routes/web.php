<?php

use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\EnfermedadController;
use App\Http\Controllers\PacienteController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('pacientes.index'));

Route::resource('pacientes', PacienteController::class)
    ->parameters(['pacientes' => 'paciente']);

Route::resource('pacientes.consultas', ConsultaController::class)
    ->shallow()
    ->except(['index', 'show'])
    ->parameters(['pacientes' => 'paciente', 'consultas' => 'consulta']);

Route::resource('enfermedades', EnfermedadController::class)
    ->parameters(['enfermedades' => 'enfermedad'])
    ->except('show');

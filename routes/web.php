<?php

use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('pacientes.index'));

Route::get('/pacientes', [PatientController::class, 'index'])->name('pacientes.index');
Route::get('/pacientes/{patient}', [PatientController::class, 'show'])->name('pacientes.show');

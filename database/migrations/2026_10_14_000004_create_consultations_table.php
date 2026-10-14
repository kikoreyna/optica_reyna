<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->dateTime('fecha_consulta');
            $table->text('motivo_consulta');
            $table->text('observaciones_clinicas')->nullable();
            $table->date('proxima_cita')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'fecha_consulta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};

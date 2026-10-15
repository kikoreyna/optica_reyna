<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enfermedad_paciente', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('enfermedad_id')->constrained('enfermedades')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['enfermedad_id', 'paciente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enfermedad_paciente');
    }
};

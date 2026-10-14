<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refractions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('consultation_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            // Ojo derecho
            $table->decimal('od_esferico', 4, 2)->nullable();
            $table->decimal('od_cilindro', 4, 2)->nullable();
            $table->unsignedSmallInteger('od_eje')->nullable();
            $table->decimal('od_add', 4, 2)->nullable();
            $table->unsignedSmallInteger('od_dip')->nullable();
            $table->unsignedSmallInteger('od_alt')->nullable();

            // Ojo izquierdo
            $table->decimal('oi_esferico', 4, 2)->nullable();
            $table->decimal('oi_cilindro', 4, 2)->nullable();
            $table->unsignedSmallInteger('oi_eje')->nullable();
            $table->decimal('oi_add', 4, 2)->nullable();
            $table->unsignedSmallInteger('oi_alt')->nullable();

            // Diagnóstico
            $table->json('padecimientos_refractivos')->nullable();
            $table->text('notas_opticas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refractions');
    }
};

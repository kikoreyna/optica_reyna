<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->date('fecha_venta');
            $table->string('metodo_pago', 20);
            $table->decimal('total', 15, 2);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['paciente_id', 'fecha_venta']);
        });

        Schema::create('partidas_venta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->string('descripcion');
            $table->unsignedSmallInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas_venta');
        Schema::dropIfExists('ventas');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_venta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->date('fecha_pago');
            $table->decimal('monto', 15, 2);
            $table->string('metodo_pago', 20);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['venta_id', 'fecha_pago']);
        });

        DB::table('ventas')
            ->orderBy('id')
            ->chunk(500, function ($ventas): void {
                $pagos = $ventas->map(fn ($venta): array => [
                    'venta_id' => $venta->id,
                    'fecha_pago' => $venta->fecha_venta,
                    'monto' => $venta->total,
                    'metodo_pago' => $venta->metodo_pago,
                    'notas' => null,
                    'created_at' => $venta->created_at,
                    'updated_at' => $venta->updated_at,
                ])->all();

                DB::table('pagos_venta')->insert($pagos);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_venta');
    }
};

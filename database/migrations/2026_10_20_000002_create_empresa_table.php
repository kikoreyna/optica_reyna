<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('nombre')->default('Óptica Reyna');
            $table->string('razon_social')->nullable();
            $table->string('rfc', 13)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('correo')->nullable();
            $table->text('direccion')->nullable();
            $table->string('sitio_web')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};

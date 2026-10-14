<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_patient', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('disease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['disease_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_patient');
    }
};

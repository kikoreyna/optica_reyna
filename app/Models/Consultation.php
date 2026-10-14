<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'fecha_consulta',
        'motivo_consulta',
        'observaciones_clinicas',
        'proxima_cita',
    ];

    protected function casts(): array
    {
        return [
            'fecha_consulta' => 'datetime',
            'proxima_cita' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function refraction(): HasOne
    {
        return $this->hasOne(Refraction::class);
    }
}

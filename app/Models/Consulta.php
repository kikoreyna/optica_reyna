<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consulta extends Model
{
    use HasFactory;

    protected $table = 'consultas';

    protected $fillable = [
        'paciente_id',
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

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function refraccion(): HasOne
    {
        return $this->hasOne(Refraccion::class, 'consulta_id');
    }
}

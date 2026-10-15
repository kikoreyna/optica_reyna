<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Enfermedad extends Model
{
    use HasFactory;

    protected $table = 'enfermedades';

    protected $fillable = [
        'nombre',
    ];

    public function pacientes(): BelongsToMany
    {
        return $this->belongsToMany(Paciente::class, 'enfermedad_paciente', 'enfermedad_id', 'paciente_id')
            ->withTimestamps();
    }
}

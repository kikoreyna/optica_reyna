<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'fecha_nacimiento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    protected function edad(): Attribute
    {
        return Attribute::get(
            fn (): ?int => $this->fecha_nacimiento?->age
        );
    }

    public function enfermedades(): BelongsToMany
    {
        return $this->belongsToMany(Enfermedad::class, 'enfermedad_paciente', 'paciente_id', 'enfermedad_id')
            ->withTimestamps();
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'paciente_id');
    }

    public function refracciones(): HasMany
    {
        return $this->hasMany(Refraccion::class, 'paciente_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'paciente_id');
    }
}

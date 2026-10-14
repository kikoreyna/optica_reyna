<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

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

    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(Disease::class)->withTimestamps();
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function refractions(): HasMany
    {
        return $this->hasMany(Refraction::class);
    }
}

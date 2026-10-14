<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Disease extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_enfermedad',
    ];

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class)->withTimestamps();
    }
}

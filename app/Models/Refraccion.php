<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refraccion extends Model
{
    use HasFactory;

    public const PADECIMIENTOS = ['Miopía', 'Hipermetropía', 'Astigmatismo', 'Presbicia'];

    protected $table = 'refracciones';

    protected $fillable = [
        'consulta_id',
        'paciente_id',
        'od_esferico',
        'od_cilindro',
        'od_eje',
        'od_add',
        'od_dip',
        'od_alt',
        'oi_esferico',
        'oi_cilindro',
        'oi_eje',
        'oi_add',
        'oi_alt',
        'padecimientos_refractivos',
        'notas_opticas',
    ];

    protected function casts(): array
    {
        return [
            'od_esferico' => 'decimal:2',
            'od_cilindro' => 'decimal:2',
            'od_eje' => 'integer',
            'od_add' => 'decimal:2',
            'od_dip' => 'integer',
            'od_alt' => 'integer',
            'oi_esferico' => 'decimal:2',
            'oi_cilindro' => 'decimal:2',
            'oi_eje' => 'integer',
            'oi_add' => 'decimal:2',
            'oi_alt' => 'integer',
            'padecimientos_refractivos' => 'array',
        ];
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}

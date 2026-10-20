<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $fillable = [
        'id',
        'nombre',
        'razon_social',
        'rfc',
        'telefono',
        'correo',
        'direccion',
        'sitio_web',
        'logo_path',
    ];
}

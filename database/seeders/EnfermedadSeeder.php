<?php

namespace Database\Seeders;

use App\Models\Enfermedad;
use Illuminate\Database\Seeder;

class EnfermedadSeeder extends Seeder
{
    public function run(): void
    {
        $enfermedades = [
            'Diabetes',
            'Hipertensión arterial',
            'Glaucoma',
            'Cataratas',
            'Degeneración macular',
            'Retinopatía diabética',
            'Queratocono',
            'Conjuntivitis crónica',
            'Ojo seco',
            'Ambliopía',
            'Estrabismo',
            'Desprendimiento de retina',
            'Migraña',
            'Artritis reumatoide',
            'Hipotiroidismo',
        ];

        foreach ($enfermedades as $nombre) {
            Enfermedad::firstOrCreate(['nombre' => $nombre]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Consulta;
use App\Models\Enfermedad;
use App\Models\Paciente;
use App\Models\Refraccion;
use Illuminate\Database\Seeder;

class PacienteSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = [
            [
                'nombre' => 'María Hernández López',
                'telefono' => '5512345678',
                'direccion' => 'Av. Juárez 120, Centro',
                'fecha_nacimiento' => '1985-03-12',
                'enfermedades' => ['Diabetes', 'Hipertensión arterial'],
                'consultas' => [
                    [
                        'fecha_consulta' => now()->subMonths(8),
                        'motivo_consulta' => 'Visión borrosa de cerca',
                        'observaciones_clinicas' => 'Se recomienda lente progresivo.',
                        'proxima_cita' => now()->subMonths(2)->toDateString(),
                        'refraccion' => [
                            'od_esferico' => 1.25, 'od_cilindro' => -0.50, 'od_eje' => 90, 'od_add' => 1.50, 'od_dip' => 31, 'od_alt' => 18,
                            'oi_esferico' => 1.00, 'oi_cilindro' => -0.75, 'oi_eje' => 80, 'oi_add' => 1.50, 'oi_alt' => 18,
                            'padecimientos_refractivos' => ['Presbicia', 'Hipermetropía'],
                            'notas_opticas' => 'Primera graduación para lente progresivo.',
                        ],
                    ],
                    [
                        'fecha_consulta' => now()->subDays(10),
                        'motivo_consulta' => 'Control anual',
                        'observaciones_clinicas' => 'Sin cambios significativos.',
                        'proxima_cita' => now()->addMonths(11)->toDateString(),
                        'refraccion' => [
                            'od_esferico' => 1.25, 'od_cilindro' => -0.50, 'od_eje' => 90, 'od_add' => 1.75, 'od_dip' => 31, 'od_alt' => 18,
                            'oi_esferico' => 1.25, 'oi_cilindro' => -0.75, 'oi_eje' => 80, 'oi_add' => 1.75, 'oi_alt' => 18,
                            'padecimientos_refractivos' => ['Presbicia'],
                            'notas_opticas' => 'Aumenta adición.',
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Carlos Ramírez Soto',
                'telefono' => '5587654321',
                'direccion' => 'Calle Hidalgo 45, Col. Norte',
                'fecha_nacimiento' => '2001-11-02',
                'enfermedades' => ['Migraña'],
                'consultas' => [
                    [
                        'fecha_consulta' => now()->subDays(30),
                        'motivo_consulta' => 'Dolor de cabeza al leer',
                        'observaciones_clinicas' => 'Fatiga visual por pantallas.',
                        'proxima_cita' => now()->addMonths(5)->toDateString(),
                        'refraccion' => [
                            'od_esferico' => -2.25, 'od_cilindro' => -0.50, 'od_eje' => 10, 'od_dip' => 32, 'od_alt' => 20,
                            'oi_esferico' => -2.50, 'oi_cilindro' => -0.25, 'oi_eje' => 170, 'oi_alt' => 20,
                            'padecimientos_refractivos' => ['Miopía', 'Astigmatismo'],
                            'notas_opticas' => 'Recomendado filtro de luz azul.',
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Rosa Elena Martínez',
                'telefono' => null,
                'direccion' => null,
                'fecha_nacimiento' => '1958-07-25',
                'enfermedades' => ['Glaucoma', 'Cataratas'],
                'consultas' => [],
            ],
        ];

        foreach ($pacientes as $datos) {
            $paciente = Paciente::updateOrCreate(
                ['nombre' => $datos['nombre'], 'fecha_nacimiento' => $datos['fecha_nacimiento']],
                [
                    'telefono' => $datos['telefono'],
                    'direccion' => $datos['direccion'],
                ]
            );

            $paciente->enfermedades()->sync(
                Enfermedad::whereIn('nombre', $datos['enfermedades'])->pluck('id')
            );

            if ($paciente->consultas()->exists()) {
                continue;
            }

            foreach ($datos['consultas'] as $datosConsulta) {
                $refraccion = $datosConsulta['refraccion'];
                unset($datosConsulta['refraccion']);

                $consulta = Consulta::create($datosConsulta + ['paciente_id' => $paciente->id]);

                Refraccion::create($refraccion + [
                    'consulta_id' => $consulta->id,
                    'paciente_id' => $paciente->id,
                ]);
            }
        }
    }
}

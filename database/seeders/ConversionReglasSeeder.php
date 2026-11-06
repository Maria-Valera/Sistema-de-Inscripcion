<?php

namespace Database\Seeders;

use App\Models\AreaFormacion;
use App\Models\ConversionRegla;
use App\Models\ConversionReglaOrigen;
use App\Models\Pensum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConversionReglasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pensum31059 = Pensum::where('codigo', '31059')->first();
        $pensum31060 = Pensum::where('codigo', '31060')->first();

        if (!$pensum31059 || !$pensum31060) {
            $this->command->warn('[ConversionReglasSeeder] Se requieren los pensums 31059 y 31060. Ejecute PensumSeeder primero.');
            return;
        }

        $citaCircular = 'Equivalencia oficial según Circular N° 01 del MPPE (enero 2024)';

        // =========================================================================
        // LAS 10 REGLAS DE LA CIRCULAR 01 (Enero 2024) ENTRE PLAN 31059 Y 31060
        // =========================================================================
        // 1. Castellano (31059) -> Lengua y Literatura (31060) [Homologación directa]
        // 2. Inglés (31059) -> Inglés (31060) [Homologación directa]
        // 3. Matemáticas (31059) -> Matemáticas (31060) [Homologación directa]
        // 4. Educación Física (31059) -> Educación Física (31060) [Homologación directa]
        // 5. Arte y Patrimonio (31059) -> Arte y Patrimonio (31060) [Homologación directa]
        // 6. Ciencias Naturales (31059) -> Ciencias Naturales (31060) [Homologación directa]
        // 7. Orientación y Convivencia (31059) -> Orientación y Convivencia (31060) [Homologación directa]
        // 8. Geografía, Historia y Ciudadanía + FSN (31059) -> Geografía, Historia y Soberanía Nacional (31060) [Promedio]
        // 9. Física, Química e Innovación Tecnológica (31060) [Exonerada - se imprime EX]
        // 10. Grupos de Creación, Recreación y Producción (31059) [No se toma en cuenta]

        // Definición de las 10 reglas por año (1er año = grado 1, 2do año = grado 2)
        $grados = [1, 2];

        foreach ($grados as $gradoId) {
            $reglas = [
                // Regla 1: Castellano (id 1) -> id 1 (directa)
                [
                    'area_destino' => 1,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación de Castellano a Lengua y Literatura',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 1],
                    ],
                ],
                // Regla 2: Arte y Patrimonio (id 2) -> id 2 (directa)
                [
                    'area_destino' => 2,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación directa Arte y Patrimonio',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 2],
                    ],
                ],
                // Regla 3: Ciencias Naturales (id 3) -> id 3 (directa)
                [
                    'area_destino' => 3,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación directa Ciencias Naturales',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 3],
                    ],
                ],
                // Regla 4: Matemáticas (id 5) -> id 5 (directa)
                [
                    'area_destino' => 5,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación directa Matemáticas',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 5],
                    ],
                ],
                // Regla 5: Educación Física (id 6) -> id 6 (directa)
                [
                    'area_destino' => 6,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación directa Educación Física',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 6],
                    ],
                ],
                // Regla 6: Inglés (id 7) -> id 7 (directa)
                [
                    'area_destino' => 7,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación directa Inglés',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 7],
                    ],
                ],
                // Regla 7: Orientación y Convivencia (id 13) -> id 13 (directa)
                [
                    'area_destino' => 13,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación cualitativa Orientación y Convivencia',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 13],
                    ],
                ],
                // Regla 8: GHC (id 8) + FSN (id 12) -> Promedia a Geografía, Historia y Soberanía Nacional (id 8)
                [
                    'area_destino' => 8,
                    'tipo'         => 'promedio',
                    'obs'          => $citaCircular . ' - Promedio aritmético de Geografía, Historia y Ciudadanía con Soberanía Nacional',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 8],
                        ['grado' => $gradoId, 'area' => 12],
                    ],
                ],
                // Regla 9: Física y Química / Innovación Tecnológica (id 10) -> Exonerada (sin origenes)
                [
                    'area_destino' => 10,
                    'tipo'         => 'exonerada',
                    'obs'          => $citaCircular . ' - Exonerada por equivalencia oficial de plan (se imprime EX)',
                    'origenes'     => [],
                ],
                // Regla 10: Formación para la Soberanía Nacional (id 12) en caso de transición individual -> No cuenta
                [
                    'area_destino' => 12,
                    'tipo'         => 'no_cuenta',
                    'obs'          => $citaCircular . ' - Contemplada dentro del promedio de Geografía e Historia',
                    'origenes'     => [],
                ],
            ];

            foreach ($reglas as $def) {
                // Verificar que el área exista
                if (!AreaFormacion::where('id', $def['area_destino'])->exists()) {
                    continue;
                }

                $regla = ConversionRegla::updateOrCreate(
                    [
                        'pensum_origen_id'  => $pensum31059->id,
                        'pensum_destino_id' => $pensum31060->id,
                        'grado_destino_id'  => $gradoId,
                        'area_destino_id'   => $def['area_destino'],
                    ],
                    [
                        'tipo_regla'  => $def['tipo'],
                        'observacion' => $def['obs'],
                        'status'      => true,
                    ]
                );

                // Sincronizar orígenes
                $regla->origenes()->delete();
                foreach ($def['origenes'] as $orig) {
                    if (AreaFormacion::where('id', $orig['area'])->exists()) {
                        ConversionReglaOrigen::create([
                            'conversion_regla_id' => $regla->id,
                            'grado_origen_id'     => $orig['grado'],
                            'area_origen_id'      => $orig['area'],
                        ]);
                    }
                }
            }
        }

        $this->command->info('[ConversionReglasSeeder] Las 10 reglas de conversión de la Circular 01 para 1ro y 2do sembradas.');
    }
}


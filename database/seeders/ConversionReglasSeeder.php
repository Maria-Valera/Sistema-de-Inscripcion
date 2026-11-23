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

        $citaCircular = 'Equivalencia oficial según Circular N° 01 del MPPE (12 de enero de 2024)';

        // =========================================================================
        // REGLAS OFICIALES DE LA CIRCULAR 01 (12/01/2024) ENTRE PLAN 31059 Y 31060
        // Para 1er y 2do año de Educación Media General:
        // -------------------------------------------------------------------------
        // COMPONENTE GENERAL:
        // 1. Lengua y Literatura (31060) <- Castellano (31059) [Homologación directa]
        // 2. Idiomas (31060) <- Inglés y otras lenguas extranjeras (31059) [Homologación directa]
        // 3. Matemática (31060) <- Matemáticas (31059) [Homologación directa]
        // 4. Educación Física (31060) <- Educación Física (31059) [Homologación directa]
        // 5. Biología, Ambiente y Tecnología (31060) <- Ciencias Naturales (31059) [Homologación directa]
        // 6. Física (31060) <- Exonerada (EX) [Consideración 4]
        // 7. Química (31060) <- Exonerada (EX) [Consideración 4]
        // 8. Geografía, Historia y Soberanía Nacional (31060) <- Arte y Patrimonio + GHC (31059) [Promedio - Consideración 3]
        // -------------------------------------------------------------------------
        // COMPONENTE FORMACIÓN CIENTÍFICA, TECNOLÓGICA Y PRODUCTIVA:
        // 9. Orientación Vocacional (31060) <- Orientación y Convivencia (31059) [Homologación directa]
        // 10. Innovación Tecnológica y Productiva (31060) <- Exonerada (EX) [Consideración 4]
        // -------------------------------------------------------------------------
        // DESCARTE:
        // 11. Participación en Grupos de Creación, Recreación y Producción (31059) <- No se toma en cuenta [Consideración 5]
        // =========================================================================

        $grados = [1, 2];

        foreach ($grados as $gradoId) {
            $reglas = [
                // 1. Lengua y Literatura (id 15) <- Castellano (id 1)
                [
                    'area_destino' => 15,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación de Castellano a Lengua y Literatura',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 1],
                    ],
                ],
                // 2. Idiomas (id 16) <- Inglés y otras lenguas extranjeras (id 7)
                [
                    'area_destino' => 16,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación de Inglés a Idiomas',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 7],
                    ],
                ],
                // 3. Matemática (id 17) <- Matemáticas (id 5)
                [
                    'area_destino' => 17,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación directa Matemática',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 5],
                    ],
                ],
                // 4. Educación Física (id 6) <- Educación Física (id 6)
                [
                    'area_destino' => 6,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación directa Educación Física',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 6],
                    ],
                ],
                // 5. Biología, Ambiente y Tecnología (id 18) <- Ciencias Naturales (id 3)
                [
                    'area_destino' => 18,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación de Ciencias Naturales a Biología, Ambiente y Tecnología',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 3],
                    ],
                ],
                // 6. Física (id 10) <- Exonerada (EX) [Consideración 4]
                [
                    'area_destino' => 10,
                    'tipo'         => 'exonerada',
                    'obs'          => $citaCircular . ' - Exonerada por no estar contemplada en Plan 31059 (se asienta EX)',
                    'origenes'     => [],
                ],
                // 7. Química (id 11) <- Exonerada (EX) [Consideración 4]
                [
                    'area_destino' => 11,
                    'tipo'         => 'exonerada',
                    'obs'          => $citaCircular . ' - Exonerada por no estar contemplada en Plan 31059 (se asienta EX)',
                    'origenes'     => [],
                ],
                // 8. Geografía, Historia y Soberanía Nacional (id 19) <- Arte y Patrimonio (id 2) + GHC (id 8) [Promedio - Consideración 3]
                [
                    'area_destino' => 19,
                    'tipo'         => 'promedio',
                    'obs'          => $citaCircular . ' - Promedio aritmético de Arte y Patrimonio con Geografía, Historia y Ciudadanía',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 2], // Arte y Patrimonio (31059)
                        ['grado' => $gradoId, 'area' => 8], // Geografía, Historia y Ciudadanía (31059)
                    ],
                ],
                // 9. Orientación Vocacional (id 20) <- Orientación y Convivencia (id 13)
                [
                    'area_destino' => 20,
                    'tipo'         => 'directa',
                    'obs'          => $citaCircular . ' - Homologación cualitativa de Orientación y Convivencia a Orientación Vocacional',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 13],
                    ],
                ],
                // 10. Innovación Tecnológica y Productiva (id 21) <- Exonerada (EX) [Consideración 4]
                [
                    'area_destino' => 21,
                    'tipo'         => 'exonerada',
                    'obs'          => $citaCircular . ' - Exonerada por no estar contemplada en Plan 31059 (se asienta EX)',
                    'origenes'     => [],
                ],
                // 11. PGCRP (id 14 en 31059) -> No se toma en cuenta [Consideración 5]
                [
                    'area_destino' => null,
                    'tipo'         => 'no_cuenta',
                    'obs'          => $citaCircular . ' - No se toma en cuenta (no contemplada en Plan 31060)',
                    'origenes'     => [
                        ['grado' => $gradoId, 'area' => 14], // PGCRP en 31059
                    ],
                ],
            ];

            foreach ($reglas as $def) {
                // Si tiene área de destino, verificar que exista
                if (!is_null($def['area_destino']) && !AreaFormacion::where('id', $def['area_destino'])->exists()) {
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

        $this->command->info('[ConversionReglasSeeder] Las reglas oficiales de conversión de la Circular N° 01 sembradas correctamente.');
    }
}

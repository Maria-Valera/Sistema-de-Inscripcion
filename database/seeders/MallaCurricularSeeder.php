<?php

namespace Database\Seeders;

use App\Models\AreaFormacion;
use App\Models\GradoAreaFormacion;
use App\Models\Pensum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MallaCurricularSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pensum31059 = Pensum::where('codigo', '31059')->first();
        $pensum31060 = Pensum::where('codigo', '31060')->first();

        if (!$pensum31059) {
            $this->command->warn('[MallaCurricularSeeder] El pensum 31059 no existe todavía. Ejecute PensumSeeder primero.');
            return;
        }

        // Garantizar existencia de áreas oficiales de Circular 01 / Planes 31059 y 31060
        $areasRequeridas = [
            ['id' => 14, 'nombre_area_formacion' => 'Participación en los Grupos de Creación, Recreación y Producción', 'codigo_area' => '14', 'siglas' => 'PGCRP', 'bloques_semanales' => 4, 'aula_id' => null, 'status' => true],
            ['id' => 15, 'nombre_area_formacion' => 'Lengua y Literatura', 'codigo_area' => '15', 'siglas' => 'LYL', 'bloques_semanales' => 4, 'aula_id' => null, 'status' => true],
            ['id' => 16, 'nombre_area_formacion' => 'Idiomas', 'codigo_area' => '16', 'siglas' => 'IDI', 'bloques_semanales' => 4, 'aula_id' => null, 'status' => true],
            ['id' => 17, 'nombre_area_formacion' => 'Matemática', 'codigo_area' => '17', 'siglas' => 'MTC', 'bloques_semanales' => 4, 'aula_id' => null, 'status' => true],
            ['id' => 18, 'nombre_area_formacion' => 'Biología, Ambiente y Tecnología', 'codigo_area' => '18', 'siglas' => 'BAT', 'bloques_semanales' => 4, 'aula_id' => 9, 'status' => true],
            ['id' => 19, 'nombre_area_formacion' => 'Geografía, Historia y Soberanía Nacional', 'codigo_area' => '19', 'siglas' => 'GHSN', 'bloques_semanales' => 4, 'aula_id' => null, 'status' => true],
            ['id' => 20, 'nombre_area_formacion' => 'Orientación Vocacional', 'codigo_area' => '20', 'siglas' => 'OV', 'bloques_semanales' => 2, 'aula_id' => null, 'status' => true],
            ['id' => 21, 'nombre_area_formacion' => 'Innovación Tecnológica y Productiva', 'codigo_area' => '21', 'siglas' => 'ITP', 'bloques_semanales' => 3, 'aula_id' => 9, 'status' => true],
        ];

        foreach ($areasRequeridas as $areaReq) {
            DB::table('area_formacions')->updateOrInsert(['id' => $areaReq['id']], $areaReq);
        }

        $areas = AreaFormacion::all()->keyBy('id');

        // =========================================================================
        // 1. MALLA CURRICULAR PLAN 31059 (Educación Media General)
        // Horas semanales exactas del DBML: 38 / 38 / 38 / 40 / 40 horas
        // =========================================================================
        $malla31059 = [
            // 1er Año (38 horas)
            1 => [
                ['area_id' => 1,  'horas' => 4, 'escala' => 'numerica'], // Castellano
                ['area_id' => 7,  'horas' => 4, 'escala' => 'numerica'], // Inglés
                ['area_id' => 5,  'horas' => 4, 'escala' => 'numerica'], // Matemáticas
                ['area_id' => 6,  'horas' => 4, 'escala' => 'numerica'], // Educación Física
                ['area_id' => 2,  'horas' => 4, 'escala' => 'numerica'], // Arte y Patrimonio
                ['area_id' => 3,  'horas' => 6, 'escala' => 'numerica'], // Ciencias Naturales
                ['area_id' => 8,  'horas' => 6, 'escala' => 'numerica'], // GHC
                ['area_id' => 13, 'horas' => 2, 'escala' => 'literal'],  // Orientación y Convivencia
                ['area_id' => 14, 'horas' => 4, 'escala' => 'literal'],  // PGCRP
            ],
            // 2do Año (38 horas)
            2 => [
                ['area_id' => 1,  'horas' => 4, 'escala' => 'numerica'], // Castellano
                ['area_id' => 7,  'horas' => 4, 'escala' => 'numerica'], // Inglés
                ['area_id' => 5,  'horas' => 4, 'escala' => 'numerica'], // Matemáticas
                ['area_id' => 6,  'horas' => 4, 'escala' => 'numerica'], // Educación Física
                ['area_id' => 2,  'horas' => 4, 'escala' => 'numerica'], // Arte y Patrimonio
                ['area_id' => 3,  'horas' => 6, 'escala' => 'numerica'], // Ciencias Naturales
                ['area_id' => 8,  'horas' => 6, 'escala' => 'numerica'], // GHC
                ['area_id' => 13, 'horas' => 2, 'escala' => 'literal'],  // Orientación y Convivencia
                ['area_id' => 14, 'horas' => 4, 'escala' => 'literal'],  // PGCRP
            ],
            // 3er Año (38 horas)
            3 => [
                ['area_id' => 1,  'horas' => 4, 'escala' => 'numerica'], // Castellano
                ['area_id' => 7,  'horas' => 4, 'escala' => 'numerica'], // Inglés
                ['area_id' => 5,  'horas' => 4, 'escala' => 'numerica'], // Matemáticas
                ['area_id' => 6,  'horas' => 4, 'escala' => 'numerica'], // Educación Física
                ['area_id' => 10, 'horas' => 4, 'escala' => 'numerica'], // Física
                ['area_id' => 11, 'horas' => 4, 'escala' => 'numerica'], // Química
                ['area_id' => 4,  'horas' => 4, 'escala' => 'numerica'], // Biología
                ['area_id' => 8,  'horas' => 4, 'escala' => 'numerica'], // GHC
                ['area_id' => 13, 'horas' => 2, 'escala' => 'literal'],  // Orientación y Convivencia
                ['area_id' => 14, 'horas' => 4, 'escala' => 'literal'],  // PGCRP
            ],
            // 4to Año (40 horas)
            4 => [
                ['area_id' => 1,  'horas' => 4, 'escala' => 'numerica'], // Castellano
                ['area_id' => 7,  'horas' => 4, 'escala' => 'numerica'], // Inglés
                ['area_id' => 5,  'horas' => 4, 'escala' => 'numerica'], // Matemáticas
                ['area_id' => 6,  'horas' => 4, 'escala' => 'numerica'], // Educación Física
                ['area_id' => 10, 'horas' => 4, 'escala' => 'numerica'], // Física
                ['area_id' => 11, 'horas' => 4, 'escala' => 'numerica'], // Química
                ['area_id' => 4,  'horas' => 4, 'escala' => 'numerica'], // Biología
                ['area_id' => 8,  'horas' => 4, 'escala' => 'numerica'], // GHC
                ['area_id' => 12, 'horas' => 2, 'escala' => 'numerica'], // FSN
                ['area_id' => 13, 'horas' => 2, 'escala' => 'literal'],  // Orientación y Convivencia
                ['area_id' => 14, 'horas' => 4, 'escala' => 'literal'],  // PGCRP
            ],
            // 5to Año (40 horas)
            5 => [
                ['area_id' => 1,  'horas' => 3, 'escala' => 'numerica'], // Castellano
                ['area_id' => 7,  'horas' => 3, 'escala' => 'numerica'], // Inglés
                ['area_id' => 5,  'horas' => 4, 'escala' => 'numerica'], // Matemáticas
                ['area_id' => 6,  'horas' => 4, 'escala' => 'numerica'], // Educación Física
                ['area_id' => 10, 'horas' => 4, 'escala' => 'numerica'], // Física
                ['area_id' => 11, 'horas' => 4, 'escala' => 'numerica'], // Química
                ['area_id' => 4,  'horas' => 4, 'escala' => 'numerica'], // Biología
                ['area_id' => 9,  'horas' => 2, 'escala' => 'numerica'], // Ciencias de la Tierra
                ['area_id' => 8,  'horas' => 4, 'escala' => 'numerica'], // GHC
                ['area_id' => 12, 'horas' => 2, 'escala' => 'numerica'], // FSN
                ['area_id' => 13, 'horas' => 2, 'escala' => 'literal'],  // Orientación y Convivencia
                ['area_id' => 14, 'horas' => 4, 'escala' => 'literal'],  // PGCRP
            ],
        ];

        foreach ($malla31059 as $gradoId => $materias) {
            foreach ($materias as $index => $item) {
                $area = $areas->get($item['area_id']);
                $sigla = strtoupper(trim((string) ($area?->siglas ?? 'MAT')));
                $codigo = sprintf('%d-%03d-%s', $gradoId, $gradoId, $sigla);

                GradoAreaFormacion::updateOrCreate(
                    [
                        'pensum_id'         => $pensum31059->id,
                        'grado_id'          => $gradoId,
                        'area_formacion_id' => $item['area_id'],
                    ],
                    [
                        'codigo'           => $codigo,
                        'horas'            => $item['horas'],
                        'nota_aprobatoria' => 10.00,
                        'escala'           => $item['escala'],
                        'componente'       => 'Formación General',
                        'orden'            => $index + 1,
                        'status'           => true,
                    ]
                );
            }
        }

        $this->command->info('[MallaCurricularSeeder] Malla del Plan 31059 sembrada (Totales: 38/38/38/40/40 horas).');

        // =========================================================================
        // 2. MALLA CURRICULAR PLAN 31060 (Ciencia y Tecnología - Circular 01)
        // Asignaturas oficiales con denominación MPPE (1er y 2do año sin horas)
        // =========================================================================
        if ($pensum31060) {
            $areas31060 = [
                // Componente General
                ['area_id' => 15, 'escala' => 'numerica', 'componente' => 'General', 'orden' => 1], // Lengua y Literatura
                ['area_id' => 16, 'escala' => 'numerica', 'componente' => 'General', 'orden' => 2], // Idiomas
                ['area_id' => 17, 'escala' => 'numerica', 'componente' => 'General', 'orden' => 3], // Matemática
                ['area_id' => 6,  'escala' => 'numerica', 'componente' => 'General', 'orden' => 4], // Educación Física
                ['area_id' => 18, 'escala' => 'numerica', 'componente' => 'General', 'orden' => 5], // Biología, Ambiente y Tecnología
                ['area_id' => 10, 'escala' => 'numerica', 'componente' => 'General', 'orden' => 6], // Física
                ['area_id' => 11, 'escala' => 'numerica', 'componente' => 'General', 'orden' => 7], // Química
                ['area_id' => 19, 'escala' => 'numerica', 'componente' => 'General', 'orden' => 8], // Geografía, Historia y Soberanía Nacional
                // Componente Formación Científica, Tecnológica y Productiva
                ['area_id' => 20, 'escala' => 'literal',  'componente' => 'Formación Científica, Tecnológica y Productiva', 'orden' => 9],  // Orientación Vocacional
                ['area_id' => 21, 'escala' => 'numerica', 'componente' => 'Formación Científica, Tecnológica y Productiva', 'orden' => 10], // Innovación Tecnológica y Productiva
            ];

            foreach ([1, 2] as $gradoId) {
                foreach ($areas31060 as $item) {
                    $area = $areas->get($item['area_id']);
                    $sigla = strtoupper(trim((string) ($area?->siglas ?? 'MAT')));
                    $codigo = sprintf('%d-%03d-%s-31060', $gradoId, $gradoId, $sigla);

                    GradoAreaFormacion::updateOrCreate(
                        [
                            'pensum_id'         => $pensum31060->id,
                            'grado_id'          => $gradoId,
                            'area_formacion_id' => $item['area_id'],
                        ],
                        [
                            'codigo'           => $codigo,
                            'horas'            => null, // Regla MPPE: Malla 31060 sin horas asignadas
                            'nota_aprobatoria' => 10.00,
                            'escala'           => $item['escala'],
                            'componente'       => $item['componente'],
                            'orden'            => $item['orden'],
                            'status'           => true,
                        ]
                    );
                }
            }

            $this->command->info('[MallaCurricularSeeder] Malla del Plan 31060 sembrada con nombres oficiales de Circular 01.');
        }
    }
}

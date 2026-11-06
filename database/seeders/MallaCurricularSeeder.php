<?php

namespace Database\Seeders;

use App\Models\AreaFormacion;
use App\Models\GradoAreaFormacion;
use App\Models\Pensum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        // =========================================================================
        // 1. ASOCIAR Y COMPLETAR DATOS DE LA MALLA DEL PLAN 31059 (Media General)
        // =========================================================================
        // Asignar pensum_id = 31059 a los registros existentes que no tengan pensum_id
        DB::table('grado_area_formacions')
            ->whereNull('pensum_id')
            ->update(['pensum_id' => $pensum31059->id]);

        // Cargar áreas de formación para asignar horas y tipo de escala
        $areas = AreaFormacion::all()->keyBy('id');

        $registros31059 = GradoAreaFormacion::where('pensum_id', $pensum31059->id)->get();
        $ordenPorGrado = [];

        foreach ($registros31059 as $item) {
            $area = $areas->get($item->area_formacion_id);
            $siglas = strtoupper(trim((string) ($area?->siglas ?? '')));

            // Regla MPPE: solo OC, PGCRP y Orientación Vocacional llevan escala literal
            $esLiteral = in_array($siglas, ['OC', 'PGCRP', 'GCRP', 'OV']);

            $gradoId = $item->grado_id;
            $ordenPorGrado[$gradoId] = ($ordenPorGrado[$gradoId] ?? 0) + 1;

            $item->update([
                'horas'            => $area?->bloques_semanales ?? 4,
                'nota_aprobatoria' => 10.00,
                'escala'           => $esLiteral ? 'literal' : 'numerica',
                'componente'       => 'Formación General',
                'orden'            => $ordenPorGrado[$gradoId],
            ]);
        }

        $this->command->info('[MallaCurricularSeeder] Malla del Plan 31059 completada con horas y escalas.');

        // =========================================================================
        // 2. SEMBRAR MALLA DEL PLAN 31060 (Ciencia y Tecnología - Circular 01)
        // Regla del DBML: "malla 31060 sin horas"
        // Nota: DatabaseSeeder utiliza WithoutModelEvents, por lo que el código
        // debe generarse explícitamente en el seeder para evitar error de campo requerido.
        // =========================================================================
        if ($pensum31060) {
            $areas1erAno = [
                ['area_id' => 1, 'escala' => 'numerica', 'orden' => 1],
                ['area_id' => 2, 'escala' => 'numerica', 'orden' => 2],
                ['area_id' => 3, 'escala' => 'numerica', 'orden' => 3],
                ['area_id' => 5, 'escala' => 'numerica', 'orden' => 4],
                ['area_id' => 6, 'escala' => 'numerica', 'orden' => 5],
                ['area_id' => 7, 'escala' => 'numerica', 'orden' => 6],
                ['area_id' => 8, 'escala' => 'numerica', 'orden' => 7],
                ['area_id' => 12, 'escala' => 'numerica', 'orden' => 8],
                ['area_id' => 13, 'escala' => 'literal',  'orden' => 9], // OC = literal
            ];

            $grados31060 = [1, 2];

            foreach ($grados31060 as $gradoId) {
                foreach ($areas1erAno as $item) {
                    $area = $areas->get($item['area_id']);
                    $sigla = strtoupper(trim((string) ($area?->siglas ?? 'MAT')));
                    $codigoGenerado = sprintf('%d-%03d-%s-31060', $gradoId, $gradoId, $sigla);

                    GradoAreaFormacion::firstOrCreate(
                        [
                            'pensum_id'         => $pensum31060->id,
                            'grado_id'          => $gradoId,
                            'area_formacion_id' => $item['area_id'],
                        ],
                        [
                            'codigo'           => $codigoGenerado,
                            'horas'            => null, // Regla de negocio: 31060 sin horas
                            'nota_aprobatoria' => 10.00,
                            'escala'           => $item['escala'],
                            'componente'       => 'Ciencia y Tecnología',
                            'orden'            => $item['orden'],
                            'status'           => true,
                        ]
                    );
                }
            }

            $this->command->info('[MallaCurricularSeeder] Malla del Plan 31060 (1er y 2do año sin horas) sembrada.');
        }
    }
}

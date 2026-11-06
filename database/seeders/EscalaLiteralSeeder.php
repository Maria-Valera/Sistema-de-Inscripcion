<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\EscalaLiteral;
use Illuminate\Database\Seeder;

class EscalaLiteralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anios = AnioEscolar::all();

        if ($anios->isEmpty()) {
            return;
        }

        $escalas = [
            ['letra' => 'A', 'descripcion' => 'Excelente', 'nota_desde' => 19, 'nota_hasta' => 20],
            ['letra' => 'B', 'descripcion' => 'Bueno', 'nota_desde' => 16, 'nota_hasta' => 18],
            ['letra' => 'C', 'descripcion' => 'Aprobado', 'nota_desde' => 12, 'nota_hasta' => 15],
            ['letra' => 'D', 'descripcion' => 'Regular / Aceptable', 'nota_desde' => 10, 'nota_hasta' => 11],
            ['letra' => 'E', 'descripcion' => 'Deficiente', 'nota_desde' => 1, 'nota_hasta' => 9],
        ];

        foreach ($anios as $anio) {
            foreach ($escalas as $esc) {
                EscalaLiteral::updateOrCreate(
                    [
                        'anio_escolar_id' => $anio->id,
                        'letra' => $esc['letra'],
                    ],
                    [
                        'descripcion' => $esc['descripcion'],
                        'nota_desde' => $esc['nota_desde'],
                        'nota_hasta' => $esc['nota_hasta'],
                    ]
                );
            }
        }

        $this->command->info('[EscalaLiteralSeeder] Escala literal (A-E) sembrada exitosamente.');
    }
}


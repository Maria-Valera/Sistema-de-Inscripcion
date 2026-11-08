<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\AutoridadFirmante;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class AutoridadFirmanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anio = AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 1)
            ->first() ?? AnioEscolar::latest()->first();

        if (!$anio) {
            $this->command->warn('[AutoridadFirmanteSeeder] No se encontró ningún año escolar activo para asociar autoridades.');
            return;
        }

        $personas = Persona::all();

        if ($personas->isEmpty()) {
            $this->command->warn('[AutoridadFirmanteSeeder] No hay personas registradas en la base de datos.');
            return;
        }

        $directorPersona = $personas->first();
        $subdirectorPersona = $personas->skip(1)->first() ?? $directorPersona;
        $controlEstudiosPersona = $personas->skip(2)->first() ?? $subdirectorPersona;
        $evaluacionPersona = $personas->skip(3)->first() ?? $controlEstudiosPersona;
        $docenteFirmantePersona = $personas->skip(4)->first() ?? $evaluacionPersona;

        $autoridades = [
            [
                'anio_escolar_id' => $anio->id,
                'cargo' => 'director',
                'persona_id' => $directorPersona->id,
                'encargado' => false,
                'gaceta' => 'Gaceta Oficial N° 42.100',
                'desde' => '2026-09-01',
                'hasta' => null,
            ],
            [
                'anio_escolar_id' => $anio->id,
                'cargo' => 'subdirector_academico',
                'persona_id' => $subdirectorPersona->id,
                'encargado' => false,
                'gaceta' => 'Resolución Ministerial N° DM/045',
                'desde' => '2026-09-01',
                'hasta' => null,
            ],
            [
                'anio_escolar_id' => $anio->id,
                'cargo' => 'coordinador_control_estudios',
                'persona_id' => $controlEstudiosPersona->id,
                'encargado' => false,
                'gaceta' => null,
                'desde' => '2026-09-01',
                'hasta' => null,
            ],
            [
                'anio_escolar_id' => $anio->id,
                'cargo' => 'coordinador_evaluacion',
                'persona_id' => $evaluacionPersona->id,
                'encargado' => false,
                'gaceta' => null,
                'desde' => '2026-09-01',
                'hasta' => null,
            ],
            [
                'anio_escolar_id' => $anio->id,
                'cargo' => 'docente_firmante_mppe',
                'persona_id' => $docenteFirmantePersona->id,
                'encargado' => false,
                'gaceta' => null,
                'desde' => '2026-09-01',
                'hasta' => null,
            ],
        ];

        foreach ($autoridades as $datos) {
            AutoridadFirmante::updateOrCreate(
                [
                    'anio_escolar_id' => $datos['anio_escolar_id'],
                    'cargo' => $datos['cargo'],
                    'persona_id' => $datos['persona_id'],
                ],
                $datos
            );
        }

        $this->command->info("[AutoridadFirmanteSeeder] Autoridades firmantes sembradas correctamente para el año {$anio->periodo}.");
    }
}


<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\Localidad;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class InstitucionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar localidad en Araure / Portuguesa o fallback
        $localidad = Localidad::whereHas('municipio.estado', function ($q) {
            $q->where('nombre_estado', 'like', '%Portuguesa%');
        })->where('nombre_localidad', 'like', '%Araure%')->first()
        ?? Localidad::whereHas('municipio.estado', function ($q) {
            $q->where('nombre_estado', 'like', '%Portuguesa%');
        })->first()
        ?? Localidad::first();

        // Buscar una persona para director(a)
        $director = Persona::where('status', 'Activo')->first();

        Institucion::updateOrCreate(
            ['codigo_plantel' => 'OD05241808'],
            [
                'nombre' => 'Liceo General Juan Guillermo Iribarren',
                'direccion' => 'Final Av. 13 de Junio, Sector Banco Obrero, Araure',
                'telefono' => '0255-6641234',
                'localidad_id' => $localidad?->id,
                'zona_educativa' => 'Zona Educativa del Estado Portuguesa',
                'director_persona_id' => $director?->id,
                'director_encargado' => false,
                'logo_ruta' => 'img/Liceo_logo.png',
                'cdcee' => 'CDCEE Portuguesa - Municipio Araure',
                'dependencia' => 'Pública Nacional (MPPE)',
                'activo' => true,
            ]
        );

        $this->command->info('[InstitucionSeeder] Plantel Liceo General Juan Guillermo Iribarren registrado correctamente.');
    }
}


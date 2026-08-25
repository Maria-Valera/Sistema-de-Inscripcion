<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\DetalleDocenteEstudio;
use App\Models\EstudiosRealizado;
use App\Models\AreaEstudioRealizado;
use App\Models\AreaFormacion;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\DocenteAreaGrado;
use App\Models\AnioEscolar;
use App\Models\TipoDocumento;
use App\Models\Genero;
use Illuminate\Support\Facades\DB;

class Docente50Seeder extends Seeder
{
    public function run(): void
    {
        // Obtener datos necesarios
        $anioEscolar = AnioEscolar::activos()->first();
        $tipoDocumento = TipoDocumento::first();
        $genero = Genero::first();
        
        if (!$anioEscolar) {
            $this->command->error('No hay año escolar activo. Se requiere un año escolar activo para este seeder.');
            return;
        }

        if (!$tipoDocumento) {
            $this->command->error('No hay tipo de documento. Se requiere al menos un tipo de documento.');
            return;
        }

        if (!$genero) {
            $this->command->error('No hay género. Se requiere al menos un género.');
            return;
        }

        $grados = Grado::where('status', true)->get();
        $secciones = Seccion::where('status', true)->get();
        $areasFormacion = AreaFormacion::where('status', true)->get();

        if ($grados->isEmpty() || $secciones->isEmpty() || $areasFormacion->isEmpty()) {
            $this->command->error('Se requieren grados, secciones y áreas de formación activas para este seeder.');
            return;
        }

        // Generar 50 docentes
        $this->command->info('Generando 50 docentes con asignaciones...');

        $nombresHombres = [
            'María', 'Juan', 'Ana', 'Carlos', 'Laura', 'Pedro', 'Sofía', 'Miguel', 
            'Elena', 'Diego', 'Lucía', 'Fernando', 'Isabel', 'Roberto', 'Carmen', 'José',
            'Patricia', 'Ricardo', 'Teresa', 'Antonio', 'Beatriz', 'Francisco', 'Adriana',
            'Luis', 'Daniela', 'Javier', 'Verónica', 'Raúl', 'Claudia', 'Miguel Ángel',
            'Gabriela', 'Sergio', 'Mónica', 'Alejandro', 'Valentina', 'Víctor', 'Natalia',
            'Fernando', 'Victoria', 'Manuel', 'Paula', 'Rafael', 'Sandra', 'Ignacio',
            'Guillermo', 'Carolina', 'Humberto', 'Pilar'
        ];

        $nombresApellidos = [
            'García', 'Pérez', 'Rodríguez', 'López', 'Martínez', 'Sánchez', 'González',
            'Hernández', 'Fernández', 'Torres', 'Ramírez', 'Flores', 'Rivera', 'Morales',
            'Jiménez', 'Reyes', 'Castillo', 'Cruz', 'Ortiz', 'Romero', 'Vargas', 'Mendoza',
            'Silva', 'Ruiz', 'Navarro', 'Álvarez', 'Rojas', 'Gutiérrez', 'Ortega', 'Pacheco'
        ];

        $areasFormacionNombres = [
            'Matemáticas', 'Lenguaje y Literatura', 'Ciencias Naturales', 'Historia', 
            'Geografía', 'Inglés', 'Educación Física', 'Arte', 'Música', 'Informática'
        ];

        $horasAcademicas = [24, 28, 30, 32, 36, 38, 40];

        $contadorDocentes = 0;
        $contadorAsignaciones = 0;

        DB::beginTransaction();

        try {
            for ($i = 0; $i < 50; $i++) {
                // Generar datos del docente
                $nombre = $nombresHombres[$i % count($nombresHombres)];
                $apellido = $nombresApellidos[$i % count($nombresApellidos)];
                $horas = $horasAcademicas[$i % count($horasAcademicas)];
                $codigo = 'DOC' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

                // Crear persona
                $persona = Persona::create([
                    'primer_nombre' => $nombre,
                    'primer_apellido' => $apellido,
                    'segundo_apellido' => $nombresApellidos[($i + 1) % count($nombresApellidos)],
                    'numero_documento' => strval(rand(10000000, 99999999)),
                    'fecha_nacimiento' => date('Y-m-d', strtotime('-' . rand(25, 60) . ' years')),
                    'telefono' => strval(rand(1000000, 9999999)),
                    'email' => strtolower($nombre . '.' . $apellido . $i . '@educacion.com'),
                    'tipo_documento_id' => $tipoDocumento->id,
                    'genero_id' => $genero->id,
                    'status' => true,
                ]);

                // Crear docente
                $docente = Docente::create([
                    'codigo' => $codigo,
                    'dependencia' => 'Liceo General Juan Guilermo Iribarren',
                    'horas_academicas' => $horas,
                    'persona_id' => $persona->id,
                    'anio_escolar_id' => $anioEscolar->id,
                    'status' => true,
                ]);

                // Crear detalle de docente estudio
                // Primero crear estudios realizados
                $estudiosRealizado = EstudiosRealizado::create([
                    'estudios' => 'Licenciatura en Educación',
                    'status' => true,
                ]);

                // Luego crear el detalle con la relación
                $detalleDocente = DetalleDocenteEstudio::create([
                    'docente_id' => $docente->id,
                    'estudios_id' => $estudiosRealizado->id,
                    'status' => true,
                ]);

                // Asignar áreas de formación al docente (2-3 áreas por docente)
                $numAreas = rand(2, 3);
                for ($j = 0; $j < $numAreas; $j++) {
                    $areaIndex = ($i + $j) % $areasFormacion->count();
                    $areaAsignar = $areasFormacion[$areaIndex];

                    // Crear área de estudio realizado para cada área (usar firstOrCreate para evitar duplicados)
                    $areaEstudioRealizado = AreaEstudioRealizado::firstOrCreate([
                        'estudios_id' => $estudiosRealizado->id,
                        'area_formacion_id' => $areaAsignar->id,
                    ], [
                        'status' => true,
                    ]);

                    // Asignar 1-2 grados por área
                    $numGrados = rand(1, 2);
                    for ($k = 0; $k < $numGrados; $k++) {
                        $gradoIndex = ($i + $j + $k) % $grados->count();
                        $grado = $grados[$gradoIndex];

                        // Asignar 1-2 secciones por grado
                        $numSecciones = rand(1, 2);
                        for ($l = 0; $l < $numSecciones; $l++) {
                            $seccionIndex = ($i + $j + $k + $l) % $secciones->count();
                            $seccion = $secciones[$seccionIndex];

                            // Verificar que la sección pertenezca al grado
                            if ($seccion->grado_id == $grado->id) {
                                DocenteAreaGrado::create([
                                    'docente_estudio_realizado_id' => $detalleDocente->id,
                                    'area_estudio_realizado_id' => $areaEstudioRealizado->id,
                                    'grado_id' => $grado->id,
                                    'seccion_id' => $seccion->id,
                                    'tipo_asignacion' => 'area',
                                    'status' => true,
                                ]);
                                $contadorAsignaciones++;
                            }
                        }
                    }
                }

                $contadorDocentes++;
            }

            DB::commit();

            $this->command->info("✓ Se crearon $contadorDocentes docentes exitosamente.");
            $this->command->info("✓ Se crearon $contadorAsignaciones asignaciones docente-área-grado.");
            $this->command->info("✓ Horas académicas variadas: " . implode(', ', $horasAcademicas) . " horas.");
            $this->command->info("✓ Docentes asignados a áreas: " . implode(', ', array_slice($areasFormacionNombres, 0, min(5, count($areasFormacionNombres)))) . "...");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error al crear docentes: " . $e->getMessage());
            throw $e;
        }
    }
}
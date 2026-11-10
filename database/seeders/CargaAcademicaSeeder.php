<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\AreaEstudioRealizado;
use App\Models\AreaFormacion;
use App\Models\Aula;
use App\Models\CargaAcademica;
use App\Models\ConfiguracionHorario;
use App\Models\DetalleDocenteEstudio;
use App\Models\Docente;
use App\Models\DocenteAreaGrado;
use App\Models\EstudiosRealizado;
use App\Models\Genero;
use App\Models\Grado;
use App\Models\Localidad;
use App\Models\Persona;
use App\Models\PrefijoTelefono;
use App\Models\Seccion;
use App\Models\seccion_aula;
use App\Models\TipoDocumento;
use App\Models\User;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CargaAcademicaSeeder extends Seeder
{
    /**
     * Llena las cargas académicas, docentes calificados con títulos,
     * aulas fijas y configuración global de horario para garantizar
     * que la generación de horarios sea 100% viable y sin errores de validación.
     */
    public function run(): void
    {
        $this->command?->info('Iniciando CargaAcademicaSeeder...');

        // 1. Identificar todos los años escolares destino a poblar:
        //    - Años activos o extendidos
        //    - Año seleccionado en la sesión actual
        //    - Fallback al último año registrado
        $targetYears = collect();

        foreach (AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->get() as $a) {
            $targetYears->put($a->id, $a);
        }

        if ($sessId = AnioEscolarSeleccionado::id()) {
            if (!$targetYears->has($sessId)) {
                $a = AnioEscolar::find($sessId);
                if ($a) {
                    $targetYears->put($a->id, $a);
                }
            }
        }

        if ($targetYears->isEmpty()) {
            $ultimo = AnioEscolar::latest('id')->first();
            if ($ultimo) {
                $targetYears->put($ultimo->id, $ultimo);
            }
        }

        if ($targetYears->isEmpty()) {
            $this->command?->warn('No se encontró ningún año escolar en el sistema para poblar.');
            return;
        }

        // 2. Garantizar la Configuración Global de Horario (07:00 AM, 4 bloques de 90 min)
        ConfiguracionHorario::firstOrCreate(
            [],
            [
                'hora_inicio' => '07:00:00',
                'numero_bloques' => 4,
                'duracion_bloque_min' => 90,
                'minutos_entre_bloques' => 0,
            ]
        );

        // 3. Ajustar bloques semanales por área según el Pensum MPPE 31059
        // (En bloques de 90 min, las áreas curriculares van de 1 a 2 bloques semanales)
        $bloquesDefecto = [
            'Castellano' => 2,
            'Arte y Patrimonio' => 2,
            'Ciencias Naturales' => 2,
            'Biología' => 2,
            'Matemáticas' => 2,
            'Educación Física' => 2,
            'Inglés y otras lenguas extranjeras' => 2,
            'Geografía, Historia y Ciudadanía' => 2,
            'Ciencias de la Tierra' => 2,
            'Física' => 2,
            'Química' => 2,
            'Formación para la Soberania Nacional' => 1,
            'Orientación y Convivencia' => 1,
        ];

        foreach ($bloquesDefecto as $nombreArea => $bloques) {
            AreaFormacion::where('nombre_area_formacion', 'LIKE', "%{$nombreArea}%")
                ->update(['bloques_semanales' => $bloques]);
        }

        // 4. Aulas: garantizar aulas regulares y laboratorios suficientes
        Aula::query()->whereNull('capacidad_maxima')->orWhere('capacidad_maxima', '<', 35)->update([
            'capacidad_maxima' => 35,
        ]);

        for ($lab = 1; $lab <= 8; $lab++) {
            Aula::firstOrCreate(
                ['nombre_aula' => "Laboratorio {$lab}"],
                [
                    'tipo_aula' => 'Laboratorio',
                    'capacidad_maxima' => 35,
                    'status' => true,
                ]
            );
        }

        $letras = ['A', 'B', 'C', 'D', 'E'];
        for ($g = 1; $g <= 5; $g++) {
            foreach ($letras as $idx => $letra) {
                $numAula = ($g * 100) + ($idx + 1);
                Aula::firstOrCreate(
                    ['nombre_aula' => "Aula {$numAula}"],
                    [
                        'tipo_aula' => 'Aula Regular',
                        'capacidad_maxima' => 35,
                        'status' => true,
                    ]
                );
            }
        }

        $aulasRegulares = Aula::where('tipo_aula', 'Aula Regular')
            ->where('status', true)
            ->orderBy('id_aula')
            ->get();

        $seccionesActivas = Seccion::where('status', true)->with('grado')->get();
        foreach ($seccionesActivas as $index => $seccion) {
            $aulaAsignada = $aulasRegulares[$index % $aulasRegulares->count()];
            seccion_aula::updateOrCreate(
                ['id_seccion' => $seccion->id],
                ['id_aula' => $aulaAsignada->id_aula, 'status' => true]
            );
        }

        // 5. Títulos y Especialidades de Estudio
        $titulosBase = [
            'Lengua y Literatura' => ['estudios' => 'Profesor en Lengua y Literatura', 'areas' => ['Castellano']],
            'Matemáticas' => ['estudios' => 'Profesor en Matemáticas', 'areas' => ['Matemáticas']],
            'Ciencias Naturales' => ['estudios' => 'Profesor en Ciencias Naturales', 'areas' => ['Ciencias Naturales', 'Biología', 'Ciencias de la Tierra']],
            'Educación Física' => ['estudios' => 'Profesor en Educación Física', 'areas' => ['Educación Física']],
            'Idiomas Modernos' => ['estudios' => 'Profesor en Idiomas Modernos (Inglés)', 'areas' => ['Inglés y otras lenguas extranjeras']],
            'Geografía e Historia' => ['estudios' => 'Profesor en Geografía e Historia', 'areas' => ['Geografía, Historia y Ciudadanía', 'Formación para la Soberania Nacional']],
            'Física y Química' => ['estudios' => 'Profesor en Física y Química', 'areas' => ['Física', 'Química']],
            'Arte y Música' => ['estudios' => 'Profesor en Artes Plásticas y Música', 'areas' => ['Arte y Patrimonio']],
            'Educación Integral' => ['estudios' => 'Profesor en Educación Integral', 'areas' => ['Orientación y Convivencia', 'Formación para la Soberania Nacional']],
        ];

        $titulosMap = [];
        foreach ($titulosBase as $clave => $tData) {
            $estudio = EstudiosRealizado::firstOrCreate(
                ['estudios' => $tData['estudios']],
                ['status' => true]
            );
            $titulosMap[$clave] = $estudio;

            foreach ($tData['areas'] as $nomArea) {
                $af = AreaFormacion::where('nombre_area_formacion', 'LIKE', "%{$nomArea}%")->first();
                if ($af) {
                    AreaEstudioRealizado::firstOrCreate(
                        [
                            'area_formacion_id' => $af->id,
                            'estudios_id' => $estudio->id,
                        ],
                        ['status' => true]
                    );
                }
            }
        }

        // 6. Docentes calificados: pool de 45 docentes para cubrir sobradamente todas las materias y secciones
        $tipoDoc = TipoDocumento::first()?->id ?? 1;
        $generoM = Genero::where('genero', 'LIKE', '%M%')->first()?->id ?? 1;
        $generoF = Genero::where('genero', 'LIKE', '%F%')->first()?->id ?? 2;
        $prefijo = PrefijoTelefono::first()?->id ?? 1;
        $localidad = Localidad::first()?->id ?? 1;

        $docentesData = [
            // Castellano (5 docentes)
            ['Castellano', 'Lengua y Literatura', 'Carlos', 'Pérez', 'M'],
            ['Castellano', 'Lengua y Literatura', 'Elena', 'González', 'F'],
            ['Castellano', 'Lengua y Literatura', 'Pedro', 'Martínez', 'M'],
            ['Castellano', 'Lengua y Literatura', 'Laura', 'Sánchez', 'F'],
            ['Castellano', 'Lengua y Literatura', 'Guillermo', 'Morales', 'M'],
            // Matemáticas (5 docentes)
            ['Matemáticas', 'Matemáticas', 'José', 'Rodríguez', 'M'],
            ['Matemáticas', 'Matemáticas', 'María', 'López', 'F'],
            ['Matemáticas', 'Matemáticas', 'Roberto', 'Díaz', 'M'],
            ['Matemáticas', 'Matemáticas', 'Carmen', 'Torres', 'F'],
            ['Matemáticas', 'Matemáticas', 'Ignacio', 'Rivas', 'M'],
            // Ciencias Naturales / Biología / Ciencias de la Tierra (6 docentes)
            ['Ciencias Naturales', 'Ciencias Naturales', 'Luis', 'Hernández', 'M'],
            ['Ciencias Naturales', 'Ciencias Naturales', 'Sofía', 'Gómez', 'F'],
            ['Biología', 'Ciencias Naturales', 'Ricardo', 'Flores', 'M'],
            ['Biología', 'Ciencias Naturales', 'Beatriz', 'Ramírez', 'F'],
            ['Ciencias de la Tierra', 'Ciencias Naturales', 'Jorge', 'Benítez', 'M'],
            ['Ciencias de la Tierra', 'Ciencias Naturales', 'Teresa', 'Acosta', 'F'],
            // Educación Física (4 docentes)
            ['Educación Física', 'Educación Física', 'Fernando', 'Álvarez', 'M'],
            ['Educación Física', 'Educación Física', 'Patricia', 'Ruiz', 'F'],
            ['Educación Física', 'Educación Física', 'Andrés', 'Castro', 'M'],
            ['Educación Física', 'Educación Física', 'Sergio', 'Guzmán', 'M'],
            // Inglés (5 docentes)
            ['Inglés y otras lenguas extranjeras', 'Idiomas Modernos', 'Javier', 'Romero', 'M'],
            ['Inglés y otras lenguas extranjeras', 'Idiomas Modernos', 'Daniela', 'Contreras', 'F'],
            ['Inglés y otras lenguas extranjeras', 'Idiomas Modernos', 'Diego', 'Navarro', 'M'],
            ['Inglés y otras lenguas extranjeras', 'Idiomas Modernos', 'Valentina', 'Mendoza', 'F'],
            ['Inglés y otras lenguas extranjeras', 'Idiomas Modernos', 'Carolina', 'Pacheco', 'F'],
            // Geografía, Historia y Ciudadanía (4 docentes)
            ['Geografía, Historia y Ciudadanía', 'Geografía e Historia', 'Francisco', 'Gutiérrez', 'M'],
            ['Geografía, Historia y Ciudadanía', 'Geografía e Historia', 'Mónica', 'Salazar', 'F'],
            ['Geografía, Historia y Ciudadanía', 'Geografía e Historia', 'Alejandro', 'Vargas', 'M'],
            ['Geografía, Historia y Ciudadanía', 'Geografía e Historia', 'Victoria', 'Castillo', 'F'],
            // Física y Química (6 docentes)
            ['Física', 'Física y Química', 'Héctor', 'Morales', 'M'],
            ['Física', 'Física y Química', 'Rosa', 'Medina', 'F'],
            ['Física', 'Física y Química', 'Víctor', 'Rojas', 'M'],
            ['Química', 'Física y Química', 'Manuel', 'Herrera', 'M'],
            ['Química', 'Física y Química', 'Isabel', 'Suárez', 'F'],
            ['Química', 'Física y Química', 'Natalia', 'Ortiz', 'F'],
            // Arte y Patrimonio (4 docentes)
            ['Arte y Patrimonio', 'Arte y Música', 'Gabriel', 'Ortiz', 'M'],
            ['Arte y Patrimonio', 'Arte y Música', 'Claudia', 'Silva', 'F'],
            ['Arte y Patrimonio', 'Arte y Música', 'Víctor', 'Ríos', 'M'],
            ['Arte y Patrimonio', 'Arte y Música', 'Sandra', 'Campos', 'F'],
            // FSN y Orientación y Convivencia (6 docentes)
            ['Formación para la Soberania Nacional', 'Geografía e Historia', 'Raúl', 'Campos', 'M'],
            ['Formación para la Soberania Nacional', 'Educación Integral', 'Adriana', 'Giménez', 'F'],
            ['Formación para la Soberania Nacional', 'Educación Integral', 'Humberto', 'Silva', 'M'],
            ['Orientación y Convivencia', 'Educación Integral', 'Enrique', 'Pereira', 'M'],
            ['Orientación y Convivencia', 'Educación Integral', 'Lucía', 'Castillo', 'F'],
            ['Orientación y Convivencia', 'Educación Integral', 'Carolina', 'Ramos', 'F'],
        ];

        $docentesPlantilla = [];
        $primerAnioId = $targetYears->first()->id;

        foreach ($docentesData as $idx => [$areaClave, $tituloClave, $nombre, $apellido, $gen]) {
            $numDoc = $idx + 1;
            $cedula = 'V-' . (21000000 + $numDoc);
            $email = "docente.mppe.{$numDoc}@liceo.edu.ve";
            $generoId = ($gen === 'M') ? $generoM : $generoF;

            $persona = Persona::firstOrCreate(
                ['numero_documento' => $cedula],
                [
                    'primer_nombre' => $nombre,
                    'primer_apellido' => $apellido,
                    'fecha_nacimiento' => '1985-05-15',
                    'direccion' => 'Comunidad Iribarren',
                    'email' => $email,
                    'telefono' => '04121234567',
                    'genero_id' => $generoId,
                    'tipo_documento_id' => $tipoDoc,
                    'prefijo_id' => $prefijo,
                    'localidad_id' => $localidad,
                    'status' => true,
                ]
            );

            $docente = Docente::firstOrCreate(
                ['persona_id' => $persona->id],
                [
                    'codigo' => 'DOC-' . (21000000 + $numDoc),
                    'dependencia' => 'Liceo General Juan Guillermo Iribarren',
                    'horas_academicas' => 36,
                    'anio_escolar_id' => $primerAnioId,
                    'status' => true,
                ]
            );
            $docente->update(['status' => true]);

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "{$nombre} {$apellido}",
                    'password' => Hash::make('12345678'),
                    'persona_id' => $persona->id,
                    'rol_principal' => 'Docente',
                    'activo' => true,
                    'debe_cambiar_password' => false,
                    'email_verified_at' => now(),
                ]
            );
            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles(['Docente']);
            }

            $titulo = $titulosMap[$tituloClave] ?? null;
            $detalleEstudio = null;
            if ($titulo) {
                $detalleEstudio = DetalleDocenteEstudio::firstOrCreate(
                    [
                        'docente_id' => $docente->id,
                        'estudios_id' => $titulo->id,
                    ],
                    ['status' => true]
                );
            }

            $afModel = AreaFormacion::where('nombre_area_formacion', 'LIKE', "%{$areaClave}%")->first();
            $areaId = $afModel?->id;

            $docentesPlantilla[] = [
                'docente' => $docente,
                'detalle_estudio' => $detalleEstudio,
                'area_id' => $areaId,
            ];
        }

        // 7. Poblar cargas académicas para CADA año escolar destino
        $grados = Grado::where('status', true)->with('gradoAreaFormacion.area_formacion')->get();
        $totalCargasCreadas = 0;

        foreach ($targetYears as $anioItem) {
            $docentesPorArea = [];
            foreach ($docentesPlantilla as $p) {
                $docentesPorArea[$p['area_id']][] = [
                    'docente' => $p['docente'],
                    'detalle_estudio' => $p['detalle_estudio'],
                    'bloques_actuales' => 0,
                ];
            }

            $cargasEsteAnio = 0;

            foreach ($seccionesActivas as $seccion) {
                $grado = $grados->firstWhere('id', $seccion->grado_id);
                if (!$grado) {
                    continue;
                }

                $materiasRequeridas = $grado->gradoAreaFormacion->pluck('area_formacion')->filter();
                if ($materiasRequeridas->isEmpty()) {
                    $materiasRequeridas = AreaFormacion::where('status', true)->get();
                }

                foreach ($materiasRequeridas as $area) {
                    $bloquesMateria = (int) ($area->bloques_semanales ?? 2);
                    if ($bloquesMateria <= 0) {
                        $bloquesMateria = 2;
                    }

                    $candidatos = &$docentesPorArea[$area->id];
                    $docenteAsignado = null;
                    $detalleEstudioAsignado = null;

                    if (!empty($candidatos)) {
                        usort($candidatos, fn($a, $b) => $a['bloques_actuales'] <=> $b['bloques_actuales']);

                        foreach ($candidatos as &$cand) {
                            if (($cand['bloques_actuales'] + $bloquesMateria) <= 16) {
                                $docenteAsignado = $cand['docente'];
                                $detalleEstudioAsignado = $cand['detalle_estudio'];
                                $cand['bloques_actuales'] += $bloquesMateria;
                                break;
                            }
                        }
                        unset($cand);
                    }

                    // Fallback 1: Cualquier docente del área hasta 18 bloques
                    if (!$docenteAsignado && !empty($candidatos)) {
                        foreach ($candidatos as &$cand) {
                            if (($cand['bloques_actuales'] + $bloquesMateria) <= 18) {
                                $docenteAsignado = $cand['docente'];
                                $detalleEstudioAsignado = $cand['detalle_estudio'];
                                $cand['bloques_actuales'] += $bloquesMateria;
                                break;
                            }
                        }
                        unset($cand);
                    }

                    // Fallback 2: Docente de cualquier otra área hasta 18 bloques
                    if (!$docenteAsignado) {
                        foreach ($docentesPorArea as &$grupo) {
                            foreach ($grupo as &$cand) {
                                if (($cand['bloques_actuales'] + $bloquesMateria) <= 18) {
                                    $docenteAsignado = $cand['docente'];
                                    $detalleEstudioAsignado = $cand['detalle_estudio'];
                                    $cand['bloques_actuales'] += $bloquesMateria;
                                    break 2;
                                }
                            }
                        }
                        unset($grupo, $cand);
                    }

                    if ($docenteAsignado) {
                        CargaAcademica::updateOrCreate(
                            [
                                'anio_escolar_id' => $anioItem->id,
                                'seccion_id' => $seccion->id,
                                'area_formacion_id' => $area->id,
                            ],
                            [
                                'docente_id' => $docenteAsignado->id,
                            ]
                        );

                        if ($detalleEstudioAsignado) {
                            $areaEstudio = AreaEstudioRealizado::where('area_formacion_id', $area->id)
                                ->where('estudios_id', $detalleEstudioAsignado->estudios_id)
                                ->first();

                            if (!$areaEstudio) {
                                $areaEstudio = AreaEstudioRealizado::firstOrCreate(
                                    [
                                        'area_formacion_id' => $area->id,
                                        'estudios_id' => $detalleEstudioAsignado->estudios_id,
                                    ],
                                    ['status' => true]
                                );
                            }

                            DocenteAreaGrado::updateOrCreate(
                                [
                                    'docente_estudio_realizado_id' => $detalleEstudioAsignado->id,
                                    'area_estudio_realizado_id' => $areaEstudio->id,
                                    'grado_id' => $seccion->grado_id,
                                    'seccion_id' => $seccion->id,
                                ],
                                [
                                    'tipo_asignacion' => 'area',
                                    'status' => true,
                                ]
                            );
                        }

                        $cargasEsteAnio++;
                    }
                }
            }

            $totalCargasCreadas += $cargasEsteAnio;
            $this->command?->info("  - Año ID {$anioItem->id} ({$anioItem->status}): {$cargasEsteAnio} cargas académicas verificadas.");
        }

        $this->command?->info("✓ CargaAcademicaSeeder completado con éxito:");
        $this->command?->info("  - Total años poblados: " . $targetYears->count());
        $this->command?->info("  - Secciones atendidas: " . $seccionesActivas->count());
        $this->command?->info("  - Total cargas académicas: {$totalCargasCreadas}");
        $this->command?->info("  - Docentes registrados con títulos: " . count($docentesPlantilla));
    }
}

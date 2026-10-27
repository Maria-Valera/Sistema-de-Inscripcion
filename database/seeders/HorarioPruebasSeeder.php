<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\AreaFormacion;
use App\Models\Aula;
use App\Models\CargaAcademica;
use App\Models\Docente;
use App\Models\Genero;
use App\Models\Grado;
use App\Models\Localidad;
use App\Models\Persona;
use App\Models\PrefijoTelefono;
use App\Models\Seccion;
use App\Models\seccion_aula;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HorarioPruebasSeeder extends Seeder
{
    /**
     * Resultados de la ejecución para reportería en comandos Artisan.
     *
     * @var array{bloquesPermitidos: int, docentesActivos: int, cargasCreadas: int, horariosLimpiados: bool}
     */
    public array $resumen = [
        'bloquesPermitidos' => 0,
        'docentesActivos' => 0,
        'cargasCreadas' => 0,
        'horariosLimpiados' => false,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Verificación de entorno: Solo local o testing
        if (!app()->environment(['local', 'testing'])) {
            $this->command?->warn("[HorarioPruebasSeeder] Omitido: Solo se permite ejecutar en entorno 'local' o 'testing'.");
            return;
        }

        // Obtener año escolar activo
        $anio = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first()
            ?? AnioEscolar::latest('id')->first();

        if (!$anio) {
            $this->command?->error("[HorarioPruebasSeeder] No se encontró ningún año escolar registrado o activo.");
            return;
        }

        // =====================================================================
        // PASO A: Ajustar bloques semanales
        // =====================================================================
        $areas = AreaFormacion::where('status', true)->get();
        $grados = Grado::where('status', true)->with('gradoAreaFormacion')->get();
        
        $areasPorGrado = $grados->map(fn($g) => $g->gradoAreaFormacion->count())->filter();
        $promedioAreas = $areasPorGrado->isNotEmpty() ? (int) round($areasPorGrado->avg()) : $areas->count();
        $totalAreas = max(1, $promedioAreas);

        $bloquesPermitidos = max(1, min(5, intdiv(20, $totalAreas)));

        AreaFormacion::query()->update([
            'bloques_semanales' => $bloquesPermitidos,
            'bloques_maximos_por_dia' => min(2, $bloquesPermitidos),
        ]);

        $this->resumen['bloquesPermitidos'] = $bloquesPermitidos;

        // Asegurar que las aulas de laboratorio cubran la demanda especializada
        $labsActivos = Aula::where('tipo_aula', 'Laboratorio')->where('status', true)->count();
        if ($labsActivos < 5) {
            for ($l = $labsActivos + 1; $l <= 5; $l++) {
                Aula::firstOrCreate(
                    ['nombre_aula' => "Laboratorio {$l}"],
                    [
                        'tipo_aula' => 'Laboratorio',
                        'capacidad_maxima' => 35,
                        'status' => true,
                    ]
                );
            }
        }

        // Asegurar que cada sección tenga un aula regular activa asignada con capacidad suficiente
        $aulaRegularDefecto = Aula::where('tipo_aula', 'Aula Regular')->where('status', true)->first();
        if ($aulaRegularDefecto) {
            foreach (Seccion::where('status', true)->with('aulaFija.aula')->get() as $seccion) {
                if (!$seccion->aulaFija || !$seccion->aulaFija->aula || !$seccion->aulaFija->aula->status) {
                    seccion_aula::updateOrCreate(
                        ['id_seccion' => $seccion->id],
                        ['id_aula' => $aulaRegularDefecto->id, 'status' => true]
                    );
                }
            }
        }

        // =====================================================================
        // PASO B: Asegurar docentes suficientes
        // =====================================================================
        $seccionesActivas = Seccion::where('status', true)->with('grado.gradoAreaFormacion')->get();
        $totalCargasEstimadas = 0;
        foreach ($seccionesActivas as $s) {
            $totalCargasEstimadas += $s->grado?->gradoAreaFormacion?->count() ?: 10;
        }
        $bloquesTotalesEstimados = $totalCargasEstimadas * $bloquesPermitidos;
        
        // Mantener tope seguro de 14 bloques por docente (límite legal es 18) para evitar saturación
        $docentesNecesarios = max(5, (int) ceil($bloquesTotalesEstimados / 14));
        $docentesActivosCount = Docente::where('status', true)->count();

        if ($docentesActivosCount < $docentesNecesarios) {
            $generoId = Genero::first()?->id ?? 1;
            $tipoDocId = TipoDocumento::first()?->id ?? 1;
            $prefijoId = PrefijoTelefono::first()?->id ?? 1;
            $localidadId = Localidad::first()?->id ?? 1;

            for ($i = $docentesActivosCount + 1; $i <= $docentesNecesarios; $i++) {
                $cedula = "8000" . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
                $email = "docente.prueba{$i}@example.com";

                $persona = Persona::firstOrCreate(
                    ['numero_documento' => $cedula],
                    [
                        'primer_nombre' => "Docente",
                        'primer_apellido' => "Prueba {$i}",
                        'fecha_nacimiento' => '1988-01-01',
                        'direccion' => 'Dirección de prueba',
                        'email' => $email,
                        'telefono' => '12345678',
                        'status' => true,
                        'genero_id' => $generoId,
                        'tipo_documento_id' => $tipoDocId,
                        'prefijo_id' => $prefijoId,
                        'localidad_id' => $localidadId,
                    ]
                );

                Docente::firstOrCreate(
                    ['persona_id' => $persona->id],
                    [
                        'codigo' => 'PRUEBA-' . $cedula,
                        'dependencia' => 'Liceo General Juan Guillermo Iribarren',
                        'anio_escolar_id' => $anio->id,
                        'horas_academicas' => 36,
                        'status' => true,
                    ]
                );

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => "Docente Prueba {$i}",
                        'password' => Hash::make('12345678'),
                        'persona_id' => $persona->id,
                        'rol_principal' => 'Docente',
                        'debe_cambiar_password' => false,
                        'activo' => true,
                        'email_verified_at' => now(),
                    ]
                );

                if (method_exists($user, 'syncRoles')) {
                    $user->syncRoles(['Docente']);
                }
            }
        }

        $docentes = Docente::where('status', true)->get();
        $this->resumen['docentesActivos'] = $docentes->count();

        // =====================================================================
        // PASO C: Asignar cargas académicas
        // =====================================================================
        $bloquesPorDocente = [];
        foreach ($docentes as $d) {
            $bloquesPorDocente[$d->id] = 0;
        }

        $docenteKeys = $docentes->pluck('id')->values()->all();
        $totalDocs = count($docenteKeys);
        $docIndex = 0;
        $cargasCreadas = 0;

        foreach ($seccionesActivas as $seccion) {
            $materias = $seccion->grado?->gradoAreaFormacion->pluck('area_formacion')->filter() ?? collect();
            if ($materias->isEmpty()) {
                $materias = $areas;
            }

            foreach ($materias as $area) {
                // Seleccionar docente de forma rotativa sin exceder 18 bloques
                $docenteAsignadoId = null;
                $intentos = 0;
                while ($intentos < $totalDocs) {
                    $candidatoId = $docenteKeys[$docIndex];
                    if (($bloquesPorDocente[$candidatoId] ?? 0) + $bloquesPermitidos <= 18) {
                        $docenteAsignadoId = $candidatoId;
                        $bloquesPorDocente[$candidatoId] += $bloquesPermitidos;
                        $docIndex = ($docIndex + 1) % $totalDocs;
                        break;
                    }
                    $docIndex = ($docIndex + 1) % $totalDocs;
                    $intentos++;
                }

                if ($docenteAsignadoId) {
                    CargaAcademica::updateOrCreate(
                        [
                            'anio_escolar_id' => $anio->id,
                            'seccion_id' => $seccion->id,
                            'area_formacion_id' => $area->id,
                        ],
                        [
                            'docente_id' => $docenteAsignadoId,
                        ]
                    );
                    $cargasCreadas++;
                }
            }
        }

        $this->resumen['cargasCreadas'] = $cargasCreadas;

        // =====================================================================
        // PASO D: Limpiar horarios viejos
        // =====================================================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('horario_asignaciones')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->resumen['horariosLimpiados'] = true;

        // Feedback por consola si se ejecuta vía artisan db:seed
        $this->command?->info("Áreas actualizadas a {$this->resumen['bloquesPermitidos']} bloques semanales.");
        $this->command?->info("Docentes activos: {$this->resumen['docentesActivos']}");
        $this->command?->info("Cargas académicas creadas/verificadas: {$this->resumen['cargasCreadas']}");
        $this->command?->info("Tabla horario_asignaciones limpiada.");
    }
}

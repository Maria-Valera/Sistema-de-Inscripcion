<?php

namespace App\Services;

use App\Models\Inscripcion;
use App\Models\Seccion;
use App\Models\EjecucionesPercentil;
use App\Models\EntradasPercentil;
use App\Models\AnioEscolar;
use App\Support\AnioEscolarSeleccionado;
use App\Services\PercentilService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SectionDistributorService
{
    protected $percentilService;

    public function __construct(PercentilService $percentilService)
    {
        $this->percentilService = $percentilService;
    }

    public function procesarGrado($grado)
    {
        return DB::transaction(function () use ($grado) {

            // Verificar Calendario Escolar activo o seleccionado
            $anioEscolarActivo = AnioEscolarSeleccionado::modelo();

            if (!$anioEscolarActivo) {
                throw new \Exception('No hay un Calendario Escolar activo');
            }

            $inscripciones = Inscripcion::where('grado_id', $grado->id)
                ->whereIn('status', ['Activo', 'Pendiente'])
                ->where(function ($q) use ($grado, $anioEscolarActivo) {

                    if ((int) $grado->numero_grado === 1) {

                        // NUEVO INGRESO
                        $q->where(function ($qq) use ($anioEscolarActivo) {
                            $qq->whereHas('nuevoIngreso')
                            ->where('inscripcions.anio_escolar_id', $anioEscolarActivo->id);
                        })

                        // REPITIENTE (NO promovido)
                        ->orWhere(function ($qq) use ($anioEscolarActivo) {
                            $qq->whereHas('prosecucion', function ($p) use ($anioEscolarActivo) {
                                $p->where('inscripcion_prosecucions.anio_escolar_id', $anioEscolarActivo->id)
                                ->where('status', 'Activo')
                                ->where('promovido', 0);
                            });
                        });

                    } else {

                        // otros grados (solo promovidos)
                        $q->whereHas('prosecucion', function ($p) {
                            $p->where('promovido', 1);
                        });
                    }
                })
                ->with(['alumno.persona', 'nuevoIngreso', 'prosecucion'])
                ->get();




            if ($inscripciones->isEmpty()) {
                throw new \Exception('No hay inscripciones activas para 1er Año');
            }

            // 2. Crear ejecución
            EntradasPercentil::whereHas('ejecucion', function ($q) use ($anioEscolarActivo) {
                $q->where('anio_escolar_id', $anioEscolarActivo->id);
            })->delete();

            Seccion::whereHas('ejecucion', function ($q) use ($anioEscolarActivo) {
                $q->where('anio_escolar_id', $anioEscolarActivo->id);
            })->delete();


            $ejecucion = EjecucionesPercentil::create([
                'anio_escolar_id' => $anioEscolarActivo->id,
                'status' => true
            ]);



            // 3. Crear entradas percentil
            $entradas = collect();

            foreach ($inscripciones as $inscripcion) {
                try {
                    $entrada = $this->percentilService
                        ->crearEntradaDesdeInscripcion($inscripcion, $ejecucion->id);


                    $entrada->ejecucion_percentil_id = $ejecucion->id;
                    $entrada->save();

                    $entradas->push($entrada);
                } catch (\Exception $e) {
                    Log::error(
                        "Error inscripción {$inscripcion->id}: {$e->getMessage()}"
                    );
                }
            }

            if ($entradas->isEmpty()) {
                throw new \Exception('No se pudo procesar ninguna inscripción');
            }

            // 4. Ordenar
            $ordenados = EntradasPercentil::where('ejecucion_percentil_id', $ejecucion->id)
                ->orderBy('indice_total')
                ->get();


            // 5. Calcular secciones
            $min = $grado->min_seccion ?? 20;
            $max = $grado->max_seccion ?? 30;
            $total = $ordenados->count();

            
            $numSecciones = (int) ceil($total / $max);

            if (($total / $numSecciones) < $min && $numSecciones > 1) {
                $numSecciones = (int) ceil($total / $min);
            }

            // 6. Crear secciones
            Seccion::where('grado_id', $grado->id)
                ->where('ejecucion_percentil_id', $ejecucion->id)
                ->delete();
            $secciones = collect();
            $letras = range('A', 'Z');

            for ($i = 0; $i < $numSecciones; $i++) {
                $secciones->push(
                    Seccion::create([
                        'nombre' => $letras[$i],
                        'cantidad_actual' => 0,
                        'grado_id' => $grado->id,
                        'ejecucion_percentil_id' => $ejecucion->id,
                        'status' => true
                    ])
                );
            }

            EntradasPercentil::where('ejecucion_percentil_id', $ejecucion->id)
                ->update(['seccion_id' => null]);

            DB::table('inscripcion_prosecucions')
                ->where('anio_escolar_id', $anioEscolarActivo->id)
                ->where('status', 'Activo')
                ->update(['seccion_id' => null]);




            // 7. Distribuir estudiantes
            $tamañoBase = (int) floor($total / $numSecciones);
            $seccionesExtras = $total % $numSecciones;

            $estudiantesAsignados = 0;

            foreach ($secciones as $indexSeccion => $seccion) {
                // Las primeras secciones con extras llevan un estudiante más
                $tamañoSeccion = $tamañoBase + ($indexSeccion < $seccionesExtras ? 1 : 0);

                // Asignar estudiantes consecutivos de la lista ordenada
                for ($i = 0; $i < $tamañoSeccion; $i++) {
                    $entrada = $ordenados[$estudiantesAsignados];

                    // Guardar sección en la entrada percentil
                    $entrada->update([
                        'seccion_id' => $seccion->id
                    ]);

                    // Guardar sección TAMBIÉN en inscripción
                    $inscripcion = $entrada->inscripcion;

                    // ¿Tiene prosecución activa para este Calendario Escolar?
                    $prosecucion = $inscripcion->prosecucion()
                        ->where('anio_escolar_id', $anioEscolarActivo->id)
                        ->where('status', 'Activo')
                        ->first();


                    if ($prosecucion) {
                        //  Asignar sección en PROSECUCIÓN
                        $prosecucion->update([
                            'seccion_id' => $seccion->id
                        ]);
                    } else {
                        // Solo si NO hay prosecución (nuevo ingreso real)
                        $inscripcion->update([
                            'seccion_id' => $seccion->id
                        ]);
                    }


                    // Aumentar contador
                    $seccion->increment('cantidad_actual');

                    $estudiantesAsignados++;
                }
            }

            Log::info('QUERY INSCRIPCIONES', [
                'sql' => $inscripciones = Inscripcion::where('grado_id', $grado->id)
                    ->where('anio_escolar_id', $anioEscolarActivo->id)
                    ->whereIn('status', ['Activo', 'Pendiente'])
                    ->whereHas('nuevoIngreso')
                    ->toSql(),
                'bindings' => $inscripciones = Inscripcion::where('grado_id', $grado->id)
                    ->where('anio_escolar_id', $anioEscolarActivo->id)
                    ->whereIn('status', ['Activo', 'Pendiente'])
                    ->whereHas('nuevoIngreso')
                    ->getBindings(),
            ]);

            return [
                'total_secciones' => $numSecciones,
                'estudiantes_procesados' => $total,
                'ejecucion_id' => $ejecucion->id
            ];
        });
    }
}

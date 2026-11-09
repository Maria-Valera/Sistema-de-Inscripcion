<?php

namespace App\Services;

use App\Models\Inscripcion;
use App\Models\Grado;
use App\Models\Alumno;
use App\Models\Persona;
use App\DTOs\InscripcionData;
use App\Models\InscripcionNuevoIngreso;
use App\Models\InscripcionProsecucion;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InscripcionService
{
    public function __construct(
        private DocumentoService $documentoService
    ) {}

    public function obtenerAnioEscolarActivo()
    {
        $anioEscolar = AnioEscolarSeleccionado::modelo();

        if (!$anioEscolar) {
            throw new \Exception('No hay un Calendario Escolar activo o seleccionado. Por favor, contacte al administrador.');
        }

        return $anioEscolar;
    }

    public function verificarCuposDisponibles($gradoId): bool
    {
        $grado = Grado::find($gradoId);

        if (!$grado) {
            return false;
        }

        $anioEscolarActivo = $this->obtenerAnioEscolarActivo();

        $inscripcionesActivas = Inscripcion::where('grado_id', $gradoId)
            ->whereIn('status', ['Activo', 'Pendiente'])
            ->where(function ($q) use ($grado, $anioEscolarActivo) {
                if ((int) $grado->numero_grado === 1) {
                    $q->where(function ($qq) use ($anioEscolarActivo) {
                        $qq->whereHas('nuevoIngreso', function ($n) use ($anioEscolarActivo) {
                            $n->where('inscripcion_nuevo_ingresos.anio_escolar_id', $anioEscolarActivo->id);
                        })
                            ->orWhereHas('prosecucion', function ($p) use ($anioEscolarActivo) {
                                $p->where('inscripcion_prosecucions.anio_escolar_id', $anioEscolarActivo->id)
                                    ->where('status', 'Activo')
                                    ->where('promovido', 0);
                            });
                    });
                } else {
                    $q->whereHas('prosecucion', function ($p) use ($anioEscolarActivo) {
                        $p->where('inscripcion_prosecucions.anio_escolar_id', $anioEscolarActivo->id)
                            ->where('promovido', 1);
                    });
                }
            })
            ->count();

        return $inscripcionesActivas < $grado->capacidad_max;
    }

    public function obtenerInfoCupos($gradoId): ?array
    {
        $grado = Grado::find($gradoId);

        if (!$grado) {
            return null;
        }

        $anioEscolarActivo = $this->obtenerAnioEscolarActivo();

        $inscritos = Inscripcion::where('grado_id', $gradoId)
            ->whereIn('status', ['Activo', 'Pendiente'])
            ->where('anio_escolar_id', $anioEscolarActivo->id)
            ->count();

        return [
            'total_cupos' => $grado->capacidad_max,
            'cupos_ocupados' => $inscritos,
            'cupos_disponibles' => max(0, $grado->capacidad_max - $inscritos),
            'porcentaje_ocupacion' => $grado->capacidad_max > 0
                ? round(($inscritos / $grado->capacidad_max) * 100, 2)
                : 0
        ];
    }

    public function validarAnioEgreso(string $fecha): bool
    {
        $anioEgreso = Carbon::parse($fecha)->year;
        $anioActual = now()->year;
        return $anioEgreso >= ($anioActual - 7)
            && $anioEgreso <= $anioActual;
    }

    public function registrar(InscripcionData $data): Inscripcion
    {
        DB::beginTransaction();

        try {
            // Validar cupos disponibles
            if (!$this->verificarCuposDisponibles($data->grado_id)) {
                throw new \Exception('El grado ha alcanzado el límite de cupos disponibles.');
            }

            $grado = Grado::findOrFail($data->grado_id);
            $esPrimerGrado = ((int) $grado->numero_grado === 1);

            // Evaluar estado de documentos
            $evaluacion = $this->documentoService->evaluarEstadoDocumentos(
                $data->documentos,
                !$data->padre_id && !$data->madre_id,
                $esPrimerGrado
            );

            // Solo bloquear si faltan documentos OBLIGATORIOS
            if (!$evaluacion['puede_guardar']) {
                $etiquetas = $this->documentoService->obtenerEtiquetas();

                $lista = collect($evaluacion['faltantes_obligatorios'])
                    ->map(fn($doc) => $etiquetas[$doc] ?? ucfirst(str_replace('_', ' ', $doc)))
                    ->implode(', ');

                throw new \Exception("Faltan documentos obligatorios: {$lista}");
            }

            // Si puede guardar, proceder con la inscripción
            $anioEscolar = $this->obtenerAnioEscolarActivo();

            $inscripcion = Inscripcion::create([
                'tipo_inscripcion' => $data->tipo_inscripcion,
                'anio_escolar_id' => $anioEscolar->id,
                'alumno_id' => $data->alumno_id,
                'grado_id' => $data->grado_id,
                'seccion_id' => $data->seccion_id,
                'padre_id' => $data->padre_id,
                'madre_id' => $data->madre_id,
                'representante_legal_id' => $data->representante_legal_id,
                'documentos' => $data->documentos,
                'estado_documentos' => $evaluacion['estado_documentos'],
                'fecha_inscripcion' => $data->fecha_inscripcion,
                'observaciones' => $data->observaciones,
                'acepta_normas_contrato' => $data->acepta_normas_contrato,
                'status' => $evaluacion['status_inscripcion'], // 'Activo' o 'Pendiente'
            ]);

            $tipo = $data->tipo_inscripcion;

            if ($tipo === 'nuevo_ingreso') {
                InscripcionNuevoIngreso::create([
                    'inscripcion_id' => $inscripcion->id,
                    'institucion_procedencia_id' => $data->institucion_procedencia_id,
                    'anio_egreso' => $data->anio_egreso,
                    'expresion_literaria_id' => $data->expresion_literaria_id,
                    'numero_zonificacion' => $data->numero_zonificacion,
                ]);
            }

            if ($tipo === 'prosecucion') {
                InscripcionProsecucion::create([
                    'inscripcion_id' => $inscripcion->id,
                    'promovido' => $data->promovido,
                    'repite_grado' => $data->repite_grado,
                ]);
            }

            DB::commit();
            return $inscripcion;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar inscripción', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);
            throw $e;
        }
    }

    public function registrarConAlumno(
        array $datosAlumno,
        InscripcionData $datosInscripcion,
        array $discapacidades = []
    ): Inscripcion {
        DB::beginTransaction();

        try {
            $persona = Persona::create([
                'primer_nombre' => $datosAlumno['primer_nombre'],
                'segundo_nombre' => $datosAlumno['segundo_nombre'] ?? null,
                'tercer_nombre' => $datosAlumno['tercer_nombre'] ?? null,
                'primer_apellido' => $datosAlumno['primer_apellido'],
                'segundo_apellido' => $datosAlumno['segundo_apellido'] ?? null,
                'tipo_documento_id' => $datosAlumno['tipo_documento_id'],
                'numero_documento' => $datosAlumno['numero_documento'],
                'genero_id' => $datosAlumno['genero_id'],
                'fecha_nacimiento' => $datosAlumno['fecha_nacimiento'],
                'localidad_id' => $datosAlumno['localidad_id'],
                'status' => true,
            ]);

            $alumno = Alumno::create([
                'persona_id' => $persona->id,
                'talla_camisa_id' => $datosAlumno['talla_camisa_id'],
                'talla_pantalon_id' => $datosAlumno['talla_pantalon_id'],
                'talla_zapato' => $datosAlumno['talla_zapato'],
                'peso' => $datosAlumno['peso_estudiante'],
                'estatura' => $datosAlumno['talla_estudiante'],
                'lateralidad_id' => $datosAlumno['lateralidad_id'],
                'orden_nacimiento_id' => $datosAlumno['orden_nacimiento_id'],
                'etnia_indigena_id' => $datosAlumno['etnia_indigena_id'],
                'status' => 'Activo',
            ]);

            if (!empty($discapacidades)) {
                foreach ($discapacidades as $discapacidad) {
                    \App\Models\DiscapacidadEstudiante::create([
                        'alumno_id' => $alumno->id,
                        'discapacidad_id' => $discapacidad['id'],
                        'status' => true
                    ]);
                }
            }

            $datosInscripcion->alumno_id = $alumno->id;
            $inscripcion = $this->registrar($datosInscripcion);

            DB::commit();
            return $inscripcion;

        } catch (QueryException $e) {
            DB::rollBack();
            \Log::error('Error de base de datos al registrar alumno', [
                'mensaje' => $e->getMessage(),
                'codigo' => $e->getCode(),
            ]);
            throw new \Exception($e);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error general al registrar con alumno', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);
            throw $e;
        }
    }
}
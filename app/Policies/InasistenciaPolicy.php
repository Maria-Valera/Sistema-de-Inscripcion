<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Alumno;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;

class InasistenciaPolicy
{
    /**
     * Superadministradores tienen acceso total excepto en update para permitir validación de conflicto de intereses
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($ability === 'update') {
            return null;
        }

        if ($user->hasRole('Admin') || $user->hasRole('Subdirectora')) {
            return true;
        }

        return null;
    }

    /**
     * Determina si el usuario puede listar inasistencias o justificaciones
     */
    public function viewAny(User $user): bool
    {
        return $user->can('inasistencia_docente.ver')
            || $user->can('justificaciones.ver')
            || $user->hasRole('Docente')
            || $user->hasRole('Representante');
    }

    /**
     * Determina si el usuario puede ver inasistencias de un alumno específico
     */
    public function view(User $user, $target = null): bool
    {
        $alumno = null;
        if ($target instanceof Alumno) {
            $alumno = $target;
        } elseif (is_object($target)) {
            $alumno = $target->alumno ?? (isset($target->alumno_id) ? Alumno::find($target->alumno_id) : null);
        }

        if (!$alumno) {
            return true;
        }

        if ($user->hasRole('Representante')) {
            $representante = $user->representante;
            if (!$representante) {
                return false;
            }

            $representanteId = $representante->id;
            return $alumno->inscripciones()
                ->where(function ($sub) use ($representanteId) {
                    $sub->where('padre_id', $representanteId)
                        ->orWhere('madre_id', $representanteId)
                        ->orWhere('representante_legal_id', $representanteId)
                        ->orWhereHas('representanteLegal', function ($rl) use ($representanteId) {
                            $rl->where('representante_id', $representanteId);
                        });
                })->exists();
        }

        if ($user->hasRole('Docente')) {
            $docente = $user->docente;
            if (!$docente) {
                return false;
            }

            $seccionesCarga = $docente->docenteAreaGrado()
                ->where('docente_area_grados.status', true)
                ->whereNotNull('seccion_id')
                ->pluck('seccion_id')
                ->toArray();

            $seccionesHorario = DB::table('horario_asignaciones')
                ->where('docente_id', $docente->id)
                ->pluck('seccion_id')
                ->toArray();

            $secciones = array_values(array_unique(array_filter(array_merge($seccionesCarga, $seccionesHorario))));

            return $alumno->inscripciones()
                ->where(function ($sub) use ($secciones) {
                    $sub->whereIn('seccion_id', $secciones)
                        ->orWhereHas('prosecucion', function ($pros) use ($secciones) {
                            $pros->whereIn('seccion_id', $secciones);
                        })
                        ->orWhereHas('entradaPercentil', function ($ep) use ($secciones) {
                            $ep->whereIn('seccion_id', $secciones);
                        });
                })->exists();
        }

        return false;
    }

    /**
     * Determina si el usuario puede registrar inasistencias o justificaciones
     */
    public function create(User $user, $target = null): bool
    {
        $alumno = null;
        if ($target instanceof Alumno) {
            $alumno = $target;
        } elseif (is_object($target)) {
            $alumno = $target->alumno ?? (isset($target->alumno_id) ? Alumno::find($target->alumno_id) : null);
        }

        if ($user->can('justificaciones.crear') || $user->can('inasistencia_docente.crear')) {
            return true;
        }

        // Representante puede registrar justificaciones para sus representados
        if ($user->hasRole('Representante')) {
            if (!$alumno) {
                return true;
            }
            return $this->view($user, $alumno);
        }

        // Docente puede registrar inasistencias de sus alumnos
        if ($user->hasRole('Docente')) {
            if (!$alumno) {
                return true;
            }
            return $this->view($user, $alumno);
        }

        return false;
    }

    /**
     * Determina si el usuario puede modificar una inasistencia o justificación
     */
    public function update(User $user, $target = null): bool
    {
        $alumno = null;
        if ($target instanceof Alumno) {
            $alumno = $target;
        } elseif (is_object($target)) {
            $alumno = $target->alumno ?? (isset($target->alumno_id) ? Alumno::find($target->alumno_id) : null);
        }

        // 1. Verificar si el usuario tiene AMBOS roles (Docente y Representante)
        $esDocente = $user->hasRole('Docente') || $user->isDocente();
        $esRepresentante = $user->hasRole('Representante') || $user->isRepresentante();

        // 2. Si intenta modificar la inasistencia de su hijo/representado, denegar y auditar
        if ($alumno && $esDocente && $esRepresentante) {
            $esRepresentado = Alumno::visiblesParaRepresentante($user)
                ->where('alumnos.id', $alumno->id)
                ->exists();

            if ($esRepresentado) {
                Auditoria::registrar(
                    'acceso_denegado',
                    $alumno,
                    null,
                    [
                        'motivo' => 'conflicto de intereses (docente-representante)',
                        'alumno_id' => $alumno->id,
                        'user_id' => $user->id,
                        'accion_intentada' => 'update_inasistencia',
                    ],
                    'conflicto de intereses (docente-representante)'
                );

                return false;
            }
        }

        return $user->can('justificaciones.editar')
            || $user->can('inasistencia_docente.editar')
            || $user->hasRole('Admin')
            || $user->hasRole('Subdirectora');
    }

    /**
     * Determina si el usuario puede eliminar una inasistencia o justificación
     */
    public function delete(User $user): bool
    {
        return $user->can('justificaciones.eliminar')
            || $user->can('inasistencia_docente.eliminar')
            || $user->hasRole('Admin')
            || $user->hasRole('Subdirectora');
    }
}

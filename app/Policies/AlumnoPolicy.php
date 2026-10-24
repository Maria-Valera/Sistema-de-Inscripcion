<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Support\Facades\DB;

class AlumnoPolicy
{
    /**
     * Superadministradores tienen acceso total
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin') || $user->hasRole('Subdirectora')) {
            return true;
        }

        return null;
    }

    /**
     * Determina si el usuario puede listar alumnos
     */
    public function viewAny(User $user): bool
    {
        return $user->can('alumnos.ver')
            || $user->hasRole('Docente')
            || $user->hasRole('Representante');
    }

    /**
     * Determina si el usuario puede ver los detalles de un alumno específico
     */
    public function view(User $user, Alumno $alumno): bool
    {
        if ($user->can('alumnos.ver')) {
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

            if (empty($secciones)) {
                return false;
            }

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
     * Determina si el usuario puede registrar un nuevo alumno
     */
    public function create(User $user): bool
    {
        return $user->can('alumnos.crear') || $user->can('alumnos.gestionar');
    }

    /**
     * Determina si el usuario puede actualizar un alumno
     */
    public function update(User $user, Alumno $alumno): bool
    {
        return $user->can('alumnos.editar') || $user->can('alumnos.gestionar');
    }

    /**
     * Determina si el usuario puede eliminar un alumno
     */
    public function delete(User $user, Alumno $alumno): bool
    {
        return $user->can('alumnos.eliminar') || $user->can('alumnos.gestionar');
    }

    /**
     * Determina si el usuario puede ver los datos sensibles del alumno
     * (tallas, peso, estatura, orden de nacimiento, lateralidad, etnia, dirección, localidad, datos de representantes)
     */
    public function viewSensitiveData(User $user, ?Alumno $alumno = null): bool
    {
        return !$user->esSoloDocente();
    }
}

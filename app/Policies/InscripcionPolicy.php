<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;

class InscripcionPolicy
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
     * Determina si el usuario puede listar inscripciones
     */
    public function viewAny(User $user): bool
    {
        return $user->can('inscripciones.ver')
            || $user->can('prosecucion.ver')
            || $user->can('portal_prosecucion.ver_planilla')
            || $user->hasRole('Representante')
            || $user->hasRole('Docente');
    }

    /**
     * Determina si el usuario puede ver una inscripción específica
     */
    public function view(User $user, Inscripcion $inscripcion): bool
    {
        if ($user->can('inscripciones.ver') || $user->can('prosecucion.ver')) {
            return true;
        }

        if ($user->hasRole('Representante')) {
            $representante = $user->representante;
            if (!$representante) {
                return false;
            }

            $representanteId = $representante->id;

            return $inscripcion->padre_id == $representanteId
                || $inscripcion->madre_id == $representanteId
                || $inscripcion->representante_legal_id == $representanteId
                || ($inscripcion->representanteLegal && $inscripcion->representanteLegal->representante_id == $representanteId);
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

            return in_array($inscripcion->seccion_id, $secciones)
                || ($inscripcion->prosecucion && in_array($inscripcion->prosecucion->seccion_id, $secciones))
                || ($inscripcion->entradaPercentil && in_array($inscripcion->entradaPercentil->seccion_id, $secciones));
        }

        return false;
    }

    /**
     * Determina si el usuario puede crear inscripciones
     */
    public function create(User $user): bool
    {
        return $user->can('inscripciones.crear')
            || $user->can('inscripciones.gestionar')
            || $user->can('prosecucion.gestionar')
            || $user->can('portal_prosecucion.registrar');
    }

    /**
     * Determina si el usuario puede actualizar una inscripción
     */
    public function update(User $user, Inscripcion $inscripcion): bool
    {
        if ($user->can('inscripciones.editar') || $user->can('inscripciones.gestionar')) {
            return true;
        }

        // Un representante solo puede modificar inscripciones en estado 'Pendiente' o en su flujo de registro
        if ($user->hasRole('Representante') && $user->can('portal_prosecucion.registrar')) {
            $representante = $user->representante;
            if (!$representante) {
                return false;
            }

            $representanteId = $representante->id;
            $esPropia = $inscripcion->padre_id == $representanteId
                || $inscripcion->madre_id == $representanteId
                || $inscripcion->representante_legal_id == $representanteId
                || ($inscripcion->representanteLegal && $inscripcion->representanteLegal->representante_id == $representanteId);

            return $esPropia && in_array(strtolower($inscripcion->status), ['pendiente', 'borrador']);
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar una inscripción
     */
    public function delete(User $user, Inscripcion $inscripcion): bool
    {
        return $user->can('inscripciones.eliminar') || $user->can('inscripciones.gestionar');
    }
}

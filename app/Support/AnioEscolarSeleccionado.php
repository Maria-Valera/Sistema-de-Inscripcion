<?php

namespace App\Support;

use App\Models\AnioEscolar;

class AnioEscolarSeleccionado
{
    /**
     * Retorna el ID del año escolar en sesión, o el ID del año escolar activo por defecto.
     */
    public static function id(): ?int
    {
        $sessionId = session('anio_escolar_id');
        if ($sessionId !== null && $sessionId !== '') {
            return (int) $sessionId;
        }

        $activo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();

        return $activo ? (int) $activo->id : null;
    }

    /**
     * Retorna el modelo AnioEscolar usando el ID de id().
     */
    public static function modelo(): ?AnioEscolar
    {
        $id = self::id();

        return $id ? AnioEscolar::find($id) : null;
    }

    /**
     * Retorna true si el ID de la sesión es igual al año activo (o si no hay nada en sesión),
     * y false si es un año anterior.
     */
    public static function esActual(): bool
    {
        $sessionId = session('anio_escolar_id');
        if ($sessionId === null || $sessionId === '') {
            return true;
        }

        $activo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();
        if (!$activo) {
            return false;
        }

        return (int) $sessionId === (int) $activo->id;
    }
}


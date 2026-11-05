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
     * Retorna true si el año seleccionado (o el activo por defecto) está actualmente
     * activo o extendido en el sistema, y false si es un año anterior/inactivo.
     */
    public static function esActual(): bool
    {
        $modelo = self::modelo();
        if (!$modelo) {
            return false;
        }

        return in_array($modelo->status, ['Activo', 'Extendido']);
    }
}


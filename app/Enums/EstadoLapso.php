<?php

namespace App\Enums;

enum EstadoLapso: string
{
    case Planificado = 'planificado';  // Creado, aún no comienza
    case Abierto = 'abierto';          // En curso
    case Cerrado = 'cerrado';          // Cierre oficial: notas congeladas

    public function label(): string
    {
        return match ($this) {
            self::Planificado => 'Planificado',
            self::Abierto => 'Abierto',
            self::Cerrado => 'Cerrado',
        };
    }
}

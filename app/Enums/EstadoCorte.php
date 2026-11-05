<?php

namespace App\Enums;

enum EstadoCorte: string
{
    case Planificado = 'planificado';  // El docente aún no puede cargar notas
    case Abierto = 'abierto';          // Carga de notas habilitada
    case Cerrado = 'cerrado';          // Notas del corte congeladas

    public function label(): string
    {
        return match ($this) {
            self::Planificado => 'Planificado',
            self::Abierto => 'Abierto',
            self::Cerrado => 'Cerrado',
        };
    }
}

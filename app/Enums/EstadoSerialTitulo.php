<?php

namespace App\Enums;

enum EstadoSerialTitulo: string
{
    case Disponible = 'disponible';
    case Asignado = 'asignado';
    case Anulado = 'anulado';    // Formato dañado (Circular 02, punto 7)
    case Devuelto = 'devuelto';  // Liquidado: devuelto a la Zona Educativa (Circular 02, punto 8)

    public function label(): string
    {
        return match ($this) {
            self::Disponible => 'Disponible',
            self::Asignado => 'Asignado',
            self::Anulado => 'Anulado',
            self::Devuelto => 'Devuelto',
        };
    }
}

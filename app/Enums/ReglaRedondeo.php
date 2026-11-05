<?php

namespace App\Enums;

// Cómo se redondea el promedio de los lapsos a la nota definitiva entera.
enum ReglaRedondeo: string
{
    case HalfUp = 'half_up';   // Normal: 9,5 sube a 10
    case Truncar = 'truncar';  // Se descartan los decimales
    case Techo = 'techo';      // Siempre hacia arriba

    public function label(): string
    {
        return match ($this) {
            self::HalfUp => 'Redondeo normal (9,5 sube a 10)',
            self::Truncar => 'Truncar (descartar decimales)',
            self::Techo => 'Siempre hacia arriba',
        };
    }
}

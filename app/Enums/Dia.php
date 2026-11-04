<?php

namespace App\Enums;

enum Dia: int
{
    case Lunes = 1;
    case Martes = 2;
    case Miercoles = 3;
    case Jueves = 4;
    case Viernes = 5;

    /**
     * Retorna el nombre en español con tilde correspondiente al día.
     */
    public function label(): string
    {
        return match ($this) {
            self::Lunes => 'Lunes',
            self::Martes => 'Martes',
            self::Miercoles => 'Miércoles',
            self::Jueves => 'Jueves',
            self::Viernes => 'Viernes',
        };
    }

    /**
     * Obtiene el caso del enum a partir del nombre del día en texto,
     * ignorando mayúsculas, minúsculas y tildes.
     */
    public static function fromNombre(?string $nombre): ?self
    {
        if ($nombre === null || trim($nombre) === '') {
            return null;
        }

        $normalizado = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'u'],
            mb_strtolower(trim($nombre), 'UTF-8')
        );

        return match ($normalizado) {
            'lunes' => self::Lunes,
            'martes' => self::Martes,
            'miercoles' => self::Miercoles,
            'jueves' => self::Jueves,
            'viernes' => self::Viernes,
            default => null,
        };
    }
}

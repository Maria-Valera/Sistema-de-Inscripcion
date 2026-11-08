<?php

namespace App\Enums;

enum CategoriaDia: string
{
    case NoLaborable = 'no_laborable';
    case Laborable = 'laborable';

    public function label(): string
    {
        return match ($this) {
            self::NoLaborable => 'No laborable',
            self::Laborable => 'Laborable',
        };
    }
}


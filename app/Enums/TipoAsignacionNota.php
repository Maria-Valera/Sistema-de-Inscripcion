<?php

namespace App\Enums;

enum TipoAsignacionNota: string
{
    case Area = 'area';
    case GrupoEstable = 'grupo_estable';

    public function label(): string
    {
        return match ($this) {
            self::Area => 'Área de Formación',
            self::GrupoEstable => 'Grupo Estable',
        };
    }
}

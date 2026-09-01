<?php

namespace App\Models;

namespace App\Enums;

use Illuminate\Database\Eloquent\Model;


enum EstadoCalendario : string{
    case PendienteRevision = 'pendiente_revision';
    case Confirmado = 'confirmado';

    public function label(): string
    {
    return match($this){
        self::PendienteRevision => 'Pendiente de Revision',
        self::Confirmado => 'confirmado',
    };

    }

}

// class EstadoCalendario extends Model
// {
//     //
// }

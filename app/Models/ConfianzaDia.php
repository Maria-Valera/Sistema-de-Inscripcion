<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Enums;

enum ConfianzaDia : string{

case Alta = 'alta';
case Dudosa = 'dudosa';
case Manual = 'manual';

public function label(): string{

    return match($this){
        self::Alta => 'Alta (hay una coincidencia automatica)',
        self::Dudosa => 'Dudosa (requiere revision humana)',
        self::Manual => 'Manual (ingresado por un usuario)',
    };
}

}

// class ConfianzaDia extends Model
// {
//     //
// }

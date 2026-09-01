<?php

namespace App\Models;

namespace App\Enums;

use Illuminate\Database\Eloquent\Model;

enum OrigenCalendario : string{

case Pdf = 'pdf';
case Manual = 'manual';

public function label(): string
{
    return match($this){
        self::Pdf => 'Extraido desde el PDF',
        self::Manual => 'Registro Manual',
    };
}

}

// class OrigenCalendario extends Model
// {
//     //

// }

<?php

namespace App\Models;

namespace App\Enums;

use Illuminate\Database\Eloquent\Model;

enum CategoriaDia : string{
    case NoLaborable = 'no_laborable';
    case Dudoso = 'dudoso';

    public function label(): string{
        return match($this){
            self::NoLaborable => 'No Laborable',
            self::Dudoso => 'Dudoso (requiere revision humana)',
        };
    }
}

// class CategoriaDia extends Model
// {
//     //
// }

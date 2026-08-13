<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



enum CategoriaPalabraClave: string
{
    case NoLaborable = 'no_laborable';
    case Efemeride = 'efemeride';
}

// class CategoriaPalabraClave extends Model
// {
//     //
// }

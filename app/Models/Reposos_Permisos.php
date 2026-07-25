<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reposos_Permisos extends Model
{
    use HasFactory;
    protected $table = 'reposos_permisos';
    protected $fillable = [
        'nombre_reposo',
        'tipo',
        'dias_reposo',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\Auditable;

class Reposos_Permisos extends Model
{
    use HasFactory, Auditable;
    protected $table = 'reposos_permisos';
    protected $fillable = [
        'nombre_reposo',
        'tipo',
        'dias_reposo',
    ];
}

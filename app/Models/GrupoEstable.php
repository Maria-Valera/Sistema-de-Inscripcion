<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoEstable extends Model
{
    use HasFactory;

    protected $table = 'grupo_estables';

    protected $fillable = [
        'codigo',
        'nombre_grupo_estable',
        'status',
    ];

    public function docenteAsignacion()
    {
        return $this->hasOne(DocenteAreaGrado::class, 'grupo_estable_id');
    }
}

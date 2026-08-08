<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaFormacion extends Model
{
    use HasFactory;
    protected $table = 'area_formacions';

    protected $fillable = [
        'nombre_area_formacion',
        'codigo_area',
        'siglas',
        'horas_semanales',
        'aula_id',
        'status',
    ];

    public function grados()
    {
        return $this->belongsToMany(Grado::class, 'grado_area_formacions')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id', 'id_aula');
    }


}

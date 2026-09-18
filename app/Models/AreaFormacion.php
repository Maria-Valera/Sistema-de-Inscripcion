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
        'bloques_maximos_por_dia',
        'dias_max_semana',
        'aula_id',
        'status',
    ];

    protected $appends = ['es_especializada'];

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

    public function getEsEspecializadaAttribute(): bool
    {
        if (!$this->aula_id) {
            return false;
        }
        $tipoAula = $this->relationLoaded('aula')
            ? $this->aula?->tipo_aula
            : $this->aula()->value('tipo_aula');
        return $tipoAula !== 'Aula Regular';
    }


}

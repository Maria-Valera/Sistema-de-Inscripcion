<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    // Valor real guardado en aulas.tipo_aula para aulas regulares (ver AulaSeeder y formularios de aulas)
    public const TIPO_REGULAR = 'Aula Regular';

    protected $table = 'aulas';
    protected $primaryKey = 'id_aula';

    // Compara sin distinguir mayúsculas/espacios para tolerar datos cargados a mano
    public static function esTipoRegular(?string $tipo): bool
    {
        return mb_strtolower(trim((string) $tipo)) === mb_strtolower(self::TIPO_REGULAR);
    }

    protected $fillable = [
        'nombre_aula',
        'tipo_aula',
        'status',
        'capacidad_maxima',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Scope para traer solo activas
    public function scopeActivas($query)
    {
        return $query->where('status', true);
    }

     public function aula(){
        return $this->hasMany(seccion_aula::class, "id_aula", "id_aula");
    }
}

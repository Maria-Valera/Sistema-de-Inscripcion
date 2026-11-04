<?php

namespace App\Models;

use App\Enums\Dia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueReservado extends Model
{
    use HasFactory;

    protected $table = 'bloque_reservados';

    protected $fillable = [
        'anio_escolar_id',
        'dia',
        'bloque',
    ];

    protected $casts = [
        'dia' => Dia::class,
        'bloque' => 'integer',
    ];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    /**
     * Retorna un mapa asociativo de los bloques reservados para un año escolar
     * en formato: [ "dia_bloque" => true ] (ej. ["1_2" => true]).
     *
     * @param int $anioEscolarId
     * @return array<string, bool>
     */
    public static function mapaPorAnio(int $anioEscolarId): array
    {
        $reservados = static::where('anio_escolar_id', $anioEscolarId)->get();

        $mapa = [];
        foreach ($reservados as $r) {
            $diaVal = $r->dia instanceof Dia ? $r->dia->value : (int) $r->dia;
            $mapa["{$diaVal}_{$r->bloque}"] = true;
        }

        return $mapa;
    }
}

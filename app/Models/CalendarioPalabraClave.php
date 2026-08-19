<?php

namespace App\Models;

namespace App\Enums;

use App\Enums\CategoriaPalabraClave;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarioPalabraClave extends Model
{
    //
    protected $fillable = [
        'palabra',
        'categoria',
        'activa',
        'agregada_por',
    ];

    protected $cast = [
        'categoria' => CategoriaPalabraClave::class,
        'activa' => 'boolean',
    ];

    /*
    quien agrego o modifico esta palabra clave. Nullable por que el diccionario de palabras clave base puede sembrarse sin una persona asociada
    */

    public function agregadaPor(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'agregada_por');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function scopeNoLaborables(Builder $query): Builder
    {
        return $query->where('categoria', CategoriaPalabraClave::NoLaborable);
    }

    public function scopeEfemerides(Builder $query): Builder
    {
        return $query->where('categoria', CategoriaPalabraClave::Efemeride);
    }

    /*
    Este es el metodo trae solo las palabras activas de una categoria , como un array simple de strings, listo
    para recorrer y comparar contra una linea de texto.
    */

    public static function palabrasActivasPorCategoria(CategoriaPalabraClave $categoria): array
    {
        return static::query()
            ->activas()
            ->where('categoria', $categoria)
            ->pluck('palabra')
            ->toArray();
    }

}

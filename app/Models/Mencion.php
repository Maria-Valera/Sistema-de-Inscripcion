<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mencion extends Model
{
    use HasFactory;

    protected $table = 'menciones';

    protected $fillable = [
        'codigo',
        'nombre',
        'ciclo',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Secciones que tienen asignada esta mención.
     */
    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'mencion_id');
    }

    /**
     * Scope para menciones activas.
     */
    public function scopeActivas($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope de búsqueda por código o nombre.
     */
    public function scopeBuscar($query, ?string $termino)
    {
        if (empty($termino)) {
            return $query;
        }

        return $query->where(function ($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%")
              ->orWhere('ciclo', 'like', "%{$termino}%");
        });
    }
}


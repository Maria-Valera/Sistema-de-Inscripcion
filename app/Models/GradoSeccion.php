<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradoSeccion extends Model
{
    protected $table = 'grado_seccion';

    protected $fillable = [
        'grado_id',
        'seccion_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }

    public function cargasAcademicas(): HasMany
    {
        return $this->hasMany(CargaAcademica::class, 'grado_seccion_id');
    }
}

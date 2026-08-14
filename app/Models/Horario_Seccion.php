<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario_Seccion extends Model
{
    protected $table = 'horario__seccion';
    
    protected $fillable = [
        'horario_id',
        'seccion_id',
        'status',
    ];
    
    protected $casts = [
        'status' => 'boolean',
    ];
    
    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
    
    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }

    public function seccionConAulaFija()
    {
        return $this->belongsTo(Seccion::class)->with('aulaFija');
    }
}

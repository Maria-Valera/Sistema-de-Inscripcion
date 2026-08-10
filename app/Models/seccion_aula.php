<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class seccion_aula extends Model
{
    protected $table= 'seccion_aula';

    protected $fillable = [
        'id_seccion',
        'id_aula',
    ];

    public function seccion(){
        return $this->belongsTo(Seccion::class, "id_seccion", "id");
    }

    public function aula(){
        return $this->belongsTo(Aula::class, "id_aula", "id_aula");
    }
}

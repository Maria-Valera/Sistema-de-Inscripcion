<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inasistencia extends Model
{
    protected $guarded = [];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }
}

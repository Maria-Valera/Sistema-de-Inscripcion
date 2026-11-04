<?php

namespace App\Http\Controllers;
use App\Enums\Dia;
use Illuminate\Http\Request;

class Dias_semanaController extends Controller
{
    public function apiIndex()
    {
        $registros = collect(Dia::cases())->map(fn(Dia $d) => [
            'id' => $d->value,
            'dia' => $d->value,
            'nombre_dia' => $d->label(),
            'nombre' => $d->label(),
        ])->values();

        return response()->json($registros);
    }
}

<?php

namespace App\Http\Controllers;
use App\Models\Dias_semana;
use Illuminate\Http\Request;

class Dias_semanaController extends Controller
{
    public function apiIndex(){
        $registros=Dias_semana::where('status', true)->get();

        return response()->json($registros);
    }
}

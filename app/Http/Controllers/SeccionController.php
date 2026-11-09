<?php

namespace App\Http\Controllers;
use App\Models\Seccion;
use Illuminate\Http\Request;

class SeccionController extends Controller
{
    public function apiIndex(){
        $registros=Seccion::where('status', true)->get();

        return response()->json($registros);
    }
}

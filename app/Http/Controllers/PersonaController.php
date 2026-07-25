<?php

namespace App\Http\Controllers;
use App\Models\Persona;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    public function apiIndex(){
        $registro=Persona::where('status', true)->get();

        return response()->json($registro);
    }
}

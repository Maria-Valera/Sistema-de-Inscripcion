<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grado;
use App\Models\AreaFormacion;
use App\Models\Seccion;

class HorarioController extends Controller
{
    public function index()
    {
        return view('admin.horario.index');
    }

    public function create()
    {
        $grados = Grado::where('status', true)
            ->orderBy('numero_grado', 'asc')
            ->get();

        $areasFormacion = AreaFormacion::where('status', true)
            ->orderBy('nombre_area_formacion', 'asc')
            ->get();

        $secciones = Seccion::where('status', true)
            ->orderBy('nombre', 'asc')
            ->get()
            ->unique('nombre');

        return view('admin.horario.create', compact('grados', 'areasFormacion', 'secciones'));
    }
}

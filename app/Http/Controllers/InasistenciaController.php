<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InasistenciaController extends Controller
{
    public function index()
    {
        return view('admin.inasistencia.index');
    }

    public function inasistencia_justificacion()
    {
        return view('admin.inasistencia.inasistencia_justificacion');
    }
}

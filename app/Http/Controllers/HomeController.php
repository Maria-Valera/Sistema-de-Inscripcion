<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Grado;
use App\Models\Docente;
use App\Models\AnioEscolar;
use App\Models\Inscripcion;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Redirigir si es representante
        if (auth()->check() && auth()->user()->hasRole('Representante')) {
            return redirect()->route('portal-representante.index');
        }

        $anioEscolar = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();

        $totalGrados = Grado::where('status', true)->count();

        $totalUsuarios = User::count();

        $totalDocentes = Docente::whereHas('persona', function ($query) {
            $query->where('status', true);
        })
            ->where('status', true)
            ->count();

        $totalNuevoIngreso = Inscripcion::whereIn('status', ['Activo', 'Pendiente'])
            ->where('anio_escolar_id', $anioEscolar?->id)
            ->whereHas('nuevoIngreso')
            ->count();

        $totalProsecucion = Inscripcion::whereIn('status', ['Activo', 'Pendiente'])
            ->where('anio_escolar_id', $anioEscolar?->id)
            ->whereHas('prosecucion')
            ->count();


        $anioEscolarActivo = $anioEscolar
            ? $anioEscolar->inicio_anio_escolar->format('Y') . '-' . $anioEscolar->cierre_anio_escolar->format('Y')
            : 'No definido';



        return view('home', compact('totalUsuarios', 'totalGrados', 'totalDocentes', 'totalNuevoIngreso', 'totalProsecucion', 'anioEscolarActivo'));
    }
}

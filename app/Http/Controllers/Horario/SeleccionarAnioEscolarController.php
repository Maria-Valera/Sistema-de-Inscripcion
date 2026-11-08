<?php

namespace App\Http\Controllers\Horario;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Http\Request;

class SeleccionarAnioEscolarController extends Controller
{
    /**
     * Muestra la vista con la lista de años escolares.
     * Si solo hay un año (el activo), redirige directamente al dashboard.
     */
    public function index()
    {
        $anios = AnioEscolar::orderBy('inicio_anio_escolar', 'desc')->get();
        $anioActivo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();

        // Si solo hay un año registrado y además está activo, se asigna en sesión y se redirige directamente al dashboard
        if ($anios->count() === 1 && $anioActivo && $anios->first()->id === $anioActivo->id) {
            session(['anio_escolar_id' => (int) $anioActivo->id]);

            return redirect()->to(auth()->user()->rutaDashboard());
        }

        $anioSeleccionadoId = AnioEscolarSeleccionado::id();

        return view('admin.maestros.stephania.seleccionar-anio', compact('anios', 'anioSeleccionadoId', 'anioActivo'));
    }

    /**
     * Valida el ID, guarda el año escolar en sesión y redirige al dashboard.
     */
    public function guardar(Request $request)
    {
        $validated = $request->validate([
            'anio_escolar_id' => ['required', 'integer', 'exists:anio_escolars,id'],
        ]);

        session(['anio_escolar_id' => (int) $validated['anio_escolar_id']]);

        return redirect()->to(auth()->user()->rutaDashboard());
    }
}


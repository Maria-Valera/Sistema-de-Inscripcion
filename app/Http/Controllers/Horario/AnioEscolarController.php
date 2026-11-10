<?php

namespace App\Http\Controllers\Horario;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnioEscolarRequest;
use App\Models\AnioEscolar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnioEscolarController extends Controller
{
    /**
     * Muestra el listado de años escolares ordenados descendentemente por fecha de inicio.
     */
    public function index(): View
    {
        $anios = AnioEscolar::orderBy('inicio_anio_escolar', 'desc')->paginate(10);

        return view('admin.maestros.stephania.anio_escolar.index', compact('anios'));
    }

    /**
     * Muestra la vista de formulario con asistente (wizard) para crear un nuevo año escolar (Paso 1).
     */
    public function create(): View
    {
        return view('admin.maestros.stephania.anio_escolar.create');
    }

    /**
     * Almacena un nuevo año escolar y avanza al paso 2 del asistente (Lapsos).
     * Regla de negocio: Si se crea con status = 'Activo', desactiva todos los demás años escolares.
     */
    public function store(AnioEscolarRequest $request): RedirectResponse
    {
        $nuevoAnio = null;

        DB::transaction(function () use ($request, &$nuevoAnio) {
            if ($request->status === 'Activo') {
                AnioEscolar::query()->update(['status' => 'Inactivo']);
            }

            $nuevoAnio = AnioEscolar::create($request->validated());
        });

        if ($nuevoAnio) {
            session(['anio_escolar_id' => (int) $nuevoAnio->id]);
            \App\Support\AsistenteAnioEscolar::iniciar($nuevoAnio);

            // Redirige directamente al asistente de configuración de lapsos (Paso 2)
            if (auth()->user()->can('lapsos.gestionar')) {
                return redirect()->route('admin.asistente.lapsos')
                    ->with('success', 'Año escolar creado exitosamente. Ahora configura sus lapsos.');
            }

            return redirect()->to(auth()->user()->rutaDashboard())
                ->with('success', 'Año escolar creado exitosamente.');
        }

        return redirect()->route('admin.anio-escolar.index')
            ->with('success', 'Año escolar registrado exitosamente.');
    }

    /**
     * Actualiza el año escolar seleccionado.
     * Regla de negocio: Si se actualiza a status = 'Activo', desactiva los demás años escolares.
     */
    public function update(AnioEscolarRequest $request, AnioEscolar $anioEscolar): RedirectResponse
    {
        DB::transaction(function () use ($request, $anioEscolar) {
            if ($request->status === 'Activo') {
                AnioEscolar::where('id', '!=', $anioEscolar->id)->update(['status' => 'Inactivo']);
            }

            $anioEscolar->update($request->validated());
        });

        return redirect()->route('admin.anio-escolar.index')
            ->with('success', 'Año escolar actualizado exitosamente.');
    }

    /**
     * Elimina el año escolar seleccionado.
     */
    public function destroy(AnioEscolar $anioEscolar): RedirectResponse
    {
        $anioEscolar->delete();

        return redirect()->route('admin.anio-escolar.index')
            ->with('success', 'Año escolar eliminado correctamente.');
    }
}


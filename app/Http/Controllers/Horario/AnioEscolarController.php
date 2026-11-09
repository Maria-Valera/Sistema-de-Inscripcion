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
     * Almacena un nuevo año escolar.
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

        if ($request->status === 'Activo' && $nuevoAnio) {
            session(['anio_escolar_id' => (int) $nuevoAnio->id]);
        }

        if ($request->header('referer') && str_contains($request->header('referer'), 'seleccionar-anio')) {
            // Asistente de año nuevo (María): si el año quedó activo, siguen sus lapsos y su calendario.
            if ($request->status === 'Activo' && $nuevoAnio && auth()->user()->can('lapsos.gestionar')) {
                \App\Support\AsistenteAnioEscolar::iniciar($nuevoAnio);

                return redirect()->route('admin.asistente.lapsos')
                    ->with('success', 'Año escolar creado y activado. Ahora carga sus lapsos.');
            }

            return redirect()->to(auth()->user()->rutaDashboard())
                ->with('success', 'Año escolar creado y activado exitosamente.');
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


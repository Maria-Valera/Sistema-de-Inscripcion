<?php

namespace App\Http\Controllers;

use App\Http\Requests\PensumRequest;
use App\Models\Pensum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PensumController extends Controller
{
    /**
     * Muestra el listado de planes de estudio (pensums).
     */
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->input('buscar'));
        $estado = $request->input('estado');

        $query = Pensum::withCount(['mallas', 'secciones']);

        if (!empty($busqueda)) {
            $query->buscar($busqueda);
        }

        if ($estado === 'vigentes') {
            $query->vigentes();
        } elseif ($estado === 'inactivos') {
            $query->where('status', false);
        } elseif ($estado === 'caducados') {
            $query->whereNotNull('anio_fin')->where('status', true);
        }

        $pensums = $query->orderBy('codigo', 'asc')->paginate(10)->withQueryString();

        return view('admin.maestros.jose.pensums.index', compact('pensums', 'busqueda', 'estado'));
    }

    /**
     * Almacena un nuevo plan de estudio.
     */
    public function store(PensumRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['status'] = $request->boolean('status', true);

        Pensum::create($datos);

        return redirect()->route('admin.pensums.index')
            ->with('success', 'Plan de estudio registrado exitosamente.');
    }

    /**
     * Actualiza el plan de estudio existente.
     */
    public function update(PensumRequest $request, Pensum $pensum): RedirectResponse
    {
        $datos = $request->validated();
        $datos['status'] = $request->boolean('status', true);

        $pensum->update($datos);

        return redirect()->route('admin.pensums.index')
            ->with('success', 'Plan de estudio actualizado exitosamente.');
    }

    /**
     * Elimina o desactiva el plan de estudio según si tiene registros asociados.
     * Regla de negocio: No borrar si otra tabla lo usa (desactivar).
     */
    public function destroy(Pensum $pensum): RedirectResponse
    {
        $tieneMallas = $pensum->mallas()->exists();
        $tieneSecciones = $pensum->secciones()->exists();
        $tieneConversiones = $pensum->conversionesOrigen()->exists() || $pensum->conversionesDestino()->exists();

        if ($tieneMallas || $tieneSecciones || $tieneConversiones) {
            $pensum->update(['status' => false]);

            return redirect()->route('admin.pensums.index')
                ->with('warning', 'El plan de estudio tiene dependencias asociadas (malla, secciones o conversiones). Ha sido desactivado para proteger la integridad de los datos.');
        }

        $pensum->delete();

        return redirect()->route('admin.pensums.index')
            ->with('success', 'Plan de estudio eliminado exitosamente.');
    }
}


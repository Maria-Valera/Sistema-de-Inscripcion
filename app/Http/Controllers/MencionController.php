<?php

namespace App\Http\Controllers;

use App\Http\Requests\MencionRequest;
use App\Models\Mencion;
use App\Models\Pensum;
use App\Models\Seccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MencionController extends Controller
{
    /**
     * Muestra el listado de menciones oficiales.
     */
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->input('buscar'));
        $query = Mencion::withCount('secciones');

        if (!empty($busqueda)) {
            $query->buscar($busqueda);
        }

        $menciones = $query->orderBy('codigo', 'asc')->paginate(10)->withQueryString();
        $pensums = Pensum::where('status', true)->orderBy('codigo', 'asc')->get();

        return view('admin.maestros.jose.menciones.index', compact('menciones', 'pensums', 'busqueda'));
    }

    /**
     * Almacena una nueva mención.
     */
    public function store(MencionRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['status'] = $request->boolean('status', true);

        Mencion::create($datos);

        return redirect()->route('admin.menciones.index')
            ->with('success', 'Mención curricular registrada exitosamente.');
    }

    /**
     * Actualiza la mención existente.
     */
    public function update(MencionRequest $request, Mencion $mencione): RedirectResponse
    {
        $datos = $request->validated();
        $datos['status'] = $request->boolean('status', true);

        $mencione->update($datos);

        return redirect()->route('admin.menciones.index')
            ->with('success', 'Mención curricular actualizada exitosamente.');
    }

    /**
     * Elimina o desactiva la mención si tiene secciones asociadas.
     */
    public function destroy(Mencion $mencione): RedirectResponse
    {
        if ($mencione->secciones()->exists()) {
            $mencione->update(['status' => false]);

            return redirect()->route('admin.menciones.index')
                ->with('warning', 'La mención está asignada a secciones activas. Ha sido desactivada para preservar los registros de los boletines.');
        }

        $mencione->delete();

        return redirect()->route('admin.menciones.index')
            ->with('success', 'Mención eliminada correctamente.');
    }

    /**
     * Asigna o actualiza el pensum y la mención de una sección.
     */
    public function asignarSeccion(Request $request, Seccion $seccion): RedirectResponse
    {
        $request->validate([
            'pensum_id'  => 'required|exists:pensums,id',
            'mencion_id' => 'nullable|exists:menciones,id',
        ]);

        $seccion->update([
            'pensum_id'  => $request->input('pensum_id'),
            'mencion_id' => $request->input('mencion_id') ?: null,
        ]);

        return back()->with('success', "Configuración de plan y mención para la sección {$seccion->nombre} actualizada correctamente.");
    }
}


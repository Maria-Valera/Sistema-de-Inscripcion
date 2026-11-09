<?php

namespace App\Http\Controllers;

use App\Http\Requests\AutoridadFirmanteRequest;
use App\Models\AutoridadFirmante;
use App\Models\Persona;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AutoridadFirmanteController extends Controller
{
    /**
     * Muestra el listado de autoridades firmantes para el año escolar seleccionado.
     */
    public function index(): View
    {
        $anioId = AnioEscolarSeleccionado::id();
        $anioModelo = AnioEscolarSeleccionado::modelo();

        $autoridades = AutoridadFirmante::with(['persona.tipoDocumento', 'anioEscolar'])
            ->where('anio_escolar_id', $anioId)
            ->orderBy('cargo')
            ->orderBy('desde', 'desc')
            ->paginate(15);

        $personas = Persona::with('tipoDocumento')
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();

        $cargos = AutoridadFirmante::CARGOS;

        return view('admin.maestros.stephania.autoridad_firmante.index', compact(
            'autoridades',
            'anioModelo',
            'personas',
            'cargos'
        ));
    }

    /**
     * Almacena una nueva autoridad firmante en la base de datos.
     */
    public function store(AutoridadFirmanteRequest $request): RedirectResponse
    {
        $anioId = AnioEscolarSeleccionado::id();

        if (!$anioId) {
            return redirect()->route('admin.autoridad-firmante.index')
                ->with('error', 'No se ha seleccionado un año escolar válido.');
        }

        $datos = $request->validated();
        $datos['anio_escolar_id'] = $anioId;

        AutoridadFirmante::create($datos);

        return redirect()->route('admin.autoridad-firmante.index')
            ->with('success', 'Autoridad firmante registrada exitosamente.');
    }

    /**
     * Actualiza la autoridad firmante seleccionada.
     */
    public function update(AutoridadFirmanteRequest $request, AutoridadFirmante $autoridadFirmante): RedirectResponse
    {
        $datos = $request->validated();

        $autoridadFirmante->update($datos);

        return redirect()->route('admin.autoridad-firmante.index')
            ->with('success', 'Autoridad firmante actualizada exitosamente.');
    }

    /**
     * Elimina la autoridad firmante seleccionada.
     */
    public function destroy(AutoridadFirmante $autoridadFirmante): RedirectResponse
    {
        $autoridadFirmante->delete();

        return redirect()->route('admin.autoridad-firmante.index')
            ->with('success', 'Autoridad firmante eliminada correctamente.');
    }
}


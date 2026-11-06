<?php

namespace App\Http\Controllers;

use App\Http\Requests\EscalaLiteralRequest;
use App\Models\EscalaLiteral;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EscalaLiteralController extends Controller
{
    /**
     * Muestra el listado de escalas literales para el año escolar seleccionado.
     */
    public function index(): View
    {
        $anioId = AnioEscolarSeleccionado::id();
        $anioModelo = AnioEscolarSeleccionado::modelo();

        $escalas = EscalaLiteral::where('anio_escolar_id', $anioId)
            ->orderBy('nota_desde', 'desc')
            ->paginate(15);

        return view('admin.maestros.stephania.escala_literal.index', compact('escalas', 'anioModelo'));
    }

    /**
     * Almacena una nueva escala literal en la base de datos.
     */
    public function store(EscalaLiteralRequest $request): RedirectResponse
    {
        $anioId = AnioEscolarSeleccionado::id();

        if (!$anioId) {
            return redirect()->route('admin.escala-literal.index')
                ->with('error', 'No se ha seleccionado un año escolar válido.');
        }

        $datos = $request->validated();
        $datos['anio_escolar_id'] = $anioId;

        EscalaLiteral::create($datos);

        return redirect()->route('admin.escala-literal.index')
            ->with('success', 'Escala literal registrada exitosamente.');
    }

    /**
     * Actualiza la escala literal existente.
     */
    public function update(EscalaLiteralRequest $request, EscalaLiteral $escalaLiteral): RedirectResponse
    {
        $datos = $request->validated();

        $escalaLiteral->update($datos);

        return redirect()->route('admin.escala-literal.index')
            ->with('success', 'Escala literal actualizada exitosamente.');
    }

    /**
     * Elimina la escala literal seleccionada.
     */
    public function destroy(EscalaLiteral $escalaLiteral): RedirectResponse
    {
        $escalaLiteral->delete();

        return redirect()->route('admin.escala-literal.index')
            ->with('success', 'Escala literal eliminada correctamente.');
    }
}


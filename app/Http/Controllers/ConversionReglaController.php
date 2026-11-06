<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConversionReglaRequest;
use App\Models\AreaFormacion;
use App\Models\ConversionRegla;
use App\Models\ConversionReglaOrigen;
use App\Models\Grado;
use App\Models\Pensum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConversionReglaController extends Controller
{
    /**
     * Muestra el listado de reglas de conversión entre pensums.
     */
    public function index(Request $request): View
    {
        $pensums = Pensum::orderBy('codigo', 'asc')->get();
        $grados = Grado::orderBy('numero_grado', 'asc')->get();
        $areas = AreaFormacion::where('status', true)->orderBy('nombre_area_formacion', 'asc')->get();

        $origenId = $request->input('pensum_origen_id', $pensums->firstWhere('codigo', '31059')?->id ?? $pensums->first()?->id);
        $destinoId = $request->input('pensum_destino_id', $pensums->firstWhere('codigo', '31060')?->id ?? $pensums->last()?->id);
        $gradoId = $request->input('grado_id');
        $tipo = $request->input('tipo_regla');

        $query = ConversionRegla::with([
            'pensumOrigen',
            'pensumDestino',
            'gradoDestino',
            'areaDestino',
            'origenes.areaOrigen',
            'origenes.gradoOrigen',
        ]);

        if (!empty($origenId)) {
            $query->where('pensum_origen_id', $origenId);
        }

        if (!empty($destinoId)) {
            $query->where('pensum_destino_id', $destinoId);
        }

        if (!empty($gradoId)) {
            $query->where('grado_destino_id', $gradoId);
        }

        if (!empty($tipo)) {
            $query->where('tipo_regla', $tipo);
        }

        $reglas = $query->orderBy('grado_destino_id', 'asc')
                        ->orderBy('area_destino_id', 'asc')
                        ->paginate(15)
                        ->withQueryString();

        $pensumOrigen = $pensums->firstWhere('id', $origenId);
        $pensumDestino = $pensums->firstWhere('id', $destinoId);

        return view('admin.maestros.jose.conversion_reglas.index', compact(
            'reglas',
            'pensums',
            'grados',
            'areas',
            'origenId',
            'destinoId',
            'gradoId',
            'tipo',
            'pensumOrigen',
            'pensumDestino'
        ));
    }

    /**
     * Almacena una nueva regla de conversión con sus materias de origen.
     */
    public function store(ConversionReglaRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['status'] = $request->boolean('status', true);

        DB::transaction(function () use ($datos, $request) {
            $regla = ConversionRegla::create([
                'pensum_origen_id'  => $datos['pensum_origen_id'],
                'pensum_destino_id' => $datos['pensum_destino_id'],
                'grado_destino_id'  => $datos['grado_destino_id'],
                'area_destino_id'   => $datos['area_destino_id'],
                'tipo_regla'        => $datos['tipo_regla'],
                'observacion'       => $datos['observacion'] ?? null,
                'status'            => $datos['status'],
            ]);

            // Guardar orígenes si la regla lo requiere (directa o promedio)
            if (in_array($datos['tipo_regla'], ['directa', 'promedio']) && !empty($datos['origenes'])) {
                foreach ($datos['origenes'] as $orig) {
                    if (!empty($orig['area_origen_id']) && !empty($orig['grado_origen_id'])) {
                        ConversionReglaOrigen::create([
                            'conversion_regla_id' => $regla->id,
                            'grado_origen_id'     => $orig['grado_origen_id'],
                            'area_origen_id'      => $orig['area_origen_id'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.conversion-reglas.index', [
            'pensum_origen_id'  => $datos['pensum_origen_id'],
            'pensum_destino_id' => $datos['pensum_destino_id'],
        ])->with('success', 'Regla de conversión registrada exitosamente.');
    }

    /**
     * Actualiza la regla de conversión y sincroniza sus orígenes.
     */
    public function update(ConversionReglaRequest $request, ConversionRegla $conversionRegla): RedirectResponse
    {
        $datos = $request->validated();
        $datos['status'] = $request->boolean('status', true);

        DB::transaction(function () use ($conversionRegla, $datos) {
            $conversionRegla->update([
                'pensum_origen_id'  => $datos['pensum_origen_id'],
                'pensum_destino_id' => $datos['pensum_destino_id'],
                'grado_destino_id'  => $datos['grado_destino_id'],
                'area_destino_id'   => $datos['area_destino_id'],
                'tipo_regla'        => $datos['tipo_regla'],
                'observacion'       => $datos['observacion'] ?? null,
                'status'            => $datos['status'],
            ]);

            // Reemplazar orígenes
            $conversionRegla->origenes()->delete();

            if (in_array($datos['tipo_regla'], ['directa', 'promedio']) && !empty($datos['origenes'])) {
                foreach ($datos['origenes'] as $orig) {
                    if (!empty($orig['area_origen_id']) && !empty($orig['grado_origen_id'])) {
                        ConversionReglaOrigen::create([
                            'conversion_regla_id' => $conversionRegla->id,
                            'grado_origen_id'     => $orig['grado_origen_id'],
                            'area_origen_id'      => $orig['area_origen_id'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.conversion-reglas.index', [
            'pensum_origen_id'  => $datos['pensum_origen_id'],
            'pensum_destino_id' => $datos['pensum_destino_id'],
        ])->with('success', 'Regla de conversión actualizada exitosamente.');
    }

    /**
     * Elimina la regla de conversión.
     */
    public function destroy(ConversionRegla $conversionRegla): RedirectResponse
    {
        $origenId = $conversionRegla->pensum_origen_id;
        $destinoId = $conversionRegla->pensum_destino_id;

        $conversionRegla->delete();

        return redirect()->route('admin.conversion-reglas.index', [
            'pensum_origen_id'  => $origenId,
            'pensum_destino_id' => $destinoId,
        ])->with('success', 'Regla de conversión eliminada correctamente.');
    }
}


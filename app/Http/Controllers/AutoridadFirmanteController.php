<?php

namespace App\Http\Controllers;

use App\Http\Requests\AutoridadFirmanteRequest;
use App\Models\AutoridadFirmante;
use App\Models\Persona;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $personas = Persona::with(['tipoDocumento', 'user.roles', 'docente'])
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();

        $cargos = AutoridadFirmante::CARGOS;

        // Estructura ligera para filtrado dinámico en frontend
        $personasCatalogo = $personas->map(function ($p) {
            $doc = $p->tipoDocumento?->abreviatura ?? 'CI';
            $esDocente = $p->docente !== null || ($p->user && $p->user->hasRole('Docente'));
            $esAdministrativo = $p->user && $p->user->hasAnyRole([
                'Admin', 'Administrador', 'Superusuario', 'Subdirectora', 'Editor'
            ]);

            return [
                'id' => $p->id,
                'nombre_completo' => $p->nombre_completo,
                'numero_documento' => $p->numero_documento,
                'tipo_documento' => $doc,
                'texto' => "{$p->nombre_completo} ({$doc}: {$p->numero_documento})",
                'es_docente' => $esDocente,
                'es_administrativo' => $esAdministrativo,
            ];
        });

        return view('admin.maestros.stephania.autoridad_firmante.index', compact(
            'autoridades',
            'anioModelo',
            'personas',
            'personasCatalogo',
            'cargos'
        ));
    }

    /**
     * Endpoint AJAX para obtener personas filtradas por cargo.
     */
    public function personasPorCargo(Request $request): JsonResponse
    {
        $cargo = $request->query('cargo');
        $personaActualId = $request->query('persona_id');

        $personas = Persona::with(['tipoDocumento', 'user.roles', 'docente'])
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();

        if ($cargo) {
            $personas = $personas->filter(function ($persona) use ($cargo, $personaActualId) {
                if ($personaActualId && (int) $persona->id === (int) $personaActualId) {
                    return true;
                }

                $esDocente = $persona->docente !== null || ($persona->user && $persona->user->hasRole('Docente'));

                if ($cargo === 'docente_firmante_mppe') {
                    return $esDocente;
                }

                if (in_array($cargo, ['director', 'subdirector_academico', 'coordinador_control_estudios', 'coordinador_evaluacion'])) {
                    $esAdministrativo = $persona->user && $persona->user->hasAnyRole([
                        'Admin', 'Administrador', 'Superusuario', 'Subdirectora', 'Editor'
                    ]);
                    return $esDocente || $esAdministrativo;
                }

                return true;
            })->values();
        }

        $resultado = $personas->map(function ($p) {
            $doc = $p->tipoDocumento?->abreviatura ?? 'CI';
            return [
                'id' => $p->id,
                'nombre_completo' => $p->nombre_completo,
                'numero_documento' => $p->numero_documento,
                'tipo_documento' => $doc,
                'texto' => "{$p->nombre_completo} ({$doc}: {$p->numero_documento})",
            ];
        });

        return response()->json($resultado);
    }

    /**
     * Almacena una nueva autoridad firmante en la base de datos.
     */
    public function store(AutoridadFirmanteRequest $request): JsonResponse|RedirectResponse
    {
        $anioId = AnioEscolarSeleccionado::id();

        if (!$anioId) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'No se ha seleccionado un año escolar válido.'], 422);
            }
            return redirect()->route('admin.autoridad-firmante.index')
                ->with('error', 'No se ha seleccionado un año escolar válido.');
        }

        $datos = $request->validated();
        $datos['anio_escolar_id'] = $anioId;

        $autoridad = AutoridadFirmante::create($datos);

        session()->flash('success', 'Autoridad firmante registrada exitosamente.');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Autoridad firmante registrada exitosamente.',
                'data' => $autoridad->load('persona.tipoDocumento'),
            ], 201);
        }

        return redirect()->route('admin.autoridad-firmante.index')
            ->with('success', 'Autoridad firmante registrada exitosamente.');
    }

    /**
     * Actualiza la autoridad firmante seleccionada.
     */
    public function update(AutoridadFirmanteRequest $request, AutoridadFirmante $autoridadFirmante): JsonResponse|RedirectResponse
    {
        $datos = $request->validated();

        $autoridadFirmante->update($datos);

        session()->flash('success', 'Autoridad firmante actualizada exitosamente.');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Autoridad firmante actualizada exitosamente.',
                'data' => $autoridadFirmante->load('persona.tipoDocumento'),
            ]);
        }

        return redirect()->route('admin.autoridad-firmante.index')
            ->with('success', 'Autoridad firmante actualizada exitosamente.');
    }

    /**
     * Elimina la autoridad firmante seleccionada.
     */
    public function destroy(Request $request, AutoridadFirmante $autoridadFirmante): JsonResponse|RedirectResponse
    {
        $autoridadFirmante->delete();

        session()->flash('success', 'Autoridad firmante eliminada correctamente.');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Autoridad firmante eliminada correctamente.',
            ]);
        }

        return redirect()->route('admin.autoridad-firmante.index')
            ->with('success', 'Autoridad firmante eliminada correctamente.');
    }
}


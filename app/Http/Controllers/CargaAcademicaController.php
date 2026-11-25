<?php

namespace App\Http\Controllers;

use App\Models\CalendarioAcademico;
use App\Models\CargaAcademica;
use App\Models\Docente;
use App\Services\Cargas\GenerarCargasAcademicasService;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CargaAcademicaController extends Controller
{
    public function __construct(
        protected GenerarCargasAcademicasService $generadorService
    ) {}

    /**
     * Listado de cargas académicas del año seleccionado con filtros y búsqueda en vivo.
     */
    public function index(Request $request)
    {
        $anioId = AnioEscolarSeleccionado::id();
        $anioModelo = AnioEscolarSeleccionado::modelo();
        $esActual = AnioEscolarSeleccionado::esActual();

        $calendario = $anioId
            ? CalendarioAcademico::where('anio_escolar_id', $anioId)->first()
            : null;

        $user = Auth::user();
        $esDocente = $user && $user->hasRole('Docente') && !$user->hasAnyRole(['Admin', 'Subdirectora']);
        $docenteAsociado = null;

        if ($esDocente) {
            $docenteAsociado = Docente::where('persona_id', $user->persona_id)->first();
        }

        $query = CargaAcademica::with([
            'docente.persona',
            'areaFormacion',
            'gradoSeccion.grado',
            'gradoSeccion.seccion',
            'grupoEstable',
        ]);

        if ($calendario) {
            $query->where('calendario_academico_id', $calendario->id);
        } else {
            $query->whereRaw('1 = 0'); // Sin año/calendario no devuelve filas
        }

        // Restricción por rol: Docente solo ve sus propias cargas
        if ($esDocente) {
            if ($docenteAsociado) {
                $query->where('docente_id', $docenteAsociado->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Búsqueda en vivo y filtros
        if ($buscar = trim($request->input('buscar', ''))) {
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('docente.persona', function ($p) use ($buscar) {
                    $p->whereRaw("CONCAT(primer_nombre, ' ', primer_apellido) LIKE ?", ["%{$buscar}%"])
                      ->orWhere('numero_documento', 'LIKE', "%{$buscar}%");
                })
                ->orWhereHas('areaFormacion', function ($a) use ($buscar) {
                    $a->where('nombre_area_formacion', 'LIKE', "%{$buscar}%")
                      ->orWhere('siglas', 'LIKE', "%{$buscar}%");
                })
                ->orWhereHas('grupoEstable', function ($g) use ($buscar) {
                    $g->where('nombre_grupo_estable', 'LIKE', "%{$buscar}%");
                })
                ->orWhereHas('gradoSeccion.grado', function ($gr) use ($buscar) {
                    $gr->where('numero_grado', 'LIKE', "%{$buscar}%");
                })
                ->orWhereHas('gradoSeccion.seccion', function ($s) use ($buscar) {
                    $s->where('nombre', 'LIKE', "%{$buscar}%");
                });
            });
        }

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($request->has('activa') && $request->input('activa') !== '') {
            $query->where('activa', (bool) $request->input('activa'));
        }

        $cargas = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('admin.cargas_academicas.index', compact(
            'cargas',
            'calendario',
            'anioModelo',
            'esActual',
            'esDocente'
        ));
    }

    /**
     * Acción para generar las cargas académicas del año a partir de docente_area_grados.
     */
    public function generar(Request $request)
    {
        // 1. Validar permiso de gestión
        if (!Auth::user()->can('carga_academica.gestionar')) {
            abort(403, 'No tienes permiso para generar cargas académicas.');
        }

        // 2. Validar que el año escolar sea actual (defensa en profundidad)
        if (!AnioEscolarSeleccionado::esActual()) {
            return redirect()->route('admin.cargas-academicas.index')
                ->with('error', 'No puedes generar cargas académicas en un año escolar de solo lectura o inactivo.');
        }

        $anioId = AnioEscolarSeleccionado::id();
        $calendario = CalendarioAcademico::where('anio_escolar_id', $anioId)->first();

        if (!$calendario) {
            return redirect()->route('admin.cargas-academicas.index')
                ->with('error', 'El año escolar seleccionado no tiene un Calendario Académico registrado. Crea o confirma el calendario antes de generar cargas.');
        }

        try {
            $resumen = $this->generadorService->generar($calendario->id);

            $mensaje = "Cargas académicas generadas exitosamente: {$resumen['creadas']} creadas, {$resumen['actualizadas']} actualizadas.";
            if ($resumen['omitidas'] > 0) {
                $mensaje .= " ({$resumen['omitidas']} omitidas por inconsistencias).";
            }

            return redirect()->route('admin.cargas-academicas.index')
                ->with('success', $mensaje)
                ->with('resumen_generacion', $resumen);
        } catch (\Throwable $e) {
            return redirect()->route('admin.cargas-academicas.index')
                ->with('error', 'Error al generar cargas académicas: ' . $e->getMessage())
                ->with('generar_error_modal', true);
        }
    }
}

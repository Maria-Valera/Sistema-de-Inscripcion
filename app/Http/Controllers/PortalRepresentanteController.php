<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;

class PortalRepresentanteController extends Controller
{
    public function index()
    {
        return view('representante.portal.index', [
            'anioEscolarActivo' => AnioEscolar::activos()->exists(),
        ]);
    }

    public function prosecucionIndex()
    {
        $inscripciones = [];
        
        if (auth()->check()) {
            $alumnosIds = \App\Models\Alumno::visiblesParaRepresentante(auth()->user())->pluck('id');

            $inscs = \App\Models\Inscripcion::where('tipo_inscripcion', 'prosecucion')
                ->whereIn('alumno_id', $alumnosIds)
                ->with(['alumno.persona', 'grado', 'seccion', 'anioEscolar'])
                ->get();

            foreach ($inscs as $ins) {
                $inscripciones[] = [
                    'id' => $ins->id,
                    'estudiante' => $ins->alumno->persona->primer_nombre . ' ' . $ins->alumno->persona->primer_apellido,
                    'cedula' => $ins->alumno->persona->numero_documento,
                    'grado' => $ins->grado ? $ins->grado->numero_grado . ' ° Nivel' : 'Sin grado',
                    'seccion' => $ins->seccion ? $ins->seccion->nombre : 'Sin sección',
                    'estado' => $ins->status,
                    'fecha' => $ins->created_at->format('d/m/Y'),
                ];
            }
        }

        return view('representante.prosecucion.index', [
            'inscripciones' => $inscripciones,
            'anioEscolarActivo' => AnioEscolar::activos()->exists(),
        ]);
    }

    public function prosecucionCreate()
    {
        return view('representante.prosecucion.create', [
            'anioEscolarActivo' => AnioEscolar::activos()->exists(),
        ]);
    }

    public function carnetIndex()
    {
        $representados = [];

        if (auth()->check()) {
            $alumnos = \App\Models\Alumno::visiblesParaRepresentante(auth()->user())
                ->with([
                    'persona',
                    'inscripciones' => function ($q) {
                        $q->with(['grado', 'seccion', 'anioEscolar'])->latest('id');
                    }
                ])
                ->get();

            foreach ($alumnos as $alumno) {
                $ins = $alumno->inscripciones->first();
                if ($ins) {
                    $representados[] = [
                        'id' => $alumno->id,
                        'nombre' => $alumno->persona->primer_nombre . ' ' . $alumno->persona->primer_apellido,
                        'cedula' => 'V-' . number_format((int)$alumno->persona->numero_documento, 0, ',', '.'),
                        'grado' => $ins->grado ? $ins->grado->numero_grado . ' ° Nivel' : 'Sin grado',
                        'seccion' => $ins->seccion ? 'Sección ' . $ins->seccion->nombre : 'Sin sección',
                        'anio' => $ins->anioEscolar ? ($ins->anioEscolar->inicio_anio_escolar->format('Y') . ' - ' . $ins->anioEscolar->cierre_anio_escolar->format('Y')) : 'No definido',
                        'foto' => null,
                    ];
                }
            }
        }

        // Fallback si no tiene inscritos
        if (empty($representados)) {
            $representados = [
                [
                    'id' => 1,
                    'nombre' => 'José Gregorio Hernández (Ejemplo)',
                    'cedula' => 'V-25.111.222',
                    'grado' => '4to Año',
                    'seccion' => 'Sección A',
                    'anio' => '2025-2026',
                    'foto' => null,
                ]
            ];
        }

        return view('representante.carnet.index', [
            'representados' => $representados,
            'estudianteSeleccionado' => $representados[0],
        ]);
    }

    public function carnetImprimir()
    {
        $estudiante = [
            'nombre' => request('nombre', 'José Gregorio Hernández'),
            'cedula' => request('cedula', 'V-25.111.222'),
            'grado' => request('grado', '4to Año'),
            'seccion' => request('seccion', 'Sección A'),
            'anio' => request('anio', '2025-2026'),
            'codigo' => request('codigo', 'CARNET-2026-00125'),
        ];

        return view('representante.carnet.imprimir', compact('estudiante'));
    }
}

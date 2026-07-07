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
            $userEmail = auth()->user()->email;
            
            // Buscar el representante asociado por el email del usuario logueado
            $representante = \App\Models\Representante::whereHas('persona', function ($query) use ($userEmail) {
                $query->where('email', $userEmail);
            })->first();

            if ($representante) {
                $inscs = \App\Models\Inscripcion::where('tipo_inscripcion', 'prosecucion')
                    ->where(function ($query) use ($representante) {
                        $query->where('padre_id', $representante->id)
                              ->orWhere('madre_id', $representante->id)
                              ->orWhere('representante_legal_id', function ($sub) use ($representante) {
                                  $sub->select('id')
                                      ->from('representante_legal')
                                      ->where('representante_id', $representante->id);
                              });
                    })
                    ->with(['alumno.persona', 'grado', 'seccion', 'anioEscolar'])
                    ->get();

                foreach ($inscs as $ins) {
                    $inscripciones[] = [
                        'id' => $ins->id,
                        'estudiante' => $ins->alumno->persona->primer_nombre . ' ' . $ins->alumno->persona->primer_apellido,
                        'cedula' => $ins->alumno->persona->numero_documento,
                        'grado' => $ins->grado->numero_grado . ' ° Nivel',
                        'seccion' => $ins->seccion ? $ins->seccion->nombre : 'Sin sección',
                        'estado' => $ins->status,
                        'fecha' => $ins->created_at->format('d/m/Y'),
                    ];
                }
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
            $userEmail = auth()->user()->email;
            
            $representante = \App\Models\Representante::whereHas('persona', function ($query) use ($userEmail) {
                $query->where('email', $userEmail);
            })->first();

            if ($representante) {
                $inscs = \App\Models\Inscripcion::where(function ($query) use ($representante) {
                        $query->where('padre_id', $representante->id)
                              ->orWhere('madre_id', $representante->id)
                              ->orWhere('representante_legal_id', function ($sub) use ($representante) {
                                  $sub->select('id')
                                      ->from('representante_legal')
                                      ->where('representante_id', $representante->id);
                              });
                    })
                    ->with(['alumno.persona', 'grado', 'seccion', 'anioEscolar'])
                    ->get();

                foreach ($inscs as $ins) {
                    $representados[] = [
                        'id' => $ins->alumno->id,
                        'nombre' => $ins->alumno->persona->primer_nombre . ' ' . $ins->alumno->persona->primer_apellido,
                        'cedula' => 'V-' . number_format((int)$ins->alumno->persona->numero_documento, 0, ',', '.'),
                        'grado' => $ins->grado->numero_grado . ' ° Nivel',
                        'seccion' => $ins->seccion ? 'Sección ' . $ins->seccion->nombre : 'Sin sección',
                        'anio' => $ins->anioEscolar->inicio_anio_escolar->format('Y') . ' - ' . $ins->anioEscolar->cierre_anio_escolar->format('Y'),
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

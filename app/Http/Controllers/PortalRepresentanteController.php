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
        $inscripciones = [
            [
                'id' => 1,
                'estudiante' => 'José Gregorio Hernández',
                'cedula' => '25111222',
                'grado' => '4to Año',
                'seccion' => 'A',
                'estado' => 'Activo',
                'fecha' => '15/03/2026',
            ],
            [
                'id' => 2,
                'estudiante' => 'María Alejandra Pérez',
                'cedula' => '26333444',
                'grado' => '3er Año',
                'seccion' => 'B',
                'estado' => 'Activo',
                'fecha' => '10/02/2026',
            ],
        ];

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
        $representados = [
            [
                'id' => 1,
                'nombre' => 'José Gregorio Hernández',
                'cedula' => 'V-25.111.222',
                'grado' => '4to Año',
                'seccion' => 'Sección A',
                'anio' => '2025-2026',
                'foto' => null,
            ],
            [
                'id' => 2,
                'nombre' => 'María Alejandra Pérez',
                'cedula' => 'V-26.333.444',
                'grado' => '3er Año',
                'seccion' => 'Sección B',
                'anio' => '2025-2026',
                'foto' => null,
            ],
        ];

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

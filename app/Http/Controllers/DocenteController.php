<?php

namespace App\Http\Controllers;

use App\Models\DetalleDocenteEstudio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\Genero;
use App\Models\PrefijoTelefono;
use App\Models\TipoDocumento;
use App\Models\EstudiosRealizado;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AnioEscolar;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\AreaFormacion;
use Illuminate\Http\JsonResponse;

class DocenteController extends Controller
{
    private function verificarAnioEscolar()
    {
        return AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    public function apiIndex(Request $request){
        $gradoId = $request->query('grado_id');
        $areaFormacionId = $request->query('area_formacion_id');
        $seccionId = $request->query('seccion_id');

        $query = Docente::with(['persona'])
            ->where('status', true)
            ->whereHas('persona', function ($query) {
                $query->where('status', true);
            });

        // Filtrar por grado si se proporciona
        if ($gradoId) {
            $query->whereHas('detalleDocenteEstudio.docenteAreaGrados', function ($query) use ($gradoId) {
                $query->where('grado_id', $gradoId)
                      ->where('status', true);
            });
        }

        // Filtrar por área de formación si se proporciona
        if ($areaFormacionId) {
            $query->whereHas('detalleDocenteEstudio.docenteAreaGrados.areaEstudios', function ($query) use ($areaFormacionId) {
                $query->whereHas('areaFormacion', function ($q) use ($areaFormacionId) {
                    $q->where('id', $areaFormacionId);
                });
            });
        }

        // Filtrar por sección si se proporciona
        if ($seccionId) {
            $query->whereHas('detalleDocenteEstudio.docenteAreaGrados', function ($query) use ($seccionId) {
                $query->where('seccion_id', $seccionId)
                      ->where('status', true);
            });
        }

        $docentes = $query->get();

        // Transformar los datos para incluir nombre completo y áreas
        $resultado = $docentes->map(function ($docente) {
            // Obtener áreas de formación del docente
            $areas = DB::table('docentes as d')
                ->join('detalle_docente_estudios as dde', 'd.id', '=', 'dde.docente_id')
                ->join('docente_area_grados as dag', 'dde.id', '=', 'dag.docente_estudio_realizado_id')
                ->join('area_estudio_realizados as aer', 'dag.area_estudio_realizado_id', '=', 'aer.id')
                ->join('area_formacions as af', 'aer.area_formacion_id', '=', 'af.id')
                ->where('d.id', $docente->id)
                ->where('dde.status', true)
                ->where('dag.status', true)
                ->where('af.status', true)
                ->pluck('af.nombre_area_formacion')
                ->toArray();

            return [
                'id' => $docente->id,
                'nombre_completo' => $docente->nombre_completo,
                'area' => !empty($areas) ? implode(', ', $areas) : 'Sin área asignada',
                'areas' => $areas,
                'horas_academicas' => $docente->horas_academicas,
                'persona_id' => $docente->persona_id,
                'codigo' => $docente->codigo
            ];
        });

        return response()->json($resultado);
    }

    public function index()
    {
        $buscar = request('buscar');
        $grado_id = request('grado_id');
        $seccion_id = request('seccion_id');
        $materia_id = request('materia_id');
        
        $personas = Persona::all();
        $prefijos = PrefijoTelefono::all();

        // Obtener datos para los filtros
        $grados = Grado::where('status', true)->orderBy('numero_grado')->get();
        $secciones = Seccion::where('status', true)->orderBy('nombre')->get();
        $materias = AreaFormacion::where('status', true)->orderBy('nombre_area_formacion')->get();

        // Construir consulta base
        $docentesQuery = Docente::with(['persona'])
            ->whereHas('persona', function ($query) {
                $query->where('status', true);
            })
            ->where('status', true);

        // Aplicar filtros si existen
        if ($grado_id) {
            $docentesQuery->whereHas('detalleDocenteEstudio.docenteAreaGrados', function ($query) use ($grado_id) {
                $query->where('grado_id', $grado_id)
                      ->where('status', true);
            });
        }

        if ($seccion_id) {
            $docentesQuery->whereHas('detalleDocenteEstudio.docenteAreaGrados', function ($query) use ($seccion_id) {
                $query->where('seccion_id', $seccion_id)
                      ->where('status', true);
            });
        }

        if ($materia_id) {
            $docentesQuery->whereHas('detalleDocenteEstudio.docenteAreaGrados.areaEstudioRealizado', function ($query) use ($materia_id) {
                $query->where('area_formacion_id', $materia_id);
            });
        }

        $docentes = $docentesQuery->buscar($buscar)->paginate(10);

        $anioEscolarActivo = $this->verificarAnioEscolar();

        return view('admin.docente.index', compact('docentes', 'anioEscolarActivo', 'personas', 'buscar', 'grados', 'secciones', 'materias'));
    }

    public function create()
    {
        $personas = Persona::all();
        $prefijos = PrefijoTelefono::all();
        $generos = Genero::all();
        $tipoDocumentos = TipoDocumento::whereIn('nombre', ['V', 'E'])->get();
        $docentes = Docente::all();

        return view('admin.docente.create', compact('personas', 'prefijos', 'generos', 'tipoDocumentos', 'docentes'));
    }

    public function obtenerAnioEscolarActivo()
    {
        $anioEscolar = AnioEscolar::activos()
            ->whereIn('status',[ 'Activo', 'Extendido'])
            ->first();

        if (!$anioEscolar) {
            throw new \Exception('No hay un Calendario Escolar activo. Por favor, contacte al administrador.');
        }

        return $anioEscolar;
    }
 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'numero_documento' => 'required|string|max:20',
            'primer_nombre' => 'required|string|max:50',
            'segundo_nombre' => 'nullable|string|max:50',
            'tercer_nombre' => 'nullable|string|max:50',
            'primer_apellido' => 'required|string|max:50',
            'segundo_apellido' => 'nullable|string|max:50',
            'genero' => 'required|exists:generos,id',
            'fecha_nacimiento' => [
                'required',
                'date',
                'before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            ],
            'correo' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:255',
            'prefijo_id' => 'nullable|exists:prefijo_telefonos,id',
            'prefijo_dos_id' => 'nullable|exists:prefijo_telefonos,id',
            'primer_telefono' => 'nullable|string|max:20',
            'telefono_dos' => 'nullable|string|max:20',
            'codigo' => 'nullable|numeric',
            'dependencia' => 'nullable|string|max:100',
            'horas_academicas' => 'nullable',
        ], [
            'tipo_documento_id.required' => 'El tipo de documento es obligatorio',
            'numero_documento.required' => 'La cédula es obligatoria',
            'numero_documento.unique' => 'Esta cédula ya está registrada',
            'primer_nombre.required' => 'El primer nombre es obligatorio',
            'primer_apellido.required' => 'El primer apellido es obligatorio',
            'genero.required' => 'El género es obligatorio',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento debe corresponder a una persona mayor de 18 años',
            'correo.email' => 'El correo electrónico no tiene un formato válido',
        ]);

        DB::beginTransaction();

        try {
            $anioEscolar = $this->obtenerAnioEscolarActivo();
            $persona = Persona::create([
                'primer_nombre' => $request->primer_nombre,
                'segundo_nombre' => $request->segundo_nombre,
                'tercer_nombre' => $request->tercer_nombre,
                'primer_apellido' => $request->primer_apellido,
                'segundo_apellido' => $request->segundo_apellido,
                'numero_documento' => $request->numero_documento,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'direccion' => $request->direccion,
                'email' => $request->correo,
                'telefono' => $request->primer_telefono,
                'telefono_dos' => $request->telefono_dos,
                'prefijo_id' => $request->prefijo_id,
                'prefijo_dos_id' => $request->prefijo_dos_id,
                'tipo_documento_id' => $request->tipo_documento_id,
                'genero_id' => $request->genero,
                'status' => true,
            ]);

            $docente = Docente::create([
                'anio_escolar_id' => $anioEscolar->id,
                'codigo' => $request->codigo,
                'dependencia' => $request->dependencia,
                'persona_id' => $persona->id,
                'horas_academicas' => $request->horas_academicas,
                'status' => true,
            ]);


            DB::commit();

            return redirect()->route('admin.docente.estudios', $docente->id)
                ->with('success', 'Docente registrado correctamente, ahora puede agregar sus estudios.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $docente = Docente::with('persona')->findOrFail($id);
        $prefijos = PrefijoTelefono::all();
        $generos = Genero::all();
        $tipoDocumentos = TipoDocumento::all();

        return view('admin.docente.edit', compact('docente', 'prefijos', 'generos', 'tipoDocumentos'));
    }

    public function update(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);
        $persona = $docente->persona;

        $validated = $request->validate(
            [
                'tipo_documento_id' => 'required|exists:tipo_documentos,id',
                'numero_documento' => 'required|string|max:20|unique:personas,numero_documento,' . $persona->id,
                'primer_nombre' => 'required|string|max:50',
                'segundo_nombre' => 'nullable|string|max:50',
                'tercer_nombre' => 'nullable|string|max:50',
                'primer_apellido' => 'required|string|max:50',
                'segundo_apellido' => 'nullable|string|max:50',
                'genero' => 'required|exists:generos,id',
                'fecha_nacimiento' => [
                    'required',
                    'date',
                    'before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                ],
                'correo' => 'nullable|email|max:100',
                'direccion' => 'nullable|string|max:255',
                'prefijo_id' => 'nullable|exists:prefijo_telefonos,id',
                'prefijo_dos_id' => 'nullable|exists:prefijo_telefonos,id',
                'primer_telefono' => 'nullable|string|max:20',
                'telefono_dos' => 'nullable|string|max:20',
                'codigo' => 'nullable|numeric',
                'dependencia' => 'nullable|string|max:100',
                'horas_academicas' => 'nullable',
            ],
            [
                'tipo_documento_id.required' => 'El tipo de documento es obligatorio',
                'numero_documento.required' => 'La cédula es obligatoria',
                'numero_documento.unique' => 'Esta cédula ya está registrada',
                'primer_nombre.required' => 'El primer nombre es obligatorio',
                'primer_apellido.required' => 'El primer apellido es obligatorio',
                'genero.required' => 'El género es obligatorio',
                'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
                'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento debe corresponder a una persona mayor de 18 años',
                'correo.email' => 'El correo electrónico no tiene un formato válido',
            ]
        );

        DB::beginTransaction();

        try {
            $anioEscolar = $this->obtenerAnioEscolarActivo();
            $persona->update([
                'primer_nombre' => $request->primer_nombre,
                'segundo_nombre' => $request->segundo_nombre,
                'tercer_nombre' => $request->tercer_nombre,
                'primer_apellido' => $request->primer_apellido,
                'segundo_apellido' => $request->segundo_apellido,
                'numero_documento' => $request->numero_documento,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'direccion' => $request->direccion,
                'email' => $request->correo,
                'telefono' => $request->primer_telefono,
                'telefono_dos' => $request->telefono_dos,
                'prefijo_id' => $request->prefijo_id,
                'prefijo_dos_id' => $request->prefijo_dos_id,
                'tipo_documento_id' => $request->tipo_documento_id,
                'genero_id' => $request->genero,
            ]);

            $docente->update([
                'anio_escolar_id' => $anioEscolar->id,
                'codigo' => $request->codigo,
                'dependencia' => $request->dependencia,
                'horas_academicas' => $request->horas_academicas,
            ]);

            DB::commit();

            return redirect()->route('admin.docente.estudios', $docente->id)
                ->with('success', 'Docente actualizado correctamente, ahora puede editar sus estudios.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $docente = Docente::with([
            'persona.tipoDocumento',
            'persona.genero',
            'persona.prefijoTelefono',
            'persona.prefijoDos',
            'detalleEstudios' => function ($q) {
                $q->where('status', true);
            },
            'detalleEstudios.estudiosRealizado'
        ])
            ->findOrFail($id);

        return view('admin.docente.modales.showModal', compact('docente'));
    }

    public function estudios($id)
    {
        $docentes = Docente::with(
            [
                'persona.tipoDocumento',
                'persona.genero',
                'persona.prefijoTelefono',
                'persona.prefijoDos',
            ]
        )
            ->findOrFail($id);
        $estudios = EstudiosRealizado::all();
        $docenteEstudios = DetalleDocenteEstudio::all();

        return view('admin.docente.estudios', compact('docentes', 'estudios', 'docenteEstudios'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $docente = Docente::findOrFail($id);
            $persona = $docente->persona;
            $docente->update(['status' => false]);
            $persona->update(['status' => false]);

            DB::commit();

            return redirect()->route(route: 'admin.docente.index')
                ->with('success', 'Docente eliminado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }


    public function reportePDF($id)
    {
        try {
            $docente = Docente::with([
                'persona.tipoDocumento',
                'persona.genero',
                'detalleDocenteEstudio.estudiosRealizado'
            ])->findOrFail($id);

            if ($docente->persona) {
                if (!$docente->persona->relationLoaded('genero')) {
                    $docente->persona->load('genero');
                }
                if (!$docente->persona->relationLoaded('tipoDocumento')) {
                    $docente->persona->load('tipoDocumento');
                }

                $docente->tipo_documento = $docente->persona->tipoDocumento->nombre ?? 'N/A';
                $docente->numero_documento = $docente->persona->numero_documento ?? 'N/A';
                $docente->primer_nombre = $docente->persona->primer_nombre ?? 'N/A';
                $docente->segundo_nombre = $docente->persona->segundo_nombre ?? '';
                $docente->tercer_nombre = $docente->persona->tercer_nombre ?? '';
                $docente->primer_apellido = $docente->persona->primer_apellido ?? 'N/A';
                $docente->segundo_apellido = $docente->persona->segundo_apellido ?? '';
                $docente->fecha_nacimiento = $docente->persona->fecha_nacimiento ?? 'N/A';
                $docente->genero = $docente->persona->genero ? $docente->persona->genero->genero : 'N/A';
                $docente->email = $docente->persona->email ?? 'N/A';
                $docente->direccion = $docente->persona->direccion ?? 'N/A';
                $docente->telefono = $docente->primer_telefono ?? $docente->segundo_telefono ?? 'N/A';
                $docente->telefono_dos = $docente->persona->telefono_dos ?? 'N/A';
            }

            $pdf = PDF::loadView('admin.docente.reportes.individual_PDF', [
                'docente' => $docente
            ]);

            $pdf->setOption('isPhpEnabled', true);

            return $pdf->stream('docente_' . ($docente->numero_documento ?? $docente->id) . '.pdf');
        } catch (\Exception $e) {
            return response('Error al generar el PDF: ' . $e->getMessage(), 500);
        }
    }

    public function reporteGeneralPDF()
    {
        try {
            $docentes = Docente::with([
                'persona.tipoDocumento',
                'persona.genero',
                'detalleDocenteEstudio.estudiosRealizado'
            ])->get()
                ->map(function ($docente) {
                    if ($docente->persona) {
                        $docente->tipo_documento = $docente->persona->tipoDocumento->nombre ?? 'N/A';
                        $docente->numero_documento = $docente->persona->numero_documento ?? 'N/A';
                        $docente->primer_nombre = $docente->persona->primer_nombre ?? 'N/A';
                        $docente->segundo_nombre = $docente->persona->segundo_nombre ?? '';
                        $docente->tercer_nombre = $docente->persona->tercer_nombre ?? '';
                        $docente->primer_apellido = $docente->persona->primer_apellido ?? 'N/A';
                        $docente->segundo_apellido = $docente->persona->segundo_apellido ?? '';
                        $docente->fecha_nacimiento = $docente->persona->fecha_nacimiento ?? 'N/A';
                        $docente->genero = $docente->persona->genero ? $docente->persona->genero->genero : 'N/A';
                        $docente->email = $docente->persona->email ?? 'N/A';
                        $docente->direccion = $docente->persona->direccion ?? 'N/A';
                        $docente->telefono = $docente->primer_telefono ?? $docente->persona->telefono ?? 'N/A';
                        $docente->telefono_dos = $docente->persona->telefono_dos ?? 'N/A';
                    }
                    return $docente;
                })
                ->sortBy(function ($docente) {
                    $primerApellido = $docente->primer_apellido ??
                        ($docente->persona->primer_apellido ?? '');
                    return strtoupper(substr($primerApellido, 0, 1));
                })
                ->values();

            $pdf = PDF::loadView('admin.docente.reportes.general_pdf', [
                'docentes' => $docentes
            ]);

            $pdf->setPaper('A4', 'landscape');
            $pdf->setOption('isPhpEnabled', true);

            return $pdf->stream('docentes_general.pdf');
        } catch (\Exception $e) {
            return response('Error al generar el PDF: ' . $e->getMessage(), 500);
        }
    }

    public function DocenteMateria(Request $request, $id = null)
    {
        try {
            // Obtener parámetros de filtro
            $buscar = $request->buscar;
            $gradoId = $request->grado_id;
            $seccionNombre = $request->seccion_id;
            $areaFormacionId = $request->area_formacion_id;
            
            // Inicializar variable
            $docentesAgrupados = [];

            // Si se proporciona un ID, mostrar solo ese docente
            if ($id) {
                // Obtener datos del docente específico usando las relaciones como en reportePDF
                $docenteModel = Docente::with([
                    'persona.tipoDocumento',
                    'persona.genero',
                    'detalleDocenteEstudio.estudiosRealizado'
                ])->findOrFail($id);

                // Preparar datos del docente
                $docenteData = [];
                \Log::info('Docente encontrado: ' . json_encode($docenteModel));
                \Log::info('Persona del docente: ' . json_encode($docenteModel->persona));
                
                if ($docenteModel->persona) {
                    $docenteData = [
                        'codigo' => $docenteModel->codigo,
                        'primer_nombre' => $docenteModel->persona->primer_nombre ?? 'N/A',
                        'segundo_nombre' => $docenteModel->persona->segundo_nombre ?? '',
                        'tercer_nombre' => $docenteModel->persona->tercer_nombre ?? '',
                        'primer_apellido' => $docenteModel->persona->primer_apellido ?? 'N/A',
                        'segundo_apellido' => $docenteModel->persona->segundo_apellido ?? '',
                        'numero_documento' => $docenteModel->persona->numero_documento ?? 'N/A',
                        'tipo_documento' => $docenteModel->persona->tipoDocumento->nombre ?? 'N/A',
                        'grados' => []
                    ];
                } else {
                    \Log::info('El docente no tiene relación con persona');
                }

                // Obtener asignaciones del docente con filtros
                $asignacionesQuery = DB::table('docente_area_grados as dag')
                    ->join('detalle_docente_estudios as dde', 'dag.docente_estudio_realizado_id', '=', 'dde.id')
                    ->join('area_estudio_realizados as aer', 'dag.area_estudio_realizado_id', '=', 'aer.id')
                    ->join('area_formacions as af', 'aer.area_formacion_id', '=', 'af.id')
                    ->join('grados as g', 'dag.grado_id', '=', 'g.id')
                    ->join('seccions as s', 'dag.seccion_id', '=', 's.id')
                    ->where('dde.docente_id', $docenteModel->id)
                    ->where('dag.status', true)
                    ->where('dde.status', true);

                // Aplicar filtros
                if ($gradoId) {
                    $asignacionesQuery->where('dag.grado_id', $gradoId);
                }

                if ($seccionNombre) {
                    $asignacionesQuery->where('s.nombre', $seccionNombre);
                }

                if ($areaFormacionId) {
                    $asignacionesQuery->where('aer.area_formacion_id', $areaFormacionId);
                }

                $asignaciones = $asignacionesQuery->select(
                        'af.nombre_area_formacion as materia',
                        'g.numero_grado as grado',
                        'g.id as grado_id',
                        's.nombre as seccion',
                        's.id as seccion_id'
                    )
                    ->orderBy('g.numero_grado')
                    ->orderBy('s.nombre')
                    ->orderBy('af.nombre_area_formacion')
                    ->get();

                // Agrupar asignaciones por grado y sección
                foreach ($asignaciones as $asignacion) {
                    $gradoSeccionKey = $asignacion->grado . '-' . $asignacion->seccion;

                    if (!isset($docenteData['grados'][$gradoSeccionKey])) {
                        $docenteData['grados'][$gradoSeccionKey] = [
                            'grado' => $asignacion->grado,
                            'grado_id' => $asignacion->grado_id,
                            'seccion' => $asignacion->seccion,
                            'seccion_id' => $asignacion->seccion_id,
                            'materias' => [],
                            'estudiantes' => []
                        ];
                    }

                    // Agregar materia (evitar duplicados)
                    if (!in_array($asignacion->materia, $docenteData['grados'][$gradoSeccionKey]['materias'])) {
                        $docenteData['grados'][$gradoSeccionKey]['materias'][] = $asignacion->materia;
                    }
                }

                // Obtener estudiantes para cada grado y sección
                foreach ($docenteData['grados'] as $gradoSeccionKey => &$gradoData) {
                    $estudiantes = DB::table('inscripcions as i')
                        ->join('alumnos as a', 'i.alumno_id', '=', 'a.id')
                        ->join('personas as p', 'a.persona_id', '=', 'p.id')
                        ->join('seccions as s', 'i.seccion_id', '=', 's.id')
                        ->where('i.grado_id', $gradoData['grado_id'])
                        ->where('i.seccion_id', $gradoData['seccion_id'])
                        ->where('i.status', 'Activo')
                        ->select(
                            'p.numero_documento',
                            'p.primer_nombre',
                            'p.segundo_nombre',
                            'p.tercer_nombre',
                            'p.primer_apellido',
                            'p.segundo_apellido',
                            's.nombre as seccion',
                            's.id as seccion_id'
                        )
                        ->orderBy('p.primer_apellido')
                        ->orderBy('p.primer_nombre')
                        ->get();

                    $gradoData['estudiantes'] = $estudiantes;
                }

                $docentesAgrupados = [$docenteModel->id => $docenteData];
            } else {
                // Si no hay ID, mostrar todos los docentes con filtros
                $docentesQuery = DB::table('docentes as d')
                    ->join('personas as p', 'd.persona_id', '=', 'p.id')
                    ->join('detalle_docente_estudios as dde', 'd.id', '=', 'dde.docente_id')
                    ->join('docente_area_grados as dag', 'dde.id', '=', 'dag.docente_estudio_realizado_id')
                    ->join('area_estudio_realizados as aer', 'dag.area_estudio_realizado_id', '=', 'aer.id')
                    ->join('area_formacions as af', 'aer.area_formacion_id', '=', 'af.id')
                    ->join('grados as g', 'dag.grado_id', '=', 'g.id')
                    ->join('seccions as s', 'dag.seccion_id', '=', 's.id')
                    ->where('d.status', true)
                    ->where('dde.status', true)
                    ->where('dag.status', true)
                    ->where('p.status', true);

                // Aplicar filtros
                if ($gradoId) {
                    $docentesQuery->where('dag.grado_id', $gradoId);
                }

                if ($seccionNombre) {
                    $docentesQuery->where('s.nombre', $seccionNombre);
                }

                if ($areaFormacionId) {
                    $docentesQuery->where('aer.area_formacion_id', $areaFormacionId);
                }

                if ($buscar) {
                    $docentesQuery->where(function($q) use ($buscar) {
                        $q->where('p.primer_nombre', 'like', '%' . $buscar . '%')
                          ->orWhere('p.primer_apellido', 'like', '%' . $buscar . '%')
                          ->orWhere('p.numero_documento', 'like', '%' . $buscar . '%');
                    });
                }

                $docentes = $docentesQuery->select(
                        'd.id as docente_id',
                        'd.codigo',
                        'p.primer_nombre',
                        'p.segundo_nombre',
                        'p.tercer_nombre',
                        'p.primer_apellido',
                        'p.segundo_apellido',
                        'p.numero_documento',
                        'af.nombre_area_formacion as materia',
                        'g.numero_grado as grado',
                        'g.id as grado_id',
                        's.nombre as seccion',
                        's.id as seccion_id'
                    )
                    ->orderBy('p.primer_apellido')
                    ->orderBy('p.primer_nombre')
                    ->orderBy('g.numero_grado')
                    ->orderBy('s.nombre')
                    ->orderBy('af.nombre_area_formacion')
                    ->get();

                // Agrupar datos por docente y grado-sección
                $docentesAgrupados = [];
                foreach ($docentes as $asignacion) {
                    $docenteId = $asignacion->docente_id;
                    $gradoKey = $asignacion->grado . '-' . $asignacion->seccion . '-' . $asignacion->grado_id;
                    
                    if (!isset($docentesAgrupados[$docenteId])) {
                        $docentesAgrupados[$docenteId] = [
                            'codigo' => $asignacion->codigo,
                            'primer_nombre' => $asignacion->primer_nombre,
                            'segundo_nombre' => $asignacion->segundo_nombre,
                            'tercer_nombre' => $asignacion->tercer_nombre,
                            'primer_apellido' => $asignacion->primer_apellido,
                            'segundo_apellido' => $asignacion->segundo_apellido,
                            'numero_documento' => $asignacion->numero_documento,
                            'grados' => []
                        ];
                    }
                    
                    if (!isset($docentesAgrupados[$docenteId]['grados'][$gradoKey])) {
                        $docentesAgrupados[$docenteId]['grados'][$gradoKey] = [
                            'grado' => $asignacion->grado,
                            'grado_id' => $asignacion->grado_id,
                            'seccion' => $asignacion->seccion,
                            'seccion_id' => $asignacion->seccion_id,
                            'materias' => [],
                            'estudiantes' => []
                        ];
                    }
                    
                    // Agregar materia (evitar duplicados)
                    if (!in_array($asignacion->materia, $docentesAgrupados[$docenteId]['grados'][$gradoKey]['materias'])) {
                        $docentesAgrupados[$docenteId]['grados'][$gradoKey]['materias'][] = $asignacion->materia;
                    }
                }

                // Obtener estudiantes para cada docente y grado-sección
                foreach ($docentesAgrupados as $docenteId => &$docente) {
                    foreach ($docente['grados'] as $gradoKey => &$gradoData) {
                        $estudiantes = DB::table('inscripcions as i')
                            ->join('alumnos as a', 'i.alumno_id', '=', 'a.id')
                            ->join('personas as p', 'a.persona_id', '=', 'p.id')
                            ->join('seccions as s', 'i.seccion_id', '=', 's.id')
                            ->where('i.grado_id', $gradoData['grado_id'])
                            ->where('i.seccion_id', $gradoData['seccion_id'])
                            ->where('i.status', 'Activo')
                            ->select(
                                'p.numero_documento',
                                'p.primer_nombre',
                                'p.segundo_nombre',
                                'p.tercer_nombre',
                                'p.primer_apellido',
                                'p.segundo_apellido',
                                's.nombre as seccion',
                                's.id as seccion_id'
                            )
                            ->orderBy('p.primer_apellido')
                            ->orderBy('p.primer_nombre')
                            ->get();

                        $gradoData['estudiantes'] = $estudiantes;
                    }
                }
            }

            // Log para depuración
            \Log::info('Variable docentesAgrupados: ' . json_encode($docentesAgrupados));

            $pdf = PDF::loadView('admin.transacciones.docente_area_grado.reportes.docente_materia_pdf', [
                'docentesAgrupados' => $docentesAgrupados,
                'filtros' => [
                    'buscar' => $buscar,
                    'grado_id' => $gradoId,
                    'seccion_id' => $seccionNombre,
                    'area_formacion_id' => $areaFormacionId
                ]
            ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('isPhpEnabled', true);

            return $pdf->stream('docentes_materias.pdf');
        } catch (\Exception $e) {
            return response('Error al generar el PDF: ' . $e->getMessage(), 500);
        }
    }

    public function verificarCedula(Request $request): JsonResponse
    {
        try {
            $numero_documento = $request->input('numero_documento');
            $personaId = $request->input('persona_id');
            
            if (!$numero_documento) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Debe proporcionar una cédula',
                ]);
            }

            $query = Persona::where('numero_documento', $numero_documento)
                ->whereHas('docente', function ($q) {
                    $q->where('status', '!=', 0);
                });

            if ($personaId) {
                $query->where('id', '!=', $personaId);
            }

            $personaExistente = $query->first();

            if ($personaExistente) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Esta cédula ya está registrada en el sistema',
                ], 409);
            }

            $registroEliminado = Persona::where('numero_documento', $numero_documento)
                ->whereHas('docente', function ($q) {
                    $q->where('status', 0);
                })
                ->when($personaId, function ($q) use ($personaId) {
                    $q->where('id', '!=', $personaId);
                })
                ->first();

            if ($registroEliminado) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'Esta cédula pertenece a un registro previamente eliminado. Puede reutilizarla.',
                    'puede_usar' => true,
                    'persona' => $registroEliminado
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Cédula disponible',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al verificar cédula: ' . $e->getMessage(),
            ], 500);
        }
    }
}

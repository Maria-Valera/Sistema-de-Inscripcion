<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Discapacidad;
use App\Models\EtniaIndigena;
use App\Models\Lateralidad;
use App\Models\OrdenNacimiento;
use App\Models\Persona;
use App\Models\TipoDocumento;
use App\Models\Genero;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Estado;
use App\Models\AnioEscolar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{

    private function verificarAnioEscolar()
    {
        return \App\Models\AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buscar = request('buscar');
        $estatus = request('estatus', 'Activo');
        $genero = request('genero');
        $tipo_documento = request('tipo_documento');

        // Construir la consulta base filtrada según el rol y visibilidad del usuario
        $query = Alumno::visiblesPara(auth()->user())->with([
            'persona',
            'etniaIndigena',
            'ordenNacimiento',
            'lateralidad',
            'inscripciones',
            'persona.genero',
            'persona.tipoDocumento',
            'inscripciones.grado',
            'inscripciones.anioEscolar',
            'inscripciones.seccion',
        ]);

        // Aplicar búsqueda
        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('persona', function ($persona) use ($buscar) {
                    $persona->where('primer_nombre', 'LIKE', "%{$buscar}%")
                        ->orWhere('segundo_nombre', 'LIKE', "%{$buscar}%")
                        ->orWhere('primer_apellido', 'LIKE', "%{$buscar}%")
                        ->orWhere('segundo_apellido', 'LIKE', "%{$buscar}%")
                        ->orWhere('numero_documento', 'LIKE', "%{$buscar}%");
                });
            });
        }

        // Aplicar filtro de estatus
        if ($estatus === 'todos') {
            $query->whereIn('status', ['Activo', 'Inactivo']);
        } else {
            $query->where('status', $estatus);
        }

        // Aplicar filtro de género
        if (!empty($genero)) {
            $query->whereHas('persona', function ($q) use ($genero) {
                $q->where('genero_id', $genero);
            });
        }

        // Aplicar filtro de tipo de documento
        if (!empty($tipo_documento)) {
            $query->whereHas('persona', function ($q) use ($tipo_documento) {
                $q->where('tipo_documento_id', $tipo_documento);
            });
        }

        // Obtener datos para los filtros
        $generos = \App\Models\Genero::all();
        $tiposDocumento = \App\Models\TipoDocumento::all();

        // Ordenar y paginar
        $alumnos = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(request()->query());

        // Si el usuario es Docente (sin Admin/Subdirectora), no enviar campos sensibles
        if (auth()->user()?->esSoloDocente()) {
            $alumnos->getCollection()->each(function ($alumno) {
                $alumno->makeHidden([
                    'talla_camisa_id',
                    'talla_pantalon_id',
                    'talla_zapato',
                    'peso',
                    'estatura',
                    'orden_nacimiento_id',
                    'lateralidad_id',
                    'etnia_indigena_id',
                ]);
                $alumno->talla_camisa_id = null;
                $alumno->talla_pantalon_id = null;
                $alumno->talla_zapato = null;
                $alumno->peso = null;
                $alumno->estatura = null;
                $alumno->orden_nacimiento_id = null;
                $alumno->lateralidad_id = null;
                $alumno->etnia_indigena_id = null;

                if ($alumno->persona) {
                    $alumno->persona->makeHidden(['direccion', 'localidad_id']);
                    $alumno->persona->direccion = null;
                    $alumno->persona->localidad_id = null;
                    $alumno->persona->unsetRelation('localidad');
                }
                $alumno->unsetRelation('etniaIndigena');
                $alumno->unsetRelation('ordenNacimiento');
                $alumno->unsetRelation('lateralidad');
            });
        }

        // Verificar si hay Calendario Escolar activo
        $anioEscolarActivo = $this->verificarAnioEscolar();

        // Se envían los datos a la vista
        return view('admin.alumnos.index', compact(
            'alumnos',
            'anioEscolarActivo',
            'buscar',
            'estatus',
            'generos',
            'tiposDocumento',
            'genero',
            'tipo_documento'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $orden_nacimiento = OrdenNacimiento::all();
        $discapacidad = Discapacidad::all();
        $etnia_indigena = EtniaIndigena::all();
        $lateralidad = Lateralidad::all();
        $persona = Persona::all();
        $tipo_documento = TipoDocumento::all();
        $genero = Genero::all();
        $localidad = Localidad::all();
        $municipio = Municipio::all();
        $estado = Estado::all();
        $anioEscolarActivo = $this->verificarAnioEscolar();


        $anioEscolarActivo = $this->verificarAnioEscolar();
        return view('admin.alumnos.create', compact('anioEscolarActivo'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Alumno $alumno)
    {
        $this->authorize('view', $alumno);

        $alumno->load([
            'persona.tipoDocumento',
            'persona.genero',
            'persona.localidad.municipio.estado.pais',
            'discapacidades',
            'ordenNacimiento',
            'lateralidad',
            'etniaIndigena',
            'tallaCamisa',
            'tallaPantalon'
        ]);

        return view('admin.alumnos.show', compact('alumno'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Alumno $alumno)
    {
        $anioEscolarActivo = $this->verificarAnioEscolar();

        return view('admin.alumnos.edit', compact(
            'alumno',
            'anioEscolarActivo'
        ));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Alumno::eliminar($id);

        return redirect()->route('admin.alumnos.index')->with('success', 'Alumno eliminado correctamente');
    }

    public function reportePDF($id)
    {
        $alumno = Alumno::where('id', $id)
            ->with([
                'persona.tipoDocumento',
                'persona.genero',
                'persona.localidad.municipio.estado.pais',
                'discapacidades',
                'ordenNacimiento',
                'lateralidad',
                'etniaIndigena'
            ])
            ->firstOrFail();

        $this->authorize('view', $alumno);

        // Cargar nombres de tallas directamente
        if ($alumno->talla_camisa_id) {
            $tallaCamisa = \App\Models\Talla::find($alumno->talla_camisa_id);
            $alumno->talla_camisa_nombre = $tallaCamisa ? $tallaCamisa->nombre : null;
        }

        if ($alumno->talla_pantalon_id) {
            $tallaPantalon = \App\Models\Talla::find($alumno->talla_pantalon_id);
            $alumno->talla_pantalon_nombre = $tallaPantalon ? $tallaPantalon->nombre : null;
        }

        // Si el usuario es Docente (sin Admin/Subdirectora), no enviar campos sensibles
        if (auth()->user()?->esSoloDocente()) {
            $alumno->makeHidden([
                'talla_camisa_id',
                'talla_pantalon_id',
                'talla_zapato',
                'peso',
                'estatura',
                'orden_nacimiento_id',
                'lateralidad_id',
                'etnia_indigena_id',
            ]);
            $alumno->talla_camisa_id = null;
            $alumno->talla_pantalon_id = null;
            $alumno->talla_zapato = null;
            $alumno->peso = null;
            $alumno->estatura = null;
            $alumno->orden_nacimiento_id = null;
            $alumno->lateralidad_id = null;
            $alumno->etnia_indigena_id = null;
            $alumno->talla_camisa_nombre = null;
            $alumno->talla_pantalon_nombre = null;

            if ($alumno->persona) {
                $alumno->persona->makeHidden(['direccion', 'localidad_id']);
                $alumno->persona->direccion = null;
                $alumno->persona->localidad_id = null;
                $alumno->persona->unsetRelation('localidad');
            }
            $alumno->unsetRelation('etniaIndigena');
            $alumno->unsetRelation('ordenNacimiento');
            $alumno->unsetRelation('lateralidad');
        }

        $pdf = PDF::loadView('admin.alumnos.reportes.general_est', compact('alumno'));
        $pdf->setOption('isPhpEnabled', true);
        return $pdf->stream('alumno_' . $alumno->persona->numero_documento . '.pdf');
    }

    public function reporteGeneralPDF(Request $request)
    {
        // Obtener los parámetros de filtrado
        $genero = $request->input('genero');
        $tipo_documento = $request->input('tipo_documento');
        $estatus = $request->input('estatus', 'Activo');

        // Construir la consulta base
        $query = Alumno::with(['persona', 'etniaIndigena', 'ordenNacimiento', 'lateralidad', 'persona.genero', 'persona.tipoDocumento']);

        // Aplicar filtro de estatus
        if ($estatus === 'todos') {
            $query->whereIn('status', ['Activo', 'Inactivo']);
        } else {
            $query->where('status', $estatus);
        }

        // Aplicar filtro de género
        if (!empty($genero)) {
            $query->whereHas('persona', function ($q) use ($genero) {
                $q->where('genero_id', $genero);
            });
        }

        // Aplicar filtro de tipo de documento
        if (!empty($tipo_documento)) {
            $query->whereHas('persona', function ($q) use ($tipo_documento) {
                $q->where('tipo_documento_id', $tipo_documento);
            });
        }

        // Asegurarse de cargar las relaciones necesarias con sus respectivas columnas
        $query->with([
            'persona.tipoDocumento:id,nombre',
            'persona.genero:id,genero',
            'persona.localidad.estado.pais:id,nameES',
            'discapacidades:id,nombre_discapacidad',
            'etniaIndigena:id,nombre'
        ]);

        // Obtener los alumnos con los filtros aplicados y formatear los datos
        $alumnos = $query->orderBy('created_at', 'desc')->get()->map(function ($alumno) {
            $tipoDocumento = optional($alumno->persona->tipoDocumento)->nombre;
            
            // Obtener nombres de discapacidades separados por comas
            $discapacidades = $alumno->discapacidades->pluck('nombre_discapacidad')->implode(', ');
            
            return [
                'tipo_documento' => $tipoDocumento ?? 'N/A',
                'numero_documento' => $alumno->persona->numero_documento ?? 'N/A',
                'primer_apellido' => $alumno->persona->primer_apellido ?? 'N/A',
                'segundo_apellido' => $alumno->persona->segundo_apellido ?? '',
                'primer_nombre' => $alumno->persona->primer_nombre ?? 'N/A',
                'segundo_nombre' => $alumno->persona->segundo_nombre ?? '',
                'tercer_nombre' => $alumno->persona->tercer_nombre ?? '',
                'fecha_nacimiento' => $alumno->persona->fecha_nacimiento ?? null,
                'edad' => $alumno->persona->fecha_nacimiento ? \Carbon\Carbon::parse($alumno->persona->fecha_nacimiento)->age : 'N/A',
                'genero' => $alumno->persona->genero->genero ?? 'N/A',
                'pais' => $alumno->persona->localidad->estado->pais->nameES ?? 'N/A',
                'discapacidad' => $discapacidades ?: 'Ninguna',
                'etnia_indigena' => $alumno->etniaIndigena->nombre ?? 'No pertenece a ninguna etnia indígena'
            ];
        });

        if ($alumnos->isEmpty()) {
            return response('No se encontraron alumnos con los criterios seleccionados', 404);
        }

        // Pasar los filtros a la vista para mostrarlos en el PDF
        $filtros = [
            'genero' => $genero ? \App\Models\Genero::find($genero)->genero ?? null : null,
            'tipo_documento' => $tipo_documento ? \App\Models\TipoDocumento::find($tipo_documento)->nombre ?? null : null,
            'estatus' => $estatus
        ];

        $pdf = PDF::loadview('admin.alumnos.reportes.Reporte_General', [
            'alumnos' => $alumnos,
            'filtros' => $filtros
        ]);
        
        $pdf->setOption('isPhpEnabled', true);
        
        return $pdf->stream('reporte_alumnos.pdf');
    }
}

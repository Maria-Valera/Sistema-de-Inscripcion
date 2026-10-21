<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Models\Persona;
use App\Models\Representante;
use App\Models\RepresentanteLegal;
use App\Models\Estado;
use App\Models\Pais;
use App\Models\Municipio;
use App\Models\Localidad;
use App\Models\Banco;
use App\Models\PrefijoTelefono;
use App\Models\Ocupacion;
use App\Models\TipoDocumento;
use App\Models\AnioEscolar;
use App\Models\Genero;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class RepresentanteController extends Controller
{
    private function verificarAnioEscolar()
    {
        return AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    private function mapearCarnetPatriaAfiliado($valor)
    {
        $mapeo = [
            'madre' => 1,
            'padre' => 2,
            'otro' => 3,
        ];

        return $mapeo[$valor] ?? 0;
    }

    private function obtenerTipoCuenta($tipo)
    {
        return strtolower((string)$tipo) === 'ahorro' ? 'Ahorro' : 'Corriente';
    }

    public function index()
    {
        $anioEscolarActivo = $this->verificarAnioEscolar();
        $buscar = request('buscar');
        $query = Representante::with([
            'persona.tipoDocumento',
            'persona.genero',
            'persona.prefijo',
            'persona.prefijoDos',
            'legal' => function ($query) {
                $query->with(['banco' => function ($q) {
                    $q->select('id', 'nombre_banco');
                }]);
            },
            'estado',
            'municipios',
            'localidads',
            'ocupacion',
            'pais'  // Agregar relación de país
        ]);

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id', 'LIKE', "%{$buscar}%")
                    ->orWhereHas('persona', function ($query) use ($buscar) {
                        $query->where('numero_documento', 'LIKE', "%{$buscar}%")
                            ->orWhere('primer_nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('segundo_nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('primer_apellido', 'LIKE', "%{$buscar}%")
                            ->orWhere('segundo_apellido', 'LIKE', "%{$buscar}%");
                    });
            });
        }

        Log::info('Filtros recibidos:', [
            'es_legal' => request('es_legal'),
            'grado_id' => request('grado_id'),
            'seccion_id' => request('seccion_id'),
            'todos_los_params' => request()->all()
        ]);

        if (request()->has('es_legal') && request('es_legal') !== '' && request('es_legal') !== null) {
            $esLegal = request('es_legal') == '1';
            if ($esLegal) {
                $query->whereHas('legal');
            } else {
                $query->whereDoesntHave('legal');
            }
            Log::info('Aplicando filtro es_legal: ' . $esLegal);
        }

        if (request()->has('grado_id') && request('grado_id') !== '' && request('grado_id') !== null) {
            $gradoId = request('grado_id');
            Log::info('Aplicando filtro grado_id: ' . $gradoId);
            $query->whereExists(function ($subquery) use ($gradoId) {
                $subquery->select(DB::raw(1))
                    ->from('inscripcions')
                    ->where(function ($q) use ($gradoId) {
                        $q->where('inscripcions.padre_id', DB::raw('representantes.id'))
                            ->orWhere('inscripcions.madre_id', DB::raw('representantes.id'))
                            ->orWhere('inscripcions.representante_legal_id', DB::raw('representantes.id'));
                    })
                    ->where('inscripcions.grado_id', $gradoId);
            });
        }

        if (request()->has('seccion_id') && request('seccion_id') !== '' && request('seccion_id') !== null && request('seccion_id') != '0') {
            $seccionNombre = request('seccion_id');
            Log::info('Aplicando filtro seccion_id: ' . $seccionNombre);
            $query->whereExists(function ($subquery) use ($seccionNombre) {
                $subquery->select(DB::raw(1))
                    ->from('inscripcions')
                    ->join('seccions', 'seccions.id', '=', 'inscripcions.seccion_id')
                    ->where(function ($q) use ($seccionNombre) {
                        $q->where('inscripcions.padre_id', DB::raw('representantes.id'))
                            ->orWhere('inscripcions.madre_id', DB::raw('representantes.id'))
                            ->orWhere('inscripcions.representante_legal_id', DB::raw('representantes.id'));
                    })
                    ->where('seccions.nombre', $seccionNombre);
            });
        }

        $query->where('status', '!=', 0)
            ->whereNull('deleted_at');

        $query->orderBy('id', 'desc');

        $representantes = $query->paginate(10)
            ->appends(request()->query());

        Log::info('Resultados después de filtros:', [
            'total_encontrados' => $representantes->total(),
            'pagina_actual' => $representantes->currentPage(),
            'sql_query' => $query->toSql()
        ]);

        $grados = \App\Models\Grado::where('status', true)
            ->orderBy('numero_grado')
            ->get();

        $secciones = \App\Models\Seccion::where('status', true)
            ->select('nombre')
            ->distinct()
            ->orderBy('nombre')
            ->get();

        $paises = \App\Models\Pais::where('status', true)
            ->orderBy('nameES', 'ASC')
            ->get();

        if ($representantes->count() > 0) {
            $primerRep = $representantes->first();
            Log::info('Datos del primer representante:', [
                'persona_id' => $primerRep->persona_id,
                'tiene_persona' => isset($primerRep->persona),
                'persona_tipo_documento_id' => $primerRep->persona ? $primerRep->persona->tipo_documento_id : null,
                'persona_tipo_documento' => $primerRep->persona ? $primerRep->persona->tipo_documento : null,
                'persona_genero' => $primerRep->persona ? $primerRep->persona->genero : null,
                'persona_prefijo' => $primerRep->persona ? $primerRep->persona->prefijo : null,
                'persona_prefijoDos' => $primerRep->persona ? $primerRep->persona->prefijoDos : null,
                'tiene_estado' => isset($primerRep->estado),
                'tiene_municipios' => isset($primerRep->municipios),
                'tiene_localidads' => isset($primerRep->localidads),
                'tiene_ocupacion' => isset($primerRep->ocupacion),
                'tiene_legal' => isset($primerRep->legal),
            ]);
        }

        return view("admin.representante.representante", compact('representantes', 'anioEscolarActivo', 'grados', 'secciones', 'paises'));
    }

    public function eliminados()
    {
        $representantes = \App\Models\Representante::with([
            'persona',
            'estado',
            'municipios',
            'localidads'
        ])
            ->where(function ($query) {
                $query->where('status', 0)
                    ->orWhereNotNull('deleted_at');
            })
            ->withTrashed()
            ->paginate(10);

        return view("admin.representante.eliminados", compact('representantes'));
    }

    public function restaurar($id)
    {
        $representante = Representante::withTrashed()->findOrFail($id);

        DB::beginTransaction();
        try {
            if ($representante->trashed()) {
                $representante->restore();
            }

            $representante->status = 1;
            $representante->save();

            if ($representante->legal) {
                if (method_exists($representante->legal, 'restore') && $representante->legal->trashed()) {
                    $representante->legal->restore();
                } elseif (isset($representante->legal->status)) {
                    $representante->legal->status = 1;
                    $representante->legal->save();
                }
            }

            DB::commit();

            return redirect()->route('representante.eliminados')
                ->with('success', 'Representante restaurado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al restaurar representante: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al restaurar el representante: ' . $e->getMessage());
        }
    }


    public function mostrarFormulario()
    {
        $from = request('from');

        // Cargar solo datos necesarios para selects de ubicación
        $paises = Pais::where('status', true)->orderBy('nameES', 'ASC')->get();
        $estados = Estado::with(['municipio' => function ($query) {
            $query->with(['localidades'])->orderBy('nombre_municipio', 'ASC');
        }])->orderBy('nombre_estado', 'ASC')->get();

        // Cargar otros datos necesarios
        $bancos = Banco::where('status', true)->orderBy("nombre_banco", "ASC")->get();
        $prefijos_telefono = PrefijoTelefono::where('status', true)->orderBy("prefijo", "ASC")->get();
        $ocupaciones = Ocupacion::where('status', true)->orderBy('nombre_ocupacion', 'ASC')->get();
        $tipoDocumentos = TipoDocumento::where('status', true)->where('nombre', '!=', 'CE')->get();
        $generos = Genero::where('status', true)->get();

        return view(
            "admin.representante.formulario_representante",
            compact('paises', 'estados', 'bancos', 'prefijos_telefono', 'ocupaciones', 'tipoDocumentos', 'generos', 'from')
        );
    }

    public function mostrarFormularioEditar(Request $request, $id = null)
    {
        $representante = null;

        // 1. Si se recibe un ID, intentar buscar por ID de representante
        if ($id && is_numeric($id)) {
            $representante = Representante::with([
                'persona.prefijo',
                'estado',
                'municipios',
                'localidads',
                'legal' => function ($query) {
                    $query->with(['banco', 'prefijo']);
                }
            ])->find($id);
        }

        // 2. Si no se encontró por ID de representante, intentar buscar si el parámetro es persona_id
        if (!$representante) {
            $personaId = $request->query('persona_id', $id);
            if ($personaId && is_numeric($personaId)) {
                $representante = Representante::with([
                    'persona.prefijo',
                    'estado',
                    'municipios',
                    'localidads',
                    'legal' => function ($query) {
                        $query->with(['banco', 'prefijo']);
                    }
                ])->where('persona_id', $personaId)->first();
            }

            // Si aún no existe pero la Persona sí existe, crear el registro base defensivamente
            if (!$representante && !empty($personaId)) {
                $persona = Persona::find($personaId);
                if ($persona) {
                    $representante = Representante::withTrashed()->where('persona_id', $persona->id)->first();
                    if ($representante && $representante->trashed()) {
                        $representante->restore();
                    }

                    if (!$representante) {
                        $ocupacionDefecto = Ocupacion::firstOrCreate(
                            ['nombre_ocupacion' => 'Comerciante'],
                            ['status' => 1]
                        );

                        $localidadDefecto = Localidad::with('municipio.estado')->first();
                        $parroquiaId = $localidadDefecto ? $localidadDefecto->id : 1;
                        $municipioId = ($localidadDefecto && $localidadDefecto->municipio_id) ? $localidadDefecto->municipio_id : 1;
                        $estadoId = ($localidadDefecto && $localidadDefecto->municipio && $localidadDefecto->municipio->estado_id)
                            ? $localidadDefecto->municipio->estado_id
                            : (Estado::value('id') ?? 1);
                        $paisId = ($localidadDefecto && $localidadDefecto->municipio && $localidadDefecto->municipio->estado && $localidadDefecto->municipio->estado->pais_id)
                            ? $localidadDefecto->municipio->estado->pais_id
                            : (Pais::value('id') ?? 1);

                        $representante = Representante::firstOrCreate(
                            ['persona_id' => $persona->id],
                            [
                                'pais_id'                             => $paisId,
                                'estado_id'                           => $estadoId,
                                'municipio_id'                        => $municipioId,
                                'parroquia_id'                        => $parroquiaId,
                                'ocupacion_representante'             => $ocupacionDefecto->id,
                                'convivenciaestudiante_representante' => 'Si',
                                'status'                              => 1,
                            ]
                        );
                    }

                    $representante->load([
                        'persona.prefijo',
                        'estado',
                        'municipios',
                        'localidads',
                        'legal' => function ($query) {
                            $query->with(['banco', 'prefijo']);
                        }
                    ]);
                }
            }
        }

        // 3. Manejo defensivo: si no se encontró ni se pudo crear, redirigir amigablemente con mensaje de error
        if (!$representante) {
            return redirect()->route('representante.index')
                ->with('error', 'El representante solicitado no fue encontrado en el sistema.');
        }

        $paises = Pais::where('status', true)->orderBy('nameES', 'ASC')->get();
        $estados = Estado::where('status', true)
            ->with(['municipio' => function ($query) {
                $query->where('status', true)
                    ->with(['localidades' => function ($q) {
                        $q->where('status', true);
                    }]);
            }])
            ->orderBy('nombre_estado', 'ASC')
            ->get();

        $municipios = Municipio::where('status', true)
            ->orderBy('nombre_municipio', 'ASC')
            ->get();

        $bancos = Banco::where('status', true)
            ->orderBy('nombre_banco', 'ASC')
            ->get();

        $ocupaciones = Ocupacion::where('status', true)
            ->orderBy('nombre_ocupacion', 'ASC')
            ->get();

        $generos = Genero::where('status', true)
            ->orderBy('genero', 'ASC')
            ->get();

        $prefijos_telefono = PrefijoTelefono::where('status', true)
            ->orderBy('prefijo', 'ASC')
            ->get();

        $tipoDocumentos = TipoDocumento::where('status', true)
            ->where('nombre', '!=', 'CE')
            ->orderBy('nombre', 'ASC')
            ->get();

        $parroquias_cargadas = Localidad::where('status', true)
            ->orderBy('nombre_localidad', 'ASC')
            ->get();

        $from = $request->query('from');
        $inscripcion_id = $request->query('inscripcion_id');

        // Determinar si la ficha es nueva o incompleta (creada automáticamente desde el gestor de usuarios)
        $esFichaNueva = empty($representante->persona->direccion) && empty($representante->persona->telefono);

        return view("admin.representante.modales.editarModal", compact(
            'representante',
            'paises',
            'estados',
            'municipios',
            'bancos',
            'ocupaciones',
            'generos',
            'prefijos_telefono',
            'tipoDocumentos',
            'parroquias_cargadas',
            'from',
            'inscripcion_id',
            'esFichaNueva'
        ));
    }

    private function parseDate($dateString)
    {
        Log::info('parseDate llamado con:', ['dateString' => $dateString]);

        if (empty($dateString)) {
            Log::info('dateString está vacío, retornando null');
            return null;
        }

        $formats = [
            'd/m/Y',
            'd-m-Y',
            'd.m.Y',
            'Y-m-d',
            'Y/m/d',
            'Y.m.d',
        ];

        foreach ($formats as $format) {
            try {
                $result = \Carbon\Carbon::createFromFormat($format, $dateString)->format('Y-m-d');
                Log::info("parseDate éxito con formato $format:", ['result' => $result]);
                return $result;
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            $result = \Carbon\Carbon::parse($dateString)->format('Y-m-d');
            Log::info('parseDate éxito con strtotime:', ['result' => $result]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Error al analizar la fecha: ' . $dateString, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function isValidDate($dateString)
    {
        if (empty($dateString)) {
            return false;
        }

        $formats = ['d/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'Y/m/d', 'Y.m.d'];

        foreach ($formats as $format) {
            $d = \DateTime::createFromFormat($format, $dateString);
            if ($d && $d->format($format) === $dateString) {
                return true;
            }
        }

        return false;
    }

    public function edit(Request $request, $id)
    {
        $representante = Representante::with(['persona', 'legal'])->findOrFail($id);

        return view('admin.representante.formulario_representante', [
            'representante' => $representante,
            'from' => $request->from,
            'inscripcion_id' => $request->inscripcion_id,
            'tipo' => $request->tipo,
            'paises' => Pais::where('status', true)->orderBy('nameES', 'ASC')->get(),
            'estados' => Estado::with('municipio.localidades')->get(),
            'bancos' => Banco::where('status', true)->get(),
            'prefijos_telefono' => PrefijoTelefono::where('status', true)->get(),
            'ocupaciones' => Ocupacion::where('status', true)->get(),
            'tipoDocumentos' => TipoDocumento::where('status', true)->get(),
            'generos' => Genero::where('status', true)->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $representante = Representante::with(['persona', 'legal'])->findOrFail($id);
        $persona = $representante->persona;
        $isLegalRepresentative = in_array($request->input('tipo_representante'), [
            'legal',
            'solo_representante',
            'progenitor_padre_representante',
            'progenitor_madre_representante'
        ]);

        Log::info('=== ACTUALIZANDO REPRESENTANTE ===', [
            'representante_id' => $id,
            'persona_id' => $persona->id,
            'tipo' => $isLegalRepresentative ? 'Legal' : 'Progenitor',
            'request_data' => $request->except(['_token', '_method', 'password']),
            'telefono_dos' => $request->input('telefono_dos'),
            'prefijo_dos' => $request->input('prefijo_dos'),
            'telefono_dos_padre' => $request->input('telefono_dos_padre'),
            'prefijo_dos_padre' => $request->input('prefijo_dos_padre'),
        ]);

        $rules = [
            'numero_documento-representante' => [
                'required',
                'string',
                'max:20',
                Rule::unique('personas', 'numero_documento')->ignore($persona->id),
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^\d{6,9}$/', $value)) {
                        $fail('El número de cédula debe contener entre 6 y 9 dígitos.');
                    }
                },
            ],
            'primer-nombre-representante' => 'required|string|max:50',
            'primer-apellido-representante' => 'required|string|max:50',
            'fecha-nacimiento-representante' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidDate($value)) {
                        $fail('El formato de la fecha debe ser DD/MM/YYYY');
                    }
                }
            ],
            'sexo-representante' => 'required|exists:generos,id',
            'tipo-ci-representante' => 'required|exists:tipo_documentos,id',
            'pais_id' => 'required|exists:pais,id',
            'estado_id' => 'required|exists:estados,id',
            'municipio_id' => 'required|exists:municipios,id',
            'parroquia_id' => 'required|exists:localidads,id',
        ];

        if ($isLegalRepresentative) {
            $rules = array_merge($rules, [
                'correo-representante' => 'required|email|max:100',
                'banco_id' => 'required|exists:bancos,id',
                'pertenece_organizacion' => 'required|boolean',
                'cual_organizacion_representante' => 'required_if:pertenece_organizacion,1|nullable|string|max:255',
            ]);
        }

        $messages = [
            'required' => 'El campo :attribute es obligatorio.',
            'numero_documento-representante.unique' => 'Este número de cédula ya está registrado',
            'numero_documento-representante.regex' => 'El número de cédula debe contener entre 6 y 8 dígitos',
            'fecha-nacimiento-representante' => 'Formato de fecha inválido',
            'parroquia_id.required' => 'La parroquia es obligatoria',
            'parroquia_id.exists' => 'La parroquia seleccionada no es válida',
            'pertenece_organizacion.required' => 'Debe seleccionar si pertenece a una organización',
            'pertenece_organizacion.boolean' => 'El valor seleccionado no es válido',
            'cual_organizacion_representante.required_if' => 'Debe especificar la organización cuando selecciona que pertenece',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            Log::error('=== ERRORES DE VALIDACIÓN ===', [
                'errors' => $validator->errors()->toArray(),
                'rules' => $rules,
                'request_all' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            Log::info('=== DETALLES DE TELÉFONOS ===', [
                'telefono_representante' => $request->input('telefono-representante'),
                'telefono_madre' => $request->input('telefono-madre'),
                'telefono_padre' => $request->input('telefono-padre'),
                'telefono_movil' => $request->input('telefono_movil'),
                'telefono_dos' => $request->input('telefono_dos'),
                'telefono_dos_padre' => $request->input('telefono_dos_padre'),
                'prefijo_telefono' => $request->input('prefijo_telefono'),
                'prefijo_dos' => $request->input('prefijo_dos'),
                'prefijo_dos_padre' => $request->input('prefijo_dos_padre'),
                'all_request_data' => $request->all()
            ]);

            Log::info('Valores a guardar en persona:', [
                'telefono' => $request->input('telefono-representante')
                    ? preg_replace('/^0+/', '', $request->input('telefono-representante'))
                    : ($request->input('telefono-madre')
                        ? preg_replace('/^0+/', '', $request->input('telefono-madre'))
                        : ($request->input('telefono-padre')
                            ? preg_replace('/^0+/', '', $request->input('telefono-padre'))
                            : null)),
                'prefijo_id' => $request->input('prefijo-representante')
                    ?: $request->input('prefijo-madre')
                    ?: $request->input('prefijo-padre'),
                'telefono_dos' => $request->filled('telefono_dos')
                    ? preg_replace('/^0+/', '', (string)$request->input('telefono_dos'))
                    : null,
                'prefijo_dos_id' => $request->input('prefijo_dos') ?: null,
                'isLegalRepresentative' => $isLegalRepresentative
            ]);

            $persona->update([
                'primer_nombre' => $request->input('primer-nombre-representante'),
                'segundo_nombre' => $request->input('segundo-nombre-representante'),
                'tercer_nombre' => $request->input('tercer-nombre-representante'),
                'primer_apellido' => $request->input('primer-apellido-representante'),
                'segundo_apellido' => $request->input('segundo-apellido-representante'),
                'numero_documento' => $request->input('numero_documento-representante'),
                'fecha_nacimiento' => $this->parseDate($request->input('fecha-nacimiento-representante')),
                'genero_id' => $request->input('sexo-representante'),
                'tipo_documento_id' => $request->input('tipo-ci-representante'),
                'prefijo_id' => $request->input('prefijo_telefono')
                    ?: $request->input('prefijo-representante')
                    ?: $request->input('prefijo-madre')
                    ?: $request->input('prefijo-padre'),
                'telefono' => $request->input('telefono_movil')
                    ? preg_replace('/^0+/', '', (string)$request->input('telefono_movil'))
                    : ($request->input('telefono-representante')
                        ? preg_replace('/^0+/', '', (string)$request->input('telefono-representante'))
                        : ($request->input('telefono-madre')
                            ? preg_replace('/^0+/', '', $request->input('telefono-madre'))
                            : ($request->input('telefono-padre')
                                ? preg_replace('/^0+/', '', $request->input('telefono-padre'))
                                : null))),
                'telefono_dos' => $request->filled('telefono_dos')
                    ? preg_replace('/^0+/', '', (string)$request->input('telefono_dos'))
                    : null,
                'prefijo_dos_id' => $request->input('prefijo_dos') ?: null,
                'email' => $request->input('correo-representante'),
                'localidad_id' => $request->input('idparroquia-representante') ?: $request->input('idparroquia-padre') ?: $request->input('idparroquia') ?: $request->input('parroquia_id'),
            ]);

            $representanteData = [
                'pais_id' => $request->input('pais_id'),
                'estado_id' => $request->input('estado_id'),
                'municipio_id' => $request->input('municipio_id'),
                'parroquia_id' => $request->input('idparroquia-representante') ?: $request->input('idparroquia-padre') ?: $request->input('idparroquia') ?: $request->input('parroquia_id'),
                'ocupacion_representante' => $request->input('ocupacion_id'),
                'convivenciaestudiante_representante' => $request->input('convive-representante', 'no'),
            ];

            $representante->update($representanteData);
            if ($isLegalRepresentative) {
                $perteneceOrganizacion = $request->boolean('pertenece_organizacion');
                $tipoCuenta = $request->filled('tipo_cuenta')
                    ? $this->obtenerTipoCuenta($request->input('tipo_cuenta'))
                    : ($this->obtenerTipoCuenta($request->input('tipo-cuenta', 'Corriente')));

                $carnetAfiliado = $request->input('carnet_patria_afiliado');
                if (is_numeric($carnetAfiliado)) {
                    $carnetAfiliado = (int) $carnetAfiliado;
                } else {
                    $carnetAfiliado = $this->mapearCarnetPatriaAfiliado($carnetAfiliado);
                }

                $legalData = [
                    'banco_id'                              => $request->input('banco_id') ?: null,
                    'tipo_cuenta'                           => $tipoCuenta ?: 'Corriente',
                    'parentesco'                            => $request->input('parentesco') ?: ($request->input('parentesco_hidden') ?: 'Madre/Padre/Tutor'),
                    'correo_representante'                  => $request->input('correo-representante') ?: ($request->input('correo_representante') ?: ($persona->email ?: '')),
                    'pertenece_a_organizacion_representante'=> $perteneceOrganizacion,
                    'cual_organizacion_representante'       => $perteneceOrganizacion ? ($request->input('cual_organizacion_representante') ?: '') : '',
                    'carnet_patria_afiliado'                => $carnetAfiliado,
                    'serial_carnet_patria_representante'    => substr((string)($request->input('serial_carnet') ?: ($request->input('serial-patria') ?: ($request->input('serial') ?: ''))), 0, 20),
                    'codigo_carnet_patria_representante'    => substr((string)($request->input('codigo_carnet') ?: ($request->input('codigo-patria') ?: ($request->input('codigo') ?: ''))), 0, 20),
                    'direccion_representante'               => $request->input('direccion_habitacion') ?: ($persona->direccion ?: ''),
                    'estados_representante'                 => (string)$request->input('estado_id', ''),
                ];

                if ($representante->legal) {
                    $representante->legal()->update($legalData);
                } else {
                    $representante->legal()->create($legalData);
                }
            } else {
                if ($representante->legal) {
                    $representante->legal()->delete();
                }
            }

            Log::info('=== ESTADO FINAL ANTES DE COMMIT ===', [
                'representante_id' => $representante->id,
                'persona_id' => $persona->id,
                'telefono' => $persona->telefono,
                'telefono_dos' => $persona->telefono_dos,
                'prefijo_id' => $persona->prefijo_id,
                'prefijo_dos_id' => $persona->prefijo_dos_id,
                'is_dirty' => $persona->isDirty()
            ]);

            DB::commit();

            $persona->refresh();
            Log::info('=== REPRESENTANTE ACTUALIZADO EXITOSAMENTE ===', [
                'representante_id' => $representante->id,
                'persona_id' => $persona->id,
                'telefono' => $persona->telefono,
                'telefono_dos' => $persona->telefono_dos,
                'prefijo_id' => $persona->prefijo_id,
                'prefijo_dos_id' => $persona->prefijo_dos_id,
                'updated_at' => $persona->updated_at
            ]);

            Log::info('FROM DEBUG', [
                'from' => $request->from,
                'inscripcion_id' => $request->inscripcion_id,
            ]);


            switch ($request->from) {
                case 'inscripcion_edit':
                    $redirect = route('admin.transacciones.inscripcion.edit', $request->inscripcion_id);
                    break;
                default:
                    $redirect = route('representante.index');
            }


            return response()->json([
                'success' => true,
                'message' => 'Representante actualizado exitosamente',
                'redirect' => $redirect
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar representante: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el representante: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save(Request $request)
    {

        Log::info('=== ACCEDIENDO A save() ===');
        Log::info('Route name:', [$request->route()->getName()]);
        Log::info('Route action:', [$request->route()->getActionName()]);
        Log::info('Request path:', [$request->path()]);
        Log::info('Request method:', [$request->method()]);
        $isUpdate = $request->has('id') || $request->has('representante_id');
        $id = $request->input('id', $request->input('representante_id'));

        Log::info('=== ' . ($isUpdate ? 'ACTUALIZANDO' : 'CREANDO') . ' REPRESENTANTE ===', [
            'request_data' => $request->all(),
            'method' => $request->method(),
            'wants_json' => $request->wantsJson(),
            'is_ajax' => $request->ajax(),
            'content_type' => $request->header('Content-Type'),
            'is_update' => $id !== null,
            'id' => $id
        ]);

        Log::info('Valores de teléfono recibidos:', [
            'telefono-representante' => $request->input('telefono-representante'),
            'telefono-madre' => $request->input('telefono-madre'),
            'telefono-padre' => $request->input('telefono-padre'),
            'telefono_dos' => $request->input('telefono_dos'),
            'prefijo_dos' => $request->input('prefijo_dos')
        ]);

        $tipoRepresentante = $request->input('tipo_representante');
        $esProgenitorRepresentante = ($tipoRepresentante === 'progenitor_representante');
        $numero_documentoRepresentante = $request->input('numero_documento-representante');
        $numero_documentoMadre = $request->input('numero_documento');
        $numero_documentoPadre = $request->input('numero_documento-padre');
        $usandoDatosMadre = !empty($numero_documentoMadre) && $numero_documentoMadre === $numero_documentoRepresentante;
        $usandoDatosPadre = !empty($numero_documentoPadre) && $numero_documentoPadre === $numero_documentoRepresentante;
        $numero_documentoProgenitor = null;
        $tipoProgenitor = null;

        if ($usandoDatosMadre) {
            $numero_documentoProgenitor = $numero_documentoMadre;
            $tipoProgenitor = 'madre';
        } elseif ($usandoDatosPadre) {
            $numero_documentoProgenitor = $numero_documentoPadre;
            $tipoProgenitor = 'padre';
        }

        if ($esProgenitorRepresentante && !$numero_documentoProgenitor && $numero_documentoRepresentante) {
            if ($numero_documentoRepresentante === $numero_documentoMadre) {
                $numero_documentoProgenitor = $numero_documentoMadre;
                $tipoProgenitor = 'madre';
                $usandoDatosMadre = true;
            } elseif ($numero_documentoRepresentante === $numero_documentoPadre) {
                $numero_documentoProgenitor = $numero_documentoPadre;
                $tipoProgenitor = 'padre';
                $usandoDatosPadre = true;
            }
        }

        Log::info('Tipo de representante:', [
            'tipo_representante' => $tipoRepresentante,
            'esProgenitorRepresentante' => $esProgenitorRepresentante,
            'usandoDatosMadre' => $usandoDatosMadre,
            'usandoDatosPadre' => $usandoDatosPadre,
            'numero_documentoProgenitor' => $numero_documentoProgenitor,
            'tipoProgenitor' => $tipoProgenitor,
            'numero_documentoRepresentante' => $numero_documentoRepresentante,
            'numero_documentoMadre' => $numero_documentoMadre,
            'numero_documentoPadre' => $numero_documentoPadre
        ]);

        $fechaNacimientoRaw = $request->input('fecha-nacimiento-representante');
        Log::info('Fecha de nacimiento recibida del formulario:', [
            'fecha-nacimiento-representante' => $fechaNacimientoRaw,
            'parseDate_result' => $this->parseDate($fechaNacimientoRaw)
        ]);

        $fechaNacimientoParseada = $this->parseDate($fechaNacimientoRaw);
        if ($fechaNacimientoParseada === null) {
            Log::warning('parseDate devolvió null, usando fecha actual como fallback');
            $fechaNacimientoParseada = now()->subYears(18)->format('Y-m-d');
        }

        $request->merge([
            'numero_numero_documento_persona' => $request->input('numero_documento-representante'),
            'nombre_uno'            => $request->input('primer-nombre-representante'),
            'nombre_dos'            => $request->input('segundo-nombre-representante'),
            'nombre_tres'           => $request->input('tercer-nombre-representante'),
            'apellido_uno'          => $request->input('primer-apellido-representante'),
            'apellido_dos'          => $request->input('segundo-apellido-representante'),
            'fecha_nacimiento' => $fechaNacimientoParseada,
            'fecha_nacimiento_personas' => $fechaNacimientoParseada,
            'sexo_representante'    => $request->input('sexo-representante')
                ?: $request->input('sexo')
                ?: $request->input('genero-padre'),
            'tipo_numero_documento_persona'   => $request->input('tipo-ci-representante'),

            'pais_id'     => $request->input('idPais-representante') ?: $request->input('idPais-padre') ?: $request->input('idPais'),
            'estado_id'    => $request->input('idEstado-representante') ?: $request->input('idEstado-padre') ?: $request->input('idEstado'),
            'municipio_id' => $request->input('idMunicipio-representante') ?: $request->input('idMunicipio-padre') ?: $request->input('idMunicipio'),
            'parroquia_id' => $request->input('idparroquia-representante') ?: $request->input('idparroquia-padre') ?: $request->input('idparroquia'),
            'telefono' => $request->input('telefono-representante')
                ? preg_replace('/^0+/', '', $request->input('telefono-representante'))
                : ($request->input('telefono-madre')
                    ? preg_replace('/^0+/', '', $request->input('telefono-madre'))
                    : ($request->input('telefono-padre')
                        ? preg_replace('/^0+/', '', $request->input('telefono-padre'))
                        : null)),
            'telefono_dos' => $request->input('telefono_dos')
                ? preg_replace('/^0+/', '', $request->input('telefono_dos'))
                : null,
            'prefijo_dos_id' => $request->input('prefijo_dos'),
            'prefijo_id' => $request->input('prefijo-representante')
                ?: $request->input('prefijo-madre')
                ?: $request->input('prefijo-padre'),
            'ocupacion_representante'             => $request->input('ocupacion-representante'),
            'convivenciaestudiante_representante' => $request->input('convive-representante'),
            'correo_representante'                    => $request->input('correo-representante'),
            'pertenece_a_organizacion_representante' => $request->input('organizacion-representante') === 'si' ? 1 : 0,
            'cual_organizacion_representante'        => $request->input('especifique-organizacion'),
            'carnet_patria_afiliado'             => $request->input('carnet-patria-afiliado'),
            'serial_carnet_patria_representante' => $request->input('serial-patria') ?: $request->input('serial'),
            'codigo_carnet_patria_representante' => $request->input('codigo-patria') ?: $request->input('codigo'),
            'banco_id'                           => $request->input('banco_id'),
            'direccion_representante'            => $request->input('direccion-habitacion'),
            'persona_id'       => $request->input('persona-id-representante'),
            'representante_id' => $request->input('representante-id'),
            'es_representate_legal' => true,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            Log::info('Es petición AJAX/JSON');

            $request->merge([
                'pais_id' => (int) $request->input('pais_id'),
                'estado_id' => (int) $request->input('estado_id'),
                'municipio_id' => (int) $request->input('municipio_id'),
                'parroquia_id' => (int) ($request->input('idparroquia-representante') ?: $request->input('idparroquia-padre') ?: $request->input('idparroquia') ?: $request->input('parroquia_id')),
            ]);

            Log::info('Verificando existencia de IDs:', [
                'estado_id' => $request->estado_id,
                'estado_existe' => DB::table('estados')->where('id', $request->estado_id)->exists(),
                'municipio_id' => $request->municipio_id,
                'municipio_existe' => DB::table('municipios')->where('id', $request->municipio_id)->exists(),
                'localidad_id' => $request->localidad_id,
                'localidad_existe' => DB::table('localidads')->where('id', $request->localidad_id)->exists(),
            ]);

            $personaId = $request->input('persona_id');
            $currentDocument = $personaId ? Persona::find($personaId)->numero_documento : null;
            $newDocument = $request->input('numero_documento-representante');

            $rules = [
                'numero_documento-representante' => [
                    'required',
                    'string',
                    'max:20',
                    function ($attribute, $value, $fail) use ($currentDocument, $newDocument, $personaId) {
                        ($currentDocument !== $newDocument) &&
                            Rule::unique('personas', 'numero_documento')->ignore($personaId);

                        if (!preg_match('/^\d{6,8}$/', $value)) {
                            $fail('El número de cédula debe contener entre 6 y 8 dígitos.');
                        }
                    },
                ],
                'primer-nombre-representante' => 'required|string|max:50',
                'primer-apellido-representante' => 'required|string|max:50',
                'fecha-nacimiento-representante' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!$this->isValidDate($value)) {
                            $fail('El formato de la fecha debe ser DD/MM/YYYY');
                        }
                    }
                ],
                'fecha_nacimiento' => 'required|date',
                'telefono-representante' => 'required|string|max:20',
                'sexo-representante' => 'required|exists:generos,id',
                'tipo-ci-representante' => 'required|exists:tipos_documentos,id',
                'pais_id' => 'required|exists:pais,id',
                'estado_id' => 'required|exists:estados,id',
                'municipio_id' => 'required|exists:municipios,id',
                'idparroquia-representante' => 'required_without_all:idparroquia-padre,idparroquia|exists:parroquias,id',
                'idparroquia-padre' => 'required_without_all:idparroquia-representante,idparroquia|exists:parroquias,id',
                'idparroquia' => 'required_without_all:idparroquia-representante,idparroquia-padre|exists:parroquias,id',
                'direccion-habitacion' => 'required|string|max:255',
                'convive-representante' => 'required|in:si,no',
                'ocupacion-representante' => 'required|exists:ocupacions,id',
                'correo-representante' => 'required|email|max:100',
                'carnet-patria-afiliado' => 'nullable',
                'codigo-patria' => 'required_unless:carnet-patria-afiliado,0|nullable|string|max:20',
                'serial-patria' => 'required_unless:carnet-patria-afiliado,0|nullable|string|max:20',
                'tipo_numero_documento_persona' => 'required|exists:tipo_documentos,id',
                'lugar-nacimiento-padre' => 'nullable|string|max:255|regex:/^[A-Za-záéíóúÁÉÍÓÚñÑ\s,]+$/',
            ];

            $messages = [
                'primer-nombre-representante.required' => 'El primer nombre es obligatorio',
                'primer-apellido-representante.required' => 'El primer apellido es obligatorio',
                'fecha-nacimiento-representante.required' => 'La fecha de nacimiento es obligatoria',
                'fecha-nacimiento-representante.date_format' => 'El formato de fecha debe ser DD/MM/YYYY',
                'telefono-representante.required' => 'El teléfono es obligatorio',
                'sexo-representante.required' => 'El género es obligatorio',
                'tipo-ci-representante.required' => 'El tipo de documento es obligatorio',
                'pais_id.required' => 'El país es obligatorio',
                'pais_id.exists' => 'El país seleccionado no es válido',
                'estado_id.required' => 'El estado es obligatorio',
                'municipio_id.required' => 'El municipio es obligatorio',
                'idparroquia-representante.required_without_all' => 'La parroquia es obligatoria',
                'idparroquia-padre.required_without_all' => 'La parroquia es obligatoria',
                'idparroquia.required_without_all' => 'La parroquia es obligatoria',
                'direccion-habitacion.required' => 'La dirección es obligatoria',
                'convive-representante.required' => 'Debe indicar si convive con el estudiante',
                'ocupacion-representante.required' => 'La ocupación es obligatoria',
                'correo-representante.required' => 'El correo electrónico es obligatorio',
                'correo-representante.email' => 'El correo electrónico no es válido',
                'codigo-patria.required_unless' => 'El código es obligatorio cuando el carnet está afiliado',
                'serial-patria.required_unless' => 'El serial es obligatorio cuando el carnet está afiliado',
                'numero_numero_documento_persona.required' => 'El número de cédula es obligatorio',
                'numero_numero_documento_persona.unique' => 'Este número de cédula ya está registrado',
                'numero_numero_documento_persona.required' => 'El número de documento es obligatorio',
                'primer-nombre-representante.required' => 'El primer nombre es obligatorio',
                'primer-apellido-representante.required' => 'El primer apellido es obligatorio',
                'fecha-nacimiento-representante.required' => 'La fecha de nacimiento es obligatoria',
                'fecha-nacimiento-representante' => 'El formato de la fecha debe ser DD/MM/YYYY',
                'telefono-representante.required' => 'El teléfono es obligatorio',
                'correo-representante.required' => 'El correo electrónico es obligatorio',
                'correo-representante.email' => 'El correo electrónico debe ser una dirección válida',
                'estado_id.required' => 'El estado es obligatorio',
                'municipio_id.required' => 'El municipio es obligatorio',
                'tipo_numero_documento_persona.required' => 'El tipo de documento es obligatorio',
                'lugar-nacimiento-padre.regex' => 'El lugar de nacimiento solo puede contener letras, espacios y comas',
            ];

            $validator = \Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                $errorMessage = $validator->errors()->first();
                $allErrors = $validator->errors()->all();

                Log::error('Errores de validación al guardar representante', [
                    'errors' => $validator->errors()->toArray(),
                    'all_errors' => $allErrors,
                    'request_data' => $request->except(['_token', 'password', 'password_confirmation']),
                    'error_message' => $errorMessage,
                    'is_progenitor_representante' => $esProgenitorRepresentante,
                    'tipo_representante' => $tipoRepresentante,
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $errorMessage,
                        'errors' => $validator->errors(),
                        'all_errors' => $allErrors
                    ], 422);
                }



                return redirect()->back()
                    ->with('error', 'Error de validación: ' . $errorMessage)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        Log::info('Campos geográficos recibidos:', [
            'pais_id' => $request->pais_id,
            'estado_id' => $request->estado_id,
            'municipio_id' => $request->municipio_id,
            'parroquia_id' => $request->parroquia_id
        ]);

        $numero_documento = $request->input('numero_documento-representante');
        $personaId = $request->id ?? $request->persona_id;
        $personaExistente = Persona::where('numero_documento', $numero_documento)
            ->when($personaId, function ($q) use ($personaId) {
                $q->where('id', '!=', $personaId);
            })
            ->first();

        if ($personaExistente) {
            $tieneRepresentanteActivo = $personaExistente->representante()
                ->where('status', '!=', 0)
                ->whereNull('deleted_at')
                ->exists();

            if ($tieneRepresentanteActivo && !$esProgenitorRepresentante) {
                Log::warning('Intento de registrar cédula duplicada', [
                    'numero_documento' => $numero_documento,
                    'persona_existente_id' => $personaExistente->id,
                    'persona_actual_id' => $personaId,
                    'esProgenitorRepresentante' => $esProgenitorRepresentante,
                    'tieneRepresentanteActivo' => $tieneRepresentanteActivo
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Error de validación',
                        'errors' => [
                            'numero_documento-representante' => ['Esta cédula ya está registrada en el sistema']
                        ]
                    ], 422);
                } else {
                    return redirect()->back()
                        ->withErrors(['numero_documento-representante' => 'Esta cédula ya está registrada en el sistema'])
                        ->withInput();
                }
            }

            $request->merge(['persona_id' => $personaExistente->id]);
            $request->merge(['persona-id-representante' => $personaExistente->id]);
        }

        $tipoRepresentante = $request->input('tipo_representante');
        $datosPersona = [
            "id" => $request->id ?? $request->input('persona-id-representante')
        ];

        if ($tipoRepresentante === 'progenitor_madre_representante') {
            Log::info('Inputs recibidos para madre:', $request->all());

            $datosPersona = array_merge($datosPersona, [
                "primer_nombre" => $request->input('primer-nombre'),
                "segundo_nombre" => $request->input('segundo-nombre'),
                "prefijo_id" => $request->input('prefijo'),
                "tercer_nombre" => $request->input('tercer-nombre'),
                "primer_apellido" => $request->input('primer-apellido'),
                "segundo_apellido" => $request->input('segundo-apellido'),
                "numero_documento" => $request->input('numero_documento'),
                "fecha_nacimiento" => $this->parseDate($request->input('fechaNacimiento')),
                "genero_id" => $request->input('sexo'),
                "localidad_id" => $request->input('idparroquia'),
                "telefono" => $request->input('telefono'),
                "tipo_documento_id" => $request->input('tipo-ci'),
                "direccion" => $request->input('direccion-habitacion'),
                "email" => $request->input('correo-representante'),
            ]);

            Log::info('Datos procesados para madre:', $datosPersona);
        }
        // Si el padre es el representante y la madre está ausente
        elseif ($tipoRepresentante === 'progenitor_padre_representante') {
            $datosPersona = array_merge($datosPersona, [
                "primer_nombre" => $request->input('primer-nombre-padre'),
                "segundo_nombre" => $request->input('segundo-nombre-padre'),
                "prefijo_id" => $request->input('prefijo-padre'),
                "tercer_nombre" => $request->input('tercer-nombre-padre'),
                "primer_apellido" => $request->input('primer-apellido-padre'),
                "segundo_apellido" => $request->input('segundo-apellido-padre'),
                "numero_documento" => $request->input('numero_documento-padre'),
                "fecha_nacimiento" => $this->parseDate($request->input('fecha-nacimiento-padre')),
                "genero_id" => $request->input('sexo-padre'),
                "localidad_id" => $request->input('idparroquia-padre'),
                "telefono" => $request->input('telefono-padre'),
                "tipo_documento_id" => $request->input('tipo-ci-padre'),
                "direccion" => $request->input('lugar-nacimiento-padre') ?? $request->input('direccion-padre') ?? $request->input('direccion-habitacion'),
                "email" => $request->input('correo-padre') ?? $request->input('correo-representante'),
            ]);
        } else {
            $datosPersona = array_merge($datosPersona, [
                "primer_nombre" => $request->input('primer-nombre-representante'),
                "segundo_nombre" => $request->input('segundo-nombre-representante'),
                "prefijo_id" => $request->input('prefijo-representante') ?: $request->input('prefijo_telefono'),
                "tercer_nombre" => $request->input('tercer-nombre-representante'),
                "primer_apellido" => $request->input('primer-apellido-representante'),
                "segundo_apellido" => $request->input('segundo-apellido-representante'),
                "numero_documento" => $request->input('numero_documento-representante'),
                "fecha_nacimiento" => $this->parseDate($request->input('fecha-nacimiento-representante')),
                "genero_id" => $request->input('sexo-representante'),
                "localidad_id" => $request->input('idparroquia-representante'),
                "telefono" => $request->input('telefono-representante') ?: $request->input('telefono_movil'),
                "tipo_documento_id" => $request->input('tipo-ci-representante'),
                "direccion" => $request->input('direccion-habitacion'),
                "email" => $request->input('correo-representante'),
            ]);
        }

        $status = 1;
        $tipoRepresentante = $request->input('tipo_representante');

        if ($request->representante_id) {
            $representanteExistente = Representante::find($request->representante_id);
            if ($representanteExistente) {
                $status = $representanteExistente->status;
            }
        }

        if ($request->has('status')) {
            $status = $request->input('status');
        } elseif (!$request->representante_id) {
            if ($tipoRepresentante === 'progenitor_padre_representante') {
                $status = 2;
            } elseif ($tipoRepresentante === 'progenitor_madre_representante') {
                $status = 3;
            }
        }

        $datosRepresentante = [
            "pais_id" => $request->pais_id,
            "estado_id" => $request->estado_id ?: 1,
            "municipio_id" => $request->municipio_id,
            "parroquia_id" => $request->parroquia_id,
            "ocupacion_representante" => $request->input('ocupacion-madre')
                ?: $request->input('ocupacion-padre')
                ?: $request->input('ocupacion-representante')
                ?: null,
            "convivenciaestudiante_representante" => $request->convivenciaestudiante_representante ?: 'no',
            "status" => $status,
        ];

        if ($request->representante_id) {
            $datosRepresentante["id"] = $request->representante_id;
        }

        Log::info('Datos procesados:', [
            'datosPersona' => $datosPersona,
            'datosRepresentante' => $datosRepresentante,
            'es_representate_legal' => $request->es_representate_legal
        ]);

        $perteneceOrganizacion = $request->pertenece_a_organizacion_representante ?: 0;
        $cualOrganizacion = '';
        if ($perteneceOrganizacion == 1) {
            $cualOrganizacion = $request->cual_organizacion_representante ?: '';
        }

        $datosRepresentanteLegal = [
            "banco_id" => $request->banco_id && $request->banco_id != '' ? $request->banco_id : null,
            "parentesco" => $request->parentesco ?: ($request->parentesco_hidden ?: 'No especificado'),
            "correo_representante" => $request->correo_representante ?: '',
            "pertenece_a_organizacion_representante" => $perteneceOrganizacion,
            "cual_organizacion_representante" => $cualOrganizacion,
            "carnet_patria_afiliado" => $this->mapearCarnetPatriaAfiliado($request->carnet_patria_afiliado),
            "serial_carnet_patria_representante" => $request->input('serial-patria') ?: ($request->input('serial') ?: ''),
            "codigo_carnet_patria_representante" => $request->input('codigo-patria') ?: ($request->input('codigo') ?: ''),
            "direccion_representante" => $request->direccion_representante ?: '',
            "estados_representante" => $request->estados_representante ?: '',
            "tipo_cuenta" => $this->obtenerTipoCuenta($request->input('tipo-cuenta', '')),
        ];

        $serialCarnet = (string) ($datosRepresentanteLegal['serial_carnet_patria_representante'] ?? '');
        $codigoCarnet = (string) ($datosRepresentanteLegal['codigo_carnet_patria_representante'] ?? '');

        if (strlen($serialCarnet) > 20 || strlen($codigoCarnet) > 20) {
            $errors = [];
            if (strlen($serialCarnet) > 20) {
                $errors['serial-patria'] = ['El serial del carnet de la patria no puede exceder 20 caracteres.'];
            }
            if (strlen($codigoCarnet) > 20) {
                $errors['codigo-patria'] = ['El código del carnet de la patria no puede exceder 20 caracteres.'];
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $errors,
                ], 422);
            }

            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }

        $datosRepresentanteLegal['serial_carnet_patria_representante'] = substr($serialCarnet, 0, 20);
        $datosRepresentanteLegal['codigo_carnet_patria_representante'] = substr($codigoCarnet, 0, 20);

        if ($request->representante_legal_id) {
            $datosRepresentanteLegal["id"] = $request->representante_legal_id;
        }

        $mensaje = '';
        $persona = null;
        $isUpdate = false;
        $representante = null;

        DB::beginTransaction();
        try {
            if ($esProgenitorRepresentante) {
                if (!$numero_documentoProgenitor) {
                    $errorMsg = 'No se pudo determinar la cédula del progenitor. Asegúrese de que la cédula del representante coincida con la de la madre o el padre.';
                    Log::error($errorMsg, [
                        'numero_documento_representante' => $numero_documentoRepresentante,
                        'numero_documento_madre' => $numero_documentoMadre,
                        'numero_documento_padre' => $numero_documentoPadre
                    ]);
                    throw new \Exception($errorMsg);
                }

                Log::info('Procesando progenitor como representante', [
                    'numero_documento' => $numero_documentoProgenitor,
                    'tipo_progenitor' => $tipoProgenitor
                ]);

                $fechaNacimiento = $request->input('fecha-nacimiento-padre');
                $prefijoId = $request->input('prefijo-padre');

                $datosCompletos = !empty($datosPersona['primer_nombre']) &&
                    !empty($datosPersona['primer_apellido']) &&
                    !empty($fechaNacimiento);

                if ($fechaNacimiento) {
                    $datosPersona['fecha_nacimiento'] = $fechaNacimiento;
                }

                if ($prefijoId) {
                    $datosPersona['prefijo_id'] = $prefijoId;
                }

                if ($datosCompletos) {
                    Log::info('Usando datos completos del formulario para el progenitor', [
                        'numero_documento' => $numero_documentoProgenitor,
                        'nombres' => $datosPersona['primer_nombre'] . ' ' . $datosPersona['primer_apellido']
                    ]);

                    $persona = Persona::updateOrCreate(
                        ['numero_documento' => $numero_documentoProgenitor],
                        $datosPersona
                    );
                    $isUpdate = true;
                } else {
                    Log::info('Buscando datos del progenitor en la base de datos', [
                        'numero_documento' => $numero_documentoProgenitor
                    ]);

                    $persona = Persona::where('numero_documento', $numero_documentoProgenitor)->first();

                    if ($persona) {
                        $isUpdate = true;
                        Log::info('Progenitor encontrado en la base de datos', [
                            'persona_id' => $persona->id,
                            'nombres' => $persona->nombre_uno . ' ' . $persona->apellido_uno
                        ]);

                        $camposActualizables = [
                            'telefono',
                            'correo_persona',
                            'direccion_habitacion',
                            'estado_id',
                            'municipio_id',
                            'parroquia_id'
                        ];

                        foreach ($camposActualizables as $campo) {
                            if (!empty($datosPersona[$campo])) {
                                $persona->$campo = $datosPersona[$campo];
                            }
                        }

                        $persona->save();
                    } else {
                        Log::info('Creando nuevo registro para el progenitor', [
                            'numero_documento' => $numero_documentoProgenitor,
                            'tipo_progenitor' => $tipoProgenitor
                        ]);
                        $datosPersona = array_merge([
                            'numero_documento' => $numero_documentoProgenitor,
                            'status' => true,
                            'tipo_documento_id' => $datosPersona['tipo_documento_id'] ?? 1,
                            'genero_id' => $datosPersona['genero_id'] ?? 1,
                            'localidad_id' => $datosPersona['localidad_id'] ?? 1,
                            'prefijo_id' => $datosPersona['prefijo_id'] ?? 1,
                            'primer_nombre' => $datosPersona['primer_nombre'] ?? 'SIN NOMBRE',
                            'primer_apellido' => $datosPersona['primer_apellido'] ?? 'SIN APELLIDO',
                            'fecha_nacimiento' => $datosPersona['fecha_nacimiento'] ?? now()->subYears(18)->format('Y-m-d')
                        ], $datosPersona);

                        try {
                            $persona = new Persona();
                            $persona->fill($datosPersona);
                            if (isset($datosPersona['telefono_dos'])) {
                                $persona->telefono_dos = $datosPersona['telefono_dos'];
                            }
                            if (isset($datosPersona['prefijo_dos_id'])) {
                                $persona->prefijo_dos_id = $datosPersona['prefijo_dos_id'];
                            }

                            $persona->save();
                            $isUpdate = false;

                            Log::info('Nuevo registro creado para el progenitor', [
                                'persona_id' => $persona->id,
                                'numero_documento' => $persona->numero_documento,
                                'nombres' => $persona->primer_nombre . ' ' . $persona->primer_apellido
                            ]);
                        } catch (\Exception $e) {
                            $errorMsg = "Error al crear el registro del {$tipoProgenitor} con cédula {$numero_documentoProgenitor}: " . $e->getMessage();
                            Log::error($errorMsg, [
                                'exception' => $e,
                                'datos_persona' => $datosPersona
                            ]);

                            return response()->json([
                                'success' => false,
                                'message' => $errorMsg
                            ], 500);
                        }
                    }
                }

                $esProgenitorNoRepresentante = false;
                $tipoProgenitor = null;
                $tipoRepresentante = $request->input('tipo_representante');
                $esRepresentanteLegal = in_array($tipoRepresentante, ['representante_legal', 'progenitor_representante', 'progenitor_madre_representante', 'progenitor_padre_representante']);

                Log::info('Validando tipo de representante', [
                    'tipo_representante' => $tipoRepresentante,
                    'esRepresentanteLegal' => $esRepresentanteLegal,
                    'numero_documento' => $request->input('numero_documento'),
                    'numero_documento_representante' => $request->input('numero_documento-representante'),
                    'numero_documento_padre' => $request->input('numero_documento-padre')
                ]);
                $datosRepresentante['status'] = 1;
                if (
                    $request->input('estado_madre') === 'Presente' &&
                    $request->input('numero_documento') === $request->input('numero_documento-representante')
                ) {

                    $tipoProgenitor = 'madre';

                    if ($esRepresentanteLegal) {
                        Log::info('Madre es representante legal, manteniendo estado 1', [
                            'numero_documento' => $request->input('numero_documento-representante'),
                            'tipo_representante' => $tipoRepresentante
                        ]);
                        $datosRepresentante['status'] = 1;
                    } else {
                        $esProgenitorNoRepresentante = true;
                        $datosRepresentante['status'] = 3;
                        Log::info('Madre no es representante legal, asignando estado 3', [
                            'numero_documento' => $request->input('numero_documento-representante'),
                            'tipo_representante' => $tipoRepresentante
                        ]);
                    }
                } elseif (
                    $request->input('estado_padre') === 'Presente' &&
                    $request->input('numero_documento-padre') === $request->input('numero_documento-representante')
                ) {

                    $tipoProgenitor = 'padre';

                    if ($esRepresentanteLegal) {
                        Log::info('Padre es representante legal, manteniendo estado 1', [
                            'numero_documento' => $request->input('numero_documento-representante'),
                            'tipo_representante' => $tipoRepresentante
                        ]);
                        $datosRepresentante['status'] = 1;
                    } else {
                        $esProgenitorNoRepresentante = true;
                        $datosRepresentante['status'] = 2;
                        Log::info('Padre no es representante legal, asignando estado 2', [
                            'numero_documento' => $request->input('numero_documento-representante'),
                            'tipo_representante' => $tipoRepresentante
                        ]);
                    }
                }

                // Si es representante legal pero no coincidió con las condiciones anteriores, asegurar status = 1
                if ($esRepresentanteLegal && !isset($tipoProgenitor)) {
                    $datosRepresentante['status'] = 1;
                    Log::info('Representante legal (general), manteniendo estado 1', [
                        'numero_documento' => $request->input('numero_documento-representante'),
                        'tipo_representante' => $tipoRepresentante
                    ]);
                }

                $representante = Representante::updateOrCreate(
                    ['persona_id' => $persona->id],
                    $datosRepresentante
                );

                Log::info('Representante guardado/actualizado', [
                    'id' => $representante->id,
                    'persona_id' => $persona->id,
                    'tipo' => $esProgenitorNoRepresentante ? 'Progenitor ' . $tipoProgenitor : 'Representante legal',
                    'status' => $representante->status
                ]);

                Log::info('Datos del representante actualizados', [
                    'persona_id' => $persona->id,
                    'representante_id' => $representante->id,
                    'tipo_progenitor' => $tipoProgenitor,
                    'usando_datos_formulario' => $datosCompletos ? 'Sí' : 'No',
                    'usando_datos_bd' => !$datosCompletos ? 'Sí' : 'No'
                ]);
            } elseif (!empty($datosPersona["id"])) {
                $persona = Persona::with(['representante', 'representante.legal'])->find($datosPersona["id"]);
                if ($persona) {
                    $isUpdate = true;
                }
            } elseif (!empty($request->input('numero_documento-representante'))) {
                // Si la cédula ya existe en personas (por ejemplo es Docente o Alumno), reutilizar su ID
                $persona = Persona::with(['representante', 'representante.legal'])
                    ->where('numero_documento', $request->input('numero_documento-representante'))
                    ->first();
                if ($persona) {
                    $isUpdate = true;
                }
            }

            if ($isUpdate && !$esProgenitorRepresentante) {
                Log::info('=== MODO ACTUALIZACIÓN NORMAL ===');
                Log::info('Actualizando persona con datos:', [
                    'persona_id' => $persona->id,
                    'datos_persona' => $datosPersona
                ]);

                $persona->fill($datosPersona);

                if (isset($datosPersona['telefono_dos'])) {
                    $persona->telefono_dos = $datosPersona['telefono_dos'];
                } else {
                    $persona->telefono_dos = null;
                }

                if (isset($datosPersona['prefijo_dos_id'])) {
                    $persona->prefijo_dos_id = $datosPersona['prefijo_dos_id'];
                } else {
                    $persona->prefijo_dos_id = null;
                }

                $persona->save();
                Log::info('Persona actualizada: ID ' . $persona->id, [
                    'telefono_dos' => $persona->telefono_dos,
                    'prefijo_dos_id' => $persona->prefijo_dos_id
                ]);

                $representante = Representante::where('persona_id', $persona->id)->first();

                if ($representante) {
                    $representante->update($datosRepresentante);
                    Log::info('Representante actualizado: ID ' . $representante->id);
                } else {
                    $datosRepresentante["persona_id"] = $persona->id;
                    $representante = Representante::create($datosRepresentante);
                    Log::info('Representante creado: ID ' . $representante->id);
                }
            } elseif ($esProgenitorRepresentante) {
                Log::info('=== MODO PROGENITOR COMO REPRESENTANTE ===');
                Log::info('Datos del progenitor actualizados como representante', [
                    'persona_id' => $persona->id,
                    'representante_id' => $representante->id
                ]);

                if ($request->es_representate_legal == true) {
                    $representanteLegal = RepresentanteLegal::where('representante_id', $representante->id)->first();

                    if ($representanteLegal) {
                        $representanteLegal->update($datosRepresentanteLegal);
                        Log::info('Representante legal actualizado: ID ' . $representanteLegal->id);
                    } else {
                        $datosRepresentanteLegal["representante_id"] = $representante->id;
                        $representanteLegal = new RepresentanteLegal($datosRepresentanteLegal);
                        $representante->legal()->save($representanteLegal);
                        Log::info('Nuevo representante legal creado: ID ' . $representanteLegal->id);
                    }
                } else if ($esProgenitorRepresentante && $request->es_representate_legal) {
                    $datosRepresentanteLegal["representante_id"] = $representante->id;
                    $representanteLegal = new RepresentanteLegal($datosRepresentanteLegal);
                    $representante->legal()->save($representanteLegal);
                    Log::info('Progenitor registrado también como representante legal: ID ' . $representanteLegal->id);
                } else {
                    $representante->legal()->delete();
                    Log::info('Representante legal eliminado');
                }

                $mensaje = "Los datos del representante han sido actualizados exitosamente";
            } else {
                $mensaje = $isUpdate ? 'Representante actualizado exitosamente' : 'Representante creado exitosamente';
                Log::info('=== MODO CREACIÓN ===');

                Log::info('=== DATOS DEL REQUEST ===', $request->all());

                $telefono = $request->input('telefono-representante');

                // Si el representante legal es padre/madre, los campos del representante suelen estar deshabilitados
                // y NO se envían en el request; por eso tomamos el teléfono desde el progenitor.
                if (is_null($telefono)) {
                    if ($tipoRepresentante === 'progenitor_padre_representante') {
                        $telefono = $request->input('telefono-padre');
                    } elseif ($tipoRepresentante === 'progenitor_madre_representante') {
                        $telefono = $request->input('telefono');
                    }
                }

                Log::info('Valor de teléfono encontrado en el request:', ['telefono-representante' => $telefono]);

                if (is_null($telefono) && isset($datosPersona['telefono'])) {
                    $telefono = $datosPersona['telefono'];
                    Log::info('Usando teléfono existente de datosPersona:', ['telefono' => $telefono]);
                }

                // Asegurar que los campos requeridos tengan valores por defecto
                $datosPersonaFiltrados = array_filter($datosPersona, static fn($v) => $v !== null);

                $datosPersona = array_merge([
                    'primer_nombre' => 'SIN NOMBRE',
                    'primer_apellido' => 'SIN APELLIDO',
                    'fecha_nacimiento' => now()->subYears(18)->format('Y-m-d'),
                    'tipo_documento_id' => 1,
                    'genero_id' => 1,
                    'localidad_id' => 1,
                    'prefijo_id' => 1,
                    'telefono' => $telefono,
                    'telefono_dos' => $request->input('telefono_dos-representante') ?: $request->input('telefono_dos') ?: $request->input('telefono_dos_padre'),
                    'prefijo_dos_id' => $request->input('prefijo_dos-representante') ?: $request->input('prefijo_dos') ?: $request->input('prefijo_dos_padre'),
                    'status' => true
                ], $datosPersonaFiltrados);

                // 1. Crear persona con asignación directa
                Log::info('Creando nueva persona con datos:', [
                    'datos_persona' => $datosPersona
                ]);


                $persona = new Persona();
                $persona->fill($datosPersona);
                $persona->telefono = $telefono;
                $persona->telefono_dos = $request->input('telefono_dos');
                $persona->prefijo_dos_id = $request->input('prefijo_dos');
                $persona->save();

                Log::info('Datos guardados en la persona:', [
                    'telefono' => $persona->telefono,
                    'telefono_dos' => $persona->telefono_dos,
                    'prefijo_dos_id' => $persona->prefijo_dos_id
                ]);

                Log::info('Persona creada: ID ' . $persona->id);

                $datosRepresentante["persona_id"] = $persona->id;
                $representante = Representante::create($datosRepresentante);
                Log::info('Representante creado: ID ' . $representante->id);
                if ($request->es_representate_legal == true) {
                    $datosRepresentanteLegal["representante_id"] = $representante->id;
                    $representanteLegal = new RepresentanteLegal($datosRepresentanteLegal);
                    $representante->legal()->save($representanteLegal);
                    Log::info('Representante legal creado: ID ' . $representanteLegal->id);
                }

                $mensaje = "Representante registrado exitosamente";
            }

            $numero_documentoMadre = $request->input('numero_documento');
            if ($numero_documentoMadre) {
                Log::info('Procesando datos de la madre', ['numero_documento' => $numero_documentoMadre]);

                $personaMadre = Persona::firstOrNew(['numero_documento' => $numero_documentoMadre]);

                $personaMadre->primer_nombre    = $request->input('primer-nombre');
                $personaMadre->segundo_nombre   = $request->input('segundo-nombre');
                $personaMadre->tercer_nombre    = $request->input('tercer-nombre');
                $personaMadre->primer_apellido  = $request->input('primer-apellido');
                $personaMadre->segundo_apellido = $request->input('segundo-apellido');
                $personaMadre->fecha_nacimiento = $request->input('fechaNacimiento');
                $personaMadre->genero_id        = $request->input('sexo');
                $personaMadre->localidad_id     = $request->input('idparroquia');
                $personaMadre->telefono         = $request->input('telefono');
                $personaMadre->telefono_dos     = $request->input('telefono_dos');
                $personaMadre->prefijo_dos_id   = $request->input('prefijo_dos');
                $personaMadre->tipo_documento_id = $request->input('tipo-ci');
                $personaMadre->prefijo_id       = $request->input('prefijo');
                $personaMadre->direccion        = $request->input('lugar-nacimiento');
                $personaMadre->status           = $personaMadre->status ?? true;

                Log::info('Datos de la madre a guardar:', [
                    'telefono' => $personaMadre->telefono,
                    'telefono_dos' => $personaMadre->telefono_dos,
                    'prefijo_dos_id' => $personaMadre->prefijo_dos_id,
                    'prefijo_id' => $personaMadre->prefijo_id
                ]);

                $personaMadre->save();

                $representanteMadre = Representante::firstOrNew([
                    'persona_id' => $personaMadre->id,
                ]);

                $esRepresentanteLegal = in_array($request->input('tipo_representante'), ['representante_legal', 'progenitor_representante', 'progenitor_madre_representante', 'progenitor_padre_representante']);

                Log::info('Guardando madre como representante', [
                    'persona_id' => $personaMadre->id,
                    'esRepresentanteLegal' => $esRepresentanteLegal,
                    'tipo_representante' => $request->input('tipo_representante')
                ]);

                $representanteMadre->pais_id = $request->input('idPais');
                $representanteMadre->estado_id = $request->input('idEstado');

                if ($esRepresentanteLegal) {
                    $representanteMadre->status = 1;
                    Log::info('Manteniendo estado 1 para madre representante legal', [
                        'numero_documento' => $numero_documentoMadre,
                        'tipo_representante' => $request->input('tipo_representante')
                    ]);
                } else {
                    $representanteMadre->status = 3;
                    Log::info('Asignando estado 3 a madre no representante legal', [
                        'numero_documento' => $numero_documentoMadre,
                        'tipo_representante' => $request->input('tipo_representante')
                    ]);
                }
                $representanteMadre->municipio_id = $request->input('idMunicipio');
                $representanteMadre->parroquia_id = $request->input('idparroquia');
                $representanteMadre->ocupacion_representante = $request->input('ocupacion-madre');
                $representanteMadre->convivenciaestudiante_representante = $request->input('convive') ?: 'no';
                if ($request->input('estado_madre') === 'Presente' && !$esRepresentanteLegal) {
                    $representanteMadre->status = 3;
                    Log::info('Asignando estado de madre (3) al representante', [
                        'numero_documento' => $numero_documentoMadre,
                        'representante_id' => $representanteMadre->id ?? 'nuevo'
                    ]);
                } else {
                    $representanteMadre->status = 1;
                }

                $representanteMadre->save();

                Log::info('Madre guardada/actualizada como representante', [
                    'persona_id' => $personaMadre->id,
                    'representante_id' => $representanteMadre->id,
                ]);
            }

            $numero_documentoPadre = $request->input('numero_documento-padre');
            if ($numero_documentoPadre) {
                Log::info('Procesando datos del padre', ['numero_documento' => $numero_documentoPadre]);

                $personaPadre = Persona::firstOrNew(['numero_documento' => $numero_documentoPadre]);

                $personaPadre->primer_nombre    = $request->input('primer-nombre-padre');
                $personaPadre->segundo_nombre   = $request->input('segundo-nombre-padre');
                $personaPadre->primer_apellido  = $request->input('primer-apellido-padre');
                $personaPadre->segundo_apellido = $request->input('segundo-apellido-padre');
                $personaPadre->fecha_nacimiento = $request->input('fecha-nacimiento-padre');
                $personaPadre->genero_id        = $request->input('sexo-padre');
                $personaPadre->localidad_id     = $request->input('idparroquia-padre');
                $personaPadre->telefono         = $request->input('telefono-padre');
                $personaPadre->telefono_dos     = $request->input('telefono_dos_padre');
                $personaPadre->prefijo_dos_id   = $request->input('prefijo_dos_padre');
                $personaPadre->tipo_documento_id = $request->input('tipo-ci-padre');
                $personaPadre->prefijo_id       = $request->input('prefijo-padre');
                $personaPadre->direccion        = $request->input('lugar-nacimiento-padre') ?? $request->input('direccion-padre');
                $personaPadre->status           = $personaPadre->status ?? true;

                Log::info('Datos del padre a guardar:', [
                    'telefono' => $personaPadre->telefono,
                    'telefono_dos' => $personaPadre->telefono_dos,
                    'prefijo_dos_id' => $personaPadre->prefijo_dos_id,
                    'prefijo_id' => $personaPadre->prefijo_id
                ]);

                $personaPadre->save();

                $representantePadre = Representante::firstOrNew([
                    'persona_id' => $personaPadre->id,
                ]);

                $esRepresentanteLegal = in_array($request->input('tipo_representante'), ['representante_legal', 'progenitor_representante', 'progenitor_madre_representante', 'progenitor_padre_representante']);

                Log::info('Guardando padre como representante', [
                    'persona_id' => $personaPadre->id,
                    'esRepresentanteLegal' => $esRepresentanteLegal,
                    'tipo_representante' => $request->input('tipo_representante')
                ]);

                $representantePadre->pais_id = $request->input('idPais-padre');
                $representantePadre->estado_id    = $request->input('idEstado-padre');
                $representantePadre->municipio_id = $request->input('idMunicipio-padre');
                $representantePadre->parroquia_id = $request->input('idparroquia-padre');
                $representantePadre->ocupacion_representante = $request->input('ocupacion-padre');
                $representantePadre->convivenciaestudiante_representante = $request->input('convive-padre') ?: 'no';

                if ($esRepresentanteLegal) {
                    $representantePadre->status = 1;
                    Log::info('Manteniendo estado 1 para padre representante legal', [
                        'numero_documento' => $numero_documentoPadre,
                        'tipo_representante' => $request->input('tipo_representante')
                    ]);
                } else if ($request->input('estado_padre') === 'Presente') {
                    $representantePadre->status = 2;
                    Log::info('Asignando estado 2 a padre no representante legal', [
                        'numero_documento' => $numero_documentoPadre,
                        'tipo_representante' => $request->input('tipo_representante')
                    ]);
                } else {
                    $representantePadre->status = 1;
                }

                $representantePadre->save();

                Log::info('Padre guardado/actualizado como representante', [
                    'persona_id' => $personaPadre->id,
                    'representante_id' => $representantePadre->id,
                ]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                $representanteResponse = Representante::with(['persona', 'legal', 'legal.banco'])
                    ->where('persona_id', $persona->id)
                    ->first();

                return response()->json([
                    'status' => 'success',
                    'message' => $mensaje,
                    'data' => $representanteResponse,
                ], 200);
            }

            if ($request->input('from') === 'inscripcion') {
                return redirect()
                    ->route('admin.transacciones.inscripcion.create')
                    ->with('success', 'Representante creado. Puedes seleccionarlo ahora en Inscripción.');
            }

            return redirect()->route('representante.index')->with('success', $mensaje);
        } catch (\Throwable $th) {
            Log::error('Error en save representante: ' . $th->getMessage());
            Log::error('Stack trace: ' . $th->getTraceAsString());
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error en el servidor al guardar el representante: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['general' => 'Error en el servidor al guardar el representante: ' . $th->getMessage()])
                ->withInput();
        }
    }

    public function buscarPornumero_documento(Request $request): JsonResponse
    {
        $numero_documento = $request->get('numero_documento');
        Log::info(" Buscando cédula: " . $numero_documento);

        if (!$numero_documento) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debe indicar la cédula',
            ], 422);
        }

        $persona = Persona::where('numero_documento', $numero_documento)->first();
        Log::info("Persona encontrada: " . ($persona ? 'SÍ (ID: ' . $persona->id . ')' : 'NO'));

        if (!$persona) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró la cédula indicada',
            ], 404);
        }

        $representante = Representante::where('persona_id', $persona->id)->first();
        Log::info("Representante encontrado: " . ($representante ? 'SÍ (ID: ' . $representante->id . ')' : 'NO'));

        if (!$representante) {
            Log::info("Creando registro de representante para progenitor");
            $representante = Representante::create([
                'persona_id' => $persona->id,
                'estado_id' => 1,
                'municipio_id' => 1,
                'parroquia_id' => 1,
                'ocupacion_representante' => 'No especificado',
                'convivenciaestudiante_representante' => 'si',
            ]);
            Log::info("Representante creado: ID " . $representante->id);
        } else {
            Log::info("DEBUG: Representante existente encontrado", [
                'representante_id' => $representante->id,
                'estado_id' => $representante->estado_id,
                'municipio_id' => $representante->municipio_id,
                'parroquia_id' => $representante->parroquia_id,
                'ocupacion_representante' => $representante->ocupacion_representante,
            ]);

            if (!$representante->municipio_id || !$representante->parroquia_id) {
                Log::info("DEBUG: Actualizando campos de ubicación faltantes");
                $representante->update([
                    'municipio_id' => $representante->municipio_id ?: 1,
                    'parroquia_id' => $representante->parroquia_id ?: 1,
                ]);
                Log::info("DEBUG: Campos actualizados", [
                    'nuevo_municipio_id' => $representante->municipio_id,
                    'nuevo_parroquia_id' => $representante->parroquia_id,
                ]);
            }
        }

        $representante->load('legal', 'persona', 'legal.banco');
        Log::info("Representante cargado con relaciones para envío");

        return response()->json([
            'status' => 'success',
            'message' => 'Progenitor encontrado y habilitado como representante',
            'data' => $representante,
        ], 200);
    }

    public function consultar(Request $request): JsonResponse
    {
        $representante = Representante::find($request->id);
        if (!$representante) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al consultar el representante: no ha sido encontrado',
            ], 404);
        }

        $representante->load(['persona', 'legal', 'pais', 'estado', 'municipios', 'localidads']);

        return response()->json([
            'status' => 'success',
            'message' => 'Representante consultado',
            'data' => $representante,
        ], 200);
    }

    public function filtar(Request $request)
    {
        $buscador = $request->buscador ?? '';
        $esLegal = $request->es_legal;
        $consulta = Representante::with('persona')
            ->where('status', '!=', 0)
            ->whereNull('deleted_at');

        if ($esLegal !== null && $esLegal !== '') {
            $esLegal = $esLegal == '1';
            if ($esLegal) {
                $consulta->whereHas('legal');
            } else {
                $consulta->whereDoesntHave('legal');
            }
        }

        if (!empty($buscador)) {
            $consulta = $consulta->whereHas('persona', function ($query) use ($buscador) {
                $query->where(function ($q) use ($buscador) {
                    $q->where('numero_documento', 'LIKE', "%{$buscador}%")
                        ->orWhere('primer_nombre', 'LIKE', "%{$buscador}%")
                        ->orWhere('segundo_nombre', 'LIKE', "%{$buscador}%")
                        ->orWhere('primer_apellido', 'LIKE', "%{$buscador}%")
                        ->orWhere('segundo_apellido', 'LIKE', "%{$buscador}%")
                        ->orWhereRaw("CONCAT(primer_nombre, ' ', COALESCE(segundo_nombre, ''), ' ', primer_apellido, ' ', COALESCE(segundo_apellido, '')) LIKE ?", ["%{$buscador}%"]);
                });
            });
        }

        $consulta->orderBy('created_at', 'desc');

        $respuesta = $consulta->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Representantes consultados correctamente',
            'data' => $respuesta,
        ], 200);
    }

    public function delete(Request $request, $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $representanteId = $id ?? $request->id ?? $request->input('id');
        Log::info('=== INICIANDO ELIMINACIÓN LÓGICA DE REPRESENTANTE ===', [
            'id' => $representanteId,
            'input' => $request->all()
        ]);

        $representante = Representante::with(['persona', 'legal'])->find($representanteId);

        Log::info('Representante encontrado:', [
            'id' => $representante ? $representante->id : null,
            'current_status' => $representante ? $representante->status : null
        ]);

        if (!$representante) {
            $error = 'Representante no encontrado';
            Log::error($error, ['id' => $representanteId]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $error,
                ], 404);
            }

            return redirect()->route('representante.index')
                ->with('error', $error);
        }

        DB::beginTransaction();
        try {
            $representante->status = 0;
            $saved = $representante->save();

            Log::info('Actualización del estado a inactivo:', [
                'saved' => $saved,
                'new_status' => $representante->status,
                'updated_at' => $representante->updated_at
            ]);

            $deleted = $representante->delete();
            Log::info('Soft delete aplicado:', ['deleted' => $deleted]);

            if ($representante->legal) {
                Log::info('Datos legales encontrados, actualizando...');
                if (method_exists($representante->legal, 'delete')) {
                    $deletedLegal = $representante->legal->delete();
                    Log::info('Datos legales marcados como eliminados (soft delete):', ['deleted' => $deletedLegal]);
                } elseif (isset($representante->legal->status)) {
                    $representante->legal->status = 0;
                    $legalSaved = $representante->legal->save();
                    Log::info('Estado de datos legales actualizado a inactivo:', ['saved' => $legalSaved]);
                }
            }

            DB::commit();

            $response = [
                'status' => 'success',
                'message' => 'Representante eliminado correctamente',
                'data' => [
                    'id' => $representante->id,
                    'status' => 0,
                    'deleted_at' => $representante->deleted_at,
                    'updated_at' => $representante->updated_at
                ]
            ];

            Log::info('Eliminación exitosa:', $response);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($response);
            }

            return redirect()->route('representante.index')
                ->with('success', '¡El representante ha sido eliminado correctamente!');
        } catch (\Throwable $th) {
            DB::rollBack();
            $errorMessage = 'Error al eliminar el representante: ' . $th->getMessage();
            Log::error($errorMessage, [
                'exception' => $th,
                'trace' => $th->getTraceAsString(),
                'representante_id' => $representanteId
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->back()
                ->with('error', $errorMessage);
        }
    }

    public function verificarnumero_documento(Request $request): JsonResponse
    {
        $numero_documento = $request->input('numero_documento');
        $personaId = $request->input('persona_id');
        if (!$numero_documento) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debe proporcionar una cédula',
            ]);
        }

        $query = Persona::where('numero_documento', $numero_documento)
            ->whereHas('representante', function ($q) {
                $q->where('status', '!=', 0)
                    ->whereNull('deleted_at');
            });

        if ($personaId) {
            $query->where('id', '!=', $personaId);
        }

        $personaExistente = $query->first();

        // 1. Si la persona existe Y ya tiene representante activo:
        if ($personaExistente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Este representante ya está registrado en el sistema',
            ], 409);
        }

        // 2. Si la persona existe en el sistema (por ejemplo, es Docente o Alumno) pero NO es representante activo:
        $personaGeneral = Persona::where('numero_documento', $numero_documento)->first();
        if ($personaGeneral) {
            return response()->json([
                'status' => 'persona_existente',
                'message' => 'Persona encontrada en el sistema. Los datos personales han sido bloqueados.',
                'persona' => [
                    'id' => $personaGeneral->id,
                    'primer_nombre' => $personaGeneral->primer_nombre,
                    'segundo_nombre' => $personaGeneral->segundo_nombre,
                    'tercer_nombre' => $personaGeneral->tercer_nombre,
                    'primer_apellido' => $personaGeneral->primer_apellido,
                    'segundo_apellido' => $personaGeneral->segundo_apellido,
                    'fecha_nacimiento' => $personaGeneral->fecha_nacimiento ? substr($personaGeneral->fecha_nacimiento, 0, 10) : '',
                    'genero_id' => $personaGeneral->genero_id,
                    'tipo_documento_id' => $personaGeneral->tipo_documento_id,
                    'email' => $personaGeneral->email,
                    'direccion' => $personaGeneral->direccion,
                    'telefono' => $personaGeneral->telefono,
                    'telefono_dos' => $personaGeneral->telefono_dos,
                    'prefijo_id' => $personaGeneral->prefijo_id,
                    'prefijo_dos_id' => $personaGeneral->prefijo_dos_id,
                ]
            ], 200);
        }

        // 3. Si pertenecía a un representante eliminado:
        $registroEliminado = Persona::where('numero_documento', $numero_documento)
            ->whereHas('representante', function ($q) {
                $q->where('status', 0)
                    ->orWhereNotNull('deleted_at');
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
    }

    public function reportePDF(Request $request)
    {
        $filtro = $request->all();
        $representantes = Representante::reportePDF($filtro);
        $representantes = $representantes->sortBy(function ($item) {
            $primerApellido = $item->primer_apellido ??
                ($item->persona->primer_apellido ?? '');
            return strtoupper(substr($primerApellido, 0, 1));
        });

        if ($representantes->isEmpty()) {
            return response()->json('No se encontraron representantes', 404);
        }

        $pdf = PDF::loadView('admin.representante.reportes.general_pdf', compact('representantes', 'filtro'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('margin-bottom', '25mm');
        $pdf->setOption('isPhpEnabled', true);

        return $pdf->stream('representantes.pdf');
    }
}

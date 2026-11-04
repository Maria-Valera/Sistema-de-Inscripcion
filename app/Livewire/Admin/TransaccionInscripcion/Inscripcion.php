<?php

namespace App\Livewire\Admin\TransaccionInscripcion;

use Livewire\Component;
use App\Services\InscripcionService;
use App\Services\DocumentoService;
use App\Repositories\InscripcionRepository;
use App\Repositories\RepresentanteRepository;
use App\DTOs\InscripcionData;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class Inscripcion extends Component
{
    protected InscripcionService $inscripcionService;
    protected DocumentoService $documentoService;
    protected InscripcionRepository $inscripcionRepository;
    protected RepresentanteRepository $representanteRepository;

    public $alumnoId;
    public $padreId;
    public $madreId;
    public $representanteLegalId;
    public $gradoId;
    public $seccion_id = null;
    public $infoCupos = null;
    public $alumnoSeleccionado = null;
    public $padreSeleccionado = null;
    public $madreSeleccionado = null;
    public $representanteLegalSeleccionado = null;
    public $paisId = null;
    public bool $esVenezolano = true;
    public $estado_id = null;
    public $municipio_id = null;
    public $localidad_id = null;
    public $otroPaisNombre = '';
    public $paises = [];
    public $estados = [];
    public $municipios = [];
    public $localidades = [];
    public $padres = [];
    public $madres = [];
    public $representantes = [];
    public $instituciones = [];
    public $grados = [];
    public $secciones = [];
    public $expresiones_literarias = [];
    public $documentos = [];
    public array $documentosFaltantes = [];
    public ?string $observaciones = null;
    public $numero_zonificacion;
    public $institucion_procedencia_id;
    public $expresion_literaria_id;
    public $anio_egreso;
    public $anio_escolar_id;
    public $acepta_normas_contrato = false;
    public $seleccionarTodos = false;
    public $discapacidades = [];
    public $discapacidadSeleccionada = null;
    public $discapacidadesAgregadas = [];
    public $documentosDisponibles = [];
    public $documentosEtiquetas = [];
    public string $estadoDocumentos = '';
    public string $statusInscripcion = '';
    public bool $esPrimerGrado = true;
    public string $tipo_inscripcion = 'nuevo_ingreso';
    public bool $gradoSinCupos = false;
    public string $mensajeCupos = '';

    public function boot(
        InscripcionService $inscripcionService,
        DocumentoService $documentoService,
        InscripcionRepository $inscripcionRepository,
        RepresentanteRepository $representanteRepository
    ) {
        $this->inscripcionService = $inscripcionService;
        $this->documentoService = $documentoService;
        $this->inscripcionRepository = $inscripcionRepository;
        $this->representanteRepository = $representanteRepository;
    }

    public function mount()
    {
        abort_unless(auth()->user()?->can('inscripciones.gestionar'), 403, 'No tiene permiso para gestionar inscripciones.');
        $this->acepta_normas_contrato = true;
        $this->discapacidadesAgregadas = [];

        $this->discapacidadSeleccionada = null;
        $this->documentosDisponibles = $this->documentoService->obtenerDocumentosDisponibles();
        $this->documentosEtiquetas = $this->documentoService->obtenerEtiquetas();
        $this->tipo_inscripcion = 'nuevo_ingreso';
        $this->cargarDatosIniciales();
        $this->estados = \App\Models\Estado::where('status', true)
            ->orderBy('nombre_estado', 'asc')
            ->get();
        $this->paises = \App\Models\Pais::where('status', true)
            ->orderBy('nameES', 'asc')
            ->get();
        $this->autoseleccionarUbicacionPorDefecto();
    }

    public function autoseleccionarUbicacionPorDefecto()
    {
        $pais = \App\Models\Pais::where('status', true)
            ->where('nameES', 'Venezuela')
            ->first();

        if (!$pais) {
            return;
        }

        $this->paisId = $pais->id;
        $this->updatedPaisId($this->paisId);

        $estado = \App\Models\Estado::where('status', true)
            ->where('pais_id', $pais->id)
            ->where('nombre_estado', 'Portuguesa')
            ->first();

        if (!$estado) {
            return;
        }

        $this->estado_id = $estado->id;
        $this->updatedEstadoId($this->estado_id);

        $municipio = \App\Models\Municipio::where('status', true)
            ->where('estado_id', $estado->id)
            ->where('nombre_municipio', 'Araure')
            ->first();

        if (!$municipio) {
            return;
        }

        $this->municipio_id = $municipio->id;
        $this->updatedMunicipioId($this->municipio_id);
    }

    public function rules()
    {
        return [
            'estado_id' => $this->esVenezolano ? 'required|exists:estados,id' : 'nullable',
            'municipio_id' => $this->esVenezolano ? 'required|exists:municipios,id' : 'nullable',
            'localidad_id' => $this->esVenezolano ? 'required|exists:localidads,id' : 'nullable',
            'numero_zonificacion' => [
                'nullable',
                'regex:/^\d+$/'
            ],
            'representanteLegalId' => 'required',
            'institucion_procedencia_id' => $this->esVenezolano
                ? 'required|exists:institucion_procedencias,id'
                : 'nullable',
            'otroPaisNombre' => !$this->esVenezolano && !$this->institucion_procedencia_id
                ? 'required|string|max:255'
                : 'nullable',
            'paisId' => 'required|exists:pais,id',
            'expresion_literaria_id' => 'required|exists:expresion_literarias,id',
            'gradoId' => [
                'required',
                'exists:grados,id',
            ],
            'seccion_id' => $this->esPrimerGrado
                ? 'nullable'
                : 'required|exists:seccions,id',
            'documentos' => 'array',
            'documentos.*' => 'string',
            'acepta_normas_contrato' => 'accepted',
            'anio_egreso' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (!$this->inscripcionService->validarAnioEgreso($value)) {
                        $fail('El año de egreso debe ser 7 años antes del año actual.');
                    }
                }
            ],
        ];
    }

    protected $messages = [
        'representanteLegalId.required' => 'Debe selecionar un representante legal',
        'paisId.required' => 'Debe seleccionar un país.',
        'paisId.exists' => 'El país seleccionado no es válido.',
        'localidad_id.required' => 'Este campo es requerido.',
        'localidad_id.exists' => 'La localidad seleccionada no es válida.',
        'tipo_inscripcion.required' => 'Debe seleccionar el tipo de inscripción.',
        'tipo_inscripcion.in' => 'El tipo de inscripción no es válido.',
        'numero_zonificacion.regex' => 'El número de zonificación solo puede contener números.',
        'institucion_procedencia_id.required' => 'Este campo es requerido.',
        'institucion_procedencia_id.exists' => 'La institución de procedencia seleccionada no es válida.',
        'otroPaisNombre.required' => 'Debe ingresar el nombre de la institución extranjera.',
        'otroPaisNombre.max' => 'El nombre de la institución no puede exceder 255 caracteres.',
        'expresion_literaria_id.required' => 'Este campo es requerido.',
        'expresion_literaria_id.exists' => 'La expresión literaria seleccionada no es válida.',
        'gradoId.required' => 'Este campo es requerido.',
        'gradoId.exists' => 'El nivel academico seleccionado no es válido.',
        'seccion_id.required' => 'Este campo es requerido.',
        'seccion_id.exists' => 'La sección seleccionada no es válida.',
        'documentos.array' => 'El formato de los documentos seleccionados no es válido.',
        'documentos.*.string' => 'Uno o más documentos seleccionados no son válidos.',
        'acepta_normas_contrato.accepted' => 'Debe aceptar las normas del contrato para continuar.',
        'anio_egreso.required' => 'Este campo es requerido.',
        'anio_egreso.date' => 'El año de egreso debe ser 7 años antes del actual.',
    ];

    public function updated($propertyName)
    {
        if (in_array($propertyName, [
            'estado_id',
            'municipio_id',
            'localidad_id'
        ])) {
            return;
        }
        if ($propertyName !== 'gradoId') {
            $this->validateOnly($propertyName);
        }
    }

    public function cargarDatosIniciales()
    {
        $datos = $this->inscripcionRepository->obtenerDatosIniciales();
        $this->expresiones_literarias = $datos['expresiones_literarias'];
        $this->grados = $datos['grados'];
        $this->padres = $this->representanteRepository->obtenerPorGenero('Masculino');
        $this->madres = $this->representanteRepository->obtenerPorGenero('Femenino');
        $this->representantes = $this->representanteRepository->obtenerRepresentantesLegales();
        $this->discapacidades = \App\Models\Discapacidad::where('status', true)
            ->orderBy('nombre_discapacidad', 'asc')
            ->get();
    }

    public function updatedPadreId($value)
    {
        $this->padreSeleccionado = $value
            ? $this->representanteRepository->obtenerConRelaciones($value)
            : null;
    }

    public function updatedMadreId($value)
    {
        $this->madreSeleccionado = $value
            ? $this->representanteRepository->obtenerConRelaciones($value)
            : null;
    }

    public function updatedRepresentanteLegalId($value)
    {
        $this->representanteLegalSeleccionado = $value
            ? $this->representanteRepository->obtenerRepresentanteLegalConRelaciones($value)
            : null;
        $this->evaluarDocumentosVisual();
    }

    public function actualizarPadreSelect($data = null)
    {
        $id = $data['value'] ?? null;
        $this->padreId = $id;
        $this->padreSeleccionado = $id
            ? $this->representanteRepository->obtenerConRelaciones($id)
            : null;
    }

    public function actualizarMadreSelect($data = null)
    {
        $id = $data['value'] ?? null;
        $this->madreId = $id;
        $this->madreSeleccionado = $id
            ? $this->representanteRepository->obtenerConRelaciones($id)
            : null;
    }

    public function actualizarRepresentanteLegalSelect($data = null)
    {
        $id = $data['value'] ?? null;
        $this->representanteLegalId = $id;
        $this->representanteLegalSeleccionado = $id
            ? $this->representanteRepository->obtenerRepresentanteLegalConRelaciones($id)
            : null;
    }

    public function updatedSeleccionarTodos($value)
    {
        $this->documentos = $value ? $this->documentosDisponibles : [];
        $this->evaluarDocumentosVisual();
        $this->recalcularObservaciones();
    }

    public function updatedDocumentos()
    {
        $this->evaluarDocumentosVisual();
        $this->recalcularObservaciones();
    }

    private function validarDocumentosEnTiempoReal(): void
    {
        $this->resetErrorBag('documentos');
        $evaluacion = $this->documentoService->evaluarEstadoDocumentos(
            $this->documentos,
            $this->requiereAutorizacion(),
            $this->esPrimerGrado
        );
        $this->documentosFaltantes = $evaluacion['faltantes'];
    }

    private function requiereAutorizacion(): bool
    {
        return !$this->padreId && !$this->madreId;
    }

    private function evaluarDocumentosVisual(): void
    {
        $evaluacion = $this->documentoService->evaluarEstadoDocumentos(
            $this->documentos,
            $this->requiereAutorizacion(),
            $this->esPrimerGrado
        );
        $this->documentosFaltantes = $evaluacion['faltantes'];
        $this->estadoDocumentos = $evaluacion['estado_documentos'];
        $this->statusInscripcion = $evaluacion['status_inscripcion'];
        $this->seleccionarTodos = count($this->documentos) === count($this->documentosDisponibles);
    }

    private function recalcularObservaciones(): void
    {
        $observaciones = [];
        $obsDocumentos = $this->documentoService->generarObservaciones(
            $this->documentos,
            !$this->padreId && !$this->madreId,
            $this->esPrimerGrado
        );
        if ($obsDocumentos) {
            $observaciones[] = $obsDocumentos;
        }
        $obsDiscapacidades = $this->generarObservacionesDiscapacidades();
        if ($obsDiscapacidades) {
            $observaciones[] = $obsDiscapacidades;
        }
        $this->observaciones = implode(PHP_EOL . PHP_EOL, $observaciones);
    }

    private function generarObservacionesDiscapacidades(): ?string
    {
        $nombres = [];
        if ($this->alumnoId) {
            $alumno = \App\Models\Alumno::with('discapacidades')->find($this->alumnoId);
            if ($alumno) {
                $nombres = $alumno->discapacidades
                    ->pluck('nombre_discapacidad')
                    ->toArray();
            }
        }
        foreach ($this->discapacidadesAgregadas as $discapacidad) {
            $nombres[] = $discapacidad['nombre'];
        }
        $nombres = array_unique($nombres);
        if (empty($nombres)) {
            return null;
        }
        return 'Discapacidades registradas:' . PHP_EOL .
            implode(PHP_EOL, $nombres);
    }

    public function updatedPaisId($value)
    {
        if ($value) {
            $this->resetErrorBag('paisId');
        }

        if (!$value) {
            $this->esVenezolano = true;
            return;
        }
        $pais = \App\Models\Pais::find($value);
        $this->esVenezolano = $pais->nameES === 'Venezuela';
        if ($this->esVenezolano) {
            $this->otroPaisNombre = '';
            $this->institucion_procedencia_id = null;
            $this->estados = \App\Models\Estado::where('status', true)
                ->orderBy('nombre_estado', 'asc')
                ->get();
        } else {
            $this->estado_id = null;
            $this->municipio_id = null;
            $this->localidad_id = null;
            $this->institucion_procedencia_id = null;
            $this->estados = [];
            $this->municipios = [];
            $this->localidades = [];
            $this->instituciones = [];
        }
    }

    public function updatedEstadoId($value)
    {
        $this->municipio_id = null;
        $this->localidad_id = null;
        $this->institucion_procedencia_id = null;
        $this->localidades = [];
        $this->instituciones = [];

        if ($value) {
            $this->resetErrorBag('estado_id');
        }

        if (!$value) {
            $this->municipios = [];
            return;
        }
        $this->municipios = \App\Models\Municipio::where('estado_id', $value)
            ->where('status', true)
            ->orderBy('nombre_municipio')
            ->get();
    }

    public function updatedMunicipioId($value)
    {
        $this->localidad_id = null;
        $this->institucion_procedencia_id = null;
        $this->instituciones = [];

        if ($value) {
            $this->resetErrorBag('municipio_id');
        }

        if (!$value) {
            $this->localidades = [];
            return;
        }
        $this->localidades = \App\Models\Localidad::where('municipio_id', $value)
            ->where('status', true)
            ->orderBy('nombre_localidad')
            ->get();
    }

    public function updatedLocalidadId($value)
    {
        $this->institucion_procedencia_id = null;
        $this->resetErrorBag('institucion_procedencia_id');

        if ($value) {
            $this->resetErrorBag('localidad_id');
        }

        if (!$value) {
            $this->instituciones = [];
            return;
        }
        $this->instituciones = \App\Models\InstitucionProcedencia::where('localidad_id', $value)
            ->where('status', true)
            ->orderBy('nombre_institucion')
            ->get();
    }



    public function updatedGradoId($value)
    {
        $this->resetErrorBag('gradoId');
        $this->gradoSinCupos = false;
        $this->mensajeCupos = '';
        if (!$value) {
            $this->infoCupos = null;
            $this->secciones = [];
            $this->seccion_id = null;
            return;
        }
        $this->infoCupos = $this->inscripcionService->obtenerInfoCupos($value);
        if ($this->infoCupos['cupos_disponibles'] <= 0) {
            $this->gradoSinCupos = true;
            $this->mensajeCupos = 'Este nivel academico ha alcanzado el máximo de cupos disponibles.';
            $this->addError('gradoId', $this->mensajeCupos);
            return;
        }
        $grado = \App\Models\Grado::find($value);
        $this->esPrimerGrado = ((int) $grado->numero_grado === 1);
        if (!$this->esPrimerGrado) {
            $this->secciones = \App\Models\Seccion::where('grado_id', $value)
                ->where('status', true)
                ->orderBy('nombre')
                ->get();
        } else {
            $this->secciones = [];
            $this->seccion_id = null;
        }
        $this->validarDocumentosEnTiempoReal();
        $this->evaluarDocumentosVisual();
        $this->recalcularObservaciones();
    }

    public function agregarDiscapacidad()
    {
        $this->validate([
            'discapacidadSeleccionada' => 'required|exists:discapacidads,id'
        ], [
            'discapacidadSeleccionada.required' => 'Debe seleccionar una discapacidad.',
            'discapacidadSeleccionada.exists' => 'La discapacidad seleccionada no es válida.'
        ]);
        if (collect($this->discapacidadesAgregadas)->contains('id', $this->discapacidadSeleccionada)) {
            $this->addError('discapacidadSeleccionada', 'Esta discapacidad ya ha sido agregada.');
            return;
        }
        $discapacidad = \App\Models\Discapacidad::find($this->discapacidadSeleccionada);
        if ($discapacidad) {
            $this->discapacidadesAgregadas[] = [
                'id' => $discapacidad->id,
                'nombre' => $discapacidad->nombre_discapacidad
            ];
            $this->discapacidadSeleccionada = null;
            $this->resetErrorBag('discapacidadSeleccionada');
            $this->recalcularObservaciones();
            session()->flash('success_temp', 'Discapacidad agregada exitosamente.');
        }
    }

    public function eliminarDiscapacidad($index)
    {
        if (isset($this->discapacidadesAgregadas[$index])) {
            $discapacidad = $this->discapacidadesAgregadas[$index];
            unset($this->discapacidadesAgregadas[$index]);
            $this->discapacidadesAgregadas = array_values($this->discapacidadesAgregadas);
            $this->recalcularObservaciones();
            session()->flash('success_temp', "Discapacidad '{$discapacidad['nombre']}' eliminada.");
        }
    }

    private function guardarDiscapacidadesAlumno($alumnoId)
    {
        foreach ($this->discapacidadesAgregadas as $discapacidad) {
            \App\Models\DiscapacidadEstudiante::create([
                'alumno_id' => $alumnoId,
                'discapacidad_id' => $discapacidad['id'],
                'status' => true
            ]);
        }
    }

    private function crearInstitucionSiNoEsVenezolano(): ?int
    {
        try {
            if ($this->esVenezolano) {
                Log::info('Inscripción Venezuela - Institución ID: ' . $this->institucion_procedencia_id);
                return $this->institucion_procedencia_id;
            }

            if (!$this->paisId) {
                Log::error('Inscripción Extranjera - No se seleccionó país');
                throw new \Exception('Debe seleccionar un país para la institución extranjera.');
            }

            $nombreInstitucion = trim($this->otroPaisNombre);

            if ($nombreInstitucion === '') {
                Log::error('Inscripción Extranjera - Nombre de institución vacío');
                throw new \Exception('Debe ingresar el nombre de la institución extranjera.');
            }

            Log::info('Creando institución extranjera', [
                'pais_id' => $this->paisId,
                'nombre' => $nombreInstitucion
            ]);

            $institucion = \App\Models\InstitucionProcedencia::firstOrCreate(
                [
                    'pais_id' => $this->paisId,
                    'nombre_institucion' => $nombreInstitucion,
                ],
                [
                    'localidad_id' => null,
                    'status' => true,
                ]
            );

            Log::info('Institución extranjera creada/encontrada', [
                'id' => $institucion->id,
                'nombre' => $institucion->nombre_institucion
            ]);

            return $institucion->id;
        } catch (\Exception $e) {
            Log::error('Error al crear institución de procedencia', [
                'mensaje' => $e->getMessage(),
                'pais_id' => $this->paisId,
                'esVenezolano' => $this->esVenezolano,
                'otroPaisNombre' => $this->otroPaisNombre,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


    public function registrar()
    {
        try {
            if (!$this->validarRepresentantes()) {
                return;
            }

            // Verificar solo documentos OBLIGATORIOS
            $evaluacion = $this->documentoService->evaluarEstadoDocumentos(
                $this->documentos,
                $this->requiereAutorizacion(),
                $this->esPrimerGrado
            );

            // Solo bloquear si faltan documentos OBLIGATORIOS
            if (!$evaluacion['puede_guardar']) {
                $mensaje = collect($evaluacion['faltantes_obligatorios'])
                    ->map(fn($doc) => $this->documentosEtiquetas[$doc] ?? $doc)
                    ->implode('<br>');

                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Documentos obligatorios incompletos',
                    'html' => 'Debe adjuntar los siguientes documentos obligatorios:<br><br>' . $mensaje
                ]);

                return;
            }

            if (!$this->inscripcionService->verificarCuposDisponibles($this->gradoId)) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Cupos agotados',
                    'html' => 'Este nivel academico ha alcanzado el máximo de cupos disponibles.'
                ]);
                return;
            }

            $this->validate();

            Log::info('Datos antes de crear DTO', [
                'esVenezolano' => $this->esVenezolano,
                'paisId' => $this->paisId,
                'institucion_procedencia_id' => $this->institucion_procedencia_id,
                'otroPaisNombre' => $this->otroPaisNombre,
            ]);

            $dto = $this->crearInscripcionDTO();

            Log::info('DTO creado exitosamente', [
                'institucion_procedencia_id' => $dto->institucion_procedencia_id
            ]);

            $inscripcion = $this->inscripcionService->registrar($dto);

            if ($this->alumnoId && !empty($this->discapacidadesAgregadas)) {
                $this->guardarDiscapacidadesAlumno($this->alumnoId);
            }

            session()->flash('success', 'Inscripción registrada exitosamente.');
            return redirect()->route('admin.transacciones.inscripcion.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en inscripción', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error en inscripción', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al registrar',
                'text' => $e->getMessage()
            ]);
        }
    }

    public function finalizar()
    {
        if (!$this->acepta_normas_contrato) {
            $this->addError('acepta_normas_contrato', 'Debe aceptar las normas para continuar.');
            return;
        }

        if (!$this->validarRepresentantes()) {
            return;
        }

        // Verificar solo documentos OBLIGATORIOS
        $evaluacion = $this->documentoService->evaluarEstadoDocumentos(
            $this->documentos,
            $this->requiereAutorizacion(),
            $this->esPrimerGrado
        );

        // Solo bloquear si faltan documentos OBLIGATORIOS
        if (!$evaluacion['puede_guardar']) {
            $mensaje = collect($evaluacion['faltantes_obligatorios'])
                ->map(fn($doc) => $this->documentosEtiquetas[$doc] ?? $doc)
                ->implode('<br>');

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Documentos obligatorios incompletos',
                'html' => 'Debe adjuntar los siguientes documentos obligatorios:<br><br>' . $mensaje
            ]);

            return;
        }

        $this->dispatch('solicitarDatosAlumno');
    }

    public function guardarTodo($datos = [])
    {
        try {
            if (empty($datos)) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'No se puede completar la inscripción',
                    'message' => 'No se recibieron datos del alumno.'
                ]);
                return;
            }

            $this->validate();

            Log::info('Guardando inscripción con alumno nuevo', [
                'esVenezolano' => $this->esVenezolano,
                'paisId' => $this->paisId,
                'otroPaisNombre' => $this->otroPaisNombre,
            ]);

            $dto = $this->crearInscripcionDTO();

            $inscripcion = $this->inscripcionService->registrarConAlumno(
                $datos,
                $dto,
                $this->discapacidadesAgregadas
            );

            session()->flash('success', 'Inscripción registrada exitosamente.');
            return redirect()->route('admin.transacciones.inscripcion.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en guardarTodo', [
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error InscripcionException en guardarTodo', [
                'mensaje' => $e->getMessage()
            ]);

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede completar la inscripción',
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error('Error general en guardarTodo', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error inesperado',
                'message' => 'Ocurrió un error al procesar la inscripción.'
            ]);
        }
    }

    private function validarRepresentantes(): bool
    {
        if (!$this->representanteLegalId) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede completar la inscripción',
                'message' => 'Debe seleccionar un representante legal.'
            ]);

            return false;
        }
        return true;
    }

    private function crearInscripcionDTO(): InscripcionData
    {
        $institucionId = $this->crearInstitucionSiNoEsVenezolano();
        return new InscripcionData([
            'tipo_inscripcion' => $this->tipo_inscripcion,
            'anio_escolar_id' => $this->anio_escolar_id,
            'alumno_id' => $this->alumnoId,
            'numero_zonificacion' => $this->numero_zonificacion,
            'institucion_procedencia_id' => $institucionId,
            'anio_egreso' => $this->anio_egreso,
            'expresion_literaria_id' => $this->expresion_literaria_id,
            'grado_id' => $this->gradoId,
            'seccion_id' => $this->seccion_id,
            'padre_id' => $this->padreId,
            'madre_id' => $this->madreId,
            'representante_legal_id' => $this->representanteLegalId,
            'documentos' => $this->documentos,
            'observaciones' => $this->observaciones,
            'acepta_normas_contrato' => $this->acepta_normas_contrato,
        ]);
    }

    protected $listeners = [
        'recibirDatosAlumno' => 'guardarTodo',
        'padreSeleccionadoEvento' => 'actualizarPadreSelect',
        'acepta_normas_contrato' => 'actualizarEstadoBoton',
        'institucionCreada' => 'manejarInstitucionCreada',
        'localidadCreada' => 'refrescarLocalidades',
    ];

    public function manejarInstitucionCreada($data)
    {
        if ($this->localidad_id) {
            $this->instituciones = \App\Models\InstitucionProcedencia::where('localidad_id', $this->localidad_id)
                ->where('status', true)
                ->orderBy('nombre_institucion')
                ->get();
        }
        $this->institucion_procedencia_id = $data['id'];
    }


    #[On('localidadCreada')]
    public function refrescarLocalidades($data)
    {
        if ($this->municipio_id == $data['municipio_id']) {
            $this->localidades = \App\Models\Localidad::where('municipio_id', $this->municipio_id)
                ->where('status', true)
                ->orderBy('nombre_localidad')
                ->get();

            $this->localidad_id = $data['id'];
        }
    }


    public function actualizarEstadoBoton($value)
    {
        $this->acepta_normas_contrato = $value;
        $this->validateOnly('acepta_normas_contrato');
    }

    public function irACrearRepresentante()
    {
        session()->put('inscripcion_temp', [
            'alumnoId' => $this->alumnoId,
            'padreId' => $this->padreId,
            'madreId' => $this->madreId,
            'representanteLegalId' => $this->representanteLegalId,
            'gradoId' => $this->gradoId,
            'observaciones' => $this->observaciones,
            'documentos' => $this->documentos,
        ]);
        return redirect()->route('representante.formulario', ['from' => 'inscripcion']);
    }

    public function render()
    {
        return view('livewire.admin.transaccion-inscripcion.inscripcion');
    }
}

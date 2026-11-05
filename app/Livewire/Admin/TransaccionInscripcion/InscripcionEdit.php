<?php

namespace App\Livewire\Admin\TransaccionInscripcion;

use Livewire\Component;
use App\Models\Inscripcion;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\InstitucionProcedencia;
use App\Models\ExpresionLiteraria;
use App\Repositories\RepresentanteRepository;
use App\Services\DocumentoService;
use App\Services\InscripcionService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;


class InscripcionEdit extends Component
{
    protected InscripcionService $inscripcionService;
    protected RepresentanteRepository $representanteRepository;
    protected DocumentoService $documentoService;

    public $inscripcionId;
    public $alumnoId;
    public $padreId;
    public $madreId;
    public $representanteLegalId;
    public $gradoId;
    public $seccionId;
    public $alumnoSeleccionado = null;
    public $padreSeleccionado = null;
    public $madreSeleccionado = null;
    public $representanteLegalSeleccionado = null;

    public $paisId = null;
    public bool $esVenezolano = true;
    public $estado_id = null;
    public $municipio_id = null;
    public $localidad_id = null;
    protected int $paisVenezuelaId = 242;


    public $otroPaisNombre = '';

    public $paises = [];
    public $estados = [];
    public $municipios = [];
    public $localidades = [];
    public $padres = [];
    public $madres = [];
    public $representantes = [];
    public $grados = [];
    public $secciones = [];
    public $instituciones = [];
    public $expresiones_literarias = [];
    public $numero_zonificacion;
    public $institucion_procedencia_id;
    public $expresion_literaria_id;
    public $anio_egreso;
    public $documentos = [];
    public $observaciones;
    public $acepta_normas_contrato = false;
    public $esPrimerGrado = true;
    public $documentosDisponibles = [];
    public $documentosEtiquetas = [];
    public $documentosFaltantes = [];
    public $seleccionarTodos = false;
    public $estadoDocumentos = '';
    public $statusInscripcion = '';

    public function boot(
        InscripcionService $inscripcionService,
        RepresentanteRepository $representanteRepository,
        DocumentoService $documentoService
    ) {
        $this->inscripcionService = $inscripcionService;
        $this->representanteRepository = $representanteRepository;
        $this->documentoService = $documentoService;
    }

    public function rules()
    {
        return [
            'gradoId' => [
                'required',
                'exists:grados,id',
                function ($attribute, $value, $fail) {
                    if (!$this->inscripcionService->verificarCuposDisponibles($value)) {
                        $fail('El nivel academico seleccionado ha alcanzado el límite de cupos disponibles.');
                    }
                }
            ],
            'seccionId' => [
                function ($attr, $value, $fail) {
                    if (!$this->esPrimerGrado && !$value) {
                        $fail('Debe seleccionar una sección.');
                    }

                    if ($value) {
                        $existe = Seccion::where('id', $value)
                            ->where('grado_id', $this->gradoId)
                            ->where('status', true)
                            ->exists();

                        if (!$existe) {
                            $fail('La sección seleccionada no pertenece al nivel academico.');
                        }
                    }
                }
            ],

            'padreId' => 'nullable|exists:representantes,id',
            'madreId' => 'nullable|exists:representantes,id',
            'representanteLegalId' => 'required|exists:representantes,id',

            'documentos' => 'array',
            'documentos.*' => 'in:' . implode(',', $this->documentosDisponibles),

            'numero_zonificacion' => [
                'nullable',
                'regex:/^\d+$/'
            ],

            'paisId' => 'required|exists:pais,id',
            'estado_id' => $this->esVenezolano ? 'required|exists:estados,id' : 'nullable',
            'municipio_id' => $this->esVenezolano ? 'required|exists:municipios,id' : 'nullable',
            'localidad_id' => $this->esVenezolano ? 'required|exists:localidads,id' : 'nullable',
            'institucion_procedencia_id' => $this->esVenezolano
                ? 'required|exists:institucion_procedencias,id'
                : 'nullable',
            'otroPaisNombre' => !$this->esVenezolano
                ? 'required|string|max:255'
                : 'nullable',

            'expresion_literaria_id' => 'required|exists:expresion_literarias,id',

            'anio_egreso' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (!$this->inscripcionService->validarAnioEgreso($value)) {
                        $fail('El año de egreso debe ser 7 años antes del año actual.');
                    }
                }
            ],


            'acepta_normas_contrato' => 'accepted',
        ];
    }

    protected $messages = [
        'gradoId.required' => 'Este campo es requerido.',
        'gradoId.exists' => 'El nivel academico seleccionado no es válido.',

        'documentos.array' => 'Formato inválido de documentos.',
        'documentos.*.in' => 'Uno o más documentos no son válidos.',

        'numero_zonificacion.regex' =>
        'El número de zonificación solo puede contener números.',

        'institucion_procedencia_id.exists' =>
        'La institución seleccionada no es válida.',

        'expresion_literaria_id.exists' =>
        'La expresión literaria seleccionada no es válida.',

        'anio_egreso.required' => 'Este campo es requerido.',
        'anio_egreso.date' => 'El año de egreso debe ser 7 años antes del actual.',

        'representanteLegalId.required' =>
        'Este campo es requerido.',

        'representanteLegalId.exists' =>
        'El representante legal seleccionado no es válido.',

        'acepta_normas_contrato.accepted' =>
        'Debe aceptar las normas del contrato.',
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

    public function mount($inscripcionId)
    {
        abort_unless(auth()->user()?->can('inscripciones.gestionar'), 403);

        $this->inscripcionId = $inscripcionId;
        $this->documentosDisponibles = $this->documentoService->obtenerDocumentosDisponibles();
        $this->documentosEtiquetas = $this->documentoService->obtenerEtiquetas();
        $this->cargarDatosIniciales();
        $this->cargarInscripcion();
        $this->estados = \App\Models\Estado::where('status', true)
            ->orderBy('nombre_estado', 'asc')
            ->get();
        $this->paises = \App\Models\Pais::where('status', true)
            ->orderBy('nameES', 'asc')
            ->get();
    }

    private function cargarDatosIniciales()
    {
        $this->grados = Grado::where('status', true)
            ->orderBy('numero_grado', 'asc')
            ->get();
        $this->expresiones_literarias = ExpresionLiteraria::where('status', true)->get();

        $this->padres = $this->representanteRepository->obtenerPorGenero('Masculino');
        $this->madres = $this->representanteRepository->obtenerPorGenero('Femenino');
        $this->representantes = $this->representanteRepository->obtenerRepresentantesLegales();
    }

    private function cargarInscripcion()
    {
        $inscripcion = Inscripcion::with([
            'alumno.persona',
            'grado',
            'seccion',
            'padre.persona',
            'madre.persona',
            'representanteLegal.representante.persona',
            'nuevoIngreso'
        ])->findOrFail($this->inscripcionId);

        $this->alumnoId = $inscripcion->alumno_id;
        $this->gradoId = $inscripcion->grado_id;
        $this->seccionId = $inscripcion->seccion_id;
        $this->padreId = $inscripcion->padre_id;
        $this->madreId = $inscripcion->madre_id;
        $this->representanteLegalId = $inscripcion->representante_legal_id;
        $this->documentos = $inscripcion->documentos ?? [];
        $this->observaciones = $inscripcion->observaciones;
        $this->acepta_normas_contrato = $inscripcion->acepta_normas_contrato;

        if ($inscripcion->nuevoIngreso) {
            $this->institucion_procedencia_id = $inscripcion->nuevoIngreso->institucion_procedencia_id;
            $this->expresion_literaria_id = $inscripcion->nuevoIngreso->expresion_literaria_id;
            $this->anio_egreso = $inscripcion->nuevoIngreso->anio_egreso;
            $this->numero_zonificacion = $inscripcion->nuevoIngreso->numero_zonificacion;
        }

        if ($this->institucion_procedencia_id) {
            $institucion = InstitucionProcedencia::with('localidad.municipio.estado', 'pais')
                ->find($this->institucion_procedencia_id);

            if ($institucion) {
                if ($institucion->pais_id) {
                    $this->paisId = $institucion->pais_id;
                    $this->esVenezolano = ($this->paisId == $this->paisVenezuelaId);
                    $this->otroPaisNombre = $institucion->nombre_institucion;

                    $this->estado_id = null;
                    $this->municipio_id = null;
                    $this->localidad_id = null;
                } elseif ($institucion->localidad) {
                    $this->localidad_id = $institucion->localidad->id;
                    $this->municipio_id = $institucion->localidad->municipio->id;
                    $this->estado_id = $institucion->localidad->municipio->estado->id;
                    $this->paisId = $this->paisVenezuelaId;
                    $this->esVenezolano = true;
                }
            }
        }

        $this->evaluarPais();

        if ($this->esVenezolano && $this->estado_id) {
            $this->municipios = \App\Models\Municipio::where('estado_id', $this->estado_id)
                ->where('status', true)
                ->orderBy('nombre_municipio')
                ->get();

            if ($this->municipio_id) {
                $this->localidades = \App\Models\Localidad::where('municipio_id', $this->municipio_id)
                    ->where('status', true)
                    ->orderBy('nombre_localidad')
                    ->get();
            }

            if ($this->localidad_id) {
                $this->instituciones = \App\Models\InstitucionProcedencia::where('localidad_id', $this->localidad_id)
                    ->where('status', true)
                    ->orderBy('nombre_institucion')
                    ->get();
            }
        }

        $this->alumnoSeleccionado = $inscripcion->alumno;
        $this->padreSeleccionado = $inscripcion->padre;
        $this->madreSeleccionado = $inscripcion->madre;
        $this->representanteLegalSeleccionado = $inscripcion->representanteLegal;

        $grado = Grado::find($this->gradoId);
        $this->esPrimerGrado = ((int) $grado->numero_grado === 1);
        $this->cargarSecciones($this->gradoId);
        $this->evaluarDocumentosVisual();
        $this->actualizarObservacionesPorDocumentos();
    }

    private function evaluarDocumentosVisual(): void
    {
        $evaluacion = $this->documentoService->evaluarEstadoDocumentos(
            $this->documentos,
            !$this->padreId && !$this->madreId,
            $this->esPrimerGrado
        );

        $this->documentosFaltantes = $evaluacion['faltantes'];
        $this->estadoDocumentos = $evaluacion['estado_documentos'];
        $this->statusInscripcion = $evaluacion['status_inscripcion'];

        $this->seleccionarTodos =
            count($this->documentos) === count($this->documentosDisponibles);
    }

    private function evaluarPais()
    {
        $this->esVenezolano = ($this->paisId == $this->paisVenezuelaId);

        if (!$this->esVenezolano) {
            $this->estado_id = null;
            $this->municipio_id = null;
            $this->localidad_id = null;

            $this->estados = [];
            $this->municipios = [];
            $this->localidades = [];
            $this->instituciones = [];
        }
    }

    public function updatedPaisId($value)
    {
        if (!$value) {
            $this->esVenezolano = true;
            return;
        }

        $paisAnterior = $this->esVenezolano;
        $this->evaluarPais();

        if ($paisAnterior !== $this->esVenezolano) {
            $this->institucion_procedencia_id = null;
            $this->otroPaisNombre = '';
        }

        if ($this->esVenezolano) {
            $this->otroPaisNombre = '';
            $this->estados = \App\Models\Estado::where('status', true)
                ->orderBy('nombre_estado')
                ->get();
        } else {
            $this->estado_id = null;
            $this->municipio_id = null;
            $this->localidad_id = null;
        }
    }

    public function updatedEstadoId($value)
    {
        $this->municipio_id = null;
        $this->localidad_id = null;
        $this->institucion_procedencia_id = null;

        $this->localidades = [];
        $this->instituciones = [];

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
        if (!$value) {
            $this->secciones = [];
            $this->seccionId = null;
            return;
        }

        $grado = Grado::find($value);
        $this->esPrimerGrado = ((int) $grado->numero_grado === 1);

        if (!$this->esPrimerGrado) {
            $this->cargarSecciones($value);
        } else {
            $this->secciones = [];
            $this->seccionId = null;
        }
    }

    private function cargarSecciones($gradoId)
    {
        $this->secciones = Seccion::where('grado_id', $gradoId)
            ->where('status', true)
            ->orderBy('nombre')
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

    public function updatedSeleccionarTodos($value)
    {
        $this->documentos = $value ? $this->documentosDisponibles : [];

        $this->actualizarObservacionesPorDocumentos();
        $this->evaluarDocumentosVisual();
    }

    public function updatedDocumentos()
    {
        $this->sincronizarDocumentos();
        $this->evaluarDocumentosVisual();
        $this->actualizarObservacionesPorDocumentos();
    }

    private function actualizarObservacionesPorDocumentos(): void
    {
        $this->observaciones = $this->documentoService->generarObservaciones(
            $this->documentos,
            !$this->padreId && !$this->madreId,
            $this->esPrimerGrado,
            $this->alumnoId
        );
    }


    private function sincronizarDocumentos(): void
    {
        $requiereAutorizacion = !$this->padreId && !$this->madreId;

        $evaluacion = $this->documentoService->evaluarEstadoDocumentos(
            $this->documentos,
            $requiereAutorizacion,
            $this->esPrimerGrado,
            $this->alumnoId
        );
        $this->documentosFaltantes = $evaluacion['faltantes'];
    }



    private function validarRepresentantes(): bool
    {
        if (!$this->padreId && !$this->madreId && !$this->representanteLegalId) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede completar la inscripción',
                'message' => 'Debe seleccionar un representante legal'
            ]);
            return false;
        }
        return true;
    }

    private function validarNuevoIngreso(Inscripcion $inscripcion): bool
    {
        if (!$inscripcion->nuevoIngreso) {
            return true;
        }

        if ($this->esVenezolano && !$this->institucion_procedencia_id) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede completar la inscripción',
                'message' => 'Debe seleccionar una institución de procedencia.'
            ]);
            return false;
        }

        if (!$this->esVenezolano && empty(trim($this->otroPaisNombre))) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede completar la inscripción',
                'message' => 'Debe ingresar el nombre de la institución de procedencia.'
            ]);
            return false;
        }

        if (!$this->expresion_literaria_id || !$this->anio_egreso) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede completar la inscripción',
                'message' => 'Debe completar todos los datos de nuevo ingreso.'
            ]);
            return false;
        }

        return true;
    }



    public function actualizar()
    {
        abort_unless(auth()->user()?->can('inscripciones.gestionar'), 403);

        if (!$this->esVenezolano && empty(trim($this->otroPaisNombre))) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede completar la inscripción',
                'message' => 'Debe ingresar el nombre de la institución de procedencia'
            ]);
            return;
        }

        if ($this->esVenezolano && !$this->institucion_procedencia_id) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede completar la inscripción',
                'message' => 'Debe seleccionar una institución de procedencia'
            ]);
            return;
        }

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede completar la inscripción',
                'message' => 'Verifique los datos ingresados.'
            ]);
            throw $e;
        }

        if (!$this->validarRepresentantes()) {
            return;
        }

        try {
            DB::beginTransaction();

            $institucionId = $this->institucion_procedencia_id;

            if (!$this->esVenezolano && $this->otroPaisNombre) {
                $institucion = \App\Models\InstitucionProcedencia::firstOrCreate(
                    [
                        'nombre_institucion' => trim($this->otroPaisNombre),
                        'pais_id' => $this->paisId,
                    ],
                    [
                        'localidad_id' => null,
                        'status' => true,
                    ]
                );
                $institucionId = $institucion->id;
            }

            $requiereAutorizacion = !$this->padreId && !$this->madreId;
            $evaluacion = $this->documentoService->evaluarEstadoDocumentos(
                $this->documentos,
                $requiereAutorizacion,
                $this->esPrimerGrado
            );

            if (!$evaluacion['puede_guardar']) {
                DB::rollBack();
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'No se puede completar la inscripción',
                    'message' => 'Faltan documentos obligatorios.'
                ]);
                return;
            }

            $inscripcion = Inscripcion::findOrFail($this->inscripcionId);

            $inscripcion->update([
                'grado_id' => $this->gradoId,
                'seccion_id' => $this->seccionId,
                'padre_id' => $this->padreId ?: null,
                'madre_id' => $this->madreId ?: null,
                'representante_legal_id' => $this->representanteLegalId ?: null,
                'documentos' => $this->documentos,
                'estado_documentos' => $evaluacion['estado_documentos'],
                'status' => $evaluacion['status_inscripcion'],
                'observaciones' => filled($this->observaciones)
                    ? $this->observaciones
                    : 'Sin observaciones',
                'acepta_normas_contrato' => $this->acepta_normas_contrato,
            ]);

            if ($inscripcion->nuevoIngreso) {
                $inscripcion->nuevoIngreso->update([
                    'numero_zonificacion' => $this->numero_zonificacion,
                    'institucion_procedencia_id' => $institucionId,
                    'expresion_literaria_id' => $this->expresion_literaria_id,
                    'anio_egreso' => $this->anio_egreso,
                ]);
            }

            DB::commit();

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Inscripción actualizada exitosamente',
                'message' => 'Estado de documentos: ' . $evaluacion['estado_documentos']
            ]);

            return redirect()->route('admin.transacciones.inscripcion.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al actualizar',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }

    protected $listeners = [
        'actualizarAlumno' => 'manejarActualizacionAlumno',
        'localidadCreada' => 'refrescarLocalidades'
    ]; 

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

    public function manejarActualizacionAlumno()
    {
        $inscripcion = Inscripcion::with('alumno.persona')->find($this->inscripcionId);
        $this->alumnoSeleccionado = $inscripcion->alumno;
        $this->actualizarObservacionesPorDocumentos();
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Datos del alumno actualizados correctamente'
        ]);
    }

    public function irACrearRepresentante()
    {
        session()->put('inscripcion_temp', [
            'alumnoId' => $this->alumnoId,
            'padreId' => $this->padreId,
            'madreId' => $this->madreId,
            'representanteLegalId' => $this->representanteLegalId,
            'gradoId' => $this->gradoId,
            'observaciones' => filled($this->observaciones)
                ? $this->observaciones
                : 'Sin observaciones',
            'documentos' => $this->documentos,
        ]);

        return redirect()->route('representante.formulario', ['from' => 'inscripcion']);
    }

    public function render()
    {
        return view('livewire.admin.transaccion-inscripcion.inscripcion-edit');
    }
}

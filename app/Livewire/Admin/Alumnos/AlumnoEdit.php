<?php

namespace App\Livewire\Admin\Alumnos;

use Livewire\Component;
use App\Models\Alumno;
use App\Models\Discapacidad;
use App\Models\Persona;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Localidad;
use App\Models\Talla;
use App\Models\Lateralidad;
use App\Models\OrdenNacimiento;
use App\Models\EtniaIndigena;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;


class AlumnoEdit extends Component
{
    public $alumnoId;
    public $persona_id;
    public $alumnoSeleccionado;

    public $tipo_documento_id;
    public $numero_documento;
    public $primer_nombre;
    public $segundo_nombre;
    public $tercer_nombre;
    public $primer_apellido;
    public $segundo_apellido;
    public $genero_id;
    public $fecha_nacimiento;
    public $edad = 0;
    public $meses = 0;

    public $documento_maxlength = 8;
    public $documento_pattern = '[0-9]+';
    public $documento_placeholder = '12345678';
    public $documento_inputmode = 'numeric';

    public $talla_estudiante = '';
    public $peso_estudiante;
    public $talla_camisa_id;
    public $talla_zapato;
    public $talla_pantalon_id;

    public $estado_id;
    public $municipio_id;
    public $localidad_id;
    public $pais_id = null;
    public $paises = [];

    public $lateralidad_id;
    public $orden_nacimiento_id;
    public $etnia_indigena_id;

    public $tipos_documentos = [];
    public $generos = [];
    public $estados = [];
    public $municipios = [];
    public $localidades = [];
    public $tallas = [];
    public $lateralidades = [];
    public $orden_nacimientos = [];
    public $etnia_indigenas = [];

    public $discapacidadesAlumno = [];
    public $discapacidadesAgregadas = [];
    public $discapacidadSeleccionada;
    public $discapacidades = [];

    public $enModoEdicion = false;
    public bool $soloEdicion = false;

    protected function rules()

    {
        return [
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'numero_documento' => [
                'required',
                'unique:personas,numero_documento,' . $this->persona_id,
                function ($attribute, $value, $fail) {
                    switch ((int) $this->tipo_documento_id) {
                        case 1:
                            if (!ctype_digit($value)) {
                                $fail('La cédula debe contener solo números.');
                            }
                            if (strlen($value) > 8) {
                                $fail('La cédula venezolana debe tener máximo 8 dígitos.');
                            }
                            break;

                        case 2:
                            if (!ctype_alnum($value)) {
                                $fail('La cédula de extranjero debe ser alfanumérica.');
                            }
                            if (strlen($value) > 12 || strlen($value) < 8) {
                                $fail('La cédula de extranjero debe tener entre 8 y 12 caracteres.');
                            }
                            break;

                        case 3:
                            if (!ctype_digit($value)) {
                                $fail('La cédula especial debe contener solo números.');
                            }
                            if (strlen($value) > 12 || strlen($value) < 10) {
                                $fail('La cédula especial debe tener entre 10 y 12 dígitos.');
                            }
                            break;
                    }
                }
            ],

            'primer_nombre' => 'required|string|max:50',
            'primer_apellido' => 'required|string|max:50',
            'segundo_nombre' => 'nullable|string|max:50',
            'tercer_nombre' => 'nullable|string|max:50',
            'segundo_apellido' => 'nullable|string|max:50',
            'genero_id' => 'required|exists:generos,id',

            'fecha_nacimiento' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $fecha = Carbon::parse($value);
                    $hoy = Carbon::today();

                    if ($fecha->greaterThan($hoy)) {
                        $fail('La fecha de nacimiento no puede ser futura.');
                        return;
                    }
                    $edad = $fecha->age;
                    if ($edad < 10 || $edad > 18) {
                        $fail('La edad debe estar entre 10 y 18 años.');
                    }
                }
            ],

            'talla_estudiante' => [
                'required',
                'regex:/^\d+([.,]\d+)?$/',
                function ($attribute, $value, $fail) {

                    $valor = (float) str_replace(',', '.', $value);

                    if ($valor <= 0) {
                        $fail('La estatura no es válida.');
                    }

                    if ($valor > 3 && ($valor < 50 || $valor > 250)) {
                        $fail('La estatura en cm debe estar entre 50 y 250.');
                    }

                    if ($valor <= 3 && ($valor < 0.5 || $valor > 2.5)) {
                        $fail('La estatura en metros debe estar entre 0.50 y 2.50.');
                    }
                }
            ],
            'peso_estudiante' => 'required|numeric|between:2,300',
            'talla_camisa_id' => 'required|exists:tallas,id',
            'talla_pantalon_id' => 'required|exists:tallas,id',
            'talla_zapato' => 'required|integer',

            'estado_id' => 'required|exists:estados,id',
            'municipio_id' => 'required|exists:municipios,id',
            'localidad_id' => 'required|exists:localidads,id',

            'lateralidad_id' => 'required|exists:lateralidads,id',
            'orden_nacimiento_id' => 'required|exists:orden_nacimientos,id',
            'etnia_indigena_id' => 'nullable|exists:etnia_indigenas,id',
            'lateralidad_id' => 'required|exists:lateralidads,id',
            'orden_nacimiento_id' => 'required|exists:orden_nacimientos,id',
            'etnia_indigena_id' => 'nullable|exists:etnia_indigenas,id',
        ];
    }

    protected $messages = [
        'tipo_documento_id.required' => 'Este campo es requerido',
        'numero_documento.required' => 'Este campo es requerido',
        'numero_documento.unique' => 'Esta cédula ya está registrada en el sistema',
        'primer_nombre.required' => 'Este campo es requerido',
        'primer_nombre.max' => 'El nombre no puede exceder 50 caracteres',
        'primer_apellido.required' => 'Este campo es requerido',
        'primer_apellido.max' => 'El apellido no puede exceder 50 caracteres',
        'genero_id.required' => 'Este campo es requerido',
        'fecha_nacimiento.required' => 'Este campo es requerido',
        'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida',
        'fecha_nacimiento.before:today' => 'La edad debe estar entre los 10 y 18 años',
        'talla_estudiante.required' => 'Este campo es requerido',
        'talla_estudiante.regex' => 'Ingrese una estatura válida (ej: 1.65 o 165)',
        'talla_estudiante.between' => 'Este campo debe estar entre 0.50 y 3.00',
        'peso_estudiante.required' => 'Este campo es requerido',
        'peso_estudiante.numeric' => 'Este campo debe ser un número',
        'peso_estudiante.between' => 'Este campo debe estar entre 2 y 300',
        'talla_camisa_id.required' => 'Este campo es requerido',
        'talla_zapato.required' => 'Este campo es requerido',
        'talla_zapato.integer' => 'La talla debe ser un número entero',
        'talla_pantalon_id.required' => 'Este campo es requerido',
        'estado_id.required' => 'Este campo es requerido',
        'municipio_id.required' => 'Este campo es requerido',
        'localidad_id.required' => 'Este campo es requerido',
        'lateralidad_id.required' => 'Este campo es requerido',
        'orden_nacimiento_id.required' => 'Este campo es requerido',
    ];

    public function updated($propertyName)
    {
        if (!$this->enModoEdicion) {
            return;
        }
        $this->validateOnly($propertyName);
    }

    public function updatedTipoDocumentoId($value)
    {
        $this->numero_documento = null;
        $this->resetErrorBag('numero_documento');

        switch ((int) $value) {
            case 1:
                $this->documento_maxlength = 8;
                $this->documento_pattern = '[0-9]+';
                $this->documento_placeholder = '12345678';
                $this->documento_inputmode = 'numeric';
                break;

            case 2:
                $this->documento_maxlength = 12;
                $this->documento_pattern = '[A-Za-z0-9]+';
                $this->documento_placeholder = 'AB1234567890';
                $this->documento_inputmode = 'text';
                break;

            case 3:
                $this->documento_maxlength = 12;
                $this->documento_pattern = '[0-9]+';
                $this->documento_placeholder = '123456789012';
                $this->documento_inputmode = 'numeric';
                break;

            default:
                $this->documento_maxlength = 8;
                $this->documento_pattern = '[0-9]+';
                $this->documento_placeholder = '';
                $this->documento_inputmode = 'numeric';
        }
    }

    public function updatedNumeroDocumento()
    {
        if ($this->tipo_documento_id) {
            $this->validateOnly('numero_documento');
        }
    }

    public function updatedPrimerNombre()
    {
        $this->validateOnly('primer_nombre');
    }

    public function updatedPrimerApellido()
    {
        $this->validateOnly('primer_apellido');
    }

    public function updatedGeneroId()
    {
        $this->validateOnly('genero_id');
    }

    public function updatedFechaNacimiento($value)
    {
        $this->calcularEdad($value);
        $this->validateOnly('fecha_nacimiento');
    }

    public function formatearEstatura()
    {
        $valor = preg_replace('/\D/', '', $this->talla_estudiante);

        if (strlen($valor) >= 2) {
            $this->talla_estudiante = substr($valor, 0, -2) . '.' . substr($valor, -2);
        }
    }

    public function validarEstatura()
    {
        $this->validateOnly('talla_estudiante');
    }

    public function mount($alumnoId,  $soloEdicion = false)
    {
        abort_unless(auth()->user()?->can('alumnos.gestionar'), 403, 'No tiene permiso para gestionar alumnos.');

        $this->alumnoId = $alumnoId;
        $this->cargarDatosIniciales();
        $this->soloEdicion = $soloEdicion;

        if ($this->soloEdicion) {
            $this->enModoEdicion = true;
        }
        $this->cargarAlumno();
        $this->cargarDiscapacidades();
    }

    private function cargarDatosIniciales()
    {
        $this->paises = \App\Models\Pais::where('status', true)->orderBy('nameES')->get();
        $this->tipos_documentos = \App\Models\TipoDocumento::where('status', true)->get();
        $this->generos = \App\Models\Genero::where('status', true)->get();
        $this->estados = Estado::where('status', true)->get();
        $this->tallas = Talla::all();
        $this->lateralidades = Lateralidad::where('status', true)->get();
        $this->orden_nacimientos = OrdenNacimiento::where('status', true)->get();
        $this->etnia_indigenas = EtniaIndigena::where('status', true)->get();

        $this->discapacidades = Discapacidad::where('status', true)
            ->orderBy('nombre_discapacidad', 'asc')
            ->get();
    }

    private function cargarAlumno()
    {
        $alumno = Alumno::with('persona.localidad.municipio.estado')->findOrFail($this->alumnoId);
        $persona = $alumno->persona;

        $this->persona_id = $persona->id;
        $this->tipo_documento_id = $persona->tipo_documento_id;
        $this->numero_documento = $persona->numero_documento;
        $this->primer_nombre = $persona->primer_nombre;
        $this->segundo_nombre = $persona->segundo_nombre;
        $this->tercer_nombre = $persona->tercer_nombre;
        $this->primer_apellido = $persona->primer_apellido;
        $this->segundo_apellido = $persona->segundo_apellido;
        $this->genero_id = $persona->genero_id;
        $this->fecha_nacimiento = $persona->fecha_nacimiento->format('Y-m-d');

        $this->talla_estudiante = $alumno->estatura;
        $this->peso_estudiante = $alumno->peso;
        $this->talla_camisa_id = $alumno->talla_camisa_id;
        $this->talla_zapato = $alumno->talla_zapato;
        $this->talla_pantalon_id = $alumno->talla_pantalon_id;

        if (
            $persona->localidad &&
            $persona->localidad->municipio &&
            $persona->localidad->municipio->estado
        ) {
            $estado = $persona->localidad->municipio->estado;
            $municipio = $persona->localidad->municipio;
            $localidad = $persona->localidad;
            $this->pais_id = $estado->pais_id;
            $this->estados = Estado::where('pais_id', $this->pais_id)
                ->where('status', true)
                ->orderBy('nombre_estado')
                ->get();

            $this->estado_id = $estado->id;

            $this->municipios = Municipio::where('estado_id', $this->estado_id)
                ->where('status', true)
                ->orderBy('nombre_municipio')
                ->get();

            $this->municipio_id = $municipio->id;

            $this->localidades = Localidad::where('municipio_id', $this->municipio_id)
                ->where('status', true)
                ->orderBy('nombre_localidad')
                ->get();

            $this->localidad_id = $localidad->id;
        }


        $this->lateralidad_id = $alumno->lateralidad_id;
        $this->orden_nacimiento_id = $alumno->orden_nacimiento_id;
        $this->etnia_indigena_id = $alumno->etnia_indigena_id;

        $this->calcularEdad($this->fecha_nacimiento);
    }

    private function cargarDiscapacidades()
    {
        $alumno = \App\Models\Alumno::with('discapacidades')->find($this->alumnoId);

        if ($alumno && $alumno->discapacidades) {
            $this->discapacidadesAlumno = $alumno->discapacidades->map(function ($disc) {
                return [
                    'id' => $disc->id,
                    'nombre' => $disc->nombre_discapacidad
                ];
            })->toArray();
        }
    }

    public function updatedPaisId($paisId)
    {
        $this->estados = Estado::where('pais_id', $paisId)
            ->where('status', true)
            ->orderBy('nombre_estado')
            ->get();

        $this->estado_id = null;
        $this->municipio_id = null;
        $this->localidad_id = null;

        $this->municipios = [];
        $this->localidades = [];
    }


    public function updatedEstadoId($value)
    {
        $this->cargarMunicipios($value);
        $this->municipio_id = null;
        $this->localidad_id = null;
        $this->localidades = [];
    }

    public function updatedMunicipioId($value)
    {
        $this->cargarLocalidades($value);
        $this->localidad_id = null;
    }

    private function cargarMunicipios($estadoId)
    {
        $this->municipios = Municipio::where('estado_id', $estadoId)
            ->where('status', true)
            ->orderBy('nombre_municipio')
            ->get();
    }

    private function cargarLocalidades($municipioId)
    {
        $this->localidades = Localidad::where('municipio_id', $municipioId)
            ->where('status', true)
            ->orderBy('nombre_localidad')
            ->get();
    }

    private function calcularEdad($fecha)
    {
        if (!$fecha) return;

        try {
            $fechaNac = Carbon::parse($fecha);
            $hoy = Carbon::now();

            $this->edad = $fechaNac->diffInYears($hoy);
            $this->meses = $fechaNac->diffInMonths($hoy) % 12;
        } catch (\Exception $e) {
            $this->edad = 0;
            $this->meses = 0;
        }
    }

    public function habilitarEdicion()
    {
        $this->enModoEdicion = true;
        $this->discapacidadesAgregadas = $this->discapacidadesAlumno;
    }

    public function cancelarEdicion()
    {
        $this->enModoEdicion = false;
        $this->cargarAlumno();
        $this->cargarDiscapacidades();
        $this->discapacidadSeleccionada = null;
        $this->resetErrorBag();
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

        $discapacidad = Discapacidad::find($this->discapacidadSeleccionada);

        if ($discapacidad) {
            $nueva = [
                'id' => $discapacidad->id,
                'nombre' => $discapacidad->nombre_discapacidad
            ];

            $this->discapacidadesAgregadas[] = $nueva;

            \App\Models\DiscapacidadEstudiante::updateOrCreate(
                [
                    'alumno_id' => $this->alumnoId,
                    'discapacidad_id' => $discapacidad->id
                ],
                ['status' => true]
            );

            $this->discapacidadesAlumno[] = $nueva;

            $this->discapacidadSeleccionada = null;
            $this->resetErrorBag('discapacidadSeleccionada');

            session()->flash('success_temp', 'Discapacidad agregada exitosamente.');
        }
    }

    public function eliminarDiscapacidad($discapacidadId)
    {
        \App\Models\DiscapacidadEstudiante::where('alumno_id', $this->alumnoId)
            ->where('discapacidad_id', $discapacidadId)
            ->update(['status' => false]);

        $this->discapacidadesAlumno = array_values(
            array_filter(
                $this->discapacidadesAlumno,
                fn($d) => $d['id'] != $discapacidadId
            )
        );

        $this->discapacidadesAgregadas = array_values(
            array_filter(
                $this->discapacidadesAgregadas,
                fn($d) => $d['id'] != $discapacidadId
            )
        );

        session()->flash('success_temp', 'Discapacidad eliminada exitosamente.');
    }

    private function guardarDiscapacidades()
    {
        \App\Models\DiscapacidadEstudiante::where('alumno_id', $this->alumnoId)
            ->update(['status' => false]);

        foreach ($this->discapacidadesAgregadas as $discapacidad) {
            \App\Models\DiscapacidadEstudiante::updateOrCreate(
                [
                    'alumno_id' => $this->alumnoId,
                    'discapacidad_id' => $discapacidad['id']
                ],
                [
                    'status' => true
                ]
            );
        }
    }

    public function guardar()
    {
        abort_unless(auth()->user()?->can('alumnos.gestionar'), 403, 'No tiene permiso para gestionar alumnos.');

        if (!$this->localidad_id) {

            session()->flash('error', 'Debe seleccionar una localidad.');
            return;
        }

        $this->validate();

        try {
            DB::beginTransaction();

            $persona = Persona::findOrFail($this->persona_id);
            $persona->update([
                'primer_nombre' => $this->primer_nombre,
                'segundo_nombre' => $this->segundo_nombre,
                'tercer_nombre' => $this->tercer_nombre,
                'primer_apellido' => $this->primer_apellido,
                'segundo_apellido' => $this->segundo_apellido,
                'tipo_documento_id' => $this->tipo_documento_id,
                'numero_documento' => $this->numero_documento,
                'genero_id' => $this->genero_id,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'localidad_id' => $this->localidad_id,
            ]);

            $alumno = Alumno::findOrFail($this->alumnoId);
            $alumno->update([
                'talla_camisa_id' => $this->talla_camisa_id,
                'talla_pantalon_id' => $this->talla_pantalon_id,
                'talla_zapato' => $this->talla_zapato,
                'peso' => $this->peso_estudiante,
                'estatura' =>  $this->talla_estudiante,
                'lateralidad_id' => $this->lateralidad_id,
                'orden_nacimiento_id' => $this->orden_nacimiento_id,
                'etnia_indigena_id' => $this->etnia_indigena_id,
            ]);

            $this->guardarDiscapacidades();

            DB::commit();

            $this->enModoEdicion = false;
            $this->cargarDiscapacidades();
            $this->dispatch('actualizarAlumno');
            session()->flash('success', 'Datos del alumno y discapacidades actualizados correctamente.');
            if ($this->soloEdicion) {
                return redirect()->route('admin.alumnos.index');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    protected $listeners = [
        'localidadCreada' => 'manejarLocalidadCreada',
        'localidadCreada' => 'refrescarLocalidades',
        'estadoCreado' => 'manejarEstadoCreado',
        'municipioCreado' => 'manejarMunicipioCreado',
    ];

    public function manejarLocalidadCreada($id, $municipio_id)
    {
        if ($this->municipio_id == $municipio_id) {
            $this->localidades = \App\Models\Localidad::where('municipio_id', $this->municipio_id)
                ->where('status', true)
                ->orderBy('nombre_localidad')
                ->get();

            $this->localidad_id = $id;
            $this->dispatch('localidadSeleccionada');
            session()->flash('success', 'Localidad creada y seleccionada correctamente.');
        }
    }

    public function manejarEstadoCreado($id, $pais_id)
    {
        if ($this->pais_id == $pais_id) {
            $this->estados = Estado::where('pais_id', $this->pais_id)
                ->where('status', true)
                ->orderBy('nombre_estado')
                ->get();

            $this->estado_id = $id;

            // fuerza carga de municipios vacía
            $this->updatedEstadoId($id);
        }
    }

    public function manejarMunicipioCreado($id, $estado_id)
    {
        if ($this->estado_id == $estado_id) {
            $this->municipios = Municipio::where('estado_id', $this->estado_id)
                ->where('status', true)
                ->orderBy('nombre_municipio')
                ->get();

            $this->municipio_id = $id;

            $this->updatedMunicipioId($id);
        }
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

    public function render()
    {
        return view('livewire.admin.alumnos.alumno-edit');
    }
}

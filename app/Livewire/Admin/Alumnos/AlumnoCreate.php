<?php

namespace App\Livewire\Admin\Alumnos;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Models\Persona;
use App\Models\Alumno;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Localidad;
use App\Models\OrdenNacimiento;
use App\Models\Lateralidad;
use App\Models\AnioEscolar;
use App\Models\EtniaIndigena;
use App\Models\Talla;
use Livewire\Attributes\On;


class AlumnoCreate extends Component
{
    use WithFileUploads;

    public $alumno_id;
    public $persona_id;
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
    public $tallas = [];
    public $talla_zapato;
    public $talla_pantalon_id;
    public $paisId = null;
    public $paises = [];

    public $estado_id;
    public $municipio_id;
    public $localidad_id;
    public $instituciones;

    public $tipos_documentos = [];
    public $generos = [];
    public $lateralidades = [];
    public $lateralidad_id;
    public $orden_nacimiento_id;
    public $orden_nacimientos = [];
    public $estados = [];
    public $municipios = [];
    public $localidades = [];
    public $etnia_indigenas = [];
    public $etnia_indigena_id;
    public $anioEscolarActivo = false;

    public function mount()
    {
        abort_unless(auth()->user()?->can('alumnos.gestionar'), 403, 'No tiene permiso para gestionar alumnos.');
    }

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

                    // Normalizar coma a punto
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
        ];
    }

    protected $messages = [
        'tipo_documento_id.required' => 'Este campo es requerido',
        'numero_documento.required' => 'Este campo es requerido',
        'numero_documento.digits_between' => 'La cédula debe tener entre 6 y 8 dígitos',
        'numero_documento.unique' => 'Esta cédula ya está registrada en el sistema',
        'primer_nombre.required' => 'Este campo es requerido',
        'primer_apellido.required' => 'Este campo es requerido',
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
        'talla_zapato.integer' => 'Este campo debe ser un número',
        'talla_pantalon_id.required' => 'Este campo es requerido',
        'estado_id.required' => 'Este campo es requerido',
        'municipio_id.required' => 'Este campo es requerido',
        'localidad_id.required' => 'Este campo es requerido',
        'lateralidad_id.required' => 'Este campo es requerido',
        'orden_nacimiento_id.required' => 'Este campo es requerido',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedTipoDocumentoId($value)
    {
        $this->numero_documento = null;
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

    public function validarEstatura()
    {
        $this->validateOnly('talla_estudiante');
    }

    public function mount($alumno_id = null)
    {
        $this->verificarAnioEscolar();
        $this->alumno_id = $alumno_id;
        $this->cargarDatosIniciales();

        if ($alumno_id) {
            $this->cargarAlumno($alumno_id);
        } else {
            $this->autoseleccionarUbicacionPorDefecto();
        }
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

        $estado = Estado::where('status', true)
            ->where('pais_id', $pais->id)
            ->where('nombre_estado', 'Portuguesa')
            ->first();

        if (!$estado) {
            return;
        }

        $this->estado_id = $estado->id;
        $this->updatedEstadoId($this->estado_id);

        $municipio = Municipio::where('status', true)
            ->where('estado_id', $estado->id)
            ->where('nombre_municipio', 'Araure')
            ->first();

        if (!$municipio) {
            return;
        }

        $this->municipio_id = $municipio->id;
        $this->updatedMunicipioId($this->municipio_id);
    }

    private function verificarAnioEscolar()
    {
        $this->anioEscolarActivo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->exists();
    }

    public function cargarDatosIniciales()
    {
        $this->paises = \App\Models\Pais::where('status', true)->orderBy('nameES')->get();
        $this->tipos_documentos = \App\Models\TipoDocumento::where('status', true)->get();
        $this->generos = \App\Models\Genero::where('status', true)->get();
        $this->lateralidades = Lateralidad::where('status', true)->get();
        $this->orden_nacimientos = OrdenNacimiento::where('status', true)->get();
        $this->etnia_indigenas = EtniaIndigena::where('status', true)->get();
        $this->tallas = Talla::all();
    }

    public function cargarAlumno($id)
    {
        $alumno = Alumno::with(
            'persona',

        )->findOrFail($id);
        $persona = $alumno->persona;
        $this->persona_id = $persona->id;
        $this->tipo_documento_id = $persona->tipo_documento_id;
        $this->numero_documento = $persona->numero_documento;
        $this->fecha_nacimiento = $persona->fecha_nacimiento->format('Y-m-d');
        $this->primer_nombre = $persona->primer_nombre;
        $this->segundo_nombre = $persona->segundo_nombre;
        $this->tercer_nombre = $persona->tercer_nombre;
        $this->primer_apellido = $persona->primer_apellido;
        $this->segundo_apellido = $persona->segundo_apellido;
        $this->genero_id = $persona->genero_id;
        $this->talla_camisa_id = $alumno->talla_camisa_id;
        $this->talla_pantalon_id = $alumno->talla_pantalon_id;
        $this->talla_zapato = $alumno->talla_zapato;
        $this->peso_estudiante = $alumno->peso;
        $this->talla_estudiante = $alumno->estatura;
        $this->lateralidad_id = $alumno->lateralidad_id;
        $this->orden_nacimiento_id = $alumno->orden_nacimiento_id;
        $this->etnia_indigena_id = $alumno->etnia_indigena_id;
        $this->updatedEstadoId($persona->localidad->municipio->estado_id);
        $this->updatedMunicipioId($persona->localidad->municipio_id);
        $this->updatedFechaNacimiento($this->fecha_nacimiento);
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


    public function updatedEstadoId($estadoId)
    {
        if (!$estadoId) {
            $this->municipios = [];
            $this->municipio_id = null;
            $this->localidades = [];
            $this->localidad_id = null;
            return;
        }

        $this->municipios = Municipio::where('estado_id', $estadoId)
            ->where('status', true)
            ->orderBy('nombre_municipio')
            ->get();

        $this->municipio_id = null;
        $this->localidad_id = null;
        $this->localidades = [];
    }


    public function updatedMunicipioId($municipioId)
    {
        $this->localidades = Localidad::where('municipio_id', $municipioId)
            ->where('status', true)
            ->orderBy('nombre_localidad')
            ->get();
        $this->localidad_id = null;
    }

    public function updatedFechaNacimiento($value)
    {
        if (!$value) return;
        try {
            $fecha = Carbon::parse($value);
            $hoy = Carbon::now();
            $this->edad = $fecha->diffInYears($hoy);
            $this->meses = $fecha->diffInMonths($hoy) % 12;
        } catch (\Exception $e) {
            $this->edad = 0;
            $this->meses = 0;
        }
    }

    public function inscripcionAnterior($anioActualId)
    {
        return $this->inscripcionProsecucions()
            ->where('anio_escolar_id', '!=', $anioActualId)
            ->latest('anio_escolar_id')
            ->first()
            ?? $this->inscripciones()
            ->where('anio_escolar_id', '!=', $anioActualId)
            ->latest('anio_escolar_id')
            ->first();
    }

    public function save()
    {
        $this->talla_estudiante = (float) str_replace(',', '.', $this->talla_estudiante);
        $this->validate();

        try {
            DB::beginTransaction();
            $persona = Persona::updateOrCreate(
                ['id' => $this->persona_id],
                [
                    'primer_nombre' => $this->primer_nombre,
                    'segundo_nombre' => $this->segundo_nombre ?? null,
                    'tercer_nombre' => $this->tercer_nombre ?? null,
                    'primer_apellido' => $this->primer_apellido,
                    'segundo_apellido' => $this->segundo_apellido ?? null,
                    'tipo_documento_id' => $this->tipo_documento_id,
                    'numero_documento' => $this->numero_documento,
                    'genero_id' => $this->genero_id,
                    'fecha_nacimiento' => $this->fecha_nacimiento,
                    'localidad_id' => $this->localidad_id,
                    'status' => true,
                ]
            );
            if ($this->alumno_id) {
                $alumno = Alumno::findOrFail($this->alumno_id);
                $alumno->update([
                    'persona_id' => $persona->id,
                    'talla_camisa_id' => $this->talla_camisa_id,
                    'talla_pantalon_id' => $this->talla_pantalon_id,
                    'talla_zapato' => $this->talla_zapato,
                    'peso' => $this->peso_estudiante,
                    'estatura' => $this->talla_estudiante,
                    'lateralidad_id' => $this->lateralidad_id,
                    'orden_nacimiento_id' => $this->orden_nacimiento_id,
                    'etnia_indigena_id' => $this->etnia_indigena_id,
                    'status' => 'Activo',
                ]);
            } else {
                Alumno::create([
                    'persona_id' => $persona->id,
                    'talla_camisa_id' => $this->talla_camisa_id,
                    'talla_pantalon_id' => $this->talla_pantalon_id,
                    'talla_zapato' => $this->talla_zapato,
                    'peso' => $this->peso_estudiante,
                    'estatura' =>  $this->talla_estudiante,
                    'lateralidad_id' => $this->lateralidad_id,
                    'orden_nacimiento_id' => $this->orden_nacimiento_id,
                    'etnia_indigena_id' => $this->etnia_indigena_id,
                    'status' => 'Activo',
                ]);
            }
            DB::commit();
            session()->flash('success', 'Alumno guardado exitosamente');
            return redirect()->route('admin.alumnos.index');
        } catch (\Exception $e) {

            DB::rollBack();
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    protected $listeners = [
        'solicitarDatosAlumno' => 'enviarDatosAlumno',
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
        if ($this->paisId == $pais_id) {
            $this->estados = Estado::where('pais_id', $this->paisId)
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

    public function enviarDatosAlumno()
    {
        abort_unless(auth()->user()?->can('alumnos.gestionar'), 403, 'No tiene permiso para gestionar alumnos.');
        $datos = $this->validate();
        $this->dispatch('recibirDatosAlumno', datos: $datos);
    }


    public function render()
    {
        return view('livewire.admin.alumnos.alumno-create');
    }
}

<?php

namespace App\Livewire\Admin\TransaccionDocente;

use Livewire\Component;
use App\Models\Docente;
use App\Models\AreaEstudioRealizado;
use App\Models\Grado;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\DocenteAreaGrado as ModeloDocenteAreaGrado;
use App\Models\Seccion;
use App\Models\GrupoEstable;

class DocenteAreaGrado extends Component
{
    public $docenteId;
    public $docentes = [];
    public $docenteSeleccionado = null;
    public $materiaId;
    public $materias = [];
    public $gradoAreaId;
    public $gradosArea = [];
    public $seccionAreaId;
    public $seccionesArea = [];
    public $grupoEstableId;
    public $gruposEstables = [];
    public $gradoGrupoId;
    public $gradosGrupo = [];
    public $seccionGrupoId;
    public $seccionesGrupo = [];
    public $asignaciones = [];
    public $modoEditar = false;
    public $asignacionAEliminar = null;
    public $alertaAsignacion = null;


    protected $listeners = [
        'asignacionCreada' => 'manejarAsignacionCreada',
    ];

    public function manejarAsignacionCreada($data)
    {
        if ($this->docenteSeleccionado) {
            $this->cargarMateriasPorEstudios();
            session()->flash('success', 'Nueva área-estudio disponible para asignar.');
        }
    }

    public function mount($docenteId = null)
    {
        abort_unless(auth()->user()?->can('asignacion_docente.ver'), 403);

        $this->cargarGruposEstables();

        if ($docenteId) {
            $this->modoEditar = true;
            $this->docenteId = $docenteId;
            $this->cargarDatosDocente();
            $this->cargarMateriasPorEstudios();
            $this->gradosArea = collect();
            $this->gradosGrupo = collect();
            $this->seccionesArea = collect();
            $this->seccionesGrupo = collect();
            $this->cargarAsignaciones();
        } else {
            $this->cargarDocentes();
        }
    }

    public function cargarDatosDocente()
    {
        $this->docenteSeleccionado = Docente::with([
            'persona',
            'persona.prefijoTelefono',
            'persona.prefijoDos',
            'persona.tipoDocumento',
            'persona.genero',
            'detalleEstudios.estudiosRealizado',
            'docenteAreaGrado.areaEstudios.areaFormacion',
            'docenteAreaGrado.grado'
        ])->findOrFail($this->docenteId);
    }

    public function cargarDocentes()
    {
        $this->docentes = Docente::with([
            'persona.tipoDocumento',
            'persona.genero',
            'persona.prefijoTelefono',
            'persona.prefijoDos',
            'detalleDocenteEstudio.estudiosRealizado'
        ])
            ->whereHas('persona', fn($q) => $q->where('status', true))
            ->where('status', true)
            ->orderBy('codigo', 'asc')
            ->get();
    }

    public function cargarAsignaciones()
    {
        if (!$this->docenteSeleccionado) {
            $this->asignaciones = collect();
            return;
        }

        $this->asignaciones = ModeloDocenteAreaGrado::with([
            'areaEstudios.areaFormacion',
            'grado',
            'seccion',
            'grupoEstable',
            'gradoGrupoEstable',
        ])
            ->whereHas('detalleDocenteEstudio', function ($q) {
                $q->where('docente_id', $this->docenteSeleccionado->id);
            })
            ->where('status', true)
            ->orderBy('tipo_asignacion')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPuedeAgregarAreaProperty()
    {
        return $this->materiaId && $this->gradoAreaId && $this->seccionAreaId;
    }

    public function getPuedeAgregarGrupoProperty()
    {
        return $this->grupoEstableId && $this->gradoGrupoId;
    }

    public function updatedDocenteId($value)
    {
        if (!$value) {
            $this->reset([
                'docenteSeleccionado',
                'materiaId',
                'materias',
                'gradoAreaId',
                'gradosArea',
                'seccionAreaId',
                'seccionesArea',
                'grupoEstableId',
                'gradoGrupoId',
                'gradosGrupo',
                'seccionGrupoId',
                'seccionesGrupo',
                'asignaciones',
            ]);
            $this->dispatch('resetSelects');
            return;
        }

        $this->docenteSeleccionado = Docente::with([
            'persona.tipoDocumento',
            'persona.genero',
            'persona.prefijoTelefono',
            'persona.prefijoDos',
            'detalleDocenteEstudio.estudiosRealizado'
        ])->find($value);

        $this->reset([
            'materiaId',
            'gradoAreaId',
            'seccionAreaId',
            'gradosArea',
            'seccionesArea',
            'grupoEstableId',
            'gradoGrupoId',
            'seccionGrupoId',
            'gradosGrupo',
            'seccionesGrupo'
        ]);

        $this->cargarMateriasPorEstudios();
        $this->cargarAsignaciones();
        $this->dispatch('resetSelects');

        session()->flash('success', 'Docente seleccionado correctamente.');
    }

    public function cargarMateriasPorEstudios()
    {
        if (!$this->docenteSeleccionado) {
            $this->materias = [];
            return;
        }

        $estudiosIds = $this->docenteSeleccionado
            ->detalleDocenteEstudio()
            ->where('status', true)
            ->pluck('estudios_id')
            ->toArray();

        if (empty($estudiosIds)) {
            $this->materias = [];
            return;
        }

        $this->materias = AreaEstudioRealizado::with('areaFormacion')
            ->where('status', true)
            ->whereIn('estudios_id', $estudiosIds)
            ->get()
            ->sortBy('areaFormacion.nombre_area_formacion')
            ->values();
    }

    public function cargarGruposEstables()
    {
        $this->gruposEstables = GrupoEstable::where('status', true)
            ->orderBy('nombre_grupo_estable', 'asc')
            ->get();
    }

    public function updatedMateriaId()
    {
        $this->alertaAsignacion = null;
        $this->reset(['gradoAreaId', 'seccionAreaId']);
        $this->seccionesArea = collect();

        if (!$this->materiaId) {
            $this->gradosArea = collect();
            return;
        }

        $areaEstudio = AreaEstudioRealizado::with('areaFormacion')->find($this->materiaId);

        if (!$areaEstudio || !$areaEstudio->area_formacion_id) {
            $this->gradosArea = collect();
            return;
        }

        $areaFormacionId = $areaEstudio->area_formacion_id;

        $this->gradosArea = Grado::where('status', true)
            ->whereHas('gradoAreaFormacion', function ($q) use ($areaFormacionId) {
                $q->where('area_formacion_id', $areaFormacionId)
                    ->where('status', true);
            })
            ->orderBy('numero_grado', 'asc')
            ->get();
    }

    public function updatedGradoAreaId()
    {
        $this->reset('seccionAreaId');

        if (!$this->gradoAreaId) {
            $this->seccionesArea = collect();
            return;
        }

        $this->seccionesArea = Seccion::where('status', true)
            ->where('grado_id', $this->gradoAreaId)
            ->orderBy('nombre', 'asc')
            ->get();
    }

    public function updatedGrupoEstableId()
    {
        $this->alertaAsignacion = null;
        $this->reset(['gradoGrupoId', 'seccionGrupoId']);
        $this->seccionesGrupo = collect();

        if (!$this->grupoEstableId) {
            $this->gradosGrupo = collect();
            return;
        }

        $this->gradosGrupo = Grado::where('status', true)
            ->orderBy('numero_grado', 'asc')
            ->get();
    }

    public function updatedGradoGrupoId()
    {
        $this->reset('seccionGrupoId');

        if (!$this->gradoGrupoId) {
            $this->seccionesGrupo = collect();
            return;
        }

        $this->seccionesGrupo = Seccion::where('status', true)
            ->where('grado_id', $this->gradoGrupoId)
            ->orderBy('nombre', 'asc')
            ->get();
    }

    public function agregarAsignacionArea()
    {
        abort_unless(auth()->user()?->can('asignacion_docente.gestionar'), 403);

        $this->validate([
            'materiaId' => 'required|exists:area_estudio_realizados,id',
            'gradoAreaId' => 'required|exists:grados,id',
            'seccionAreaId' => 'required|exists:seccions,id',
        ]);

        DB::beginTransaction();

        try {
            $area = AreaEstudioRealizado::findOrFail($this->materiaId);

            $gradoTieneArea = \App\Models\GradoAreaFormacion::where([
                'grado_id' => $this->gradoAreaId,
                'area_formacion_id' => $area->area_formacion_id,
                'status' => true
            ])->exists();

            if (!$gradoTieneArea) {
                throw ValidationException::withMessages([
                    'gradoAreaId' => 'El nivel académico no tiene asignada esta área de formación.'
                ]);
            }

            $detalleEstudio = $this->docenteSeleccionado->detalleDocenteEstudio()
                ->where('estudios_id', $area->estudios_id)
                ->where('status', true)
                ->first();

            if (!$detalleEstudio) {
                throw ValidationException::withMessages([
                    'materiaId' => 'El docente no posee el estudio correspondiente a esta área.'
                ]);
            }

            $existe = ModeloDocenteAreaGrado::where([
                'docente_estudio_realizado_id' => $detalleEstudio->id,
                'area_estudio_realizado_id' => $this->materiaId,
                'grado_id' => $this->gradoAreaId,
                'seccion_id' => $this->seccionAreaId,
                'tipo_asignacion' => 'area',
                'status' => true,
            ])->exists();

            if ($existe) {
                throw ValidationException::withMessages([
                    'materiaId' => 'Esta asignación ya existe para este docente.'
                ]);
            }

            $asignacionExistente = ModeloDocenteAreaGrado::where([
                'grado_id' => $this->gradoAreaId,
                'seccion_id' => $this->seccionAreaId,
                'tipo_asignacion' => 'area',
                'status' => true,
            ])
                ->whereHas('areaEstudios', function ($q) use ($area) {
                    $q->where('area_formacion_id', $area->area_formacion_id);
                })
                ->with('detalleDocenteEstudio.docente.persona')
                ->first();

            if ($asignacionExistente) {
                $docente = $asignacionExistente->detalleDocenteEstudio->docente;
                $nombre = $docente->persona->primer_nombre . ' ' . $docente->persona->primer_apellido;

                $this->alertaAsignacion = " Esta área ya está asignada en este nivel academico y sección al docente {$nombre}.";
                return;
            }


            ModeloDocenteAreaGrado::create([
                'docente_estudio_realizado_id' => $detalleEstudio->id,
                'area_estudio_realizado_id' => $this->materiaId,
                'grado_id' => $this->gradoAreaId,
                'seccion_id' => $this->seccionAreaId,
                'tipo_asignacion' => 'area',
                'status' => true,
            ]);

            DB::commit();

            $this->cargarAsignaciones();
            $this->reset(['materiaId', 'gradoAreaId', 'seccionAreaId', 'gradosArea', 'seccionesArea']);
            $this->dispatch('resetSelects');

            session()->flash('success', 'Asignación de área registrada correctamente.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Error al registrar la asignación: ' . $e->getMessage());
        }
    }

    public function agregarAsignacionGrupo()
    {
        abort_unless(auth()->user()?->can('asignacion_docente.gestionar'), 403);

        $this->validate([
            'grupoEstableId' => 'required|exists:grupo_estables,id',
            'gradoGrupoId'   => 'required|exists:grados,id',
        ]);

        DB::beginTransaction();

        try {

            $detalleEstudio = $this->docenteSeleccionado
                ->detalleDocenteEstudio()
                ->where('status', true)
                ->first();

            if (!$detalleEstudio) {
                throw ValidationException::withMessages([
                    'grupoEstableId' => 'El docente no tiene estudios registrados.',
                ]);
            }

            $existeGrupo = ModeloDocenteAreaGrado::where([
                'docente_estudio_realizado_id' => $detalleEstudio->id,
                'tipo_asignacion' => 'grupo_estable',
                'status' => true,
            ])->exists();

            if ($existeGrupo) {
                throw ValidationException::withMessages([
                    'grupoEstableId' => 'Este docente ya tiene un grupo estable asignado.',
                ]);
            }

            $asignacionExistente = ModeloDocenteAreaGrado::where([
                'grupo_estable_id' => $this->grupoEstableId,
                'grado_grupo_estable_id' => $this->gradoGrupoId,
                'tipo_asignacion' => 'grupo_estable',
                'status' => true,
            ])
                ->with('detalleDocenteEstudio.docente.persona')
                ->first();

            if ($asignacionExistente) {
                $docente = $asignacionExistente->detalleDocenteEstudio->docente;
                $nombre = $docente->persona->primer_nombre . ' ' . $docente->persona->primer_apellido;

                $this->alertaAsignacion = " Este grupo estable ya está asignado al docente {$nombre}.";
                return;
            }



            ModeloDocenteAreaGrado::create([
                'docente_estudio_realizado_id' => $detalleEstudio->id,
                'grupo_estable_id' => $this->grupoEstableId,
                'grado_grupo_estable_id' => $this->gradoGrupoId,
                'tipo_asignacion' => 'grupo_estable',
                'status' => true,
            ]);



            DB::commit();

            $this->cargarAsignaciones();
            $this->reset(['grupoEstableId', 'gradoGrupoId', 'gradosGrupo']);
            $this->dispatch('resetSelects');

            session()->flash('success', 'Asignación de grupo estable registrada correctamente.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Error al registrar el grupo estable: ' . $e->getMessage());
        }
    }


    public function eliminarAsignacion($asignacionId)
    {
        abort_unless(auth()->user()?->can('asignacion_docente.gestionar'), 403);

        DB::beginTransaction();
        try {
            $asignacion = ModeloDocenteAreaGrado::findOrFail($asignacionId);
            $asignacion->update(['status' => false]);

            DB::commit();
            $this->cargarAsignaciones();

            session()->flash('success', 'Asignación eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al eliminar la asignación: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.transaccion-docente.docente-area-grado', [
            'totalGrados' => Grado::where('status', true)->count(),
            'totalSecciones' => Seccion::where('status', true)->count(),
        ]);
    }
}

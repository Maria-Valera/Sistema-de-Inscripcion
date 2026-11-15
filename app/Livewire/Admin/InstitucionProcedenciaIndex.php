<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\InstitucionProcedencia;
use App\Models\Localidad;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\AnioEscolar;

class InstitucionProcedenciaIndex extends Component
{
    use WithPagination;

    public $institucion_id;
    public $nombre_institucion;
    public $estado_id;
    public $municipio_id;
    public $localidad_id;
    public $search = '';
    public $updateMode = false;

    public $anioEscolarActivo = false;
    public $municipios = [];
    public $localidades = [];
    public $modoModal = false;

    protected $rules = [
        'nombre_institucion' => 'required|string|max:255',
        'estado_id' => 'required|integer|exists:estados,id',
        'municipio_id' => 'required|integer|exists:municipios,id',
        'localidad_id' => 'required|integer|exists:localidads,id',
    ];

    public function mount($modoModal = false)
    {
        $this->modoModal = $modoModal;
        $this->verificarAnioEscolar();
    }

    private function verificarAnioEscolar()
    {
        $this->anioEscolarActivo = AnioEscolar::activos()
            ->where('cierre_anio_escolar', '>=', now())
            ->exists();
    }

    private function verificarAccion()
    {
        if (!$this->anioEscolarActivo) {
            session()->flash('warning', 'Debe registrar un Calendario Escolar activo para realizar esta acción.');
            return false;
        }
        return true;
    }

    // ========================================
    // LISTENERS DE CAMBIO (SELECTS DINÁMICOS)
    // ========================================

    /**
     * Cuando cambia el estado, cargar municipios de ese estado
     */
    public function updatedEstadoId($estadoId)
    {
        if ($estadoId) {
            $this->municipios = Municipio::where('estado_id', $estadoId)
                ->where('status', true)
                ->orderBy('nombre_municipio', 'asc')
                ->get();
        } else {
            $this->municipios = [];
        }

        // Resetear selecciones dependientes
        $this->municipio_id = null;
        $this->localidad_id = null;
        $this->localidades = [];
    }

    /**
     * Cuando cambia el municipio, cargar localidades de ese municipio
     */
    public function updatedMunicipioId($municipioId)
    {
        if ($municipioId) {
            $this->localidades = Localidad::where('municipio_id', $municipioId)
                ->where('status', true)
                ->orderBy('nombre_localidad', 'asc')
                ->get();
        } else {
            $this->localidades = [];
        }

        // Resetear selección dependiente
        $this->localidad_id = null;
    }

    public function render()
    {
        $estados = Estado::where('status', true)
            ->select('id', 'nombre_estado')
            ->orderBy('nombre_estado', 'asc')
            ->get();

        $instituciones = InstitucionProcedencia::where('institucion_procedencias.status', true)
            ->join('localidads', 'institucion_procedencias.localidad_id', '=', 'localidads.id')
            ->join('municipios', 'localidads.municipio_id', '=', 'municipios.id')
            ->join('estados', 'municipios.estado_id', '=', 'estados.id')
            ->when($this->search, function ($query) {
                $query->where('institucion_procedencias.nombre_institucion', 'like', '%' . $this->search . '%')
                    ->orWhere('localidads.nombre_localidad', 'like', '%' . $this->search . '%')
                    ->orWhere('municipios.nombre_municipio', 'like', '%' . $this->search . '%')
                    ->orWhere('estados.nombre_estado', 'like', '%' . $this->search . '%');
            })
            ->select(
                'institucion_procedencias.id',
                'institucion_procedencias.nombre_institucion',
                'institucion_procedencias.localidad_id',
                'institucion_procedencias.status',
                'localidads.nombre_localidad as localidad_nombre',
                'municipios.nombre_municipio as municipio_nombre',
                'estados.nombre_estado as estado_nombre'
            )
            ->orderBy('nombre_institucion', 'asc')
            ->paginate(10);

        return view('livewire.admin.institucion-procedencia-index', compact('instituciones', 'estados'));
    }

    public function paginationView()
    {
        return 'vendor.livewire.bootstrap-custom';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // ========================================
    // CRUD 
    // ========================================

    public function resetInputFields()
    {
        $this->institucion_id = null;
        $this->nombre_institucion = '';
        $this->estado_id = null;
        $this->municipio_id = null;
        $this->localidad_id = null;
        $this->municipios = [];
        $this->localidades = [];
        $this->updateMode = false;
        $this->resetValidation();
    }

    #[On('abrirModalCrear')]
    public function create()
    {
        $this->resetInputFields();
        $this->resetValidation();
    }

    public function store()
    {
        if (!$this->verificarAccion()) {
            return;
        }

        $this->validate();

        // Validar que no exista una institución con el mismo nombre en la misma localidad
        $existe = InstitucionProcedencia::where('nombre_institucion', $this->nombre_institucion)
            ->where('localidad_id', $this->localidad_id)
            ->where('status', true)
            ->exists();

        if ($existe) {
            session()->flash('error', 'Ya existe una institución con ese nombre en esta localidad.');
            return;
        }

        $institucion = InstitucionProcedencia::create([
            'nombre_institucion' => $this->nombre_institucion,
            'localidad_id' => $this->localidad_id,
            'status' => true,
        ]);

        session()->flash('success', 'Institución creada correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();

        // NUEVO: Si está en modo modal, notificar al componente padre
        if ($this->modoModal) {
            $this->dispatch('institucionCreada', [
                'id' => $institucion->id,
                'nombre' => $institucion->nombre_institucion
            ]);
        }
    }

    public function edit($id)
    {
        $this->resetValidation();
        if (!$this->verificarAccion()) {
            return;
        }

        $inst = InstitucionProcedencia::with([
            'localidad:id,nombre_localidad,municipio_id',
            'localidad.municipio:id,nombre_municipio,estado_id',
            'localidad.municipio.estado:id,nombre_estado'
        ])
        ->select('id', 'nombre_institucion', 'localidad_id')
        ->findOrFail($id);

        $this->institucion_id = $inst->id;
        $this->nombre_institucion = $inst->nombre_institucion;

        // Obtener estado, municipio y localidad desde las relaciones
        if ($inst->localidad && $inst->localidad->municipio && $inst->localidad->municipio->estado) {
            $this->estado_id = $inst->localidad->municipio->estado->id;
            $this->municipio_id = $inst->localidad->municipio->id;
            $this->localidad_id = $inst->localidad_id;

            // Cargar municipios del estado seleccionado
            $this->municipios = Municipio::where('estado_id', $this->estado_id)
                ->where('status', true)
                ->select('id', 'nombre_municipio', 'estado_id')
                ->orderBy('nombre_municipio', 'asc')
                ->get();

            // Cargar localidades del municipio seleccionado
            $this->localidades = Localidad::where('municipio_id', $this->municipio_id)
                ->where('status', true)
                ->select('id', 'nombre_localidad', 'municipio_id')
                ->orderBy('nombre_localidad', 'asc')
                ->get();
        }

        $this->updateMode = true;
    }

    public function update()
    {
        if (!$this->verificarAccion()) {
            return;
        }

        $this->validate();

        $institucion = InstitucionProcedencia::find($this->institucion_id);

        if (!$institucion) {
            session()->flash('error', 'La institución no existe.');
            return;
        }

        // Validar que no exista otra institución con el mismo nombre en la misma localidad
        $existe = InstitucionProcedencia::where('nombre_institucion', $this->nombre_institucion)
            ->where('localidad_id', $this->localidad_id)
            ->where('status', true)
            ->where('id', '!=', $this->institucion_id)
            ->exists();

        if ($existe) {
            session()->flash('error', 'Ya existe una institución con ese nombre en esta localidad.');
            return;
        }

        $institucion->update([
            'nombre_institucion' => $this->nombre_institucion,
            'localidad_id' => $this->localidad_id,
        ]);

        session()->flash('success', 'Institución actualizada correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();
    }

    public function destroy($id)
    {
        if (!$this->verificarAccion()) {
            return;
        }

        $institucion = InstitucionProcedencia::findOrFail($id);
        $institucion->update(['status' => false]);

        session()->flash('success', 'Institución eliminada correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetPage();
    }
}

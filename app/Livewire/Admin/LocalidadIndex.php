<?php

namespace App\Livewire\Admin;

use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Localidad;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\AnioEscolar;

class LocalidadIndex extends Component
{
    use WithPagination;
    public $nombre_localidad;
    public $estado_id;
    public $municipio_id;
    public $localidad_id;
    public $pais_id;
    public $updateMode = false;
    public $search = '';
    public $anioEscolarActivo = false;
    public $paises = [];
    public $estados = [];
    public $municipios = [];

    protected $rules = [
        'nombre_localidad' => 'required|string|max:255',
        'estado_id' => 'required|integer|exists:estados,id',
        'municipio_id' => 'required|integer|exists:municipios,id',
        'pais_id' => 'required|integer|exists:pais,id',
    ];

    public function mount()
    {
        $this->verificarAnioEscolar();
        $this->paises = \App\Models\Pais::where('status', true)->get();
    }

    private function verificarAnioEscolar()
    {
        $this->anioEscolarActivo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])
            ->orWhere('status', 'Extendido')
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

    public function render()
    {
        $localidades = Localidad::where('status', true)
            ->when($this->search, function ($query) {
                $query->where('nombre_localidad', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre_localidad')
            ->paginate(10);

        return view('livewire.admin.localidad-index', compact('localidades'));
    }

    public function paginationView()
    {
        return 'vendor.livewire.bootstrap-custom';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPaisId($paisId)
    {
        $this->estados = Estado::where('pais_id', $paisId)
            ->where('status', true)
            ->orderBy('nombre_estado')
            ->get();
        $this->estado_id = null;
        $this->municipio_id = null;
        $this->municipios = [];
    }

    public function updatedEstadoId($estadoId)
    {
        $this->municipios = Municipio::where('estado_id', $estadoId)
            ->where('status', true)
            ->orderBy('nombre_municipio')
            ->get();
        $this->municipio_id = null;
    }

    public function resetInputFields()
    {
        $this->nombre_localidad = '';
        $this->estado_id = null;
        $this->municipio_id = null;
        $this->localidad_id = null;
        $this->pais_id = null;
        $this->estados = [];
        $this->municipios = [];
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
        $this->validate();
        if (Localidad::where('nombre_localidad', $this->nombre_localidad)->where('status', true)->exists()) {
            session()->flash('error', 'Ya existe una localidad con ese nombre.');
            return;
        }
        Localidad::create([
            'nombre_localidad' => $this->nombre_localidad,
            'municipio_id' => $this->municipio_id,
            'status' => true,
        ]);
        session()->flash('success', 'Localidad creada correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $this->resetValidation();
        $localidad = Localidad::with('municipio.estado.pais')->findOrFail($id);
        $this->localidad_id = $localidad->id;
        $this->nombre_localidad = $localidad->nombre_localidad;
        $this->pais_id = $localidad->municipio->estado->pais->id ?? null;
        $this->estado_id = $localidad->municipio->estado->id ?? null;
        $this->municipio_id = $localidad->municipio_id;
        if ($this->pais_id) {
            $this->estados = Estado::where('pais_id', $this->pais_id)
                ->where('status', true)
                ->orderBy('nombre_estado')
                ->get();
        }
        if ($this->estado_id) {
            $this->municipios = Municipio::where('estado_id', $this->estado_id)
                ->where('status', true)
                ->orderBy('nombre_municipio')
                ->get();
        }
        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();
        $localidad = Localidad::findOrFail($this->localidad_id);
        $localidad->update([
            'nombre_localidad' => $this->nombre_localidad,
            'municipio_id' => $this->municipio_id,
        ]);
        session()->flash('success', 'Localidad actualizada correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();
    }

    public function destroy($id)
    {
        $localidad = Localidad::findOrFail($id);
        $localidad->update(['status' => false]);
        session()->flash('success', 'Localidad eliminada correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetPage();
    }
}

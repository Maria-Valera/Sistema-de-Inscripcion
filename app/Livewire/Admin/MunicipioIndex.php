<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\AnioEscolar;
use App\Models\Pais;

class MunicipioIndex extends Component
{
    use WithPagination;
    public $nombre_municipio;
    public $municipio_id;
    public $estado_id;
    public $updateMode = false;
    public $search = '';
    public $pais_id;
    public $estados = [];
    public $anioEscolarActivo = false;

    public function mount()
    {
        $this->verificarAnioEscolar();
    }

    private function verificarAnioEscolar()
    {
        $this->anioEscolarActivo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    protected $rules = [
        'nombre_municipio' => 'required|string|max:255',
        'estado_id' => 'required|integer|exists:estados,id',
        'pais_id' => 'required|integer|exists:pais,id',
    ];
    public function render()
    {
        $paises = Pais::where('status', true)
            ->select('id', 'nameES')
            ->orderBy('nameES', 'asc')
            ->get();

        $municipios = Municipio::where('municipios.status', true)
            ->join('estados', 'municipios.estado_id', '=', 'estados.id')
            ->join('pais', 'estados.pais_id', '=', 'pais.id')
            ->when($this->search, function ($query) {
                $query->where('municipios.nombre_municipio', 'like', '%' . $this->search . '%');
            })
            ->select(
                'municipios.id',
                'municipios.nombre_municipio',
                'municipios.estado_id',
                'municipios.status',
                'pais.nameES as pais_nombre',
                'estados.nombre_estado as estado_nombre'
            )
            ->orderBy('municipios.nombre_municipio', 'asc')
            ->paginate(10);

        return view('livewire.admin.municipio-index', compact('municipios', 'paises'));
    }

    public function updatedPaisId($paisId)
    {
        $this->estados = Estado::where('pais_id', $paisId)
            ->where('status', true)
            ->select('id', 'nombre_estado', 'pais_id')
            ->orderBy('nombre_estado')
            ->get();
        $this->estado_id = null;
    }

    public function paginationView()
    {
        return 'vendor.livewire.bootstrap-custom';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function resetInputFields()
    {
        $this->nombre_municipio = '';
        $this->municipio_id = null;
        $this->estado_id = null;
        $this->pais_id = null;
        $this->estados = [];
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
        if (Municipio::where('nombre_municipio', $this->nombre_municipio)
            ->where('estado_id', $this->estado_id)
            ->where('status', true)->exists()
        ) {
            session()->flash('error', 'Ya existe un municipio con ese nombre.');
            return;
        }
        Municipio::create([
            'nombre_municipio' => $this->nombre_municipio,
            'estado_id' => $this->estado_id,
            'status' => true,
        ]);
        session()->flash('success', 'Municipio creado correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $this->resetValidation();
        $municipio = Municipio::with('estado:id,pais_id')
            ->select('id', 'nombre_municipio', 'estado_id')
            ->findOrFail($id);

        $this->municipio_id = $municipio->id;
        $this->nombre_municipio = $municipio->nombre_municipio;
        $this->pais_id = $municipio->estado ? $municipio->estado->pais_id : null;
        $this->estado_id = $municipio->estado_id;

        // Solo recargar estados si no están cargados para este país
        if (empty($this->estados) || !is_iterable($this->estados) || empty($this->estados->first()) || $this->estados->first()->pais_id != $this->pais_id) {
            $this->estados = Estado::where('pais_id', $this->pais_id)
                ->where('status', true)
                ->select('id', 'nombre_estado', 'pais_id')
                ->orderBy('nombre_estado')
                ->get();
        }

        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();
        $municipio = Municipio::find($this->municipio_id);
        $municipio->update(['nombre_municipio' => $this->nombre_municipio, 'estado_id' => $this->estado_id, 'pais_id' => $this->pais_id]);
        session()->flash('success', 'Municipio actualizado correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();
    }

    public function destroy($id)
    {
        $municipio = Municipio::findOrFail($id);
        $municipio->update(['status' => false]);
        session()->flash('success', 'Municipio eliminado correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetPage();
    }
}

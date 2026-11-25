<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Estado;
use App\Models\AnioEscolar;
use App\Models\Pais;

class EstadoIndex extends Component
{
    use WithPagination;
    public $nombre_estado;
    public $estado_id;
    public $updateMode = false;
    public $search = '';
    public $pais_id;
    public $anioEscolarActivo = false;

    protected $rules = [
        'nombre_estado' => 'required|string|max:255',
        'pais_id' => 'required|integer|exists:pais,id',
    ];

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
        $paises = Pais::where('status', true)
            ->select('id', 'nameES')
            ->orderBy('nameES', 'asc')
            ->get();

        $estados = Estado::where('estados.status', true)
            ->join('pais', 'estados.pais_id', '=', 'pais.id')
            ->when($this->search, function ($query) {
                $query->where('estados.nombre_estado', 'like', '%' . $this->search . '%');
            })
            ->select('estados.id', 'estados.nombre_estado', 'estados.pais_id', 'estados.status', 'pais.nameES')
            ->orderBy('estados.nombre_estado', 'asc')
            ->paginate(10);

        return view('livewire.admin.estado-index', compact('estados', 'paises'));
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
        $this->nombre_estado = '';
        $this->estado_id = null;
        $this->pais_id = null;
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
        if (Estado::where('nombre_estado', $this->nombre_estado)->where('status', true)->exists()) {
            session()->flash('error', 'Ya existe un estado con ese nombre.');
            return;
        }
        Estado::create([
            'pais_id' => $this->pais_id,
            'nombre_estado' => $this->nombre_estado,
            'status' => true,
        ]);
        session()->flash('success', 'Estado creado correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $this->resetValidation();
        if (!$this->verificarAccion()) {
            return;
        }
        $estado = Estado::select('id', 'nombre_estado', 'pais_id')->findOrFail($id);
        $this->estado_id = $estado->id;
        $this->nombre_estado = $estado->nombre_estado;
        $this->pais_id = $estado->pais_id;
        $this->updateMode = true;
    }

    public function update()
    {
        if (!$this->verificarAccion()) {
            return;
        }
        $this->validate();
        $estado = Estado::find($this->estado_id);
        $estado->update([
            'nombre_estado' => $this->nombre_estado,
            'pais_id' => $this->pais_id,
        ]);
        session()->flash('success', 'Estado actualizado correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();
    }

    public function destroy($id)
    {
        if (!$this->verificarAccion()) {
            return;
        }
        $estado = Estado::findOrFail($id);
        $estado->update(['status' => false]);
        session()->flash('success', 'Estado eliminado correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetPage();
    }
}

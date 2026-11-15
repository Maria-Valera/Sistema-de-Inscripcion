<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\AnioEscolar;
use App\Models\Pais;

class PaisIndex extends Component
{
    use WithPagination;
    public $nameES;
    public $iso2;
    public $pais_id;
    public $updateMode = false;
    public $search = '';
    public $anioEscolarActivo = false;
    protected $rules = [
        'nameES' => 'required|string|max:255',
        'iso2' => 'required|string|max:3',
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
            ->when($this->search, function ($query) {
                $query->where('nameES', 'like', '%' . $this->search . '%')
                ->orWhere('iso2', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nameES', 'asc')
            ->paginate(10);
        return view('livewire.admin.pais-index', compact('paises'));
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
        $this->nameES = '';
        $this->iso2 = '';
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
        if (Pais::where('nameES', $this->nameES)->where('status', true)->orWhere('iso2', $this->iso2)->exists()) {
            session()->flash('error', 'Ya existe un país con ese nombre o código ISO.');
            return;
        }
        Pais::create([
            'nameES' => $this->nameES,
            'iso2' => $this->iso2,
            'status' => true,
        ]);
        session()->flash('success', 'País creado correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $this->resetValidation();
        if (!$this->verificarAccion()) {
            return;
        }
        $paises = Pais::findOrFail($id);
        $this->pais_id = $id;
        $this->iso2 = $paises->iso2;
        $this->nameES = $paises->nameES;
        $this->updateMode = true;
    }

    public function update()
    {
        if (!$this->verificarAccion()) {
            return;
        }
        $this->validate();
        $paises = Pais::find($this->pais_id);
        $paises->update(['nameES' => $this->nameES, 'iso2' => $this->iso2]);
        session()->flash('success', 'País actualizado correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetInputFields();
    }

    public function destroy($id)
    {
        if (!$this->verificarAccion()) {
            return;
        }
        $paises = Pais::findOrFail($id);
        $paises->update(['status' => false]);
        session()->flash('success', 'País eliminado correctamente.');
        $this->dispatch('cerrarModal');
        $this->resetPage();
    }
}

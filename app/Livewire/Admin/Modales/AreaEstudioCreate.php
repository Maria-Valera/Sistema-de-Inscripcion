<?php

namespace App\Livewire\Admin\Modales;

use Livewire\Component;
use App\Models\AreaEstudioRealizado;
use App\Models\AreaFormacion;
use App\Models\EstudiosRealizado;

class AreaEstudioCreate extends Component
{
    public $area_formacion_id;
    public $estudios_id;

    public $areas_formacion = [];
    public $estudios = [];

    protected $rules = [
        'area_formacion_id' => 'required|exists:area_formacions,id',
        'estudios_id' => 'required|exists:estudios_realizados,id',
    ];

    protected $messages = [
        'area_formacion_id.required' => 'Debe seleccionar un área de formación',
        'area_formacion_id.exists' => 'El área de formación seleccionada no es válida',
        'estudios_id.required' => 'Debe seleccionar un título universitario',
        'estudios_id.exists' => 'El título universitario seleccionado no es válido',
    ];

    public function mount()
    {
        $this->areas_formacion = AreaFormacion::where('status', true)
            ->orderBy('nombre_area_formacion', 'asc')
            ->get();

        $this->estudios = EstudiosRealizado::where('status', true)
            ->orderBy('estudios', 'asc')
            ->get();
    }

    public function store()
    {
        abort_unless(auth()->user()?->can('area_estudio_realizado.gestionar'), 403);

        $this->validate();

        $existe = AreaEstudioRealizado::where('area_formacion_id', $this->area_formacion_id)
            ->where('estudios_id', $this->estudios_id)
            ->exists();

        if ($existe) {
            $this->addError('area_formacion_id', 'Esta asignación ya existe.');
            return;
        }

        try {
            $asignacion = AreaEstudioRealizado::create([
                'area_formacion_id' => $this->area_formacion_id,
                'estudios_id' => $this->estudios_id,
                'status' => true,
            ]);

            $this->dispatch('asignacionCreada', [
                'id' => $asignacion->id,
                'area_formacion_id' => $asignacion->area_formacion_id,
                'estudios_id' => $asignacion->estudios_id,
            ]);

            session()->flash('success', 'Asignación creada exitosamente.');

            $this->dispatch('cerrarModalDespuesDe', ['delay' => 1500]);

            $this->resetInputFields();
        } catch (\Exception $e) {
            $this->addError('general', 'Error al crear la asignación: ' . $e->getMessage());
        }
    }

    public function resetInputFields()
    {
        $this->area_formacion_id = null;
        $this->estudios_id = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.modales.area-estudio-create');
    }
}

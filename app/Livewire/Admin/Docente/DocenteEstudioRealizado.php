<?php

namespace App\Livewire\Admin\Docente;

use Livewire\Component;
use App\Models\Docente;
use App\Models\EstudiosRealizado;
use App\Models\DetalleDocenteEstudio;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocenteEstudioRealizado extends Component
{
    public Docente $docente;
    public $estudiosId;
    public $estudios;
    public $estudiosAsignados = [];

    public $estudioSeleccionado = null;


    public function mount(Docente $docente)
    {
        abort_unless(auth()->user()?->can('personal.ver'), 403, 'No tiene permiso para ver personal.');
        $this->docente = $docente;
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->estudios = EstudiosRealizado::where('status', true)
            ->orderBy('estudios', 'asc')
            ->get();

        $this->estudiosAsignados = $this->docente->detalleDocenteEstudio()
            ->with('estudiosRealizado')
            ->where('status', true)
            ->get();

        $this->reset('estudiosId');
    }
    
    public function agregarEstudio()
    {
        abort_unless(auth()->user()?->can('personal.gestionar'), 403, 'No tiene permiso para gestionar personal.');
        $this->validate([

            'estudiosId' => 'required|exists:estudios_realizados,id',
        ], [
            'estudiosId.required' => 'Debe seleccionar un estudio',
            'estudiosId.exists' => 'El estudio seleccionado no es válido',
        ]);

        DB::beginTransaction();
        try {
            $existe = DetalleDocenteEstudio::where('docente_id', $this->docente->id)
                ->where('estudios_id', $this->estudiosId)
                ->where('status', true)
                ->exists();

            if ($existe) {
                throw ValidationException::withMessages([
                    'estudiosId' => 'Este estudio ya está asignado al docente.'
                ]);
            }

            DetalleDocenteEstudio::create([
                'docente_id' => $this->docente->id,
                'estudios_id' => $this->estudiosId,
                'status' => true,
            ]);

            DB::commit();

            $this->cargarDatos();

            session()->flash('success', 'Estudio agregado correctamente.');

            $this->dispatch('resetSelect');
            
        } catch (ValidationException $e) {
            DB::rollBack();
            
            throw $e;
            
        } catch (Exception $e) {
            DB::rollBack();
            
            session()->flash('error', 'Error al agregar el estudio: ' . $e->getMessage());
        }
    }

    public function eliminarEstudio($detalleId)
    {
        abort_unless(auth()->user()?->can('personal.gestionar'), 403, 'No tiene permiso para gestionar personal.');
        DB::beginTransaction();

        try {
            $detalle = DetalleDocenteEstudio::where('id', $detalleId)
                ->where('docente_id', $this->docente->id)
                ->where('status', true)
                ->firstOrFail();

            $detalle->update(['status' => false]);

            DB::commit();

            $this->cargarDatos();

            session()->flash('success', 'Estudio eliminado correctamente.');
            
        } catch (Exception $e) {
            DB::rollBack();
            
            session()->flash('error', 'Error al eliminar el estudio: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.docente.docente-estudio-realizado');
    }
}
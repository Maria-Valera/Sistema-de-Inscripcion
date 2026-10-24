<?php

namespace App\Livewire\Admin\Auditoria;

use App\Models\Auditoria;
use Livewire\Component;
use Livewire\WithPagination;

class IndexAuditoria extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filtros de búsqueda
    public $searchUsuario = '';
    public $filtroAccion = '';
    public $filtroModelo = '';
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $perPage = 15;

    // Reseteo de paginación al cambiar filtros
    public function updatingSearchUsuario()
    {
        $this->resetPage();
    }

    public function updatingFiltroAccion()
    {
        $this->resetPage();
    }

    public function updatingFiltroModelo()
    {
        $this->resetPage();
    }

    public function updatingFechaDesde()
    {
        $this->resetPage();
    }

    public function updatingFechaHasta()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['searchUsuario', 'filtroAccion', 'filtroModelo', 'fechaDesde', 'fechaHasta']);
        $this->resetPage();
    }

    public function render()
    {
        // Verificar autorización explícita
        abort_unless(auth()->user()->can('auditoria.ver'), 403, 'No tiene permiso para consultar las auditorías del sistema.');

        $query = Auditoria::with('usuario')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($this->searchUsuario)) {
            $busqueda = trim($this->searchUsuario);
            $query->where(function ($q) use ($busqueda) {
                $q->where('usuario_email', 'like', "%{$busqueda}%")
                  ->orWhereHas('usuario', function ($u) use ($busqueda) {
                      $u->where('name', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%");
                  });
            });
        }

        if (!empty($this->filtroAccion)) {
            $query->where('accion', $this->filtroAccion);
        }

        if (!empty($this->filtroModelo)) {
            $modeloFiltro = trim($this->filtroModelo);
            $query->where(function ($q) use ($modeloFiltro) {
                $q->where('auditable_type', 'like', "%{$modeloFiltro}%");
            });
        }

        if (!empty($this->fechaDesde)) {
            $query->whereDate('created_at', '>=', $this->fechaDesde);
        }

        if (!empty($this->fechaHasta)) {
            $query->whereDate('created_at', '<=', $this->fechaHasta);
        }

        $auditorias = $query->paginate($this->perPage);

        // Obtener listas de opciones para los desplegables de filtro
        $accionesDisponibles = Auditoria::select('accion')
            ->distinct()
            ->whereNotNull('accion')
            ->orderBy('accion')
            ->pluck('accion');

        $modelosDisponibles = Auditoria::select('auditable_type')
            ->distinct()
            ->whereNotNull('auditable_type')
            ->orderBy('auditable_type')
            ->pluck('auditable_type');

        return view('livewire.admin.auditoria.index-auditoria', [
            'auditorias' => $auditorias,
            'accionesDisponibles' => $accionesDisponibles,
            'modelosDisponibles' => $modelosDisponibles,
        ]);
    }
}

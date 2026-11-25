<?php

namespace App\Livewire\Admin\Usuarios;

use App\Actions\Usuarios\GestionarModulosUsuarioAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ModalPermisos extends Component
{
    public int $userId;
    public string $usuarioNombre = '';
    public string $usuarioEmail = '';

    // Permisos y estados calculados
    public array $permisosSeleccionados = [];
    public array $permisosDirectos = [];
    public array $permisosPorRol = [];
    public array $modulosConPermisos = [];
    public array $conteoPorRolPorModulo = [];
    public array $conteoDirectosPorModulo = [];

    public function mount($userId): void
    {
        $this->userId = (int) $userId;
        $this->verificarPermiso();
        $this->cargarDatosUsuario();
    }

    protected function verificarPermiso(): void
    {
        $user = Auth::user();
        if (!$user || !$user->can('usuarios.gestionar_modulos')) {
            abort(403, 'No tienes permiso para gestionar permisos.');
        }
    }

    protected function cargarDatosUsuario(): void
    {
        $usuario = User::with(['roles.permissions', 'permissions'])->findOrFail($this->userId);
        $this->usuarioNombre = $usuario->name;
        $this->usuarioEmail = $usuario->email;

        // Calcular TODO una sola vez en el mount()
        $this->permisosDirectos = $usuario->permissions->pluck('name')->toArray();
        $this->permisosPorRol = $usuario->getPermissionsViaRoles()->pluck('name')->toArray();
        $this->permisosSeleccionados = $this->permisosDirectos;

        // Precomputar estados y conteos por módulo una sola vez
        $permisosPorRolSet = array_flip($this->permisosPorRol);
        $permisosDirectosSet = array_flip($this->permisosDirectos);

        $this->modulosConPermisos = [];
        $this->conteoPorRolPorModulo = [];
        $this->conteoDirectosPorModulo = [];

        foreach (config('modulos', []) as $key => $moduloData) {
            $pMod = $moduloData['permisos'] ?? [];
            $cRol = 0;
            $cDir = 0;
            foreach ($pMod as $p) {
                if (isset($permisosPorRolSet[$p])) {
                    $cRol++;
                }
                if (isset($permisosDirectosSet[$p])) {
                    $cDir++;
                }
            }
            $this->conteoPorRolPorModulo[$key] = $cRol;
            $this->conteoDirectosPorModulo[$key] = $cDir;
            $this->modulosConPermisos[$key] = ($cRol > 0 || $cDir > 0);
        }
    }

    public function guardarPermisos(GestionarModulosUsuarioAction $action): void
    {
        $this->verificarPermiso();

        $user = User::findOrFail($this->userId);

        $nuevosDirectosDeseados = array_values(array_diff($this->permisosSeleccionados, $this->permisosPorRol));
        $directosActuales = $user->getDirectPermissions()->pluck('name')->toArray();

        $aAsignar = array_values(array_diff($nuevosDirectosDeseados, $directosActuales));
        $aRetirar = array_values(array_diff($directosActuales, $nuevosDirectosDeseados));

        if (!empty($aAsignar)) {
            $action->asignar($user, $aAsignar);
        }

        if (!empty($aRetirar)) {
            $action->retirar($user, $aRetirar);
        }

        session()->flash('success', "Permisos del usuario {$user->name} actualizados exitosamente.");
        $this->cerrarModal();
    }

    public function cerrarModal(): void
    {
        $this->dispatch('cerrarModalPermisos');
    }

    public function render()
    {
        return view('livewire.admin.usuarios.modal-permisos', [
            'modulosConfig' => config('modulos', []),
        ]);
    }
}

<?php

namespace App\Livewire\Admin\Usuarios;

use App\Actions\Usuarios\CorregirCorreoAction;
use App\Actions\Usuarios\CrearUsuarioParaPersonaAction;
use App\Actions\Usuarios\GestionarModulosUsuarioAction;
use App\Actions\Usuarios\ReenviarAccesoAction;
use App\Models\Auditoria;
use App\Models\Persona;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class GestionarUsuarios extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filtros de búsqueda
    public $search = '';
    public $filtroRol = '';
    public $filtroActivo = '';

    // Modales de control
    public $modalModulosAbierto = false;
    public $modalCorreoAbierto = false;
    public $modalCrearAbierto = false;

    // Estado para gestionar módulos extra
    public $usuarioSeleccionadoId = null;
    public $usuarioSeleccionadoNombre = '';
    public $usuarioSeleccionadoEmail = '';

    // Estado para corregir correo
    public $corregirPersonaId = null;
    public $corregirCorreoActual = '';
    public $nuevoCorreo = '';

    // Estado para crear usuario
    public $tipo_documento_id = '';
    public $numero_documento = '';
    public $email_crear = '';

    // Mensaje de éxito de contraseña temporal
    public $passwordTemporalGenerada = null;
    public $usuarioPasswordTemporal = null;

    protected function rules(): array
    {
        return [
            'nuevoCorreo' => 'required|email|max:255',
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'numero_documento' => 'required|string|max:20',
            'email_crear' => 'required|email|max:255|unique:users,email',
        ];
    }

    public function mount(): void
    {
        $this->verificarPermiso();
    }

    protected function verificarPermiso(): void
    {
        $user = Auth::user();
        if (!$user || !$user->can('usuarios.gestionar_modulos')) {
            abort(403, 'No tienes permiso para gestionar usuarios y módulos.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroRol(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroActivo(): void
    {
        $this->resetPage();
    }

    /**
     * Alternar estado activo / inactivo de un usuario (sin borrarlo).
     */
    public function toggleActivo(int $userId): void
    {
        $this->verificarPermiso();

        $user = User::findOrFail($userId);

        // Prevenir auto-desactivación del propio administrador
        if (Auth::id() === $user->id && $user->activo) {
            session()->flash('error', 'No puedes desactivar tu propia cuenta de usuario.');
            return;
        }

        $estadoAnterior = $user->activo;
        $user->activo = !$user->activo;
        $user->save();

        Auditoria::registrar(
            'usuario_estado_cambiado',
            $user,
            ['activo' => $estadoAnterior],
            ['activo' => $user->activo],
            "Usuario {$user->email} " . ($user->activo ? 'activado' : 'desactivado')
        );

        session()->flash('success', "El usuario {$user->name} fue " . ($user->activo ? 'activado' : 'desactivado') . ' exitosamente.');
    }

    /**
     * Reenviar acceso al usuario con una nueva contraseña temporal.
     */
    public function reenviarAcceso(int $userId, ReenviarAccesoAction $action): void
    {
        $this->verificarPermiso();

        $user = User::findOrFail($userId);

        $tempPass = $action->ejecutar($user);

        $this->passwordTemporalGenerada = $tempPass;
        $this->usuarioPasswordTemporal = $user->email;

        session()->flash('success', "Se generó una nueva contraseña temporal y se envió a {$user->email}.");
    }

    /**
     * Abrir modal para gestionar módulos extra.
     */
    public function abrirModalModulos(int $userId): void
    {
        $this->verificarPermiso();

        $user = User::findOrFail($userId);
        $this->usuarioSeleccionadoId = $user->id;
        $this->usuarioSeleccionadoNombre = $user->name;
        $this->usuarioSeleccionadoEmail = $user->email;
        $this->modalModulosAbierto = true;
    }

    public function cerrarModalModulos(): void
    {
        $this->modalModulosAbierto = false;
        $this->usuarioSeleccionadoId = null;
    }

    /**
     * Asignar módulo extra.
     */
    public function asignarModulo(string $claveModulo, GestionarModulosUsuarioAction $action): void
    {
        $this->verificarPermiso();

        $user = User::findOrFail($this->usuarioSeleccionadoId);
        $action->asignar($user, $claveModulo);

        session()->flash('success', "Módulo '{$claveModulo}' asignado exitosamente como extra.");
    }

    /**
     * Retirar módulo extra.
     */
    public function retirarModulo(string $claveModulo, GestionarModulosUsuarioAction $action): void
    {
        $this->verificarPermiso();

        $user = User::findOrFail($this->usuarioSeleccionadoId);
        $conserva = $action->retirar($user, $claveModulo);

        if ($conserva) {
            session()->flash('info', "Se retiraron los permisos directos, pero el usuario aún conserva el módulo '{$claveModulo}' a través de sus roles.");
        } else {
            session()->flash('success', "Módulo '{$claveModulo}' retirado exitosamente.");
        }
    }

    /**
     * Abrir modal para corregir correo.
     */
    public function abrirModalCorreo(int $personaId): void
    {
        $this->verificarPermiso();

        $persona = Persona::findOrFail($personaId);
        $this->corregirPersonaId = $persona->id;
        $this->corregirCorreoActual = $persona->email;
        $this->nuevoCorreo = $persona->email;
        $this->modalCorreoAbierto = true;
    }

    public function cerrarModalCorreo(): void
    {
        $this->modalCorreoAbierto = false;
        $this->corregirPersonaId = null;
        $this->reset(['nuevoCorreo', 'corregirCorreoActual']);
    }

    /**
     * Guardar corrección de correo.
     */
    public function guardarCorreo(CorregirCorreoAction $action): void
    {
        $this->verificarPermiso();

        $this->validate([
            'nuevoCorreo' => 'required|email|max:255',
        ]);

        $persona = Persona::findOrFail($this->corregirPersonaId);
        $action->ejecutar($persona, $this->nuevoCorreo);

        session()->flash('success', "Correo electrónico actualizado a {$this->nuevoCorreo}. Se invalidó la verificación para forzar la re-verificación.");
        $this->cerrarModalCorreo();
    }

    /**
     * Abrir modal para crear usuario con contraseña temporal.
     */
    public function abrirModalCrear(): void
    {
        $this->verificarPermiso();
        $this->reset(['tipo_documento_id', 'numero_documento', 'email_crear']);
        $this->modalCrearAbierto = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->modalCrearAbierto = false;
        $this->reset(['tipo_documento_id', 'numero_documento', 'email_crear']);
    }

    /**
     * Crear usuario para persona precargada con contraseña temporal.
     */
    public function crearUsuario(CrearUsuarioParaPersonaAction $action): void
    {
        $this->verificarPermiso();

        $this->validate([
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'numero_documento'  => 'required|string|max:20',
            'email_crear'       => 'required|email|max:255|unique:users,email',
        ]);

        try {
            $resultado = $action->ejecutar([
                'tipo_documento_id' => $this->tipo_documento_id,
                'numero_documento'  => $this->numero_documento,
                'email'             => $this->email_crear,
            ]);

            $user = $resultado['user'];
            $pass = $resultado['password_temporal'];

            $this->passwordTemporalGenerada = $pass;
            $this->usuarioPasswordTemporal = $user->email;

            session()->flash('success', "Usuario creado exitosamente con contraseña temporal para {$user->email}.");
            $this->cerrarModalCrear();

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        }
    }

    public function limpiarAvisoPassword(): void
    {
        $this->passwordTemporalGenerada = null;
        $this->usuarioPasswordTemporal = null;
    }

    public function render()
    {
        $this->verificarPermiso();

        $query = User::with(['roles', 'persona', 'permissions'])
            ->when($this->search, function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhereHas('persona', function ($p) use ($term) {
                            $p->where('numero_documento', 'like', $term);
                        });
                });
            })
            ->when($this->filtroRol, function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', $this->filtroRol));
            })
            ->when($this->filtroActivo !== '', function ($q) {
                $q->where('activo', (bool)$this->filtroActivo);
            })
            ->orderBy('id', 'desc');

        $usuarios = $query->paginate(10);

        // Módulos para el modal de gestión si hay usuario seleccionado
        $infoModulos = [];
        if ($this->usuarioSeleccionadoId) {
            $usuarioSel = User::with(['roles', 'permissions'])->find($this->usuarioSeleccionadoId);
            if ($usuarioSel) {
                $permsRol = $usuarioSel->getPermissionsViaRoles()->pluck('name')->flip();
                $directos = $usuarioSel->getDirectPermissions()->pluck('name')->flip();

                foreach (config('modulos', []) as $clave => $configMod) {
                    $pMod = $configMod['permisos'] ?? [];

                    $tienePorRol = false;
                    foreach ($pMod as $p) {
                        if (isset($permsRol[$p])) {
                            $tienePorRol = true;
                            break;
                        }
                    }

                    $tieneDirecto = false;
                    foreach ($pMod as $p) {
                        if (isset($directos[$p])) {
                            $tieneDirecto = true;
                            break;
                        }
                    }

                    $infoModulos[$clave] = [
                        'nombre'        => $configMod['nombre'] ?? $clave,
                        'grupo'         => $configMod['grupo'] ?? 'general',
                        'tiene_por_rol' => $tienePorRol,
                        'tiene_directo' => $tieneDirecto,
                        'activo'        => $tienePorRol || $tieneDirecto,
                    ];
                }
            }
        }

        $rolesDisponibles = Role::pluck('name');
        $tiposDocumento = TipoDocumento::where('status', 1)->get();

        return view('livewire.admin.usuarios.gestionar-usuarios', [
            'usuarios'         => $usuarios,
            'infoModulos'      => $infoModulos,
            'rolesDisponibles' => $rolesDisponibles,
            'tiposDocumento'   => $tiposDocumento,
        ]);
    }
}

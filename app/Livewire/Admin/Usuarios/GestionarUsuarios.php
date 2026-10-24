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
    public $permisosSeleccionados = [];
    public $permisosPorRol = [];

    // Estado para corregir correo
    public $corregirPersonaId = null;
    public $corregirCorreoActual = '';
    public $nuevoCorreo = '';

    // Estado para crear usuario (Flujo híbrido con múltiples roles)
    public $tipo_documento_id = '';
    public $numero_documento = '';
    public $primer_nombre = '';
    public $primer_apellido = '';
    public $email_crear = '';
    public $roles_crear = [];
    public $rol_principal_crear = '';

    // Estado de detección/autocompletado de Persona
    public $personaEncontrada = false;
    public $personaTieneUsuario = false;
    public $personaInfoTexto = '';

    // Propiedad pública para los tipos de documentos
    public $tipos_documentos = [];

    // Mensaje de éxito de contraseña temporal
    public $passwordTemporalGenerada = null;
    public $usuarioPasswordTemporal = null;

    protected function rules(): array
    {
        return [
            'nuevoCorreo'         => 'required|email|max:255',
            'tipo_documento_id'   => 'required|exists:tipo_documentos,id',
            'numero_documento'    => 'required|string|max:9',
            'primer_nombre'       => 'required|string|max:50',
            'primer_apellido'     => 'required|string|max:50',
            'email_crear'         => 'required|email|max:255|unique:users,email',
            'roles_crear'         => 'required|array|min:1',
            'roles_crear.*'       => 'in:Admin,Subdirectora,Docente,Representante',
            'rol_principal_crear' => 'required|string',
        ];
    }

    public function updated($propertyName): void
    {
        // Si el usuario vacía la cédula para reescribirla, no mostrar 'campo obligatorio' en tiempo real
        if ($propertyName === 'numero_documento' && empty(trim($this->numero_documento))) {
            return;
        }

        $this->validateOnly($propertyName);
    }

    public function updatedNumeroDocumento(): void
    {
        $this->buscarYAutocompletarPersona();
    }

    public function updatedTipoDocumentoId(): void
    {
        $this->buscarYAutocompletarPersona();
    }

    /**
     * Detección en tiempo real y autocompletado de Persona existente al tipear cédula.
     */
    protected function buscarYAutocompletarPersona(): void
    {
        // Si se vacía el número de documento, limpiar datos autocompletados y validaciones
        if (empty(trim($this->numero_documento))) {
            $this->reset([
                'primer_nombre',
                'primer_apellido',
                'email_crear',
                'personaEncontrada',
                'personaTieneUsuario',
                'personaInfoTexto',
            ]);
            $this->resetValidation(['numero_documento', 'primer_nombre', 'primer_apellido', 'email_crear']);
            return;
        }

        $this->personaEncontrada = false;
        $this->personaTieneUsuario = false;
        $this->personaInfoTexto = '';

        if (empty($this->tipo_documento_id)) {
            return;
        }

        $persona = Persona::where('tipo_documento_id', $this->tipo_documento_id)
            ->where('numero_documento', trim($this->numero_documento))
            ->first();

        if ($persona) {
            $this->personaEncontrada = true;
            $this->primer_nombre = $persona->primer_nombre ?? $this->primer_nombre;
            $this->primer_apellido = $persona->primer_apellido ?? $this->primer_apellido;
            if (!empty($persona->email)) {
                $this->email_crear = $persona->email;
            }

            // Validar si ya tiene un User registrado
            $tieneUsuario = User::where('persona_id', $persona->id)->exists();
            if ($tieneUsuario) {
                $this->personaTieneUsuario = true;
                $this->addError('numero_documento', 'Esta persona ya tiene un usuario registrado en el sistema.');
            } else {
                $this->resetErrorBag('numero_documento');
                $rolesPersona = [];
                if (\App\Models\Docente::where('persona_id', $persona->id)->where('status', 1)->exists()) {
                    $rolesPersona[] = 'Docente';
                }
                if (\App\Models\Representante::where('persona_id', $persona->id)->where('status', 1)->exists()) {
                    $rolesPersona[] = 'Representante';
                }
                $detalle = !empty($rolesPersona) ? ' (' . implode(', ', $rolesPersona) . ')' : '';
                $this->personaInfoTexto = "Persona registrada{$detalle}: {$persona->primer_nombre} {$persona->primer_apellido}. Datos autocompletados.";
            }

            // Validar campos autocompletados
            $this->validateOnly('primer_nombre');
            $this->validateOnly('primer_apellido');
            if (!empty($this->email_crear) && !$this->personaTieneUsuario) {
                $this->validateOnly('email_crear');
            }
        } else {
            // Si la persona ya no existe (por ejemplo cambió el último dígito) y antes estaba autocompletada
            $this->resetValidation(['numero_documento']);
        }
    }

    public function updatedRolesCrear($value): void
    {
        // Si el rol principal ya no está en los roles seleccionados, restablecerlo al primero o vacío
        if (empty($this->roles_crear)) {
            $this->rol_principal_crear = '';
        } elseif (!in_array($this->rol_principal_crear, $this->roles_crear)) {
            $this->rol_principal_crear = $this->roles_crear[0];
        }
        $this->validateOnly('roles_crear');
        if (!empty($this->rol_principal_crear)) {
            $this->validateOnly('rol_principal_crear');
        }
    }

    public function mount(): void
    {
        $this->verificarPermiso();
        $this->tipos_documentos = TipoDocumento::where('status', 1)->get();
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
     * Abrir modal para gestionar módulos extra con permisos granulares.
     */
     public function abrirModalModulos(int $userId): void
     {
         $this->verificarPermiso();
 
         $user = User::with(['roles', 'permissions'])->findOrFail($userId);
         $this->usuarioSeleccionadoId = $user->id;
         $this->usuarioSeleccionadoNombre = $user->name;
         $this->usuarioSeleccionadoEmail = $user->email;
         $this->permisosPorRol = $user->getPermissionsViaRoles()->pluck('name')->toArray();
         $this->permisosSeleccionados = $user->getDirectPermissions()->pluck('name')->toArray();
         $this->modalModulosAbierto = true;
     }
 
     public function cerrarModalModulos(): void
     {
         $this->modalModulosAbierto = false;
         $this->usuarioSeleccionadoId = null;
         $this->permisosSeleccionados = [];
         $this->permisosPorRol = [];
     }
 
     /**
      * Guarda la selección granular de permisos para el usuario.
      */
     public function guardarPermisosModulos(GestionarModulosUsuarioAction $action): void
     {
         $this->verificarPermiso();
 
         if (!$this->usuarioSeleccionadoId) {
             return;
         }
 
         $user = User::findOrFail($this->usuarioSeleccionadoId);
 
         // Permisos que se desean tener de forma directa (excluyendo los que ya tiene por rol)
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
         $this->cerrarModalModulos();
     }
 
     /**
      * Asignar módulo completo extra (retrocompatibilidad).
      */
     public function asignarModulo(string $claveModulo, GestionarModulosUsuarioAction $action): void
     {
         $this->verificarPermiso();
 
         $user = User::findOrFail($this->usuarioSeleccionadoId);
         $action->asignar($user, $claveModulo);
 
         $this->permisosSeleccionados = $user->getDirectPermissions()->pluck('name')->toArray();
         session()->flash('success', "Módulo '{$claveModulo}' asignado exitosamente como extra.");
     }
 
     /**
      * Retirar módulo completo extra (retrocompatibilidad).
      */
     public function retirarModulo(string $claveModulo, GestionarModulosUsuarioAction $action): void
     {
         $this->verificarPermiso();
 
         $user = User::findOrFail($this->usuarioSeleccionadoId);
         $conserva = $action->retirar($user, $claveModulo);
 
         $this->permisosSeleccionados = $user->getDirectPermissions()->pluck('name')->toArray();
 
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
        $this->reset([
            'tipo_documento_id',
            'numero_documento',
            'primer_nombre',
            'primer_apellido',
            'email_crear',
            'roles_crear',
            'rol_principal_crear',
            'personaEncontrada',
            'personaTieneUsuario',
            'personaInfoTexto',
        ]);
        $this->resetErrorBag();
        $this->modalCrearAbierto = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->modalCrearAbierto = false;
        $this->reset([
            'tipo_documento_id',
            'numero_documento',
            'primer_nombre',
            'primer_apellido',
            'email_crear',
            'roles_crear',
            'rol_principal_crear',
            'personaEncontrada',
            'personaTieneUsuario',
            'personaInfoTexto',
        ]);
        $this->resetErrorBag();
    }

    /**
     * Crear usuario para persona con contraseña temporal y flujo de redirección híbrido.
     */
    public function crearUsuario(CrearUsuarioParaPersonaAction $action)
    {
        $this->verificarPermiso();

        $this->validate([
            'tipo_documento_id'   => 'required|exists:tipo_documentos,id',
            'numero_documento'    => 'required|string|max:9',
            'primer_nombre'       => 'required|string|max:50',
            'primer_apellido'     => 'required|string|max:50',
            'email_crear'         => 'required|email|max:255|unique:users,email',
            'roles_crear'         => 'required|array|min:1',
            'roles_crear.*'       => 'in:Admin,Subdirectora,Docente,Representante',
            'rol_principal_crear' => 'required|string',
        ]);

        try {
            $resultado = $action->ejecutar([
                'tipo_documento_id' => $this->tipo_documento_id,
                'numero_documento'  => $this->numero_documento,
                'primer_nombre'     => $this->primer_nombre,
                'primer_apellido'   => $this->primer_apellido,
                'email'             => $this->email_crear,
                'roles'             => $this->roles_crear,
                'rol_principal'     => $this->rol_principal_crear,
            ]);

            $user = $resultado['user'];
            $pass = $resultado['password_temporal'];
            $persona = $resultado['persona'];

            $this->passwordTemporalGenerada = $pass;
            $this->usuarioPasswordTemporal = $user->email;

            // Verificar si requiere completar la ficha en otro módulo
            if (!empty($resultado['redirigir_a_docente'])) {
                session()->flash('success', "Usuario creado. Ahora por favor completa la ficha del docente.");
                $this->cerrarModalCrear();
                return redirect()->route('admin.docente.create', ['persona_id' => $persona->id]);
            }

            if (!empty($resultado['redirigir_a_representante'])) {
                // Asegurar que exista el registro en la tabla representantes antes de redirigir
                $representante = \App\Models\Representante::withTrashed()->where('persona_id', $persona->id)->first();
                if ($representante && $representante->trashed()) {
                    $representante->restore();
                }

                if (!$representante) {
                    $ocupacionDefecto = \App\Models\Ocupacion::firstOrCreate(
                        ['nombre_ocupacion' => 'Comerciante'],
                        ['status' => 1]
                    );

                    $localidadDefecto = \App\Models\Localidad::with('municipio.estado')->first();
                    $parroquiaId = $localidadDefecto ? $localidadDefecto->id : 1;
                    $municipioId = ($localidadDefecto && $localidadDefecto->municipio_id) ? $localidadDefecto->municipio_id : 1;
                    $estadoId = ($localidadDefecto && $localidadDefecto->municipio && $localidadDefecto->municipio->estado_id) 
                        ? $localidadDefecto->municipio->estado_id 
                        : (\App\Models\Estado::value('id') ?? 1);
                    $paisId = ($localidadDefecto && $localidadDefecto->municipio && $localidadDefecto->municipio->estado && $localidadDefecto->municipio->estado->pais_id)
                        ? $localidadDefecto->municipio->estado->pais_id
                        : (\App\Models\Pais::value('id') ?? 1);

                    $representante = \App\Models\Representante::firstOrCreate(
                        ['persona_id' => $persona->id],
                        [
                            'pais_id'                             => $paisId,
                            'estado_id'                           => $estadoId,
                            'municipio_id'                        => $municipioId,
                            'parroquia_id'                        => $parroquiaId,
                            'ocupacion_representante'             => $ocupacionDefecto->id,
                            'convivenciaestudiante_representante' => 'Si',
                            'status'                              => 1,
                        ]
                    );
                }

                session()->flash('success', "Usuario creado. Ahora por favor completa la ficha del representante.");
                $this->cerrarModalCrear();
                return redirect()->route('representante.editar', ['id' => $representante->id]);
            }

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

        return view('livewire.admin.usuarios.gestionar-usuarios', [
            'usuarios'         => $usuarios,
            'infoModulos'      => $infoModulos,
            'rolesDisponibles' => $rolesDisponibles,
            'tipos_documentos' => $this->tipos_documentos,
        ]);
    }
}

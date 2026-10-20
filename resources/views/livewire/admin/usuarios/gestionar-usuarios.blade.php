<div>
    {{-- Header AdminLTE --}}
    <div class="content-header-modern mb-4">
        <div class="header-content d-flex justify-content-between align-items-center">
            <div class="header-title d-flex align-items-center">
                <div class="icon-wrapper me-3" style="width: 48px; height: 48px; background: #007bff; color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div>
                    <h1 class="title-main m-0" style="font-size: 24px; font-weight: 700;">Gestión de Usuarios y Accesos</h1>
                    <p class="title-subtitle m-0 text-muted" style="font-size: 14px;">Administración de cuentas, estados, contraseñas temporales y módulos extra</p>
                </div>
            </div>

            <button type="button" class="btn btn-primary" wire:click="abrirModalCrear">
                <i class="fas fa-user-plus me-1"></i>
                <span>Crear Usuario</span>
            </button>
        </div>
    </div>

    {{-- Notificaciones y alertas --}}
    @if (session()->has('success') || session()->has('error') || session()->has('info'))
        <div class="mb-3">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session()->has('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    @endif

    {{-- Banner de contraseña temporal generada --}}
    @if ($passwordTemporalGenerada)
        <div class="alert alert-warning alert-dismissible fade show p-3 mb-4 shadow-sm" role="alert" style="border-left: 5px solid #ffc107;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="alert-heading m-0"><i class="fas fa-key me-2"></i> Contraseña Temporal Generada</h5>
                    <p class="mb-1 mt-1">Usuario: <strong>{{ $usuarioPasswordTemporal }}</strong></p>
                    <p class="mb-0">Contraseña asignada: <code style="font-size: 16px; font-weight: bold; background: #fff; padding: 3px 8px; border-radius: 4px; color: #d63384;">{{ $passwordTemporalGenerada }}</code></p>
                    <small class="text-muted">El usuario deberá verificar su correo y cambiar su contraseña al iniciar sesión.</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-dark" wire:click="limpiarAvisoPassword">Entendido</button>
            </div>
        </div>
    @endif

    {{-- Tarjeta Principal con Filtros y Tabla --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            {{-- Barra de búsqueda y filtros --}}
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control" placeholder="Buscar por nombre, correo o cédula..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filtroRol">
                        <option value="">Todos los roles</option>
                        @foreach ($rolesDisponibles as $rol)
                            <option value="{{ $rol }}">{{ $rol }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filtroActivo">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-outline-secondary w-100" wire:click="$set('search', '')">
                        <i class="fas fa-undo me-1"></i> Limpiar
                    </button>
                </div>
            </div>

            {{-- Tabla de Usuarios --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre / Cédula</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Rol Principal</th>
                            <th>Módulos por Defecto</th>
                            <th>Módulos Extra</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $u)
                            @php
                                $permsRol = $u->getPermissionsViaRoles()->pluck('name')->flip();
                                $directos = $u->getDirectPermissions()->pluck('name')->flip();

                                $modulosDefecto = [];
                                $modulosExtra = [];

                                foreach (config('modulos', []) as $clave => $cMod) {
                                    $pMod = $cMod['permisos'] ?? [];
                                    $tieneRol = false;
                                    foreach ($pMod as $p) {
                                        if (isset($permsRol[$p])) {
                                            $tieneRol = true;
                                            break;
                                        }
                                    }
                                    if ($tieneRol) {
                                        $modulosDefecto[] = $cMod['nombre'] ?? $clave;
                                    } else {
                                        $tieneDir = false;
                                        foreach ($pMod as $p) {
                                            if (isset($directos[$p])) {
                                                $tieneDir = true;
                                                break;
                                            }
                                        }
                                        if ($tieneDir) {
                                            $modulosExtra[] = $cMod['nombre'] ?? $clave;
                                        }
                                    }
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $u->name }}</div>
                                    <small class="text-muted">
                                        {{ $u->persona?->tipoDocumento?->siglas ?? 'V' }}-{{ $u->persona?->numero_documento ?? 'S/C' }}
                                    </small>
                                </td>
                                <td>
                                    <div>{{ $u->email }}</div>
                                    @if ($u->email_verified_at)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 11px;">
                                            <i class="fas fa-check-circle"></i> Verificado
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 11px;">
                                            <i class="fas fa-clock"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @foreach ($u->roles as $r)
                                        <span class="badge bg-primary text-white mb-1">{{ $r->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $u->rolPrincipalEfectivo() ?? 'Ninguno' }}</span>
                                </td>
                                <td>
                                    <div style="max-width: 200px; display: flex; flex-wrap: wrap; gap: 3px;">
                                        @forelse (array_slice($modulosDefecto, 0, 3) as $mDef)
                                            <span class="badge bg-secondary text-white" style="font-size: 11px;">{{ $mDef }}</span>
                                        @empty
                                            <small class="text-muted">Ninguno</small>
                                        @endforelse
                                        @if (count($modulosDefecto) > 3)
                                            <span class="badge bg-light text-dark border" style="font-size: 11px;">+{{ count($modulosDefecto) - 3 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-light text-dark border me-1">
                                            {{ count($modulosExtra) }} extra
                                        </span>
                                        <button class="btn btn-sm btn-outline-primary" wire:click="abrirModalModulos({{ $u->id }})" title="Gestionar módulos extra">
                                            <i class="fas fa-cog"></i> Gestionar
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    @if ($u->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        {{-- Activar/Desactivar --}}
                                        <button class="btn btn-sm {{ $u->activo ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            wire:click="toggleActivo({{ $u->id }})"
                                            title="{{ $u->activo ? 'Desactivar usuario' : 'Activar usuario' }}">
                                            <i class="fas {{ $u->activo ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                        </button>

                                        {{-- Reenviar acceso --}}
                                        <button class="btn btn-sm btn-outline-warning text-dark"
                                            wire:click="reenviarAcceso({{ $u->id }})"
                                            wire:confirm="¿Deseas generar y reenviar una nueva contraseña temporal a {{ $u->email }}?"
                                            title="Reenviar acceso por correo">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>

                                        {{-- Corregir correo --}}
                                        @if ($u->persona_id)
                                            <button class="btn btn-sm btn-outline-info"
                                                wire:click="abrirModalCorreo({{ $u->persona_id }})"
                                                title="Corregir correo electrónico">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-user-slash fa-2x mb-2"></i>
                                    <div>No se encontraron usuarios que coincidan con la búsqueda.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-3">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL GESTIONAR MÓDULOS EXTRA --}}
    @if ($modalModulosAbierto)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-cubes me-2"></i>
                            Módulos del Usuario: {{ $usuarioSeleccionadoNombre }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalModulos"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted">
                            Los módulos que provienen de los <strong>roles</strong> del usuario están bloqueados (no se pueden retirar aquí).
                            Puedes habilitar o remover <strong>módulos extra</strong> mediante permisos directos.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Módulo</th>
                                        <th>Grupo</th>
                                        <th>Origen</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($infoModulos as $claveMod => $datosMod)
                                        <tr>
                                            <td class="fw-bold">{{ $datosMod['nombre'] }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ ucfirst($datosMod['grupo']) }}</span></td>
                                            <td>
                                                @if ($datosMod['tiene_por_rol'])
                                                    <span class="badge bg-secondary"><i class="fas fa-lock me-1"></i> Por Rol</span>
                                                @elseif ($datosMod['tiene_directo'])
                                                    <span class="badge bg-success"><i class="fas fa-plus-circle me-1"></i> Extra (Directo)</span>
                                                @else
                                                    <span class="badge bg-light text-muted border">Sin asignar</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($datosMod['activo'])
                                                    <span class="badge bg-success">Habilitado</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if ($datosMod['tiene_por_rol'])
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Módulo otorgado por rol">
                                                        <i class="fas fa-lock"></i> Bloqueado
                                                    </button>
                                                @elseif ($datosMod['tiene_directo'])
                                                    <button class="btn btn-sm btn-danger" wire:click="retirarModulo('{{ $claveMod }}')">
                                                        <i class="fas fa-trash-alt me-1"></i> Retirar
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-primary" wire:click="asignarModulo('{{ $claveMod }}')">
                                                        <i class="fas fa-plus me-1"></i> Asignar Extra
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModalModulos">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CORREGIR CORREO --}}
    @if ($modalCorreoAbierto)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-envelope-open-text me-2"></i> Corregir Correo Electrónico</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalCorreo"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted">Correo Actual:</label>
                            <div class="fw-bold">{{ $corregirCorreoActual }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nuevo Correo Electrónico:</label>
                            <input type="email" class="form-control @error('nuevoCorreo') is-invalid @enderror" wire:model="nuevoCorreo">
                            @error('nuevoCorreo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i> Al guardar, se actualizará el correo en la persona y su usuario asociado, invalidando la verificación previa (email_verified_at = null).
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModalCorreo">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="guardarCorreo">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CREAR USUARIO --}}
    @if ($modalCrearAbierto)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Crear Usuario con Contraseña Temporal</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalCrear"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted" style="font-size: 13px;">
                            Ingresa la cédula de la persona precargada (Docente o Representante). El sistema le generará una contraseña temporal segura de 12 caracteres y requerirá cambio al ingresar.
                        </p>

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label">Tipo Doc.</label>
                                <select class="form-select @error('tipo_documento_id') is-invalid @enderror" wire:model="tipo_documento_id">
                                    <option value="">Sel...</option>
                                    @foreach ($tiposDocumento as $td)
                                        <option value="{{ $td->id }}">{{ $td->siglas ?? $td->descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_documento_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-8">
                                <label class="form-label">Número Cédula</label>
                                <input type="text" class="form-control @error('numero_documento') is-invalid @enderror" wire:model="numero_documento" placeholder="Ej: 12345678">
                                @error('numero_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico de la Cuenta</label>
                            <input type="email" class="form-control @error('email_crear') is-invalid @enderror" wire:model="email_crear" placeholder="correo@ejemplo.com">
                            @error('email_crear')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModalCrear">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="crearUsuario">
                            <i class="fas fa-check me-1"></i> Crear Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

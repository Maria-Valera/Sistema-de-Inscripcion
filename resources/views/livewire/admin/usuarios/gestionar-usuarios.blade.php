<div>
    <style>
        /* Estilos alineados con la identidad visual y Calendarios Escolares */
        .badge-role-pill {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            display: inline-block;
        }
        .badge-role-admin {
            background-color: #eef2ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }
        .badge-role-docente {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .badge-role-subdirectora {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-role-representante {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .badge-role-default {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }
        .badge-modulo-pill {
            font-size: 0.72rem;
            font-weight: 500;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
        }
        .user-dropdown-menu {
            min-width: 190px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.4rem 0;
            z-index: 1050;
        }
        .user-dropdown-menu .dropdown-item {
            font-size: 0.85rem;
            padding: 0.45rem 1rem;
            transition: all 0.2s ease;
        }
        .user-dropdown-menu .dropdown-item:hover {
            background-color: #f8fafc;
        }
        .action-dropdown-btn {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #64748b;
            transition: all 0.2s ease;
        }
        .action-dropdown-btn:hover, .action-dropdown-btn:focus {
            background: #f8fafc;
            color: #1e293b;
            border-color: #cbd5e1;
        }
    </style>

    {{-- Header idéntico a Calendarios Escolares --}}
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Usuarios y Accesos</h1>
                    <p class="title-subtitle">Administración de cuentas, estados, contraseñas temporales y módulos extra</p>
                </div>
            </div>
            <button type="button" class="btn-create" wire:click="abrirModalCrear">
                <i class="fas fa-user-plus"></i>
                <span>Crear Usuario</span>
            </button>
        </div>
    </div>

    {{-- Alertas y notificaciones --}}
    @if (session('success') || session('error') || session('info') || session('warning'))
        <div class="alerts-container mb-3">
            @if (session('success'))
                <div class="alert-modern alert-success alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-modern alert-error alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Error</h4>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert-modern alert-warning alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Advertencia</h4>
                        <p>{{ session('warning') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert" style="border-radius: var(--radius);">
                    <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    @endif

    {{-- Banner de contraseña temporal generada --}}
    @if ($passwordTemporalGenerada)
        <div class="alert alert-warning alert-dismissible fade show p-3 mb-4 shadow-sm" role="alert" style="border-left: 5px solid #f59e0b; border-radius: var(--radius); background-color: #fffbeb;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="alert-heading m-0 fw-bold text-dark"><i class="fas fa-key me-2 text-warning"></i> Contraseña Temporal Generada</h5>
                    <p class="mb-1 mt-1">Usuario: <strong>{{ $usuarioPasswordTemporal }}</strong></p>
                    <p class="mb-0">Contraseña asignada: <code style="font-size: 15px; font-weight: bold; background: #ffffff; padding: 3px 8px; border-radius: 6px; border: 1px solid #fde68a; color: var(--primary);">{{ $passwordTemporalGenerada }}</code></p>
                    <small class="text-muted">El usuario deberá verificar su correo y cambiar su contraseña al iniciar sesión.</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-dark" wire:click="limpiarAvisoPassword">Entendido</button>
            </div>
        </div>
    @endif

    {{-- Tarjeta Principal idéntica a card-modern de Calendarios --}}
    <div class="card-modern">
        <div class="card-header-modern">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div>
                    <h3>Listado de Usuarios</h3>
                    <p>{{ $usuarios->total() }} registros encontrados</p>
                </div>
            </div>

            {{-- Barra de búsqueda y filtros en el header --}}
            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="search-box">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                    <input type="text" class="form-control search-input" placeholder="Buscar usuario..." wire:model.live.debounce.300ms="search">
                    @if($search)
                        <a href="javascript:void(0)" class="clear-search" wire:click="$set('search', '')">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>

                <div class="status-filter">
                    <select class="form-select" wire:model.live="filtroRol">
                        <option value="">Todos los roles</option>
                        @foreach ($rolesDisponibles as $rol)
                            <option value="{{ $rol }}">{{ $rol }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="status-filter" style="min-width: 130px;">
                    <select class="form-select" wire:model.live="filtroActivo">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body-modern">
            <div class="table-wrapper">
                <table class="table-modern overflow-hidden align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 170px;">Nombre / Cédula</th>
                            <th style="min-width: 180px;">Email</th>
                            <th>Roles</th>
                            <th>Rol Principal</th>
                            <th>Módulos por Defecto</th>
                            <th class="text-center">Módulos Extra</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center" width="70">Acciones</th>
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
                                    <div class="fw-semibold text-dark">{{ $u->name }}</div>
                                    <small class="text-muted">
                                        <i class="far fa-id-card me-1 text-primary"></i>{{ $u->persona?->tipoDocumento?->siglas ?? 'V' }}-{{ $u->persona?->numero_documento ?? 'S/C' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ $u->email }}</div>
                                    @if ($u->email_verified_at)
                                        <span class="status-badge status-active mt-1" style="padding: 0.15rem 0.55rem; font-size: 0.72rem;">
                                            <span class="status-dot"></span>
                                            Verificado
                                        </span>
                                    @else
                                        <span class="status-badge status-pending mt-1" style="padding: 0.15rem 0.55rem; font-size: 0.72rem;">
                                            <span class="status-dot"></span>
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse ($u->roles as $r)
                                            @php
                                                $roleClass = match(strtolower($r->name)) {
                                                    'admin' => 'badge-role-admin',
                                                    'docente' => 'badge-role-docente',
                                                    'subdirectora' => 'badge-role-subdirectora',
                                                    'representante' => 'badge-role-representante',
                                                    default => 'badge-role-default'
                                                };
                                            @endphp
                                            <span class="badge-role-pill {{ $roleClass }}">{{ $r->name }}</span>
                                        @empty
                                            <span class="text-muted small">Sin rol</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $rp = $u->rolPrincipalEfectivo();
                                    @endphp
                                    @if($rp)
                                        <span class="badge-role-pill badge-role-admin">
                                            <i class="fas fa-star me-1 text-warning" style="font-size: 0.65rem;"></i>{{ $rp }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Ninguno</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="max-width: 190px; display: flex; flex-wrap: wrap; gap: 4px;">
                                        @forelse (array_slice($modulosDefecto, 0, 2) as $mDef)
                                            <span class="badge-modulo-pill bg-light text-dark border" title="{{ $mDef }}">{{ $mDef }}</span>
                                        @empty
                                            <small class="text-muted">Ninguno</small>
                                        @endforelse
                                        @if (count($modulosDefecto) > 2)
                                            <span class="badge-modulo-pill bg-secondary text-white" title="{{ implode(', ', array_slice($modulosDefecto, 2)) }}">
                                                +{{ count($modulosDefecto) - 2 }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if(count($modulosExtra) > 0)
                                        <span class="status-badge status-parent" style="padding: 0.2rem 0.65rem; font-size: 0.75rem;">
                                            {{ count($modulosExtra) }} extra
                                        </span>
                                    @else
                                        <span class="text-muted small">0 extra</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($u->activo)
                                        <span class="status-badge status-active">
                                            <span class="status-dot"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <span class="status-dot"></span>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <div class="dropdown dropstart text-center">
                                            <button class="btn btn-light btn-sm rounded-circle shadow-sm action-dropdown-btn"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end shadow user-dropdown-menu">
                                                {{-- Gestionar Módulos --}}
                                                <li>
                                                    <button class="dropdown-item d-flex align-items-center text-primary"
                                                        wire:click="abrirModalModulos({{ $u->id }})">
                                                        <i class="fas fa-cubes me-2 text-primary" style="width: 16px;"></i>
                                                        Gestionar Módulos
                                                    </button>
                                                </li>

                                                {{-- Corregir Correo --}}
                                                @if ($u->persona_id)
                                                    <li>
                                                        <button class="dropdown-item d-flex align-items-center text-secondary"
                                                            wire:click="abrirModalCorreo({{ $u->persona_id }})">
                                                            <i class="fas fa-envelope-open-text me-2" style="width: 16px;"></i>
                                                            Corregir Correo
                                                        </button>
                                                    </li>
                                                @endif

                                                {{-- Reenviar Acceso --}}
                                                <li>
                                                    <button class="dropdown-item d-flex align-items-center text-warning"
                                                        wire:click="reenviarAcceso({{ $u->id }})"
                                                        wire:confirm="¿Deseas generar y reenviar una nueva contraseña temporal a {{ $u->email }}?">
                                                        <i class="fas fa-paper-plane me-2 text-warning" style="width: 16px;"></i>
                                                        Reenviar Acceso
                                                    </button>
                                                </li>

                                                <li><hr class="dropdown-divider my-1"></li>

                                                {{-- Activar / Desactivar --}}
                                                <li>
                                                    <button class="dropdown-item d-flex align-items-center {{ $u->activo ? 'text-danger' : 'text-success' }}"
                                                        wire:click="toggleActivo({{ $u->id }})">
                                                        <i class="fas {{ $u->activo ? 'fa-user-slash text-danger' : 'fa-user-check text-success' }} me-2" style="width: 16px;"></i>
                                                        {{ $u->activo ? 'Desactivar Usuario' : 'Activar Usuario' }}
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 text-secondary"></i>
                                    <h5>No se encontraron usuarios</h5>
                                    <p class="small mb-0">Intenta cambiar los términos de búsqueda o filtros aplicados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación idéntica --}}
            <div class="pagination-wrapper mt-3">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL GESTIONAR MÓDULOS EXTRA (Diseño idéntico a modales modernos) --}}
    @if ($modalModulosAbierto)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content modal-modern shadow-lg">
                    <div class="modal-header-view" style="position: relative;">
                        <h4 class="modal-title-view">
                            <i class="fas fa-cubes me-2"></i>
                            Módulos del Usuario: {{ $usuarioSeleccionadoNombre }}
                        </h4>
                        <button type="button" class="btn-close-modal" wire:click="cerrarModalModulos" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background: #ffffff;">
                        <div class="alert alert-light border mb-3 py-2 px-3 small text-muted d-flex align-items-center gap-2" style="border-radius: var(--radius);">
                            <i class="fas fa-info-circle text-primary"></i>
                            <div>
                                Los módulos otorgados por <strong>roles</strong> están bloqueados por seguridad. Puedes asignar o retirar <strong>módulos extra</strong> como permisos directos.
                            </div>
                        </div>

                        <div class="table-wrapper">
                            <table class="table-modern w-100 align-middle">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">Módulo</th>
                                        <th style="text-align: left;">Grupo</th>
                                        <th style="text-align: center;">Origen</th>
                                        <th style="text-align: center;">Estado</th>
                                        <th style="text-align: center;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($infoModulos as $claveMod => $datosMod)
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $datosMod['nombre'] }}</td>
                                            <td>
                                                <span class="badge-modulo-pill bg-light text-dark border">{{ ucfirst($datosMod['grupo']) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if ($datosMod['tiene_por_rol'])
                                                    <span class="badge-role-pill badge-role-default">
                                                        <i class="fas fa-lock me-1"></i> Por Rol
                                                    </span>
                                                @elseif ($datosMod['tiene_directo'])
                                                    <span class="badge-role-pill badge-role-docente">
                                                        <i class="fas fa-plus-circle me-1"></i> Extra
                                                    </span>
                                                @else
                                                    <span class="text-muted small">Sin asignar</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($datosMod['activo'])
                                                    <span class="status-badge status-active" style="padding: 0.15rem 0.5rem; font-size: 0.72rem;">
                                                        <span class="status-dot"></span> Habilitado
                                                    </span>
                                                @else
                                                    <span class="status-badge status-inactive" style="padding: 0.15rem 0.5rem; font-size: 0.72rem;">
                                                        <span class="status-dot"></span> Inactivo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($datosMod['tiene_por_rol'])
                                                    <button class="btn btn-sm btn-light border text-muted" disabled style="font-size: 0.75rem; border-radius: 6px;">
                                                        <i class="fas fa-lock me-1"></i> Bloqueado
                                                    </button>
                                                @elseif ($datosMod['tiene_directo'])
                                                    <button class="btn btn-sm btn-danger text-white" wire:click="retirarModulo('{{ $claveMod }}')" style="font-size: 0.75rem; border-radius: 6px;">
                                                        <i class="fas fa-trash-alt me-1"></i> Retirar
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-primary" wire:click="asignarModulo('{{ $claveMod }}')" style="font-size: 0.75rem; border-radius: 6px;">
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

                    <div class="modal-footer-view">
                        <button type="button" class="btn btn-secondary px-4" wire:click="cerrarModalModulos" style="border-radius: var(--radius);">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CORREGIR CORREO --}}
    @if ($modalCorreoAbierto)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-modern shadow-lg">
                    <div class="modal-header-view" style="position: relative;">
                        <h4 class="modal-title-view">
                            <i class="fas fa-envelope-open-text me-2"></i>
                            Corregir Correo Electrónico
                        </h4>
                        <button type="button" class="btn-close-modal" wire:click="cerrarModalCorreo" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background: #ffffff;">
                        <div class="mb-3 p-3 bg-light border rounded">
                            <label class="text-muted small mb-1 d-block">Correo Electrónico Actual:</label>
                            <span class="fw-bold text-dark font-monospace" style="font-size: 0.95rem;">{{ $corregirCorreoActual }}</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Nuevo Correo Electrónico:</label>
                            <input type="email" class="form-control @error('nuevoCorreo') is-invalid @enderror" wire:model="nuevoCorreo" placeholder="correo.nuevo@ejemplo.com" style="border-radius: 8px;">
                            @error('nuevoCorreo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="small text-muted mt-2 d-flex align-items-start gap-1">
                                <i class="fas fa-info-circle text-primary mt-1"></i>
                                <span>Al guardar, se actualizará en la persona y el usuario vinculado, invalidando la verificación previa (deberá verificar el nuevo email).</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-view gap-2">
                        <button type="button" class="btn btn-light border px-4" wire:click="cerrarModalCorreo" style="border-radius: var(--radius);">
                            Cancelar
                        </button>
                        <button type="button" class="btn-create" wire:click="guardarCorreo">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CREAR USUARIO (Flujo Híbrido) --}}
    @if ($modalCrearAbierto)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content modal-modern shadow-lg">
                    <div class="modal-header-create">
                        <div class="modal-icon-create">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h4 class="modal-title-create">Crear Usuario</h4>
                        <p class="modal-subtitle-create mb-0">Asignar cuenta con contraseña temporal y vinculación de persona</p>
                        <button type="button" class="btn-close-modal" wire:click="cerrarModalCrear" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background: #ffffff;">
                        <p class="text-muted small mb-3">
                            Ingresa los datos del usuario. Si la persona no existe previamente, el sistema la creará automáticamente. Si seleccionas rol <strong>Docente</strong> o <strong>Representante</strong> y no tiene ficha completa, se te redirigirá para completarla.
                        </p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Tipo Doc. *</label>
                                <select class="form-select @error('tipo_documento_id') is-invalid @enderror" wire:model.live="tipo_documento_id" style="border-radius: 8px;">
                                    <option value="">Sel...</option>
                                    @foreach ($tipos_documentos as $td)
                                        <option value="{{ $td->id }}">{{ $td->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_documento_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small text-dark">Número de Cédula *</label>
                                <input type="text" class="form-control @error('numero_documento') is-invalid @enderror" wire:model.live.debounce.400ms="numero_documento" placeholder="Ej: 12345678" maxlength="9" style="border-radius: 8px;">
                                @error('numero_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Aviso de persona existente detectada y autocompletada --}}
                        @if ($personaEncontrada && $personaInfoTexto && !$personaTieneUsuario)
                            <div class="alert alert-success py-2 px-3 mb-3 d-flex align-items-center gap-2 small" style="border-radius: 8px; font-size: 0.85rem;">
                                <i class="fas fa-check-circle text-success fs-6"></i>
                                <div>
                                    <strong>{{ $personaInfoTexto }}</strong>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Primer Nombre *</label>
                                <input type="text" class="form-control @error('primer_nombre') is-invalid @enderror" wire:model.live.debounce.500ms="primer_nombre" placeholder="Ej: María" style="border-radius: 8px;" {{ $personaEncontrada && !$personaTieneUsuario ? 'readonly' : '' }}>
                                @error('primer_nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Primer Apellido *</label>
                                <input type="text" class="form-control @error('primer_apellido') is-invalid @enderror" wire:model.live.debounce.500ms="primer_apellido" placeholder="Ej: González" style="border-radius: 8px;" {{ $personaEncontrada && !$personaTieneUsuario ? 'readonly' : '' }}>
                                @error('primer_apellido')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark">Correo Electrónico de la Cuenta *</label>
                            <input type="email" class="form-control @error('email_crear') is-invalid @enderror" wire:model.live.debounce.500ms="email_crear" placeholder="correo@ejemplo.com" style="border-radius: 8px;">
                            @error('email_crear')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark d-block mb-2">Roles a Asignar *</label>
                                <div class="p-2 border rounded bg-light @error('roles_crear') border-danger @enderror" style="border-radius: 8px;">
                                    @php
                                        $rolesOpciones = ['Admin', 'Subdirectora', 'Docente', 'Representante'];
                                    @endphp
                                    @foreach ($rolesOpciones as $rolOpcion)
                                        <div class="form-check form-check-inline me-3 mb-1">
                                            <input class="form-check-input" type="checkbox" value="{{ $rolOpcion }}" id="rol_check_{{ $rolOpcion }}" wire:model.live="roles_crear">
                                            <label class="form-check-label small fw-medium" for="rol_check_{{ $rolOpcion }}">
                                                {{ $rolOpcion }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('roles_crear')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark mb-2">Rol Principal *</label>
                                <select class="form-select @error('rol_principal_crear') is-invalid @enderror" wire:model.live="rol_principal_crear" style="border-radius: 8px;">
                                    <option value="">Selecciona rol principal...</option>
                                    @foreach ($roles_crear as $rolAsignado)
                                        <option value="{{ $rolAsignado }}">{{ $rolAsignado }}</option>
                                    @endforeach
                                </select>
                                @error('rol_principal_crear')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                                    Define la interfaz y dashboard predeterminado para el usuario.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-view gap-2">
                        <button type="button" class="btn btn-light border px-4" wire:click="cerrarModalCrear" style="border-radius: var(--radius);">
                            Cancelar
                        </button>
                        <button type="button" class="btn-create" wire:click="crearUsuario">
                            <i class="fas fa-check me-1"></i> Crear Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

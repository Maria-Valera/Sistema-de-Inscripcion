<div>
    <style>
        /* Estilos alineados con la identidad visual moderna y minimalista */
        .table-modern tbody td {
            padding-top: 0.9rem !important;
            padding-bottom: 0.9rem !important;
            vertical-align: middle;
        }
        .badge-role-pill {
            font-size: 0.73rem;
            font-weight: 500;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
        }
        .badge-role-admin {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .badge-role-docente {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .badge-role-subdirectora {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-role-representante {
            background-color: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .badge-role-default {
            background-color: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }
        .status-badge-soft-success {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.2rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }
        .status-badge-soft-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.2rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
            background-color: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
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
        /* Contenedor equilibrado para monitores grandes */
        .user-view-container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .table-modern {
            min-width: 840px;
            width: 100%;
        }
        .table-modern thead th {
            padding: 0.85rem 0.75rem !important;
            font-size: 0.76rem !important;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .table-modern tbody td {
            padding-top: 0.85rem !important;
            padding-bottom: 0.85rem !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            vertical-align: middle;
        }
    </style>

    {{-- Contenedor con ancho máximo para pantallas grandes --}}
    <div class="user-view-container">

    {{-- Hero Header de sección moderno azul institucional --}}
    <div class="content-header-modern hero-header-horario hero-header-modern mb-4" style="background: linear-gradient(135deg, #4A6FC1 0%, #3B5BB3 100%) !important; color: #FFFFFF !important; border-radius: 12px; padding: 1.5rem 2rem;">
        <div class="header-content d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper" style="background: rgba(255, 255, 255, 0.2) !important; color: #FFFFFF !important; width: 48px; height: 48px; min-width: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fas fa-users-cog text-white"></i>
                </div>
                <div>
                    <h1 class="title-main text-white m-0" style="color: #FFFFFF !important; font-size: 1.45rem; font-weight: 700;">Gestión de Usuarios y Accesos</h1>
                    <p class="title-subtitle text-white m-0" style="color: rgba(255, 255, 255, 0.9) !important; font-size: 0.88rem; margin-top: 0.25rem !important;">Administración de cuentas, estados, contraseñas temporales y módulos extra</p>
                </div>
            </div>
            <button type="button" class="btn-hero-white border-0 d-inline-flex align-items-center gap-2" wire:click="abrirModalCrear">
                <i class="fas fa-plus"></i>
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

    {{-- Tarjeta Principal --}}
    <div class="card-modern">
        <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3" style="padding: 1.25rem 1.5rem !important;">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon header-icon-pastel-blue">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div>
                    <h3 class="m-0" style="font-size: 1.15rem; font-weight: 700; color: #1E293B;">Listado de Usuarios</h3>
                    <p class="mb-0 text-muted" style="font-size: 0.85rem;">{{ $usuarios->total() }} registros encontrados</p>
                </div>
            </div>

            {{-- Barra de búsqueda y filtros en el header --}}
            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input-modern" placeholder="Buscar usuario..." wire:model.live.debounce.300ms="search">
                    @if($search)
                        <a href="javascript:void(0)" class="clear-search position-absolute end-0 me-2 text-muted" wire:click="$set('search', '')" style="cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>

                <div class="status-filter" style="min-width: 150px;">
                    <select class="form-select select-modern" wire:model.live="filtroRol" style="height: 38px; border-radius: 8px; border-color: #E2E8F0; font-size: 0.85rem; color: #1E293B;">
                        <option value="">Todos los roles</option>
                        @foreach ($rolesDisponibles as $rol)
                            <option value="{{ $rol }}">{{ $rol }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="status-filter" style="min-width: 140px;">
                    <select class="form-select select-modern" wire:model.live="filtroActivo" style="height: 38px; border-radius: 8px; border-color: #E2E8F0; font-size: 0.85rem; color: #1E293B;">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body-modern">
            <div class="table-wrapper table-responsive">
                <table class="table-modern overflow-hidden align-middle">
                    <thead>
                        <tr>
                            <th style="width: 20%;" class="text-nowrap">Nombre / Cédula</th>
                            <th style="width: 20%;" class="text-nowrap">Email</th>
                            <th style="width: 18%;" class="text-nowrap">Roles</th>
                            <th style="width: 22%;">Módulos por Defecto</th>
                            <th style="width: 10%;" class="text-center text-nowrap">Módulos Extra</th>
                            <th class="text-center text-nowrap" style="width: 5%;">Estado</th>
                            <th class="text-center text-nowrap" style="width: 5%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $u)
                            @php
                                $esAdmin = $u->roles->contains('name', 'Admin');
                                $rp = $u->rolPrincipalEfectivo();
                            @endphp
                            <tr>
                                {{-- 1. Nombre / Cédula: Jerarquía limpia (20%) --}}
                                <td style="width: 20%;">
                                    <div class="fw-bold text-dark" style="font-size: 0.92rem;">{{ $u->name }}</div>
                                    <div class="text-muted small" style="font-size: 0.78rem;">
                                        <i class="far fa-id-card me-1 text-secondary"></i>{{ $u->persona?->tipoDocumento?->siglas ?? 'V' }}-{{ $u->persona?->numero_documento ?? 'S/C' }}
                                    </div>
                                </td>

                                {{-- 2. Email: Verificación discreta (20%) --}}
                                <td style="width: 20%;">
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        <span class="text-dark" style="font-size: 0.88rem;">{{ $u->email }}</span>
                                        @if (! $u->email_verified_at)
                                            <span class="text-muted d-inline-flex align-items-center ms-1 text-nowrap" style="font-size: 0.72rem;" title="Pendiente de verificación">
                                                <i class="fas fa-clock text-secondary me-1" style="font-size: 0.7rem;"></i> Pendiente
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- 3. Roles: Fusión horizontal con flexbox gap (18%) --}}
                                <td style="width: 18%; min-width: 140px;">
                                    <div class="d-flex flex-wrap align-items-center gap-1" style="flex-direction: row;">
                                        @forelse ($u->roles as $r)
                                            @php
                                                $esPrincipal = ($r->name === $rp);
                                                $roleClass = match(strtolower($r->name)) {
                                                    'admin' => 'badge-role-admin',
                                                    'docente' => 'badge-role-docente',
                                                    'subdirectora' => 'badge-role-subdirectora',
                                                    'representante' => 'badge-role-representante',
                                                    default => 'badge-role-default'
                                                };
                                            @endphp
                                            <span class="badge-role-pill {{ $roleClass }} text-nowrap" title="{{ $esPrincipal ? 'Rol Principal' : '' }}">
                                                @if ($esPrincipal && $u->roles->count() > 1)
                                                    <i class="fas fa-star text-warning me-1" style="font-size: 0.65rem;"></i>
                                                @endif
                                                {{ $r->name }}
                                            </span>
                                        @empty
                                            <span class="text-muted small text-nowrap">Sin rol</span>
                                        @endforelse
                                    </div>
                                </td>

                                {{-- 4. Módulos / Roles Asignados (22%) --}}
                                <td style="width: 22%;">
                                    @if ($esAdmin)
                                        <span class="text-primary fw-bold small text-nowrap" style="font-size: 0.82rem;">
                                            <i class="fas fa-infinity me-1"></i> Acceso Total al Sistema
                                        </span>
                                    @else
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            <span class="text-secondary small" style="font-size: 0.8rem;">
                                                <i class="fas fa-shield-alt text-muted me-1"></i>
                                                {{ $u->roles->pluck('name')->implode(', ') ?: 'Sin rol asignado' }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- 5. Módulos Extra / Permisos Granulares (10%) --}}
                                <td class="text-center text-nowrap" style="width: 10%;">
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-primary fw-medium" wire:click="abrirModalModulos({{ $u->id }})" title="Gestionar Permisos Granulares" style="font-size: 0.8rem;">
                                        <i class="fas fa-sliders-h me-1"></i> Permisos
                                    </button>
                                </td>

                                {{-- 6. Estado: Ajustado a contenido con text-nowrap --}}
                                <td class="text-center text-nowrap" style="width: 5%;">
                                    @if ($u->activo)
                                        <span class="status-badge-soft-success text-nowrap">
                                            <span class="status-dot" style="width: 6px; height: 6px; background-color: #16a34a; border-radius: 50%;"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="status-badge-soft-secondary text-nowrap">
                                            <span class="status-dot" style="width: 6px; height: 6px; background-color: #94a3b8; border-radius: 50%;"></span>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                {{-- 7. Acciones: Ajustado a contenido con text-nowrap --}}
                                <td class="text-center text-nowrap" style="width: 5%;">
                                    <div class="action-buttons d-inline-block">
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
                                                    <button type="button" class="dropdown-item d-flex align-items-center text-warning"
                                                        onclick="confirmarReenvioAcceso({{ $u->id }}, '{{ $u->email }}')">
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
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 text-secondary"></i>
                                    <h5>No se encontraron usuarios</h5>
                                    <p class="small mb-0">Intenta cambiar los términos de búsqueda o filtros aplicados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="pagination-wrapper mt-3">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>
    </div> {{-- Fin .user-view-container --}}

    {{-- MODAL GESTIONAR MÓDULOS Y PERMISOS GRANULARES (Componente Independiente) --}}
    @if ($usuarioParaPermisos)
        <livewire:admin.usuarios.modal-permisos 
            :userId="$usuarioParaPermisos" 
            :key="'modal-permisos-' . $usuarioParaPermisos" 
        />
    @endif

    {{-- MODAL CORREGIR CORREO: Minimalista, sin input deshabilitado innecesario --}}
    @if ($modalCorreoAbierto)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0 !important; border-radius: 14px !important; overflow: hidden !important;">
                    <div class="modal-header d-flex align-items-center justify-content-between p-3 px-4 border-bottom" style="background-color: #FFFFFF !important; border-bottom: 1px solid #E2E8F0 !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background-color: #EFF6FF !important; color: #4A6FC1 !important; font-size: 1.15rem; flex-shrink: 0;">
                                <i class="fas fa-envelope-open-text" style="color: #4A6FC1 !important;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title m-0 fw-bold" style="color: #1E293B !important; font-size: 1.15rem;">Corregir Correo Electrónico</h5>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="cerrarModalCorreo" aria-label="Close" style="font-size: 0.85rem; box-shadow: none;"></button>
                    </div>

                    <div class="modal-body p-4" style="background: #ffffff;">
                        {{-- Correo actual como texto simple --}}
                        <p class="text-muted small mb-3">
                            Correo actual: <span class="fw-semibold text-dark font-monospace">{{ $corregirCorreoActual }}</span>
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Nuevo Correo Electrónico:</label>
                            <input type="email" class="form-control @error('nuevoCorreo') is-invalid @enderror" wire:model="nuevoCorreo" placeholder="ejemplo@correo.com" style="border-radius: 8px;">
                            @error('nuevoCorreo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="small text-muted mt-2 d-flex align-items-start gap-1" style="font-size: 0.78rem;">
                                <i class="fas fa-info-circle text-secondary mt-1"></i>
                                <span>Al guardar, se actualizará en la persona y el usuario vinculado, invalidando la verificación previa.</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end gap-2 p-3 px-4 border-top" style="background-color: #FFFFFF !important; border-top: 1px solid #E2E8F0 !important;">
                        <button type="button" class="btn-secondary-modern" wire:click="cerrarModalCorreo">
                            Cancelar
                        </button>
                        <button type="button" class="btn-primary-modern" wire:click="guardarCorreo">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CREAR USUARIO (Flujo Híbrido Moderno) --}}
    @if ($modalCrearAbierto)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" style="max-width: 800px; width: 100%;">
                <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0 !important; border-radius: 14px !important; overflow: hidden !important;">
                    
                    {{-- Header limpio blanco con icono azul pastel a la izquierda --}}
                    <div class="modal-header d-flex align-items-center justify-content-between p-3 px-4 border-bottom" style="background-color: #FFFFFF !important; border-bottom: 1px solid #E2E8F0 !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background-color: #EFF6FF !important; color: #4A6FC1 !important; font-size: 1.15rem; flex-shrink: 0;">
                                <i class="fas fa-user-plus" style="color: #4A6FC1 !important;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title m-0 fw-bold" style="color: #1E293B !important; font-size: 1.15rem;">Crear Usuario</h5>
                                <p class="m-0 text-muted" style="font-size: 0.82rem;">Asignar cuenta con contraseña temporal y vinculación de persona</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="cerrarModalCrear" aria-label="Close" style="font-size: 0.85rem; box-shadow: none;"></button>
                    </div>

                    {{-- Cuerpo del Modal con Alerta informativa y Grid de Campos --}}
                    <div class="modal-body p-4" style="background: #ffffff; max-height: 65vh; overflow-y: auto;">
                        {{-- Alerta informativa en azul suave con icono --}}
                        <div class="alert p-3 mb-4 d-flex align-items-start gap-2 border-0" style="background-color: #EFF6FF; color: #1E3A8A; border-radius: 8px; font-size: 13px; line-height: 1.45;">
                            <i class="fas fa-info-circle fs-6 mt-1 flex-shrink-0" style="color: #4A6FC1;"></i>
                            <div>
                                Ingresa los datos del usuario. Si la persona no existe previamente, el sistema la creará automáticamente. Si seleccionas rol <strong>Docente</strong> o <strong>Representante</strong> y no tiene ficha completa, se te redirigirá para completarla.
                            </div>
                        </div>

                        {{-- Grid de Campos --}}
                        <!-- Fila 1: Tipo Doc y Cédula -->
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px; margin-bottom: 16px;">
                            <div class="floating-group" style="margin-bottom: 0;">
                                <select class="form-select @error('tipo_documento_id') is-invalid @enderror" wire:model.live="tipo_documento_id" id="tipo_documento_id" placeholder=" " style="border-radius: 8px;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($tipos_documentos as $td)
                                        <option value="{{ $td->id }}">{{ $td->nombre }}</option>
                                    @endforeach
                                </select>
                                <label for="tipo_documento_id">Tipo Doc. *</label>
                                @error('tipo_documento_id')
                                    <div class="invalid-feedback d-block text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="floating-group" style="margin-bottom: 0;">
                                <input type="text" class="form-control @error('numero_documento') is-invalid @enderror" wire:model.live.debounce.400ms="numero_documento" id="numero_documento" placeholder=" " maxlength="9" autocomplete="off" style="border-radius: 8px;">
                                <label for="numero_documento">Número de Cédula *</label>
                                @error('numero_documento')
                                    <div class="invalid-feedback d-block text-danger small mt-1">{{ $message }}</div>
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

                        <!-- Fila 2: Nombres y Apellidos -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="floating-group" style="margin-bottom: 0;">
                                <input type="text" class="form-control @error('primer_nombre') is-invalid @enderror" wire:model.live.debounce.500ms="primer_nombre" id="primer_nombre" placeholder=" " {{ $personaEncontrada && !$personaTieneUsuario ? 'readonly' : '' }} autocomplete="off" style="border-radius: 8px;">
                                <label for="primer_nombre">Primer Nombre *</label>
                                @error('primer_nombre')
                                    <div class="invalid-feedback d-block text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="floating-group" style="margin-bottom: 0;">
                                <input type="text" class="form-control @error('primer_apellido') is-invalid @enderror" wire:model.live.debounce.500ms="primer_apellido" id="primer_apellido" placeholder=" " {{ $personaEncontrada && !$personaTieneUsuario ? 'readonly' : '' }} autocomplete="off" style="border-radius: 8px;">
                                <label for="primer_apellido">Primer Apellido *</label>
                                @error('primer_apellido')
                                    <div class="invalid-feedback d-block text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Fila 3: Correo (Ancho completo) -->
                        <div class="floating-group" style="margin-bottom: 24px;">
                            <input type="email" class="form-control @error('email_crear') is-invalid @enderror" wire:model.live.debounce.500ms="email_crear" id="email_crear" placeholder=" " autocomplete="off" style="border-radius: 8px;">
                            <label for="email_crear">Correo Electrónico *</label>
                            @error('email_crear')
                                <div class="invalid-feedback d-block text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Separador y Sección de Roles --}}
                        <hr class="my-4 text-muted">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-shield-alt text-primary me-2"></i> Asignar Roles al Usuario</h6>

                        {{-- Roles Dinámicos desde la BD --}}
                        <div class="row g-3 mb-3">
                            @foreach($roles as $rol)
                                <div class="col-md-4">
                                    <label class="d-flex align-items-center p-2 border rounded hover-bg-light user-select-none" style="cursor: pointer; border-color: {{ in_array($rol->name, $roles_crear) ? '#4A6FC1' : '#E2E8F0' }}; border-radius: 8px; background-color: {{ in_array($rol->name, $roles_crear) ? '#EFF6FF' : '#FFFFFF' }}; transition: all 0.2s ease;" for="rol_check_{{ $rol->id }}">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" value="{{ $rol->name }}" id="rol_check_{{ $rol->id }}" wire:model.live="roles_crear" style="cursor: pointer;">
                                        <span class="text-dark fw-medium" style="font-size: 14px;">{{ $rol->name }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('roles_crear')
                            <div class="text-danger small mb-3">{{ $message }}</div>
                        @enderror

                        {{-- Selector de Rol Principal si se seleccionan roles --}}
                        @if (!empty($roles_crear))
                            <div class="row g-3 mt-1">
                                <div class="col-md-12">
                                    <div class="floating-group mb-0">
                                        <select class="form-select @error('rol_principal_crear') is-invalid @enderror" wire:model.live="rol_principal_crear" id="rol_principal_crear" style="border-radius: 8px;">
                                            <option value="">Selecciona rol principal...</option>
                                            @foreach ($roles_crear as $rolAsignado)
                                                <option value="{{ $rolAsignado }}">{{ $rolAsignado }}</option>
                                            @endforeach
                                        </select>
                                        <label for="rol_principal_crear">Rol Principal *</label>
                                        @error('rol_principal_crear')
                                            <div class="invalid-feedback d-block text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                                        Define la interfaz y dashboard predeterminado para el usuario.
                                    </small>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Footer del Modal --}}
                    <div class="modal-footer d-flex justify-content-end gap-2 p-3 px-4 border-top" style="background-color: #FFFFFF !important; border-top: 1px solid #E2E8F0 !important;">
                        <button type="button" class="btn-secondary-modern" wire:click="cerrarModalCrear">
                            Cancelar
                        </button>
                        <button type="button" class="btn-primary-modern" wire:click="crearUsuario">
                            <i class="fas fa-check me-1"></i> Crear Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Script para inicialización de tooltips de Bootstrap y confirmaciones SweetAlert2 --}}
    <script>
        function confirmarReenvioAcceso(userId, email) {
            const mensaje = email
                ? `Se generará y enviará una nueva contraseña temporal al correo ${email}.`
                : "Se generará y enviará una nueva contraseña temporal al correo del usuario.";

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Reenviar contraseña?',
                    text: mensaje,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4A6FC1',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Sí, reenviar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.reenviarAcceso(userId);
                    }
                });
            } else {
                if (window.confirm("¿Deseas generar y reenviar una nueva contraseña temporal al correo del usuario?")) {
                    @this.reenviarAcceso(userId);
                }
            }
        }

        function initUserTooltips() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                document.querySelectorAll('[data-bs-toggle="tooltip"], [data-toggle="tooltip"]').forEach(function (el) {
                    new bootstrap.Tooltip(el);
                });
            } else if (window.$ && typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip();
            }
        }
        document.addEventListener('DOMContentLoaded', initUserTooltips);
        document.addEventListener('livewire:initialized', function () {
            if (window.Livewire && Livewire.hook) {
                Livewire.hook('morph.updated', () => {
                    initUserTooltips();
                });
            }
        });
    </script>
</div>

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

    {{-- Header de sección --}}
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

    {{-- Tarjeta Principal --}}
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
                                $rp = $u->rolPrincipalEfectivo();
                                $esAdmin = $u->roles->contains('name', 'Admin');
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

                                {{-- 4. Módulos por defecto: Admin Acceso Total o Truncado con Tooltip (22%) --}}
                                <td style="width: 22%;">
                                    @if ($esAdmin)
                                        <span class="text-primary fw-bold small text-nowrap" style="font-size: 0.82rem;">
                                            <i class="fas fa-infinity me-1"></i> Acceso Total al Sistema
                                        </span>
                                    @elseif (count($modulosDefecto) > 0)
                                        @php
                                            $primerosModulos = array_slice($modulosDefecto, 0, 3);
                                            $restantesModulos = array_slice($modulosDefecto, 3);
                                            $cantRestantes = count($restantesModulos);
                                        @endphp
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            <span class="text-secondary small" style="font-size: 0.8rem; line-height: 1.35;">
                                                {{ implode(', ', $primerosModulos) }}
                                            </span>
                                            @if ($cantRestantes > 0)
                                                <span class="badge bg-light text-muted border user-select-none" 
                                                      style="font-size: 0.7rem; font-weight: 500; cursor: pointer;"
                                                      data-bs-toggle="tooltip"
                                                      data-toggle="tooltip" 
                                                      data-bs-placement="top"
                                                      title="{{ implode(', ', $restantesModulos) }}">
                                                    +{{ $cantRestantes }} más
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- 5. Módulos Extra: Sin "0 extra" innecesario (10%) --}}
                                <td class="text-center text-nowrap" style="width: 10%;">
                                    @if(count($modulosExtra) > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="padding: 0.25rem 0.6rem; font-size: 0.75rem; border-radius: 9999px;">
                                            {{ count($modulosExtra) }} extra
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
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

    {{-- MODAL GESTIONAR MÓDULOS Y PERMISOS GRANULARES --}}
    @if ($modalModulosAbierto)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content modal-modern shadow-lg">
                    <div class="modal-header-view" style="position: relative;">
                        <h4 class="modal-title-view">
                            <i class="fas fa-cubes me-2"></i>
                            Gestión Granular de Permisos: {{ $usuarioSeleccionadoNombre }}
                        </h4>
                        <button type="button" class="btn-close-modal" wire:click="cerrarModalModulos" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background: #ffffff; max-height: 70vh; overflow-y: auto;">
                        <div class="alert alert-light border mb-3 py-2 px-3 small text-muted d-flex align-items-center gap-2" style="border-radius: var(--radius); border-color: #e2e8f0 !important;">
                            <i class="fas fa-info-circle text-primary fa-lg"></i>
                            <div>
                                Los permisos <strong>Por Rol</strong> aparecen deshabilitados por herencia de perfil. Marca o desmarca los permisos disponibles para asignarlos como <strong>permisos extra</strong> directos.
                            </div>
                        </div>

                        <div class="accordion" id="accordionModulosPermisos">
                            @php $modulosConfig = config('modulos', []); @endphp
                            @foreach ($modulosConfig as $claveModulo => $moduloData)
                                @php
                                    $permisosDelModulo = $moduloData['permisos'] ?? [];
                                    $cantPorRol = count(array_intersect($permisosDelModulo, $permisosPorRol));
                                    $cantDirectos = count(array_intersect($permisosDelModulo, array_diff($permisosSeleccionados, $permisosPorRol)));
                                @endphp
                                <div class="accordion-item mb-2 border rounded overflow-hidden" style="border-color: #e2e8f0 !important;">
                                    <h2 class="accordion-header" id="heading_{{ $claveModulo }}">
                                        <button class="accordion-button collapsed py-2 px-3 bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $claveModulo }}" aria-expanded="false" aria-controls="collapse_{{ $claveModulo }}">
                                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="{{ $moduloData['icono'] ?? 'fas fa-cube' }} text-secondary" style="width: 18px;"></i>
                                                    <span class="fw-semibold text-dark">{{ $moduloData['nombre'] ?? ucfirst($claveModulo) }}</span>
                                                    <span class="text-muted small" style="font-size: 0.72rem;">· {{ ucfirst($moduloData['grupo'] ?? 'general') }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($cantPorRol > 0)
                                                        <span class="badge bg-light text-secondary border small" style="font-size: 0.68rem; font-weight: normal;"><i class="fas fa-lock me-1"></i>{{ $cantPorRol }} por rol</span>
                                                    @endif
                                                    @if ($cantDirectos > 0)
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small" style="font-size: 0.68rem;"><i class="fas fa-plus-circle me-1"></i>{{ $cantDirectos }} extra</span>
                                                    @endif
                                                    <span class="text-muted small" style="font-size: 0.72rem;">({{ count($permisosDelModulo) }})</span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse_{{ $claveModulo }}" class="accordion-collapse collapse" aria-labelledby="heading_{{ $claveModulo }}" data-bs-parent="#accordionModulosPermisos">
                                        <div class="accordion-body p-3 bg-white">
                                            {{-- Grilla limpia sin cajas internas pesadas --}}
                                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2">
                                                @foreach ($permisosDelModulo as $permiso)
                                                    @php
                                                        $esDeRol = in_array($permiso, $permisosPorRol);
                                                        $partes = explode('.', $permiso);
                                                        $accionRaw = end($partes);
                                                        $nombresAcciones = [
                                                            'ver' => 'Ver',
                                                            'crear' => 'Crear',
                                                            'editar' => 'Editar',
                                                            'eliminar' => 'Eliminar',
                                                            'gestionar' => 'Gestionar',
                                                            'gestionar_modulos' => 'Gestionar Módulos',
                                                            'reporte' => 'Reportes',
                                                            'descargar' => 'Descargar',
                                                            'exportar' => 'Exportar',
                                                            'importar' => 'Importar',
                                                            'auditar' => 'Auditar',
                                                            'generar' => 'Generar',
                                                            'asignar' => 'Asignar',
                                                            'inscribir' => 'Inscribir',
                                                            'prosecucion' => 'Prosecución',
                                                            'secciones' => 'Secciones',
                                                            'carnet' => 'Carnets',
                                                        ];
                                                        $verboLegible = $nombresAcciones[strtolower($accionRaw)] ?? ucfirst(str_replace('_', ' ', $accionRaw));
                                                    @endphp
                                                    <div class="col">
                                                        <div class="form-check d-flex align-items-center gap-2 py-1 mb-0">
                                                            @if ($esDeRol)
                                                                <input class="form-check-input mt-0" type="checkbox" checked disabled id="p_rol_{{ md5($permiso) }}">
                                                                <label class="form-check-label text-muted small user-select-none" for="p_rol_{{ md5($permiso) }}" title="{{ $permiso }}">
                                                                    {{ $verboLegible }}
                                                                    <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem; font-weight: normal;">Por Rol</span>
                                                                </label>
                                                            @else
                                                                <input class="form-check-input mt-0" type="checkbox" value="{{ $permiso }}" wire:model="permisosSeleccionados" id="p_chk_{{ md5($permiso) }}">
                                                                <label class="form-check-label small text-dark user-select-none" for="p_chk_{{ md5($permiso) }}" title="{{ $permiso }}">
                                                                    {{ $verboLegible }}
                                                                    @if (in_array($permiso, $permisosSeleccionados))
                                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.65rem;">Extra</span>
                                                                    @endif
                                                                </label>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="modal-footer-view d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-secondary px-4" wire:click="cerrarModalModulos" style="border-radius: var(--radius);">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary px-4 shadow-sm" wire:click="guardarPermisosModulos" wire:loading.attr="disabled" style="border-radius: var(--radius);">
                            <span wire:loading.remove wire:target="guardarPermisosModulos">
                                <i class="fas fa-save me-1"></i> Guardar Permisos
                            </span>
                            <span wire:loading wire:target="guardarPermisosModulos">
                                <i class="fas fa-spinner fa-spin me-1"></i> Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CORREGIR CORREO: Minimalista, sin input deshabilitado innecesario --}}
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

    {{-- Script para inicialización de tooltips de Bootstrap --}}
    <script>
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

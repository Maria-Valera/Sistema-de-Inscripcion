<div>
    <style>
        .form-control-modern {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.88rem;
            padding: 0.45rem 0.75rem;
            transition: all 0.2s ease;
        }
        .form-control-modern:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }
        .table-modern tbody td {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
            vertical-align: middle !important;
        }
        .table-modern thead th {
            padding-top: 0.95rem !important;
            padding-bottom: 0.95rem !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
            vertical-align: middle !important;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .text-xs {
            font-size: 0.75rem;
        }
        details summary::-webkit-details-marker {
            display: none;
        }
    </style>

    {{-- Tarjeta de Filtros Superior --}}
    <div class="card-modern mb-4">
        <div class="card-header-modern d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="header-icon" style="width: 40px; height: 40px; font-size: 1rem;">
                    <i class="fas fa-filter"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.1rem;">Filtros de Auditoría</h3>
                    <p>Filtra los movimientos por usuario, acción o rango de fechas</p>
                </div>
            </div>
            <button type="button" wire:click="limpiarFiltros" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                <i class="fas fa-undo-alt mr-1"></i> Limpiar Filtros
            </button>
        </div>
        <div class="p-4 bg-white">
            <div class="row align-items-end g-3">
                {{-- Filtro Usuario (Email/Nombre) --}}
                <div class="col-md-4 mb-2">
                    <label for="searchUsuario" class="form-label font-weight-bold small text-dark mb-1">Usuario (Email o Nombre)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                                <i class="fas fa-user text-muted small"></i>
                            </span>
                        </div>
                        <input type="text" id="searchUsuario" wire:model.live.debounce.300ms="searchUsuario" class="form-control form-control-modern" placeholder="Buscar por email..." style="border-left: 0; border-radius: 0 8px 8px 0;">
                    </div>
                </div>

                {{-- Filtro Acción --}}
                <div class="col-md-3 mb-2">
                    <label for="filtroAccion" class="form-label font-weight-bold small text-dark mb-1">Acción</label>
                    <select id="filtroAccion" wire:model.live="filtroAccion" class="form-control form-control-modern custom-select">
                        <option value="">Todas las acciones</option>
                        @foreach($accionesDisponibles as $accion)
                            @php
                                $nombreAccionOpcion = match(strtolower($accion)) {
                                    'crear' => 'Creación',
                                    'actualizar' => 'Actualización',
                                    'eliminar' => 'Eliminación',
                                    'acceso_denegado' => 'Acceso denegado',
                                    'login' => 'Inicio de sesión',
                                    'logout' => 'Cierre de sesión',
                                    'modulo_asignado' => 'Módulo asignado',
                                    'modulo_retirado' => 'Módulo retirado',
                                    'correo_corregido' => 'Correo corregido',
                                    default => ucfirst(str_replace('_', ' ', $accion))
                                };
                            @endphp
                            <option value="{{ $accion }}">{{ $nombreAccionOpcion }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro Desde --}}
                <div class="col-md-2 mb-2">
                    <label for="fechaDesde" class="form-label font-weight-bold small text-dark mb-1">Desde</label>
                    <input type="date" id="fechaDesde" wire:model.live="fechaDesde" class="form-control form-control-modern">
                </div>

                {{-- Filtro Hasta --}}
                <div class="col-md-3 mb-2">
                    <label for="fechaHasta" class="form-label font-weight-bold small text-dark mb-1">Hasta</label>
                    <input type="date" id="fechaHasta" wire:model.live="fechaHasta" class="form-control form-control-modern">
                </div>
            </div>

            <div class="d-flex align-items-center mt-3 pt-3 border-top text-muted small" style="font-size: 0.78rem;">
                <i class="fas fa-shield-alt text-secondary mr-2"></i>
                <span>Registros inmutables de solo lectura protegidos por políticas y triggers de base de datos.</span>
            </div>
        </div>
    </div>

    {{-- Tabla de Auditorías: Idéntica a Calendarios Escolares (card-modern, card-header-modern, table-modern) --}}
    <div class="card-modern">
        <div class="card-header-modern">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h3>Historial de Movimientos</h3>
                    <p>{{ $auditorias->total() }} registros encontrados</p>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <select wire:model.live="perPage" class="form-control form-control-sm border" style="border-radius: 8px; width: auto; display: inline-block;">
                    <option value="15">15 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>
        </div>

        <div class="card-body-modern">
            <div class="table-wrapper">
                <table class="table-modern overflow-hidden align-middle">
                    <thead>
                        <tr>
                            <th style="width: 14%;" class="pl-4 text-nowrap">Fecha y Hora</th>
                            <th style="width: 20%;" class="text-nowrap">Usuario</th>
                            <th style="width: 15%; text-align: center;" class="text-center text-nowrap">Acción</th>
                            <th style="width: 12%; text-align: center;" class="text-center text-nowrap">Entidad</th>
                            <th style="width: 39%;">Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditorias as $auditoria)
                            @php
                                // Datos de usuario e IP
                                $emailDisplay = $auditoria->usuario?->email ?? $auditoria->usuario_email ?? 'Sistema / Invitado';
                                $rolDisplay = $auditoria->usuario?->roles?->pluck('name')->implode(', ') ?: ($auditoria->usuario ? 'Usuario' : 'Sistema');
                                $ipDisplay = $auditoria->ip ?: '127.0.0.1';

                                // Configuración de píldoras pastel
                                $accionRaw = strtolower(trim($auditoria->accion ?? ''));
                                [$badgeBg, $badgeColor, $badgeBorder] = match($accionRaw) {
                                    'crear', 'modulo_asignado' => ['#d4edda', '#155724', '#c3e6cb'],
                                    'actualizar', 'login', 'correo_corregido' => ['#cce5ff', '#004085', '#b8daff'],
                                    'eliminar', 'acceso_denegado', 'fallo' => ['#f8d7da', '#721c24', '#f5c6cb'],
                                    'modulo_retirado' => ['#fff3cd', '#856404', '#ffeeba'],
                                    default => ['#e2e3e5', '#383d41', '#d6d8db'],
                                };

                                $textoAccion = match($accionRaw) {
                                    'crear' => 'Creación',
                                    'actualizar' => 'Actualización',
                                    'eliminar' => 'Eliminación',
                                    'acceso_denegado' => 'Acceso denegado',
                                    'login' => 'Inicio de sesión',
                                    'logout' => 'Cierre de sesión',
                                    'modulo_asignado' => 'Módulo asignado',
                                    'modulo_retirado' => 'Módulo retirado',
                                    'correo_corregido' => 'Correo corregido',
                                    default => ucfirst(str_replace('_', ' ', $accionRaw))
                                };

                                // Ocultar ruta técnica de la descripción para tooltip
                                $desc = $auditoria->descripcion ?: 'Sin descripción';
                                $rutaInfo = null;
                                if (preg_match('/\[Ruta:\s*([^\]]+)\]/i', $desc, $matches)) {
                                    $rutaInfo = trim($matches[1]);
                                    $desc = trim(str_replace($matches[0], '', $desc));
                                }
                            @endphp
                            <tr>
                                {{-- 1. Fecha y Hora (14%) --}}
                                <td class="align-middle pl-4" style="width: 14%;">
                                    <span class="d-block font-weight-bold text-dark" style="font-size: 0.9rem;">
                                        <i class="far fa-calendar-alt text-muted mr-1"></i> {{ \Carbon\Carbon::parse($auditoria->created_at)->format('d/m/Y') }}
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($auditoria->created_at)->format('H:i:s') }}
                                    </small>
                                </td>

                                {{-- 2. Usuario (20%) con IP y Rol debajo --}}
                                <td class="align-middle" style="width: 20%;">
                                    <span class="d-block font-weight-bold text-dark text-truncate" style="max-width: 220px; font-size: 0.9rem;" title="{{ $emailDisplay }}">
                                        {{ $emailDisplay }}
                                    </span>
                                    <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;">
                                        {{ $rolDisplay }} • <span class="text-xs">IP: {{ $ipDisplay }}</span>
                                    </small>
                                </td>

                                {{-- 3. Acción (15%): Píldora pastel centrada --}}
                                <td class="align-middle text-center text-nowrap" style="width: 15%;">
                                    <span class="badge px-3 py-2 font-weight-bold" style="background-color: {{ $badgeBg }}; color: {{ $badgeColor }}; border: 1px solid {{ $badgeBorder }}; border-radius: 30px; font-size: 0.78rem;">
                                        {{ $textoAccion }}
                                    </span>
                                </td>

                                {{-- 4. Entidad (12%): Píldora gris sutil centrada --}}
                                <td class="align-middle text-center text-nowrap" style="width: 12%;">
                                    @if($auditoria->auditable_type)
                                        <span class="badge bg-light border text-muted px-2 py-1" style="font-size: 0.78rem; font-weight: 500;">
                                            {{ class_basename($auditoria->auditable_type) }} <span class="text-secondary">#{{ $auditoria->auditable_id ?? 'N/A' }}</span>
                                        </span>
                                    @else
                                        <span class="text-muted font-italic small">-</span>
                                    @endif
                                </td>

                                {{-- 5. Descripción (39%): Frase humana limpia con enlaces de acción --}}
                                <td class="align-middle" style="width: 39%;">
                                    <div class="text-secondary font-weight-normal" style="line-height: 1.45; font-size: 0.88rem;">
                                        {{ $desc }}
                                    </div>
                                    <div class="mt-1 d-flex align-items-center flex-wrap" style="gap: 12px;">
                                        @if($rutaInfo)
                                            <span class="text-xs text-info cursor-pointer" data-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="top" title="Ruta: {{ $rutaInfo }}" style="font-size: 0.75rem;">
                                                <i class="fas fa-code mr-1"></i> Ver detalles técnicos
                                            </span>
                                        @endif

                                        @if($auditoria->valores_nuevos || $auditoria->valores_anteriores)
                                            <details class="d-inline-block">
                                                <summary class="text-xs text-muted cursor-pointer" style="font-size: 0.75rem; list-style: none;">
                                                    <i class="fas fa-history mr-1"></i> Ver cambios de datos
                                                </summary>
                                                <div class="mt-2 p-2 bg-light rounded border small">
                                                    @if($auditoria->valores_anteriores)
                                                        <div class="mb-1">
                                                            <span class="font-weight-bold text-danger">Valores Anteriores:</span>
                                                            <pre class="mb-1 p-1 bg-white border text-monospace text-xs" style="max-height: 120px; overflow-y: auto;">{{ json_encode($auditoria->valores_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    @endif
                                                    @if($auditoria->valores_nuevos)
                                                        <div>
                                                            <span class="font-weight-bold text-success">Valores Nuevos:</span>
                                                            <pre class="mb-0 p-1 bg-white border text-monospace text-xs" style="max-height: 120px; overflow-y: auto;">{{ json_encode($auditoria->valores_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    @endif
                                                </div>
                                            </details>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-search fa-2x mb-2 d-block text-secondary"></i>
                                    No se encontraron registros de auditoría con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper mt-3 px-3 pb-3">
                {{ $auditorias->links() }}
            </div>
        </div>
    </div>

    {{-- Script de inicialización de tooltips --}}
    <script>
        function initAuditoriaTooltips() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                document.querySelectorAll('[data-bs-toggle="tooltip"], [data-toggle="tooltip"]').forEach(function (el) {
                    new bootstrap.Tooltip(el);
                });
            } else if (window.$ && typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip();
            }
        }
        document.addEventListener('DOMContentLoaded', initAuditoriaTooltips);
        document.addEventListener('livewire:initialized', function () {
            if (window.Livewire && Livewire.hook) {
                Livewire.hook('morph.updated', () => {
                    initAuditoriaTooltips();
                });
            }
        });
    </script>
</div>

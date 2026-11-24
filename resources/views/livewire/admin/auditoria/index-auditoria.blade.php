<div>
    <style>
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

    {{-- Tarjeta Principal: Historial de Movimientos --}}
    <div class="card-modern">
        <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon header-icon-pastel-blue">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h3 class="m-0 fw-bold text-dark" style="font-size: 1.15rem;">Historial de Movimientos</h3>
                    <p class="m-0 text-muted small">{{ $auditorias->total() }} registros encontrados</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Botón Global de Filtros (Offcanvas) --}}
                <button class="btn btn-filter-global" type="button" data-bs-toggle="offcanvas" data-bs-target="#auditoriaFiltros" aria-controls="auditoriaFiltros">
                    <i class="fas fa-sliders-h"></i>
                    <span>Filtrar</span>
                    @php
                        $filtrosActivosCount = ($searchUsuario ? 1 : 0) + ($filtroAccion ? 1 : 0) + ($filtroModelo ? 1 : 0) + ($fechaDesde ? 1 : 0) + ($fechaHasta ? 1 : 0);
                    @endphp
                    @if ($filtrosActivosCount > 0)
                        <span class="badge rounded-pill bg-primary ms-1" style="font-size: 0.68rem; padding: 0.2em 0.55em;">
                            {{ $filtrosActivosCount }}
                        </span>
                    @endif
                </button>

                {{-- Selector de Registros por Página alineado a la derecha --}}
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto; border-radius: 8px; font-weight: 500; font-size: 0.85rem; padding-top: 0.5rem; padding-bottom: 0.5rem; border-color: #cbd5e1;">
                    <option value="15">15 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>
        </div>

        <div class="card-body-modern">
            <div class="table-wrapper">
                <table class="table-modern w-100 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 14%;" class="ps-3 text-nowrap">Fecha y Hora</th>
                            <th style="width: 20%;" class="text-nowrap">Usuario</th>
                            <th style="width: 15%;" class="text-center text-nowrap">Acción</th>
                            <th style="width: 12%;" class="text-center text-nowrap">Entidad</th>
                            <th style="width: 39%;">Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditorias as $auditoria)
                            @php
                                // Datos del usuario
                                $emailDisplay = $auditoria->usuario?->email ?? $auditoria->usuario_email ?? 'Sistema / Invitado';
                                $rolDisplay = $auditoria->usuario?->roles?->pluck('name')->implode(', ') ?: ($auditoria->usuario ? 'Usuario' : 'Sistema');
                                $ipDisplay = $auditoria->ip ?: '127.0.0.1';

                                // Configuración de colores pastel para las acciones
                                $accionRaw = strtolower(trim($auditoria->accion ?? ''));
                                [$badgeBg, $badgeColor, $badgeBorder] = match($accionRaw) {
                                    'crear', 'modulo_asignado' => ['var(--pastel-green-bg)', 'var(--pastel-green-text)', 'var(--pastel-green-border)'],
                                    'actualizar', 'correo_corregido' => ['var(--pastel-blue-bg)', 'var(--pastel-blue-text)', 'var(--pastel-blue-border)'],
                                    'login', 'logout' => ['var(--pastel-purple-bg)', 'var(--pastel-purple-text)', 'var(--pastel-purple-border)'],
                                    'eliminar', 'acceso_denegado', 'fallo' => ['var(--pastel-pink-bg)', 'var(--pastel-pink-text)', 'var(--pastel-pink-border)'],
                                    'modulo_retirado' => ['var(--pastel-yellow-bg)', 'var(--pastel-yellow-text)', 'var(--pastel-yellow-border)'],
                                    default => ['#F1F5F9', '#475569', '#CBD5E1'],
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

                                // Procesamiento de descripción y ruta técnica
                                $desc = $auditoria->descripcion ?: 'Sin descripción';
                                $rutaInfo = null;
                                if (preg_match('/\[Ruta:\s*([^\]]+)\]/i', $desc, $matches)) {
                                    $rutaInfo = trim($matches[1]);
                                    $desc = trim(str_replace($matches[0], '', $desc));
                                }
                            @endphp
                            <tr>
                                {{-- 1. Fecha y Hora --}}
                                <td class="align-middle ps-3" style="width: 14%;">
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                                        <i class="far fa-calendar-alt text-muted me-1"></i> {{ \Carbon\Carbon::parse($auditoria->created_at)->format('d/m/Y') }}
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                        <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($auditoria->created_at)->format('H:i:s') }}
                                    </div>
                                </td>

                                {{-- 2. Usuario --}}
                                <td class="align-middle" style="width: 20%;">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 220px; font-size: 0.88rem;" title="{{ $emailDisplay }}">
                                        {{ $emailDisplay }}
                                    </div>
                                    <div class="text-muted small text-truncate" style="font-size: 0.75rem;">
                                        {{ $rolDisplay }} • <span class="font-monospace">IP: {{ $ipDisplay }}</span>
                                    </div>
                                </td>

                                {{-- 3. Acción (Píldora Pastel) --}}
                                <td class="align-middle text-center text-nowrap" style="width: 15%;">
                                    <span class="badge px-3 py-1.5 fw-semibold" style="background-color: {{ $badgeBg }}; color: {{ $badgeColor }}; border: 1px solid {{ $badgeBorder }}; border-radius: 9999px; font-size: 0.76rem;">
                                        {{ $textoAccion }}
                                    </span>
                                </td>

                                {{-- 4. Entidad (Píldora Pastel Púrpura) --}}
                                <td class="align-middle text-center text-nowrap" style="width: 12%;">
                                    @if($auditoria->auditable_type)
                                        <span class="badge px-2.5 py-1 fw-semibold text-nowrap" style="background-color: var(--pastel-purple-bg); color: var(--pastel-purple-text); border: 1px solid var(--pastel-purple-border); border-radius: 6px; font-size: 0.74rem;">
                                            {{ class_basename($auditoria->auditable_type) }} #{{ $auditoria->auditable_id ?? 'N/A' }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- 5. Descripción y detalles --}}
                                <td class="align-middle" style="width: 39%;">
                                    <div class="text-secondary" style="line-height: 1.45; font-size: 0.86rem;">
                                        {{ $desc }}
                                    </div>
                                    <div class="mt-1 d-flex align-items-center flex-wrap gap-2">
                                        @if($rutaInfo)
                                            <span class="text-info cursor-pointer small" data-bs-toggle="tooltip" data-toggle="tooltip" title="Ruta: {{ $rutaInfo }}" style="font-size: 0.75rem;">
                                                <i class="fas fa-code me-1"></i> Detalles técnicos
                                            </span>
                                        @endif

                                        @if($auditoria->valores_nuevos || $auditoria->valores_anteriores)
                                            <details class="d-inline-block">
                                                <summary class="text-muted cursor-pointer small" style="font-size: 0.75rem; list-style: none;">
                                                    <i class="fas fa-history me-1"></i> Ver cambios de datos
                                                </summary>
                                                <div class="mt-2 p-2 bg-light rounded border small">
                                                    @if($auditoria->valores_anteriores)
                                                        <div class="mb-1">
                                                            <span class="fw-bold text-danger">Valores Anteriores:</span>
                                                            <pre class="mb-1 p-1 bg-white border text-monospace" style="max-height: 120px; overflow-y: auto; font-size: 0.72rem;">{{ json_encode($auditoria->valores_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    @endif
                                                    @if($auditoria->valores_nuevos)
                                                        <div>
                                                            <span class="fw-bold text-success">Valores Nuevos:</span>
                                                            <pre class="mb-0 p-1 bg-white border text-monospace" style="max-height: 120px; overflow-y: auto; font-size: 0.72rem;">{{ json_encode($auditoria->valores_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
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

    {{-- Panel Lateral Offcanvas de Filtros --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="auditoriaFiltros" aria-labelledby="auditoriaFiltrosLabel" wire:ignore.self style="width: 380px; max-width: 90vw;">
        <div class="offcanvas-header border-bottom bg-white p-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="offcanvas-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="auditoriaFiltrosLabel" style="font-size: 1.1rem;">
                <i class="fas fa-sliders-h" style="color: #4A6FC1;"></i> Filtros de Auditoría
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-4 bg-light">
            {{-- Filtro Usuario (Input) --}}
            <div class="mb-3">
                <label for="searchUsuario" class="form-label fw-semibold text-dark small mb-1">
                    <i class="fas fa-user me-1" style="color: #4A6FC1;"></i> Usuario
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                        <i class="fas fa-search text-muted small"></i>
                    </span>
                    <input type="text" id="searchUsuario" wire:model.live.debounce.300ms="searchUsuario" class="form-control border-start-0" placeholder="Buscar por email o nombre..." style="border-radius: 0 8px 8px 0; border-color: #cbd5e1;">
                </div>
            </div>

            {{-- Filtro Acción (Select) --}}
            <div class="mb-3">
                <label for="filtroAccion" class="form-label fw-semibold text-dark small mb-1">
                    <i class="fas fa-bolt me-1" style="color: #4A6FC1;"></i> Acción
                </label>
                <select id="filtroAccion" wire:model.live="filtroAccion" class="form-select select-modern" style="border-radius: 8px; border-color: #cbd5e1;">
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

            {{-- Filtro Fecha Desde (Input Date) --}}
            <div class="mb-3">
                <label for="fechaDesde" class="form-label fw-semibold text-dark small mb-1">
                    <i class="far fa-calendar-alt me-1" style="color: #4A6FC1;"></i> Fecha Desde
                </label>
                <input type="date" id="fechaDesde" wire:model.live="fechaDesde" class="form-control" style="border-radius: 8px; border-color: #cbd5e1;">
            </div>

            {{-- Filtro Fecha Hasta (Input Date) --}}
            <div class="mb-3">
                <label for="fechaHasta" class="form-label fw-semibold text-dark small mb-1">
                    <i class="far fa-calendar-alt me-1" style="color: #4A6FC1;"></i> Fecha Hasta
                </label>
                <input type="date" id="fechaHasta" wire:model.live="fechaHasta" class="form-control" style="border-radius: 8px; border-color: #cbd5e1;">
            </div>

            {{-- Filtro Entidad / Modelo (Select) --}}
            <div class="mb-3">
                <label for="filtroModelo" class="form-label fw-semibold text-dark small mb-1">
                    <i class="fas fa-cube me-1" style="color: #4A6FC1;"></i> Entidad
                </label>
                <select id="filtroModelo" wire:model.live="filtroModelo" class="form-select select-modern" style="border-radius: 8px; border-color: #cbd5e1;">
                    <option value="">Todas las entidades</option>
                    @foreach($modelosDisponibles as $modelo)
                        <option value="{{ $modelo }}">{{ class_basename($modelo) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex align-items-center mt-4 pt-3 border-top text-muted small" style="font-size: 0.78rem;">
                <i class="fas fa-shield-alt text-secondary me-2"></i>
                <span>Registros inmutables de solo lectura protegidos por el sistema.</span>
            </div>
        </div>

        {{-- Footer con Botones Limpiar y Aplicar --}}
        <div class="offcanvas-footer p-3 px-4 border-top bg-white d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-secondary-modern" wire:click="limpiarFiltros">
                <i class="fas fa-undo-alt me-1"></i> Limpiar
            </button>
            <button type="button" class="btn btn-primary-modern" data-bs-dismiss="offcanvas">
                <i class="fas fa-check me-1"></i> Aplicar
            </button>
        </div>
    </div>

    {{-- Script de inicialización de tooltips y control de Offcanvas --}}
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

        // Manejador reactivo para Offcanvas (compatible con Bootstrap 5 y fallback vanilla)
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-bs-toggle="offcanvas"], [data-toggle="offcanvas"]');
            if (trigger) {
                e.preventDefault();
                const selector = trigger.getAttribute('data-bs-target') || trigger.getAttribute('data-target') || trigger.getAttribute('href');
                if (selector) {
                    const panel = document.querySelector(selector);
                    if (panel) {
                        if (window.bootstrap && window.bootstrap.Offcanvas) {
                            window.bootstrap.Offcanvas.getOrCreateInstance(panel).show();
                        } else {
                            panel.style.visibility = 'visible';
                            panel.classList.add('show');
                            let backdrop = document.querySelector('.offcanvas-backdrop');
                            if (!backdrop) {
                                backdrop = document.createElement('div');
                                backdrop.className = 'offcanvas-backdrop fade show';
                                document.body.appendChild(backdrop);
                                backdrop.addEventListener('click', function () {
                                    panel.classList.remove('show');
                                    setTimeout(() => { if (!panel.classList.contains('show')) panel.style.visibility = 'hidden'; }, 300);
                                    backdrop.remove();
                                    document.body.style.overflow = '';
                                });
                            }
                            document.body.style.overflow = 'hidden';
                        }
                    }
                }
                return;
            }

            const dismiss = e.target.closest('[data-bs-dismiss="offcanvas"], [data-dismiss="offcanvas"]');
            if (dismiss) {
                const panel = dismiss.closest('.offcanvas');
                if (panel) {
                    if (window.bootstrap && window.bootstrap.Offcanvas) {
                        window.bootstrap.Offcanvas.getOrCreateInstance(panel).hide();
                    } else {
                        panel.classList.remove('show');
                        setTimeout(() => { if (!panel.classList.contains('show')) panel.style.visibility = 'hidden'; }, 300);
                        document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove());
                        document.body.style.overflow = '';
                    }
                }
            }
        });

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

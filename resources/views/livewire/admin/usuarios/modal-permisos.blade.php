<div>
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 950px; width: 100%;">
            <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0 !important; border-radius: 14px !important; overflow: hidden !important;">
                
                {{-- Header limpio blanco con icono azul pastel a la izquierda --}}
                <div class="modal-header d-flex align-items-center justify-content-between p-3 px-4 border-bottom" style="background-color: #FFFFFF !important; border-bottom: 1px solid #E2E8F0 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background-color: #EFF6FF !important; color: #4A6FC1 !important; font-size: 1.15rem; flex-shrink: 0;">
                            <i class="fas fa-shield-alt" style="color: #4A6FC1 !important;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title m-0 fw-bold" style="color: #1E293B !important; font-size: 1.15rem;">Gestión Granular de Permisos</h5>
                            <p class="m-0 text-muted" style="font-size: 0.82rem;">Usuario: <strong class="text-dark">{{ $usuarioNombre }}</strong></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" wire:click="cerrarModal" aria-label="Close" style="font-size: 0.85rem; box-shadow: none;"></button>
                </div>

                {{-- Cuerpo del modal: 100% ancho, columna vertical limpia --}}
                <div class="modal-body p-4 w-100" style="background: #ffffff; max-height: 70vh; overflow-y: auto;">
                    <div class="alert p-3 mb-3 d-flex align-items-start gap-2 border-0 w-100" style="background-color: #EFF6FF; color: #1E3A8A; border-radius: 8px; font-size: 13px; line-height: 1.45;">
                        <i class="fas fa-info-circle fs-6 mt-1 flex-shrink-0" style="color: #4A6FC1;"></i>
                        <div>
                            Los permisos <strong>Por Rol</strong> aparecen deshabilitados por herencia de perfil. Marca o desmarca los permisos disponibles para asignarlos como <strong>permisos extra</strong> directos.
                        </div>
                    </div>

                    <div class="accordion w-100" id="accordionModulosPermisos" wire:ignore.self>
                        @foreach ($modulosConfig as $claveModulo => $moduloData)
                            @php
                                $permisosDelModulo = $moduloData['permisos'] ?? [];
                                $cantPorRol = $conteoPorRolPorModulo[$claveModulo] ?? 0;
                                $cantDirectos = $conteoDirectosPorModulo[$claveModulo] ?? 0;
                                $tienePermisosInicial = $modulosConPermisos[$claveModulo] ?? false;
                                $grupoRaw = strtolower($moduloData['grupo'] ?? 'general');
                                $badgeGrupoStyle = match($grupoRaw) {
                                    'academico' => 'background-color: var(--pastel-blue-bg); color: var(--pastel-blue-text); border: 1px solid var(--pastel-blue-border);',
                                    'portal' => 'background-color: var(--pastel-purple-bg); color: var(--pastel-purple-text); border: 1px solid var(--pastel-purple-border);',
                                    'seguridad' => 'background-color: var(--pastel-green-bg); color: var(--pastel-green-text); border: 1px solid var(--pastel-green-border);',
                                    'asistencia' => 'background-color: var(--pastel-yellow-bg); color: var(--pastel-yellow-text); border: 1px solid var(--pastel-yellow-border);',
                                    default => 'background-color: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;'
                                };
                            @endphp
                            <div class="accordion-item mb-2 border rounded overflow-hidden {{ $tienePermisosInicial ? 'has-permissions' : '' }}" 
                                 x-data="{ 
                                     hasPermissions: {{ $tienePermisosInicial ? 'true' : 'false' }},
                                     checkPermissions() {
                                         this.hasPermissions = Array.from($el.querySelectorAll('input[type=checkbox]')).some(cb => cb.checked);
                                     }
                                 }"
                                 :class="{ 'has-permissions': hasPermissions }"
                                 style="border: 1px solid #E2E8F0 !important; border-radius: 10px !important; transition: all 0.2s ease;" 
                                 wire:key="modulo-{{ $claveModulo }}">
                                
                                {{-- Cabecera del acordeón de ancho completo --}}
                                <h2 class="accordion-header" id="heading_{{ $claveModulo }}">
                                    <button class="accordion-button collapsed py-2 px-3 border-0 d-flex justify-content-between align-items-center w-100" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse_{{ $claveModulo }}" 
                                            aria-expanded="false" 
                                            aria-controls="collapse_{{ $claveModulo }}" 
                                            :style="hasPermissions ? 'background-color: #E2E8F0 !important; color: #1E293B !important; box-shadow: none;' : 'background-color: #FFFFFF !important; color: #1E293B !important; box-shadow: none;'"
                                            style="color: #1E293B !important; box-shadow: none;">
                                        {{-- Título y grupo a la izquierda --}}
                                        <div class="d-flex align-items-center gap-2 flex-grow-1 me-3" style="min-width: 0;">
                                            <i class="{{ $moduloData['icono'] ?? 'fas fa-cube' }}" style="color: #4A6FC1; width: 18px; flex-shrink: 0;"></i>
                                            <span class="fw-semibold text-dark text-nowrap" style="white-space: nowrap;">{{ $moduloData['nombre'] ?? ucfirst($claveModulo) }}</span>
                                            <span class="badge fw-medium px-2 py-1 ms-1 text-nowrap flex-shrink-0" style="{{ $badgeGrupoStyle }} font-size: 0.70rem; border-radius: 6px; white-space: nowrap;">
                                                {{ ucfirst($grupoRaw) }}
                                            </span>
                                        </div>
                                        {{-- Badges de conteo a la derecha --}}
                                        <div class="d-flex align-items-center gap-2 flex-shrink-0 me-2">
                                            @if ($cantPorRol > 0)
                                                <span class="badge border text-nowrap" style="background-color: #F8FAFC; color: #64748B; border-color: #CBD5E1 !important; font-size: 0.68rem; font-weight: 500; border-radius: 6px; white-space: nowrap;"><i class="fas fa-lock me-1"></i>{{ $cantPorRol }} por rol</span>
                                            @endif
                                            @if ($cantDirectos > 0)
                                                <span class="badge border text-nowrap" style="background-color: #EFF6FF; color: #2563EB; border-color: #BFDBFE !important; font-size: 0.68rem; font-weight: 500; border-radius: 6px; white-space: nowrap;"><i class="fas fa-plus-circle me-1"></i>{{ $cantDirectos }} extra</span>
                                            @endif
                                            <span class="text-muted small text-nowrap" style="font-size: 0.72rem; white-space: nowrap;">({{ count($permisosDelModulo) }})</span>
                                        </div>
                                    </button>
                                </h2>

                                {{-- Contenido desplegable: Grid de 3 columnas para los checkboxes --}}
                                <div id="collapse_{{ $claveModulo }}" class="accordion-collapse collapse" aria-labelledby="heading_{{ $claveModulo }}" data-bs-parent="#accordionModulosPermisos" wire:ignore.self>
                                    <div class="p-3 bg-white" style="border-top: 1px solid #E2E8F0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
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
                                            <div class="permission-card p-2 border rounded" 
                                                 style="background-color: {{ $esDeRol ? '#F8FAFC' : '#FFFFFF' }}; border-color: #E2E8F0 !important; border-radius: 8px; display: flex; align-items: center; gap: 8px;"
                                                 wire:key="permiso-{{ md5($permiso) }}">
                                                @if ($esDeRol)
                                                    <input class="form-check-input mt-0" type="checkbox" checked disabled id="p_rol_{{ md5($permiso) }}" style="cursor: not-allowed; flex-shrink: 0;">
                                                    <label class="form-check-label text-muted small user-select-none mb-0 d-flex align-items-center justify-content-between w-100" for="p_rol_{{ md5($permiso) }}" title="{{ $permiso }}" style="cursor: not-allowed;">
                                                        <span class="text-truncate">{{ $verboLegible }}</span>
                                                        <span class="badge border ms-1 flex-shrink-0" style="background-color: #FFFFFF; color: #64748B; border-color: #CBD5E1 !important; font-size: 0.65rem; font-weight: normal; border-radius: 4px;">Por Rol</span>
                                                    </label>
                                                @else
                                                    <input class="form-check-input mt-0" 
                                                           type="checkbox" 
                                                           value="{{ $permiso }}" 
                                                           wire:model="permisosSeleccionados" 
                                                           id="p_chk_{{ md5($permiso) }}" 
                                                           style="cursor: pointer; flex-shrink: 0;"
                                                           x-on:change="checkPermissions()">
                                                    <label class="form-check-label small text-dark user-select-none mb-0 w-100 text-truncate" for="p_chk_{{ md5($permiso) }}" title="{{ $permiso }}" style="cursor: pointer;">
                                                        {{ $verboLegible }}
                                                    </label>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end gap-2 p-3 px-4 border-top" style="background-color: #FFFFFF !important; border-top: 1px solid #E2E8F0 !important;">
                    <button type="button" class="btn-secondary-modern" wire:click="cerrarModal">
                        Cancelar
                    </button>
                    <button type="button" class="btn-primary-modern" wire:click="guardarPermisos" wire:loading.attr="disabled" wire:target="guardarPermisos">
                        <span wire:loading.remove wire:target="guardarPermisos">
                            <i class="fas fa-save me-2"></i> Guardar Permisos
                        </span>
                        <span wire:loading wire:target="guardarPermisos" style="display: none;">
                            <i class="fas fa-spinner fa-spin me-2"></i> Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Rol con Permisos Granulares -->
<div class="modal fade" id="viewModalEditar{{ $datos->id }}" tabindex="-1" aria-labelledby="viewModalEditarLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0 !important; border-radius: 14px !important; overflow: hidden !important;">

            {{-- Cabecera del modal moderna --}}
            <div class="modal-header d-flex align-items-center justify-content-between p-3 px-4" style="background-color: #FFFFFF !important; border-bottom: 1px solid #E2E8F0 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background-color: #EFF6FF !important; color: #4A6FC1 !important; font-size: 1.15rem; flex-shrink: 0;">
                        <i class="fas fa-user-edit" style="color: #4A6FC1 !important;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="viewModalEditarLabel{{ $datos->id }}" style="color: #1E293B !important; font-size: 1.15rem;">
                            Editar Rol: {{ $datos->name }}
                        </h5>
                        <p class="m-0 text-muted" style="font-size: 0.82rem;">Modifica el nombre del rol y actualiza los permisos asociados</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="font-size: 0.85rem; box-shadow: none;"></button>
            </div>

            {{-- Cuerpo del modal con formulario y scroll controlado --}}
            <div class="modal-body p-4" style="max-height: 65vh !important; overflow-y: auto !important; background-color: #FFFFFF !important;">
                <form action="{{ route('admin.roles.modales.update', $datos->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $datos->id }}">

                    {{-- Nombre del Rol (Floating Group SaaS) --}}
                    <div class="floating-group mb-4">
                        <input type="text" 
                               class="form-control" 
                               id="name_{{ $datos->id }}" 
                               name="name" 
                               maxlength="100"
                               placeholder=" "
                               value="{{ $datos->name }}" 
                               {{ $datos->name === 'Admin' ? 'readonly' : '' }}
                               required
                               autocomplete="off"
                               style="background-color: #FFFFFF !important; border: 1px solid #E2E8F0 !important; border-radius: 8px !important;">
                        <label for="name_{{ $datos->id }}">
                            <i class="fas fa-tag me-1" style="color: #4A6FC1;"></i> Nombre del Rol *
                        </label>
                        @error('name')
                            <div class="invalid-feedback d-block text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Helper para títulos y descripciones amigables de permisos --}}
                    @php
                        $obtenerInfoPermiso = function ($permiso) {
                            $partes = explode('.', $permiso);
                            $accionKey = count($partes) > 1 ? end($partes) : $permiso;

                            $mapa = [
                                'ver' => ['titulo' => 'Ver', 'desc' => 'Acceso de solo lectura'],
                                'crear' => ['titulo' => 'Crear', 'desc' => 'Registrar nuevos datos'],
                                'editar' => ['titulo' => 'Editar', 'desc' => 'Modificar existentes'],
                                'eliminar' => ['titulo' => 'Eliminar', 'desc' => 'Dar de baja o suprimir'],
                                'gestionar' => ['titulo' => 'Gestionar', 'desc' => 'Control administrativo total'],
                                'cambiar' => ['titulo' => 'Cambiar', 'desc' => 'Alternar estado o selección'],
                                'descargar' => ['titulo' => 'Descargar', 'desc' => 'Exportar o bajar archivos'],
                                'imprimir' => ['titulo' => 'Imprimir', 'desc' => 'Generar reporte impreso'],
                                'aprobar' => ['titulo' => 'Aprobar', 'desc' => 'Validar y autorizar'],
                                'rechazar' => ['titulo' => 'Rechazar', 'desc' => 'Denegar solicitudes'],
                                'reporte' => ['titulo' => 'Reporte', 'desc' => 'Consultar estadísticas'],
                            ];

                            return $mapa[$accionKey] ?? [
                                'titulo' => ucfirst($accionKey),
                                'desc' => 'Acción de ' . $accionKey,
                            ];
                        };
                    @endphp

                    {{-- Panel de Permisos Granulares --}}
                    <div class="permisos-panel mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-semibold mb-0" style="color: #1E293B; font-size: 0.95rem;">
                                <i class="fas fa-key me-1" style="color: #4A6FC1;"></i> Permisos Asignados al Rol
                            </label>
                            <span class="text-muted small">Marca o desmarca los permisos para este rol</span>
                        </div>

                        <div class="accordion" id="accordionEditarRolModulos{{ $datos->id }}">
                            @php 
                                $modulosConfig = $modulos ?? config('modulos', []);
                                $permisosDelRol = $datos->permissions->pluck('name')->flip();
                            @endphp
                            @foreach ($modulosConfig as $claveMod => $datosMod)
                                @php 
                                    $permisosMod = $datosMod['permisos'] ?? [];
                                    $activosEnRol = count(array_filter($permisosMod, fn($p) => isset($permisosDelRol[$p])));
                                    $grupoKey = strtolower($datosMod['grupo'] ?? 'general');
                                    $badgeStyle = match($grupoKey) {
                                        'academico' => 'background-color: var(--pastel-blue-bg) !important; color: var(--pastel-blue-text) !important;',
                                        'portal' => 'background-color: var(--pastel-purple-bg) !important; color: var(--pastel-purple-text) !important;',
                                        'configuracion' => 'background-color: #FEF3C7 !important; color: #92400E !important;',
                                        'seguridad' => 'background-color: #FEE2E2 !important; color: #991B1B !important;',
                                        'asistencia' => 'background-color: var(--pastel-green-bg) !important; color: var(--pastel-green-text) !important;',
                                        default => 'background-color: #F1F5F9 !important; color: #475569 !important;'
                                    };

                                    $accionesBasicasClaves = ['ver', 'crear', 'editar', 'eliminar'];
                                    $permisosBasicos = [];
                                    $permisosAvanzados = [];

                                    foreach ($permisosMod as $p) {
                                        $partes = explode('.', $p);
                                        $accionKey = count($partes) > 1 ? end($partes) : $p;
                                        if (in_array($accionKey, $accionesBasicasClaves)) {
                                            $permisosBasicos[] = $p;
                                        } else {
                                            $permisosAvanzados[] = $p;
                                        }
                                    }
                                    $tieneAmbos = count($permisosBasicos) > 0 && count($permisosAvanzados) > 0;
                                @endphp
                                <div class="accordion-item mb-2 shadow-none overflow-hidden" style="background-color: #FFFFFF !important; border: 1px solid #E2E8F0 !important; border-radius: 8px !important; margin-bottom: 0.5rem !important;">
                                    <h2 class="accordion-header" id="headingEdit_{{ $datos->id }}_{{ $claveMod }}">
                                        <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEdit_{{ $datos->id }}_{{ $claveMod }}" aria-expanded="false" aria-controls="collapseEdit_{{ $datos->id }}_{{ $claveMod }}" style="background-color: #FFFFFF !important; box-shadow: none !important; border: none !important;">
                                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px; border-radius: 6px; background-color: #EFF6FF !important; color: #4A6FC1 !important; font-size: 0.95rem; flex-shrink: 0;">
                                                        <i class="{{ $datosMod['icono'] ?? 'fas fa-cube' }}" style="color: #4A6FC1 !important;"></i>
                                                    </div>
                                                    <span class="fw-bold" style="color: #1E293B !important; font-size: 0.92rem;">{{ $datosMod['nombre'] ?? ucfirst($claveMod) }}</span>
                                                    <span class="badge rounded-pill fw-semibold" style="{{ $badgeStyle }} font-size: 0.72rem; padding: 0.35em 0.75em;">
                                                        {{ ucfirst($datosMod['grupo'] ?? 'general') }}
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($activosEnRol > 0)
                                                        <span class="badge rounded-pill fw-semibold" style="background-color: #DCFCE7 !important; color: #166534 !important; font-size: 0.72rem; padding: 0.35em 0.75em;">
                                                            {{ $activosEnRol }} asignados
                                                        </span>
                                                    @endif
                                                    <span class="text-muted" style="font-size: 0.78rem; color: #94A3B8 !important;">({{ count($permisosMod) }} acciones)</span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseEdit_{{ $datos->id }}_{{ $claveMod }}" class="accordion-collapse collapse" aria-labelledby="headingEdit_{{ $datos->id }}_{{ $claveMod }}">
                                        <div class="accordion-body p-3 pt-2" style="background-color: #FFFFFF !important; border-top: 1px solid #F1F5F9 !important;">
                                            {{-- Barra superior con botón estilizado de Marcar / Desmarcar --}}
                                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-bottom: 1px dashed #E2E8F0 !important;">
                                                <span class="small text-muted fw-semibold" style="font-size: 0.8rem;">
                                                    <i class="fas fa-shield-alt me-1 text-primary"></i> Acciones permitidas
                                                </span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" style="border-radius: 6px; font-size: 12px; padding: 4px 10px; font-weight: 500;" onclick="marcarGrupo('grupo_edit_{{ $datos->id }}_{{ $claveMod }}', true)">
                                                        <i class="fas fa-check-square"></i> Seleccionar todos
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="border-radius: 6px; font-size: 12px; padding: 4px 10px; font-weight: 500;" onclick="marcarGrupo('grupo_edit_{{ $datos->id }}_{{ $claveMod }}', false)">
                                                        <i class="fas fa-square"></i> Desmarcar
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Grid de Tarjetas de Permisos --}}
                                            <div class="grupo_edit_{{ $datos->id }}_{{ $claveMod }}">
                                                @if ($tieneAmbos)
                                                    <div class="mb-3">
                                                        <h6 class="text-muted fw-bold mb-2" style="font-size: 11px; letter-spacing: 0.5px;">ACCIONES BÁSICAS</h6>
                                                        <div class="permisos-grid">
                                                            @foreach ($permisosBasicos as $permiso)
                                                                @php 
                                                                    $info = $obtenerInfoPermiso($permiso); 
                                                                    $tienePermiso = isset($permisosDelRol[$permiso]);
                                                                @endphp
                                                                <label class="permiso-card cursor-pointer" for="chk_edit_{{ $datos->id }}_{{ md5($permiso) }}">
                                                                    <input class="form-check-input mt-0 me-3" 
                                                                           type="checkbox" 
                                                                           name="permisos_seleccionados[]" 
                                                                           value="{{ $permiso }}" 
                                                                           id="chk_edit_{{ $datos->id }}_{{ md5($permiso) }}"
                                                                           {{ $tienePermiso ? 'checked' : '' }}>
                                                                    <div>
                                                                        <span class="d-block fw-bold text-dark" style="font-size: 13.5px; line-height: 1.2;">{{ $info['titulo'] }}</span>
                                                                        <span class="d-block text-muted" style="font-size: 11.5px; margin-top: 2px;">{{ $info['desc'] }}</span>
                                                                    </div>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <h6 class="text-muted fw-bold mb-2 mt-3" style="font-size: 11px; letter-spacing: 0.5px;">ACCIONES AVANZADAS</h6>
                                                        <div class="permisos-grid">
                                                            @foreach ($permisosAvanzados as $permiso)
                                                                @php 
                                                                    $info = $obtenerInfoPermiso($permiso); 
                                                                    $tienePermiso = isset($permisosDelRol[$permiso]);
                                                                @endphp
                                                                <label class="permiso-card cursor-pointer" for="chk_edit_{{ $datos->id }}_{{ md5($permiso) }}">
                                                                    <input class="form-check-input mt-0 me-3" 
                                                                           type="checkbox" 
                                                                           name="permisos_seleccionados[]" 
                                                                           value="{{ $permiso }}" 
                                                                           id="chk_edit_{{ $datos->id }}_{{ md5($permiso) }}"
                                                                           {{ $tienePermiso ? 'checked' : '' }}>
                                                                    <div>
                                                                        <span class="d-block fw-bold text-dark" style="font-size: 13.5px; line-height: 1.2;">{{ $info['titulo'] }}</span>
                                                                        <span class="d-block text-muted" style="font-size: 11.5px; margin-top: 2px;">{{ $info['desc'] }}</span>
                                                                    </div>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="permisos-grid">
                                                        @foreach ($permisosMod as $permiso)
                                                            @php 
                                                                $info = $obtenerInfoPermiso($permiso); 
                                                                $tienePermiso = isset($permisosDelRol[$permiso]);
                                                            @endphp
                                                            <label class="permiso-card cursor-pointer" for="chk_edit_{{ $datos->id }}_{{ md5($permiso) }}">
                                                                <input class="form-check-input mt-0 me-3" 
                                                                       type="checkbox" 
                                                                       name="permisos_seleccionados[]" 
                                                                       value="{{ $permiso }}" 
                                                                       id="chk_edit_{{ $datos->id }}_{{ md5($permiso) }}"
                                                                       {{ $tienePermiso ? 'checked' : '' }}>
                                                                <div>
                                                                    <span class="d-block fw-bold text-dark" style="font-size: 13.5px; line-height: 1.2;">{{ $info['titulo'] }}</span>
                                                                    <span class="d-block text-muted" style="font-size: 11.5px; margin-top: 2px;">{{ $info['desc'] }}</span>
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Botones del Footer con clases modernas --}}
                    <div class="modal-footer d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="background-color: #FFFFFF !important; border-top: 1px solid #E2E8F0 !important; padding-right: 0 !important; padding-left: 0 !important;">
                        <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary-modern">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Modal Editar Rol con Permisos Granulares -->
<div class="modal fade" id="viewModalEditar{{ $datos->id }}" tabindex="-1" aria-labelledby="viewModalEditarLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow-lg">

            {{-- Cabecera del modal --}}
            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h5 class="modal-title-edit" id="viewModalEditarLabel{{ $datos->id }}">
                    Editar Rol: {{ $datos->name }}
                </h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Cuerpo del modal con formulario --}}
            <div class="modal-body-edit p-4" style="max-height: 75vh; overflow-y: auto;">
                <form action="{{ route('admin.roles.modales.update', $datos->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $datos->id }}">

                    {{-- Nombre del rol --}}
                    <div class="form-group-modern mb-4">
                        <label for="name_{{ $datos->id }}" class="form-label-modern fw-semibold">
                            <i class="fas fa-tag me-1 text-primary"></i> Nombre del Rol
                        </label>
                        <input type="text" 
                               class="form-control-modern" 
                               id="name_{{ $datos->id }}" 
                               name="name" 
                               maxlength="100"
                               placeholder="Edite el nombre del rol"
                               value="{{ $datos->name }}" 
                               {{ $datos->name === 'Admin' ? 'readonly' : '' }}
                               required>
                        @error('name')
                            <div class="error-message text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Panel de Permisos Granulares --}}
                    <div class="permisos-panel mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label-modern fw-semibold mb-0">
                                <i class="fas fa-key me-1 text-primary"></i> Permisos Asignados al Rol
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
                                @endphp
                                <div class="accordion-item mb-2 border rounded shadow-sm overflow-hidden">
                                    <h2 class="accordion-header" id="headingEdit_{{ $datos->id }}_{{ $claveMod }}">
                                        <button class="accordion-button collapsed py-2 px-3 bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEdit_{{ $datos->id }}_{{ $claveMod }}" aria-expanded="false" aria-controls="collapseEdit_{{ $datos->id }}_{{ $claveMod }}">
                                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="{{ $datosMod['icono'] ?? 'fas fa-cube' }} text-primary" style="width: 18px;"></i>
                                                    <span class="fw-semibold text-dark">{{ $datosMod['nombre'] ?? ucfirst($claveMod) }}</span>
                                                    <span class="badge bg-secondary text-white small" style="font-size: 0.68rem;">{{ ucfirst($datosMod['grupo'] ?? 'general') }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($activosEnRol > 0)
                                                        <span class="badge bg-success text-white small" style="font-size: 0.7rem;">{{ $activosEnRol }} asignados</span>
                                                    @endif
                                                    <span class="text-muted small">({{ count($permisosMod) }} acciones)</span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseEdit_{{ $datos->id }}_{{ $claveMod }}" class="accordion-collapse collapse" aria-labelledby="headingEdit_{{ $datos->id }}_{{ $claveMod }}">
                                        <div class="accordion-body p-3 bg-white">
                                            <div class="d-flex justify-content-end mb-2">
                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none me-3 small" onclick="marcarGrupo('grupo_edit_{{ $datos->id }}_{{ $claveMod }}', true)">
                                                    Marcar todos
                                                </button>
                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted small" onclick="marcarGrupo('grupo_edit_{{ $datos->id }}_{{ $claveMod }}', false)">
                                                    Desmarcar
                                                </button>
                                            </div>
                                            <div class="row g-2 grupo_edit_{{ $datos->id }}_{{ $claveMod }}">
                                                @foreach ($permisosMod as $permiso)
                                                    @php $tienePermiso = isset($permisosDelRol[$permiso]); @endphp
                                                    <div class="col-md-6">
                                                        <div class="p-2 border rounded d-flex align-items-center bg-white h-100">
                                                            <div class="form-check mb-0">
                                                                <input class="form-check-input" 
                                                                       type="checkbox" 
                                                                       name="permisos_seleccionados[]" 
                                                                       value="{{ $permiso }}" 
                                                                       id="chk_edit_{{ $datos->id }}_{{ md5($permiso) }}"
                                                                       {{ $tienePermiso ? 'checked' : '' }}>
                                                                <label class="form-check-label small ms-1" for="chk_edit_{{ $datos->id }}_{{ md5($permiso) }}">
                                                                    <code>{{ $permiso }}</code>
                                                                </label>
                                                            </div>
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

                    {{-- Botones --}}
                    <div class="modal-footer-edit mt-4 pt-3 border-top">
                        <div class="footer-buttons d-flex justify-content-end gap-2 w-100">
                            <button type="button" class="btn-modal-cancel btn btn-secondary px-4" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-modal-edit btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

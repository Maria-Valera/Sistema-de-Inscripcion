<!-- Modal Crear Rol con Permisos Granulares -->
<div class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow-lg">

            {{-- Cabecera del modal --}}
            <div class="modal-header-create">
                <div class="modal-icon-create">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h5 class="modal-title-create" id="modalCrearLabel">Nuevo Rol y Permisos</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Cuerpo del modal con formulario --}}
            <div class="modal-body-create p-4" style="max-height: 75vh; overflow-y: auto;">
                <form action="{{ route('admin.roles.modales.store') }}" method="POST" id="formCrearRol">
                    @csrf

                    {{-- Contenedor para alertas de validación --}}
                    <div id="contenedorAlertaCrear"></div>

                    {{-- Nombre del Rol --}}
                    <div class="form-group-modern mb-4">
                        <label for="name" class="form-label-modern fw-semibold">
                            <i class="fas fa-tag me-1 text-primary"></i> Nombre del Rol
                        </label>
                        <input type="text" 
                               class="form-control-modern" 
                               id="name" 
                               name="name"
                               maxlength="100"
                               placeholder="Ej: Coordinador, Asistente, Secretaria..."
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
                                <i class="fas fa-key me-1 text-primary"></i> Permisos Granulares por Módulo
                            </label>
                            <span class="text-muted small">Selecciona las acciones permitidas para este rol</span>
                        </div>

                        <div class="accordion" id="accordionCrearRolModulos">
                            @php $modulosConfig = $modulos ?? config('modulos', []); @endphp
                            @foreach ($modulosConfig as $claveMod => $datosMod)
                                @php $permisosMod = $datosMod['permisos'] ?? []; @endphp
                                <div class="accordion-item mb-2 border rounded shadow-sm overflow-hidden">
                                    <h2 class="accordion-header" id="headingCrear_{{ $claveMod }}">
                                        <button class="accordion-button collapsed py-2 px-3 bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCrear_{{ $claveMod }}" aria-expanded="false" aria-controls="collapseCrear_{{ $claveMod }}">
                                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="{{ $datosMod['icono'] ?? 'fas fa-cube' }} text-primary" style="width: 18px;"></i>
                                                    <span class="fw-semibold text-dark">{{ $datosMod['nombre'] ?? ucfirst($claveMod) }}</span>
                                                    <span class="badge bg-secondary text-white small" style="font-size: 0.68rem;">{{ ucfirst($datosMod['grupo'] ?? 'general') }}</span>
                                                </div>
                                                <span class="text-muted small">({{ count($permisosMod) }} acciones)</span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseCrear_{{ $claveMod }}" class="accordion-collapse collapse" aria-labelledby="headingCrear_{{ $claveMod }}">
                                        <div class="accordion-body p-3 bg-white">
                                            <div class="d-flex justify-content-end mb-2">
                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none me-3 small" onclick="marcarGrupo('grupo_crear_{{ $claveMod }}', true)">
                                                    Marcar todos
                                                </button>
                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted small" onclick="marcarGrupo('grupo_crear_{{ $claveMod }}', false)">
                                                    Desmarcar
                                                </button>
                                            </div>
                                            <div class="row g-2 grupo_crear_{{ $claveMod }}">
                                                @foreach ($permisosMod as $permiso)
                                                    <div class="col-md-6">
                                                        <div class="p-2 border rounded d-flex align-items-center bg-white h-100">
                                                            <div class="form-check mb-0">
                                                                <input class="form-check-input" type="checkbox" name="permisos_seleccionados[]" value="{{ $permiso }}" id="chk_crear_{{ md5($permiso) }}">
                                                                <label class="form-check-label small ms-1" for="chk_crear_{{ md5($permiso) }}">
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
                    <div class="modal-footer-create mt-4 pt-3 border-top">
                        <div class="footer-buttons d-flex justify-content-end gap-2 w-100">
                            <button type="button" class="btn-modal-cancel btn btn-secondary px-4" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-modal-create btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Rol
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
function marcarGrupo(claseGrupo, estado) {
    document.querySelectorAll('.' + claseGrupo + ' input[type="checkbox"]').forEach(function(chk) {
        chk.checked = estado;
    });
}
</script>

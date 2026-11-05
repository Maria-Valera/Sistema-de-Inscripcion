<!-- Modal Crear Grupo Estable -->
<div class="modal fade" id="modalCrearGrupoEstable" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearGrupoEstableLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" id="modalCrearGrupoEstableLabel" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Nuevo Grupo Estable</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Registro para actividades complementarias y boletín</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-create p-4">
                <form id="formGrupoEstable" action="{{ route('admin.area_formacion.modalesGrupoEstable.storeGrupoEstable') }}" method="POST">
                    @csrf

                    <!-- Código del grupo estable -->
                    <div class="form-group-modern mb-3">
                        <label for="codigo_grupo_estable" class="form-label-modern d-flex align-items-center mb-1">
                            <i class="fas fa-barcode text-primary mr-2 me-2"></i>
                            <span class="fw-bold font-weight-bold">Código</span>
                            <small class="text-muted ml-1 ms-1">(Opcional, ej: AP 041, GE-01)</small>
                        </label>
                        <input type="text" 
                            name="codigo" 
                            id="codigo_grupo_estable" 
                            class="form-control-modern @error('codigo') is-invalid @enderror" 
                            style="height: 46px; border-radius: 10px;"
                            placeholder="Ej: AP 041"
                            maxlength="20"
                            value="{{ old('codigo') }}">
                        @error('codigo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nombre del grupo estable -->
                    <div class="form-group-modern mb-3">
                        <label for="nombre_grupo_estable" class="form-label-modern d-flex align-items-center mb-1">
                            <i class="fas fa-book text-primary mr-2 me-2"></i>
                            <span class="fw-bold font-weight-bold">Nombre del Grupo Estable</span>
                            <span class="text-danger ml-1 ms-1">*</span>
                        </label>
                        <input type="text" 
                            name="nombre_grupo_estable" 
                            id="nombre_grupo_estable" 
                            class="form-control-modern @error('nombre_grupo_estable') is-invalid @enderror" 
                            style="height: 46px; border-radius: 10px;"
                            placeholder="Ingrese el nombre del grupo estable"
                            value="{{ old('nombre_grupo_estable') }}"
                            required>
                        @error('nombre_grupo_estable')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>              

                    {{-- Botones --}}
                    <div class="modal-footer-create py-3 px-0 d-flex justify-content-end align-items-center border-top mt-4" style="background: transparent; gap: 0.75rem;">
                        <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary btn-modal-create px-4 py-2" style="background: var(--purple, #5b6ee1); border-color: var(--purple, #5b6ee1); border-radius: 8px; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(91, 110, 225, 0.3);">
                            <i class="fas fa-save mr-1 me-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModalEditar{{ $datos->id }}" tabindex="-1"
    aria-labelledby="viewModalEditarLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #FEF3C7; color: #D97706;">
                        <i class="fas fa-pen" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="viewModalEditarLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.1rem;">
                            Editar Discapacidad
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Actualización de condición especial</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('admin.discapacidad.modales.update', $datos->id) }}" method="POST" class="m-0">
                @csrf
                <input type="hidden" name="id" value="{{ $datos->id }}">

                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div class="mb-3">
                        <label for="nombre_discapacidad_{{ $datos->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-wheelchair text-primary me-1"></i>
                            <span>Nombre de la Discapacidad</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('nombre_discapacidad') is-invalid @enderror"
                               style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                               id="nombre_discapacidad_{{ $datos->id }}"
                               name="nombre_discapacidad"
                               inputmode="text"
                               maxlength="100"
                               placeholder="Edite el nombre de la discapacidad"
                               value="{{ $datos->nombre_discapacidad }}"
                               required>
                        @error('nombre_discapacidad')
                            <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Footer Alineado a la Derecha -->
                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="btn-primary-modern">
                        <i class="fas fa-save me-1"></i>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

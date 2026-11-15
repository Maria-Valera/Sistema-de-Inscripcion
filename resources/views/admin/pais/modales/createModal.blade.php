<div wire:ignore.self class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-globe-americas" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalCrearLabel" style="color: #1E293B; font-size: 1.1rem;">
                            Nuevo País
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Registro de país y código ISO internacional</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form wire:submit.prevent="store" id="formCrearPais" class="m-0">
                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div id="contenedorAlertaCrear"></div>

                    <div class="mb-3">
                        <label for="iso2_crear" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-hashtag text-primary me-1"></i>
                            <span>Código ISO (Alfa-2)</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control text-uppercase fw-semibold @error('iso2') is-invalid @enderror"
                               style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                               id="iso2_crear"
                               wire:model.defer="iso2"
                               maxlength="10"
                               placeholder="Ej: VE, CO, ES, US"
                               required>
                        @error('iso2')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nameES_crear" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-flag text-primary me-1"></i>
                            <span>Nombre del País</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('nameES') is-invalid @enderror"
                               style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                               id="nameES_crear"
                               wire:model.defer="nameES"
                               maxlength="100"
                               placeholder="Ej: Venezuela, Colombia, España"
                               required>
                        @error('nameES')
                            <div class="text-danger small mt-1">{{ $message }}</div>
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
                        <span>Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

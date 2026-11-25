<div class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-phone" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalCrearLabel" style="color: #1E293B; font-size: 1.1rem;">
                            Nuevo Prefijo Telefónico
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Registro de código de área o prefijo de operadora</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('admin.prefijo_telefono.modales.store') }}" method="POST" id="formCrearPrefijo" class="m-0">
                @csrf
                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div id="contenedorAlertaCrear"></div>
                    <div class="mb-3">
                        <label for="prefijo" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-hashtag text-primary me-1"></i>
                            <span>Número de Prefijo</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control fw-semibold @error('prefijo') is-invalid @enderror"
                               style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                               id="prefijo"
                               name="prefijo"
                               inputmode="numeric"
                               pattern="[0-9]+"
                               maxlength="4"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               placeholder="Ej: 0412, 0251"
                               required>
                        @error('prefijo')
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
                        <span>Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

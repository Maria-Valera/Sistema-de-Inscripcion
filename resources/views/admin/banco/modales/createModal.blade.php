<div class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-university" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalCrearLabel" style="color: #1E293B; font-size: 1.1rem;">
                            Nuevo Banco
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Registro de entidad bancaria e institución financiera</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('admin.banco.modales.store') }}" method="POST" id="formCrearBanco" class="m-0">
                @csrf
                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div id="contenedorAlertaCrear"></div>

                    <div class="mb-3">
                        <label for="codigo_banco" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-hashtag text-primary me-1"></i>
                            <span>Código Bancario</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control fw-semibold @error('codigo_banco') is-invalid @enderror"
                               style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                               id="codigo_banco"
                               name="codigo_banco"
                               inputmode="numeric"
                               placeholder="Ej: 0102"
                               pattern="[0-9]+"
                               maxlength="4"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               required>
                        @error('codigo_banco')
                            <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nombre_banco" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-university text-primary me-1"></i>
                            <span>Nombre de la Entidad</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('nombre_banco') is-invalid @enderror"
                               style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                               id="nombre_banco"
                               name="nombre_banco"
                               inputmode="text"
                               maxlength="100"
                               placeholder="Ej: Banco de Venezuela"
                               required>
                        @error('nombre_banco')
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

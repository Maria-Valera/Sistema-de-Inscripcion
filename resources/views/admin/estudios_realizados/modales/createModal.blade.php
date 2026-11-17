<div class="modal fade" id="modalCrearEstudio" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearEstudioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">

            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-plus" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalCrearEstudioLabel" style="color: #1E293B; font-size: 1.1rem;">
                            Nuevo Estudio Realizado
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.8rem;">Registrar nuevo nivel académico o título</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('admin.estudios_realizados.modales.store') }}" method="POST" id="formCrearEstudiosRealizados" class="m-0">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div id="contenedorAlertaCrear"></div>

                    <div class="mb-3">
                        <label for="estudios" class="form-label" style="font-size: 13px; font-weight: 600; color: #334155;">
                            Nombre del Estudio <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="estudios" 
                               name="estudios"
                               maxlength="100"
                               placeholder="Ej: Licenciatura en Educación"
                               style="height: 42px; border-radius: 8px; border: 1px solid #E2E8F0;"
                               required>
                        @error('estudios')
                            <div class="invalid-feedback d-block text-danger mt-1" style="font-size: 12px;">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-primary-modern">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

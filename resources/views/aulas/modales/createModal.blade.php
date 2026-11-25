<div class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">

            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-door-open" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" style="color: #1E293B; font-size: 1.1rem;">
                            Nueva Aula
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Registro de espacio físico o laboratorio</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('aulas.store') }}" method="POST" id="formCrearAula" class="m-0">
                @csrf

                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div class="mb-3">
                        <label for="nombre_aula_crear" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-chalkboard text-primary me-1"></i>
                            <span>Nombre del Aula</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                               id="nombre_aula_crear"
                               name="nombre_aula"
                               maxlength="100"
                               placeholder="Ej: Aula 101, Laboratorio de Computación"
                               autocomplete="off"
                               required>

                        {{-- Mensaje vacío --}}
                        <div class="error-message text-danger small mt-1" id="error-crear-vacio" style="display:none;">
                            <i class="fas fa-exclamation-circle me-1"></i> El nombre del aula es obligatorio.
                        </div>

                        {{-- Mensaje duplicado --}}
                        <div class="error-message text-danger small mt-1" id="error-crear-duplicado" style="display:none;">
                            <i class="fas fa-exclamation-circle me-1"></i> Ya existe un aula con ese nombre.
                        </div>

                        {{-- Mensaje OK --}}
                        <div class="error-message text-success small mt-1" id="ok-crear" style="display:none;">
                            <i class="fas fa-check-circle me-1"></i> Nombre disponible.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_aula_crear" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-layer-group text-primary me-1"></i>
                            <span>Tipo de Aula</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select"
                                style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                                id="tipo_aula_crear"
                                name="tipo_aula"
                                required>
                            <option value="">Seleccione el tipo de aula</option>
                            <option value="Aula Regular">Aula Regular</option>
                            <option value="Laboratorio">Laboratorio</option>
                            <option value="Sala Especializada">Sala Especializada</option>
                            <option value="Aula Magna">Aula Magna</option>
                            <option value="Biblioteca">Biblioteca</option>
                            <option value="Gimnasio">Gimnasio</option>
                            <option value="Patio">Patio</option>
                        </select>

                        <div class="error-message text-danger small mt-1" id="error-tipo-crear-vacio" style="display:none;">
                            <i class="fas fa-exclamation-circle me-1"></i> El tipo de aula es obligatorio.
                        </div>
                    </div>
                </div>

                <!-- Footer Alineado a la Derecha -->
                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="btn-primary-modern" id="btnGuardarCrear">
                        <i class="fas fa-save me-1"></i>
                        <span>Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

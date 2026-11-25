<div class="modal fade" id="modalCrearGrado" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalCrearGradoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-layer-group" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalCrearGradoLabel" style="color: #1E293B; font-size: 1.1rem;">
                            Nuevo Nivel Académico
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Definición de nivel y capacidad de cupos</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('admin.grado.modales.store') }}" method="POST" id="formCrearGrado" novalidate class="m-0">
                @csrf

                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div id="contenedorAlertaCrear"></div>

                    {{-- Grid de 2 Columnas --}}
                    <div class="row">
                        {{-- Nivel Académico --}}
                        <div class="col-md-6 mb-3">
                            <label for="numero_grado" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-hashtag text-primary me-1"></i>
                                <span>Nivel Académico</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-sm text-center fw-semibold @error('numero_grado') is-invalid @enderror"
                                   style="height: 38px; border-radius: 8px; font-size: 0.9rem;"
                                   id="numero_grado"
                                   name="numero_grado"
                                   placeholder="Ej: 1"
                                   inputmode="numeric"
                                   pattern="[0-9]+"
                                   maxlength="2"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                   required>
                            @error('numero_grado')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Capacidad Máx. Cupos --}}
                        <div class="col-md-6 mb-3">
                            <label for="capacidad_max" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-users text-primary me-1"></i>
                                <span>Capacidad Máx. Cupos</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-sm text-center fw-semibold @error('capacidad_max') is-invalid @enderror"
                                   style="height: 38px; border-radius: 8px; font-size: 0.9rem;"
                                   id="capacidad_max"
                                   name="capacidad_max"
                                   placeholder="Ej: 35"
                                   inputmode="numeric"
                                   pattern="[0-9]+"
                                   maxlength="3"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                   required>
                            @error('capacidad_max')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mínimo de Sección --}}
                        <div class="col-md-6 mb-3">
                            <label for="min_seccion" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-layer-group text-primary me-1"></i>
                                <span>Mínimo de Sección</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-sm text-center fw-semibold @error('min_seccion') is-invalid @enderror"
                                   style="height: 38px; border-radius: 8px; font-size: 0.9rem;"
                                   id="min_seccion"
                                   name="min_seccion"
                                   placeholder="Ej: 1"
                                   inputmode="numeric"
                                   pattern="[0-9]+"
                                   maxlength="2"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                   required>
                            @error('min_seccion')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Máximo de Sección --}}
                        <div class="col-md-6 mb-3">
                            <label for="max_seccion" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-layer-group text-primary me-1"></i>
                                <span>Máximo de Sección</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-sm text-center fw-semibold @error('max_seccion') is-invalid @enderror"
                                   style="height: 38px; border-radius: 8px; font-size: 0.9rem;"
                                   id="max_seccion"
                                   name="max_seccion"
                                   placeholder="Ej: 4"
                                   inputmode="numeric"
                                   pattern="[0-9]+"
                                   maxlength="2"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                   required>
                            @error('max_seccion')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Footer Alineado a la Derecha con Clases del Design System (Fuera de la fila) -->
                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="btn-primary-modern" id="btnGuardarGrado">
                        <i class="fas fa-save me-1"></i>
                        <span>Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

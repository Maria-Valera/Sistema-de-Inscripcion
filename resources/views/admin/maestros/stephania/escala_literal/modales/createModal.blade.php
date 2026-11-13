<div class="modal fade" id="modalCrearEscala" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearEscalaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">

            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-sort-alpha-down" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalCrearEscalaLabel" style="color: #1E293B; font-size: 1.1rem;">
                            Nueva Escala Literal
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Año Escolar: {{ $anioModelo?->nombre_completo ?? 'Activo' }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('admin.escala-literal.store') }}" method="POST" id="formCrearEscala" novalidate class="m-0">
                @csrf
                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    
                    {{-- Fila 1: Tres en línea (Letra, Nota Desde, Nota Hasta) --}}
                    <div class="row mb-3 align-items-end g-3">
                        {{-- Letra (col-md-3) --}}
                        <div class="col-md-3 col-3">
                            <label for="letra" class="form-label fw-bold text-dark mb-1 text-nowrap" style="font-size: 0.85rem;">
                                <i class="fas fa-font text-primary me-1"></i>
                                <span>Letra</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control text-center fw-bold @error('letra') is-invalid @enderror"
                                   style="text-transform: uppercase; font-size: 1.15rem; height: 42px; text-align: center; border-radius: 10px;"
                                   id="letra"
                                   name="letra"
                                   maxlength="1"
                                   value="{{ old('letra') }}"
                                   placeholder="A"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                            @error('letra')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nota Desde (col-md-3) --}}
                        <div class="col-md-3 col-4">
                            <label for="nota_desde" class="form-label fw-bold text-dark mb-1 text-nowrap" style="font-size: 0.85rem;">
                                <i class="fas fa-arrow-circle-down text-primary me-1"></i>
                                <span>Nota Desde</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control text-center fw-semibold @error('nota_desde') is-invalid @enderror"
                                   style="height: 42px; border-radius: 10px; font-size: 0.95rem; text-align: center;"
                                   id="nota_desde"
                                   name="nota_desde"
                                   min="1"
                                   max="20"
                                   value="{{ old('nota_desde', 1) }}"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                            @error('nota_desde')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nota Hasta (col-md-3) --}}
                        <div class="col-md-3 col-4">
                            <label for="nota_hasta" class="form-label fw-bold text-dark mb-1 text-nowrap" style="font-size: 0.85rem;">
                                <i class="fas fa-arrow-circle-up text-primary me-1"></i>
                                <span>Nota Hasta</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control text-center fw-semibold @error('nota_hasta') is-invalid @enderror"
                                   style="height: 42px; border-radius: 10px; font-size: 0.95rem; text-align: center;"
                                   id="nota_hasta"
                                   name="nota_hasta"
                                   min="1"
                                   max="20"
                                   value="{{ old('nota_hasta', 20) }}"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                            @error('nota_hasta')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Fila 2: Descripción (col-md-9 alineada con el borde de Nota Hasta) --}}
                    <div class="row g-3">
                        <div class="col-md-9 col-11">
                            <label for="descripcion" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-align-left text-primary me-1"></i>
                                <span>Descripción Cualitativa</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('descripcion') is-invalid @enderror"
                                   style="height: 42px; border-radius: 10px; font-size: 0.9rem;"
                                   id="descripcion"
                                   name="descripcion"
                                   value="{{ old('descripcion') }}"
                                   placeholder="Ej: Excelente, Aprobado, Destacado...">
                            <div class="invalid-feedback error-message"></div>
                            @error('descripcion')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <!-- Footer Alineado a la Derecha con Clases del Design System -->
                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="btn-primary-modern" id="btnGuardarEscala">
                        <i class="fas fa-save me-1"></i>
                        <span>Guardar</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

                <!-- Footer Alineado a la Derecha con Clases del Design System -->
                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="btn-primary-modern" id="btnGuardarEscala">
                        <i class="fas fa-save me-1"></i>
                        <span>Guardar</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

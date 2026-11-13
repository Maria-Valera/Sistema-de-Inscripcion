<div class="modal fade" id="modalEditarEscala{{ $escala->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalEditarEscalaLabel{{ $escala->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">

            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-edit" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalEditarEscalaLabel{{ $escala->id }}" style="color: #1E293B; font-size: 1.1rem;">
                            Editar Escala Literal
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Escala [{{ $escala->letra }}] - {{ $anioModelo?->nombre_completo ?? 'Activo' }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('admin.escala-literal.update', $escala) }}" method="POST" id="formEditarEscala{{ $escala->id }}" novalidate class="m-0">
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    
                    {{-- Fila 1: Tres en línea (Letra, Nota Desde, Nota Hasta) --}}
                    <div class="row mb-3 align-items-end g-3">
                        {{-- Letra (col-md-3) --}}
                        <div class="col-md-3 col-3">
                            <label for="letra_{{ $escala->id }}" class="form-label fw-bold text-dark mb-1 text-nowrap" style="font-size: 0.85rem;">
                                <i class="fas fa-font text-primary me-1"></i>
                                <span>Letra</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control text-center fw-bold"
                                   style="text-transform: uppercase; font-size: 1.15rem; height: 42px; text-align: center; border-radius: 10px;"
                                   id="letra_{{ $escala->id }}"
                                   name="letra"
                                   maxlength="1"
                                   value="{{ old('letra', $escala->letra) }}"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                        </div>

                        {{-- Nota Desde (col-md-3) --}}
                        <div class="col-md-3 col-4">
                            <label for="nota_desde_{{ $escala->id }}" class="form-label fw-bold text-dark mb-1 text-nowrap" style="font-size: 0.85rem;">
                                <i class="fas fa-arrow-circle-down text-primary me-1"></i>
                                <span>Nota Desde</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control text-center fw-semibold"
                                   style="height: 42px; border-radius: 10px; font-size: 0.95rem; text-align: center;"
                                   id="nota_desde_{{ $escala->id }}"
                                   name="nota_desde"
                                   min="1"
                                   max="20"
                                   value="{{ old('nota_desde', $escala->nota_desde) }}"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                        </div>

                        {{-- Nota Hasta (col-md-3) --}}
                        <div class="col-md-3 col-4">
                            <label for="nota_hasta_{{ $escala->id }}" class="form-label fw-bold text-dark mb-1 text-nowrap" style="font-size: 0.85rem;">
                                <i class="fas fa-arrow-circle-up text-primary me-1"></i>
                                <span>Nota Hasta</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control text-center fw-semibold"
                                   style="height: 42px; border-radius: 10px; font-size: 0.95rem; text-align: center;"
                                   id="nota_hasta_{{ $escala->id }}"
                                   name="nota_hasta"
                                   min="1"
                                   max="20"
                                   value="{{ old('nota_hasta', $escala->nota_hasta) }}"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                        </div>
                    </div>

                    {{-- Fila 2: Descripción (col-md-9 alineada con el borde de Nota Hasta) --}}
                    <div class="row g-3">
                        <div class="col-md-9 col-11">
                            <label for="descripcion_{{ $escala->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-align-left text-primary me-1"></i>
                                <span>Descripción Cualitativa</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   style="height: 42px; border-radius: 10px; font-size: 0.9rem;"
                                   id="descripcion_{{ $escala->id }}"
                                   name="descripcion"
                                   value="{{ old('descripcion', $escala->descripcion) }}"
                                   placeholder="Ej: Excelente, Aprobado, Destacado...">
                            <div class="invalid-feedback error-message"></div>
                        </div>
                    </div>

                </div>

                <!-- Footer Alineado a la Derecha con Clases del Design System -->
                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="btn-primary-modern">
                        <i class="fas fa-save me-1"></i>
                        <span>Actualizar</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

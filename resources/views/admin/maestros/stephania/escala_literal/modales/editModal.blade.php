<div class="modal fade" id="modalEditarEscala{{ $escala->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">

            {{-- Cabecera Compacta en Índigo --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Editar Escala Literal</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Escala [{{ $escala->letra }}] - {{ $anioModelo?->nombre_completo ?? 'Activo' }}</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.escala-literal.update', $escala) }}" method="POST" id="formEditarEscala{{ $escala->id }}" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body-create p-4">
                    <div class="row g-3">
                        {{-- Letra --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="letra_{{ $escala->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-font text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Letra</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern text-center fw-bold font-weight-bold"
                                   style="height: 46px; border-radius: 10px; text-transform: uppercase; font-size: 1.1rem;"
                                   id="letra_{{ $escala->id }}"
                                   name="letra"
                                   maxlength="1"
                                   value="{{ old('letra', $escala->letra) }}"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                        </div>

                        {{-- Descripción --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="descripcion_{{ $escala->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-align-left text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Descripción</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern"
                                   style="height: 46px; border-radius: 10px;"
                                   id="descripcion_{{ $escala->id }}"
                                   name="descripcion"
                                   value="{{ old('descripcion', $escala->descripcion) }}"
                                   placeholder="Ej: Excelente, Aprobado...">
                            <div class="invalid-feedback error-message"></div>
                        </div>

                        {{-- Nota Desde --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="nota_desde_{{ $escala->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-arrow-circle-down text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Nota Desde</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="number"
                                   class="form-control-modern text-center"
                                   style="height: 46px; border-radius: 10px; font-size: 1rem;"
                                   id="nota_desde_{{ $escala->id }}"
                                   name="nota_desde"
                                   min="1"
                                   max="20"
                                   value="{{ old('nota_desde', $escala->nota_desde) }}"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                        </div>

                        {{-- Nota Hasta --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="nota_hasta_{{ $escala->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-arrow-circle-up text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Nota Hasta</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="number"
                                   class="form-control-modern text-center"
                                   style="height: 46px; border-radius: 10px; font-size: 1rem;"
                                   id="nota_hasta_{{ $escala->id }}"
                                   name="nota_hasta"
                                   min="1"
                                   max="20"
                                   value="{{ old('nota_hasta', $escala->nota_hasta) }}"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                        </div>
                    </div>
                </div>

                {{-- Pie de Modal con botones Índigo / Gris --}}
                <div class="modal-footer-create py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                    <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-modal-create px-4 py-2" style="background: var(--purple, #5b6ee1); border-color: var(--purple, #5b6ee1); border-radius: 8px; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(91, 110, 225, 0.3);">
                        <i class="fas fa-save mr-1 me-1"></i> Actualizar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


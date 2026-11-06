<div class="modal fade" id="modalCrearMencion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">

            {{-- Cabecera con degradado índigo / púrpura --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none; flex-shrink: 0;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Nueva Mención Curricular</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Código oficial para boletines y títulos MPPE</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.menciones.store') }}" method="POST" id="formCrearMencion" style="display: flex; flex-direction: column; overflow: hidden; margin: 0; max-height: 90vh;">
                @csrf
                <div class="modal-body-create p-4" style="max-height: calc(85vh - 140px); overflow-y: auto; flex: 1 1 auto;">
                    <div class="row g-3">

                        {{-- Código de Mención --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="codigo" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-hashtag text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Código Mención</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern text-center fw-bold @error('codigo') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px; font-size: 1.1rem; letter-spacing: 2px;"
                                   id="codigo"
                                   name="codigo"
                                   maxlength="10"
                                   value="{{ old('codigo') }}"
                                   placeholder="01"
                                   required>
                            <small class="text-muted">Ejemplo: 01, 02...</small>
                            @error('codigo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ciclo MPPE --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="ciclo" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-sync text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Código Ciclo</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern text-center @error('ciclo') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px; font-size: 1.1rem;"
                                   id="ciclo"
                                   name="ciclo"
                                   maxlength="20"
                                   value="{{ old('ciclo', '50') }}"
                                   placeholder="50"
                                   required>
                            <small class="text-muted">Generalmente 50 (Media General).</small>
                            @error('ciclo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nombre de la Mención --}}
                        <div class="col-12 form-group-modern mb-3">
                            <label for="nombre" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-award text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Nombre de la Mención</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('nombre') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="nombre"
                                   name="nombre"
                                   value="{{ old('nombre') }}"
                                   placeholder="Ej: Educación Media General"
                                   required>
                            @error('nombre')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estado Activo --}}
                        <div class="col-12 mb-2">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input mr-2 me-2" type="checkbox" id="status_mencion_crear" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }} style="cursor: pointer;">
                                <label class="form-check-label fw-bold font-weight-bold" for="status_mencion_crear" style="cursor: pointer;">
                                    Mención habilitada en el sistema
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Footer con botones --}}
                <div class="modal-footer-create py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f8fafc; gap: 0.75rem; flex-shrink: 0; position: relative; z-index: 10;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border: none; border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-save mr-2 me-2"></i> Guardar Mención
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


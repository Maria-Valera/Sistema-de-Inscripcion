<div class="modal fade" id="modalEditarPensum{{ $pensum->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden; max-height: 90vh;">

            {{-- Cabecera con degradado índigo / púrpura --}}
            <div class="modal-header-edit py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, #f59e0b, #d97706); border-bottom: none; flex-shrink: 0;">
                <div class="modal-icon-edit" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-edit mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Editar Plan: {{ $pensum->codigo }}</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Modificar especificaciones del pensum</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.pensums.update', $pensum) }}" method="POST" id="formEditarPensum{{ $pensum->id }}" style="display: flex; flex-direction: column; overflow: hidden;">
                @csrf
                @method('PUT')

                <div class="modal-body-edit p-4" style="max-height: calc(85vh - 140px); overflow-y: auto;">
                    <div class="row g-3">
                        {{-- Código del Plan (5 dígitos exactos) --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="codigo_{{ $pensum->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-hashtag text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Código MPPE</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern text-center fw-bold font-weight-bold @error('codigo') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px; font-size: 1.1rem; letter-spacing: 2px;"
                                   id="codigo_{{ $pensum->id }}"
                                   name="codigo"
                                   maxlength="5"
                                   pattern="[0-9]{5}"
                                   value="{{ old('codigo', $pensum->codigo) }}"
                                   required>
                            <small class="form-text text-muted">5 dígitos oficiales.</small>
                            @error('codigo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nombre del Plan --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="nombre_{{ $pensum->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-book text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Nombre del Plan</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('nombre') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="nombre_{{ $pensum->id }}"
                                   name="nombre"
                                   value="{{ old('nombre', $pensum->nombre) }}"
                                   required>
                            @error('nombre')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Año de Inicio --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="anio_inicio_{{ $pensum->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-plus text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Año de Inicio</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="number"
                                   class="form-control-modern text-center @error('anio_inicio') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="anio_inicio_{{ $pensum->id }}"
                                   name="anio_inicio"
                                   min="1970"
                                   max="2100"
                                   value="{{ old('anio_inicio', $pensum->anio_inicio) }}"
                                   required>
                            @error('anio_inicio')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Año de Fin (Vigencia) --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="anio_fin_{{ $pensum->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-times text-secondary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Año de Fin (Opcional)</span>
                            </label>
                            <input type="number"
                                   class="form-control-modern text-center @error('anio_fin') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="anio_fin_{{ $pensum->id }}"
                                   name="anio_fin"
                                   min="1970"
                                   max="2100"
                                   value="{{ old('anio_fin', $pensum->anio_fin) }}"
                                   placeholder="Vacío = Vigente">
                            <small class="form-text text-muted"><strong>Vacío = Vigente</strong></small>
                            @error('anio_fin')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Descripción / Referencia --}}
                        <div class="col-12 form-group-modern mb-3">
                            <label for="descripcion_{{ $pensum->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-info-circle text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Descripción o Referencia Legal</span>
                            </label>
                            <textarea class="form-control-modern @error('descripcion') is-invalid @enderror"
                                      style="border-radius: 10px; min-height: 80px;"
                                      id="descripcion_{{ $pensum->id }}"
                                      name="descripcion"
                                      rows="3">{{ old('descripcion', $pensum->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estado Activo --}}
                        <div class="col-12 mb-2">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input mr-2 me-2" type="checkbox" id="status_{{ $pensum->id }}" name="status" value="1" {{ old('status', $pensum->status) ? 'checked' : '' }} style="cursor: pointer;">
                                <label class="form-check-label fw-bold font-weight-bold" for="status_{{ $pensum->id }}" style="cursor: pointer;">
                                    Habilitado en el sistema
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer con botones --}}
                <div class="modal-footer-edit py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f8fafc; gap: 0.75rem; flex-shrink: 0; position: relative; z-index: 10;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning text-white px-4 shadow-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706); border: none; border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-save mr-2 me-2"></i> Actualizar Plan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<div class="modal fade" id="modalEditarAnioEscolar{{ $anio->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">

            {{-- Cabecera Compacta en Índigo --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Editar Año Escolar</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Período: {{ $anio->periodo ?? ($anio->inicio_anio_escolar?->format('Y') . ' - ' . $anio->cierre_anio_escolar?->format('Y')) }}</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.anio-escolar.update', $anio) }}" method="POST" id="formEditarAnioEscolar{{ $anio->id }}">
                @csrf
                @method('PUT')
                <div class="modal-body-create p-4">
                    <div class="row g-3">
                        {{-- Inicio Año Escolar --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="inicio_anio_escolar_edit_{{ $anio->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-alt text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Inicio</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('inicio_anio_escolar') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="inicio_anio_escolar_edit_{{ $anio->id }}"
                                   name="inicio_anio_escolar"
                                   value="{{ old('inicio_anio_escolar', $anio->inicio_anio_escolar ? \Carbon\Carbon::parse($anio->inicio_anio_escolar)->format('Y-m-d') : '') }}"
                                   required>
                            @error('inicio_anio_escolar')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cierre Año Escolar --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="cierre_anio_escolar_edit_{{ $anio->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-check text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Cierre</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('cierre_anio_escolar') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="cierre_anio_escolar_edit_{{ $anio->id }}"
                                   name="cierre_anio_escolar"
                                   value="{{ old('cierre_anio_escolar', $anio->cierre_anio_escolar ? \Carbon\Carbon::parse($anio->cierre_anio_escolar)->format('Y-m-d') : '') }}"
                                   required>
                            @error('cierre_anio_escolar')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Extensión Año Escolar --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="extencion_anio_escolar_edit_{{ $anio->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-week text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Extensión</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('extencion_anio_escolar') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="extencion_anio_escolar_edit_{{ $anio->id }}"
                                   name="extencion_anio_escolar"
                                   value="{{ old('extencion_anio_escolar', $anio->extencion_anio_escolar ? \Carbon\Carbon::parse($anio->extencion_anio_escolar)->format('Y-m-d') : '') }}">
                            <small class="text-muted d-block mt-1">Opcional. Aplica en prórrogas del calendario.</small>
                            @error('extencion_anio_escolar')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estado --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="status_edit_{{ $anio->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-toggle-on text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Estado</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select class="form-control-modern form-select @error('status') is-invalid @enderror"
                                    style="height: 46px; border-radius: 10px;"
                                    id="status_edit_{{ $anio->id }}"
                                    name="status"
                                    required>
                                <option value="Inactivo" {{ old('status', $anio->status) === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                <option value="Activo" {{ old('status', $anio->status) === 'Activo' ? 'selected' : '' }}>Activo</option>
                                <option value="Extendido" {{ old('status', $anio->status) === 'Extendido' ? 'selected' : '' }}>Extendido</option>
                            </select>
                            <small class="text-info d-block mt-1">
                                <i class="fas fa-info-circle mr-1"></i>Si cambias a <strong>Activo</strong>, los demás años pasarán a <strong>Inactivo</strong>.
                            </small>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Pie de Modal --}}
                <div class="modal-footer-create py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f8fafc; gap: 0.75rem;">
                    <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-modal-save px-4 py-2" style="border-radius: 8px; font-weight: 600; background: var(--purple, #5b6ee1); border-color: var(--purple, #5b6ee1);">
                        <i class="fas fa-save mr-1 me-1"></i>
                        Actualizar Año Escolar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


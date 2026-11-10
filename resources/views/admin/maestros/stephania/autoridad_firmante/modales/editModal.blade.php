<div class="modal fade" id="modalEditarAutoridad{{ $aut->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">

            {{-- Cabecera Compacta en Índigo --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Editar Autoridad Firmante</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">{{ $aut->cargo_nombre }} - {{ $aut->persona?->nombre_completo }}</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.autoridad-firmante.update', $aut) }}" method="POST" id="formEditarAutoridad{{ $aut->id }}" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body-create p-4">
                    <div class="row g-3">
                        {{-- Cargo --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="cargo_edit_{{ $aut->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-user-tag text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Cargo</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select class="form-control-modern form-select @error('cargo') is-invalid @enderror"
                                    style="height: 46px; border-radius: 10px;"
                                    id="cargo_edit_{{ $aut->id }}"
                                    name="cargo"
                                    required>
                                @foreach ($cargos as $valor => $etiqueta)
                                    <option value="{{ $valor }}" {{ old('cargo', $aut->cargo) === $valor ? 'selected' : '' }}>
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback error-message"></div>
                            @error('cargo')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Persona --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="persona_id_edit_{{ $aut->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-user text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Persona</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select class="form-control-modern form-select @error('persona_id') is-invalid @enderror"
                                    style="height: 46px; border-radius: 10px;"
                                    id="persona_id_edit_{{ $aut->id }}"
                                    name="persona_id"
                                    required>
                                @foreach ($personas as $per)
                                    <option value="{{ $per->id }}" {{ old('persona_id', $aut->persona_id) == $per->id ? 'selected' : '' }}>
                                        {{ $per->nombre_completo }} ({{ $per->tipoDocumento?->abreviatura ?? 'CI' }}: {{ $per->numero_documento }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback error-message"></div>
                            @error('persona_id')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Gaceta / Resolución --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="gaceta_edit_{{ $aut->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-file-contract text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Gaceta Oficial / Resolución</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('gaceta') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="gaceta_edit_{{ $aut->id }}"
                                   name="gaceta"
                                   value="{{ old('gaceta', $aut->gaceta) }}"
                                   placeholder="Ej: Gaceta N° 42.100 o Resolución DM/045">
                            <div class="invalid-feedback error-message"></div>
                            @error('gaceta')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Encargado (Checkbox) --}}
                        <div class="col-md-4 form-group-modern mb-3 d-flex align-items-center">
                            <div class="form-check form-switch pt-4 mt-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       id="encargado_edit_{{ $aut->id }}"
                                       name="encargado"
                                       value="1"
                                       style="width: 2.5rem; height: 1.3rem; cursor: pointer;"
                                       {{ old('encargado', $aut->encargado) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold font-weight-bold ml-2 ms-2" for="encargado_edit_{{ $aut->id }}" style="cursor: pointer;">
                                    ¿Es Encargado(a)?
                                </label>
                            </div>
                        </div>

                        {{-- Fecha Desde --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="desde_edit_{{ $aut->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-plus text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Vigencia Desde</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('desde') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="desde_edit_{{ $aut->id }}"
                                   name="desde"
                                   value="{{ old('desde', $aut->desde ? \Carbon\Carbon::parse($aut->desde)->format('Y-m-d') : '') }}"
                                   required>
                            <div class="invalid-feedback error-message"></div>
                            @error('desde')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fecha Hasta --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="hasta_edit_{{ $aut->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-check text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Vigencia Hasta</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('hasta') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="hasta_edit_{{ $aut->id }}"
                                   name="hasta"
                                   value="{{ old('hasta', $aut->hasta ? \Carbon\Carbon::parse($aut->hasta)->format('Y-m-d') : '') }}">
                            <div class="invalid-feedback error-message"></div>
                            <small class="text-muted d-block mt-1">Opcional. Dejar vacío si la vigencia es indefinida / en curso.</small>
                            @error('hasta')
                                <div class="text-danger small mt-1 server-error">{{ $message }}</div>
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
                        Actualizar Autoridad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


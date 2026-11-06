<div class="modal fade" id="modalEditarRegla{{ $regla->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">

            {{-- Cabecera con degradado naranja / ámbar --}}
            <div class="modal-header-edit py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, #f59e0b, #d97706); border-bottom: none; flex-shrink: 0;">
                <div class="modal-icon-edit" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-edit mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Editar Regla de Conversión</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Destino: {{ $regla->areaDestino->nombre_area_formacion ?? '' }} ({{ $regla->gradoDestino ? ($regla->gradoDestino->nombre_grado ?? ($regla->gradoDestino->numero_grado . '° Año')) : '' }})</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.conversion-reglas.update', $regla) }}" method="POST" id="formEditarRegla{{ $regla->id }}" style="display: flex; flex-direction: column; overflow: hidden; margin: 0; max-height: 90vh;">
                @csrf
                @method('PUT')

                <div class="modal-body-edit p-4" style="max-height: calc(85vh - 140px); overflow-y: auto; flex: 1 1 auto;">
                    <div class="row g-3">

                        {{-- Plan Origen y Plan Destino --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="pensum_origen_id_{{ $regla->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-sign-out-alt text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Plan Origen</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="pensum_origen_id" id="pensum_origen_id_{{ $regla->id }}" class="form-select @error('pensum_origen_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                @foreach($pensums as $p)
                                    <option value="{{ $p->id }}" {{ old('pensum_origen_id', $regla->pensum_origen_id) == $p->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        {{ $p->codigo }} - {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pensum_origen_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="pensum_destino_id_{{ $regla->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-sign-in-alt text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Plan Destino</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="pensum_destino_id" id="pensum_destino_id_{{ $regla->id }}" class="form-select @error('pensum_destino_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                @foreach($pensums as $p)
                                    <option value="{{ $p->id }}" {{ old('pensum_destino_id', $regla->pensum_destino_id) == $p->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        {{ $p->codigo }} - {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pensum_destino_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Grado y Asignatura Destino --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="grado_destino_id_{{ $regla->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-user-graduate text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Grado Destino</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="grado_destino_id" id="grado_destino_id_{{ $regla->id }}" class="form-select @error('grado_destino_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                @foreach($grados as $g)
                                    <option value="{{ $g->id }}" {{ old('grado_destino_id', $regla->grado_destino_id) == $g->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        {{ $g->nombre_grado ?? ($g->numero_grado ? $g->numero_grado . '° Año' : 'Grado ' . $g->id) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grado_destino_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="area_destino_id_{{ $regla->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-book text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Asignatura Resultante (Destino)</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="area_destino_id" id="area_destino_id_{{ $regla->id }}" class="form-select @error('area_destino_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                @foreach($areas as $a)
                                    <option value="{{ $a->id }}" {{ old('area_destino_id', $regla->area_destino_id) == $a->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        [{{ $a->siglas }}] {{ $a->nombre_area_formacion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('area_destino_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tipo de Regla --}}
                        <div class="col-md-12 form-group-modern mb-3">
                            <label for="tipo_regla_{{ $regla->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-cogs text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Tipo de Regla</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="tipo_regla" id="tipo_regla_{{ $regla->id }}" class="form-select @error('tipo_regla') is-invalid @enderror" required style="height: 46px; border-radius: 10px;">
                                <option value="directa" {{ old('tipo_regla', $regla->tipo_regla) == 'directa' ? 'selected' : '' }}>
                                    Homologación Directa (1 materia de origen)
                                </option>
                                <option value="promedio" {{ old('tipo_regla', $regla->tipo_regla) == 'promedio' ? 'selected' : '' }}>
                                    Promedio (2 o más materias de origen)
                                </option>
                                <option value="exonerada" {{ old('tipo_regla', $regla->tipo_regla) == 'exonerada' ? 'selected' : '' }}>
                                    Exonerada (Se imprime «EX» y «EXONERADA», 0 orígenes)
                                </option>
                                <option value="no_cuenta" {{ old('tipo_regla', $regla->tipo_regla) == 'no_cuenta' ? 'selected' : '' }}>
                                    No se toma en cuenta (0 orígenes)
                                </option>
                            </select>
                            @error('tipo_regla')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Asignaturas de Origen Actuales --}}
                        @php
                            $orig1 = $regla->origenes->first();
                            $orig2 = $regla->origenes->skip(1)->first();
                        @endphp
                        <div class="col-12" id="bloqueOrigenes{{ $regla->id }}">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold font-weight-bold text-dark mb-2">
                                    <i class="fas fa-link text-warning mr-2 me-2"></i> Asignaturas de Origen
                                </h6>

                                {{-- Origen 1 --}}
                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-md-1 text-center font-weight-bold text-muted small">#1</div>
                                    <div class="col-md-4">
                                        <select name="origenes[0][grado_origen_id]" class="form-select form-select-sm" style="border-radius: 8px;">
                                            <option value="">Grado origen...</option>
                                            @foreach($grados as $g)
                                                <option value="{{ $g->id }}" {{ old('origenes.0.grado_origen_id', $orig1?->grado_origen_id) == $g->id ? 'selected' : '' }}>
                                                    {{ $g->nombre_grado }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <select name="origenes[0][area_origen_id]" class="form-select form-select-sm" style="border-radius: 8px;">
                                            <option value="">Materia origen...</option>
                                            @foreach($areas as $a)
                                                <option value="{{ $a->id }}" {{ old('origenes.0.area_origen_id', $orig1?->area_origen_id) == $a->id ? 'selected' : '' }}>
                                                    [{{ $a->siglas }}] {{ $a->nombre_area_formacion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Origen 2 --}}
                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-md-1 text-center font-weight-bold text-muted small">#2</div>
                                    <div class="col-md-4">
                                        <select name="origenes[1][grado_origen_id]" class="form-select form-select-sm" style="border-radius: 8px;">
                                            <option value="">Grado origen (si aplica)...</option>
                                            @foreach($grados as $g)
                                                <option value="{{ $g->id }}" {{ old('origenes.1.grado_origen_id', $orig2?->grado_origen_id) == $g->id ? 'selected' : '' }}>
                                                    {{ $g->nombre_grado }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <select name="origenes[1][area_origen_id]" class="form-select form-select-sm" style="border-radius: 8px;">
                                            <option value="">Segunda materia para promedio (opcional)...</option>
                                            @foreach($areas as $a)
                                                <option value="{{ $a->id }}" {{ old('origenes.1.area_origen_id', $orig2?->area_origen_id) == $a->id ? 'selected' : '' }}>
                                                    [{{ $a->siglas }}] {{ $a->nombre_area_formacion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Observación / Cita Circular --}}
                        <div class="col-12 form-group-modern mb-2">
                            <label for="observacion_{{ $regla->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-file-contract text-warning mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Base Legal / Observación</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('observacion') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="observacion_{{ $regla->id }}"
                                   name="observacion"
                                   value="{{ old('observacion', $regla->observacion) }}">
                            @error('observacion')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estado Activo --}}
                        <div class="col-12 mb-2">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input mr-2 me-2" type="checkbox" id="status_{{ $regla->id }}" name="status" value="1" {{ old('status', $regla->status) ? 'checked' : '' }} style="cursor: pointer;">
                                <label class="form-check-label fw-bold font-weight-bold" for="status_{{ $regla->id }}" style="cursor: pointer;">
                                    Regla activa para conversiones
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
                        <i class="fas fa-save mr-2 me-2"></i> Actualizar Regla
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


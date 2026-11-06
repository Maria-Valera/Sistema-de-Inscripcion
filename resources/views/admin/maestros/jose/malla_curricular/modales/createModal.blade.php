<div class="modal fade" id="modalCrearMalla" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden; max-height: 90vh;">

            {{-- Cabecera con degradado índigo / púrpura --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none; flex-shrink: 0;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Nueva Asignatura en Malla</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Asignar área de formación a un grado y pensum oficial</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.malla-curricular.store') }}" method="POST" id="formCrearMalla" style="display: flex; flex-direction: column; overflow: hidden;">
                @csrf
                <div class="modal-body-create p-4" style="max-height: calc(85vh - 140px); overflow-y: auto;">
                    <div class="row g-3">
                        {{-- Pensum / Plan de Estudio --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="pensum_id" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-graduation-cap text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Plan de Estudio (Pensum)</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="pensum_id" id="pensum_id" class="form-select @error('pensum_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                <option value="" style="color: #64748b;">Seleccione el plan...</option>
                                @foreach($pensums as $p)
                                    <option value="{{ $p->id }}" {{ old('pensum_id', $pensumId) == $p->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        {{ $p->codigo }} - {{ $p->nombre }} {{ $p->es_vigente ? '(Vigente)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pensum_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Grado / Año Escolar --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="grado_id" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-user-graduate text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Grado / Año</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="grado_id" id="grado_id" class="form-select @error('grado_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                <option value="" style="color: #64748b;">Seleccione el grado...</option>
                                @foreach($grados as $g)
                                    <option value="{{ $g->id }}" {{ old('grado_id', $gradoId) == $g->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        {{ $g->nombre_grado ?? ($g->numero_grado ? $g->numero_grado . '° Año' : 'Grado ' . $g->id) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grado_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Área de Formación --}}
                        <div class="col-md-12 form-group-modern mb-3">
                            <label for="area_formacion_id" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-book text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Área de Formación (Asignatura)</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="area_formacion_id" id="area_formacion_id" class="form-select @error('area_formacion_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                <option value="" style="color: #64748b;">Seleccione el área de formación...</option>
                                @foreach($areas as $a)
                                    <option value="{{ $a->id }}" data-siglas="{{ $a->siglas }}" {{ old('area_formacion_id') == $a->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        [{{ $a->siglas }}] {{ $a->nombre_area_formacion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('area_formacion_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Horas Semanales / Bloques --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="horas" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-clock text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Horas / Bloques</span>
                            </label>
                            <input type="number"
                                   class="form-control-modern text-center @error('horas') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="horas"
                                   name="horas"
                                   min="1"
                                   max="20"
                                   value="{{ old('horas', 4) }}"
                                   placeholder="Ej: 4">
                            <small class="text-muted">Carga horaria semanal.</small>
                            @error('horas')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Escala de Calificación --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="escala" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-sort-numeric-up text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Escala Evaluativa</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="escala" id="escala" class="form-select @error('escala') is-invalid @enderror" required style="height: 46px; border-radius: 10px;">
                                <option value="numerica" {{ old('escala', 'numerica') == 'numerica' ? 'selected' : '' }}>Numérica (1 a 20 pts)</option>
                                <option value="literal" {{ old('escala') == 'literal' ? 'selected' : '' }}>Literal (A a E)</option>
                            </select>
                            <small class="text-muted">Literal solo para OC, PGCRP y Orientación Vocacional.</small>
                            @error('escala')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nota Aprobatoria --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="nota_aprobatoria" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-check-circle text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Nota Aprobatoria</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control-modern text-center @error('nota_aprobatoria') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="nota_aprobatoria"
                                   name="nota_aprobatoria"
                                   min="1"
                                   max="20"
                                   value="{{ old('nota_aprobatoria', '10.00') }}"
                                   required>
                            @error('nota_aprobatoria')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Componente Curricular --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="componente" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-tag text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Componente Curricular</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('componente') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="componente"
                                   name="componente"
                                   value="{{ old('componente', 'Formación General') }}"
                                   placeholder="Ej: Formación General, Ciencia y Tecnología...">
                            @error('componente')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Orden en Boletín --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="orden" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-sort-amount-down text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Orden en Boletín</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="number"
                                   class="form-control-modern text-center @error('orden') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="orden"
                                   name="orden"
                                   min="1"
                                   max="99"
                                   value="{{ old('orden', 1) }}"
                                   required>
                            @error('orden')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estado Activo --}}
                        <div class="col-12 mb-2">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input mr-2 me-2" type="checkbox" id="status" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }} style="cursor: pointer;">
                                <label class="form-check-label fw-bold font-weight-bold" for="status" style="cursor: pointer;">
                                    Asignatura activa en la malla
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
                        <i class="fas fa-save mr-2 me-2"></i> Asignar a Malla
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


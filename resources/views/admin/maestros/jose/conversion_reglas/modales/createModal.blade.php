<div class="modal fade" id="modalCrearRegla" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">

            {{-- Cabecera con degradado índigo / púrpura --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none; flex-shrink: 0;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Nueva Regla de Conversión</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Equivalencia entre planes curriculares (Circular 01 MPPE)</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.conversion-reglas.store') }}" method="POST" id="formCrearRegla" style="display: flex; flex-direction: column; overflow: hidden; margin: 0; max-height: 90vh;">
                @csrf
                <div class="modal-body-create p-4" style="max-height: calc(85vh - 140px); overflow-y: auto; flex: 1 1 auto;">
                    <div class="row g-3">

                        {{-- Plan de Origen y Plan de Destino --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="pensum_origen_id" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-sign-out-alt text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Plan Origen</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="pensum_origen_id" id="pensum_origen_id" class="form-select @error('pensum_origen_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                @foreach($pensums as $p)
                                    <option value="{{ $p->id }}" {{ old('pensum_origen_id', $origenId) == $p->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        {{ $p->codigo }} - {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pensum_origen_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="pensum_destino_id" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-sign-in-alt text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Plan Destino</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="pensum_destino_id" id="pensum_destino_id" class="form-select @error('pensum_destino_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                @foreach($pensums as $p)
                                    <option value="{{ $p->id }}" {{ old('pensum_destino_id', $destinoId) == $p->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        {{ $p->codigo }} - {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pensum_destino_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Asignatura y Grado Destino --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="grado_destino_id" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-user-graduate text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Grado Destino</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="grado_destino_id" id="grado_destino_id" class="form-select @error('grado_destino_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                <option value="" style="color: #64748b;">Seleccione grado...</option>
                                @foreach($grados as $g)
                                    <option value="{{ $g->id }}" {{ old('grado_destino_id') == $g->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        {{ $g->nombre_grado ?? ($g->numero_grado ? $g->numero_grado . '° Año' : 'Grado ' . $g->id) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grado_destino_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="area_destino_id" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-book text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Asignatura Resultante (Destino)</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="area_destino_id" id="area_destino_id" class="form-select @error('area_destino_id') is-invalid @enderror" required style="height: 46px; border-radius: 10px; color: #0f172a !important; background-color: #ffffff !important;">
                                <option value="" style="color: #64748b;">Seleccione la materia en pensum destino...</option>
                                @foreach($areas as $a)
                                    <option value="{{ $a->id }}" {{ old('area_destino_id') == $a->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                        [{{ $a->siglas }}] {{ $a->nombre_area_formacion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('area_destino_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tipo de Regla MPPE --}}
                        <div class="col-md-12 form-group-modern mb-3">
                            <label for="tipo_regla" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-cogs text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Tipo de Regla (Circular 01 MPPE)</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select name="tipo_regla" id="tipo_regla" class="form-select @error('tipo_regla') is-invalid @enderror" required style="height: 46px; border-radius: 10px;" onchange="ajustarCamposOrigenes(this.value, 'crear')">
                                <option value="directa" {{ old('tipo_regla', 'directa') == 'directa' ? 'selected' : '' }}>
                                    Homologación Directa (Pasa nota idéntica de 1 materia de origen)
                                </option>
                                <option value="promedio" {{ old('tipo_regla') == 'promedio' ? 'selected' : '' }}>
                                    Promedio (Promedia notas de 2 o más materias del plan viejo)
                                </option>
                                <option value="exonerada" {{ old('tipo_regla') == 'exonerada' ? 'selected' : '' }}>
                                    Exonerada (Se imprime «EX» y «EXONERADA», no lleva materias de origen)
                                </option>
                                <option value="no_cuenta" {{ old('tipo_regla') == 'no_cuenta' ? 'selected' : '' }}>
                                    No se toma en cuenta (PGCRP u otras sin equivalencia)
                                </option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                • Directa: 1 origen | • Promedio: 2 o más orígenes | • Exonerada / No cuenta: 0 orígenes.
                            </small>
                            @error('tipo_regla')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('origenes')
                                <div class="alert alert-danger p-2 mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Bloque de Asignaturas de Origen --}}
                        <div class="col-12" id="bloqueOrigenesCrear">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold font-weight-bold text-dark mb-2">
                                    <i class="fas fa-link text-primary mr-2 me-2"></i> Asignaturas del Plan de Origen que aportan
                                </h6>
                                <p class="text-muted small mb-3" id="ayudaOrigenesCrear">
                                    Seleccione la materia de origen que se homologará directamente a la materia destino.
                                </p>

                                {{-- Origen 1 --}}
                                <div class="row g-2 mb-2 align-items-center fila-origen" id="filaOrigen1Crear">
                                    <div class="col-md-1 text-center font-weight-bold text-muted small">#1</div>
                                    <div class="col-md-4">
                                        <select name="origenes[0][grado_origen_id]" class="form-select form-select-sm" style="border-radius: 8px;">
                                            <option value="">Grado origen...</option>
                                            @foreach($grados as $g)
                                                <option value="{{ $g->id }}" {{ old('origenes.0.grado_origen_id') == $g->id ? 'selected' : '' }}>
                                                    {{ $g->nombre_grado }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <select name="origenes[0][area_origen_id]" class="form-select form-select-sm" style="border-radius: 8px;">
                                            <option value="">Seleccione materia origen...</option>
                                            @foreach($areas as $a)
                                                <option value="{{ $a->id }}" {{ old('origenes.0.area_origen_id') == $a->id ? 'selected' : '' }}>
                                                    [{{ $a->siglas }}] {{ $a->nombre_area_formacion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Origen 2 (Para regla Promedio) --}}
                                <div class="row g-2 mb-2 align-items-center fila-origen" id="filaOrigen2Crear" style="display: none;">
                                    <div class="col-md-1 text-center font-weight-bold text-muted small">#2</div>
                                    <div class="col-md-4">
                                        <select name="origenes[1][grado_origen_id]" class="form-select form-select-sm" style="border-radius: 8px;">
                                            <option value="">Grado origen...</option>
                                            @foreach($grados as $g)
                                                <option value="{{ $g->id }}" {{ old('origenes.1.grado_origen_id') == $g->id ? 'selected' : '' }}>
                                                    {{ $g->nombre_grado }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <select name="origenes[1][area_origen_id]" class="form-select form-select-sm" style="border-radius: 8px;">
                                            <option value="">Segunda materia para promediar...</option>
                                            @foreach($areas as $a)
                                                <option value="{{ $a->id }}" {{ old('origenes.1.area_origen_id') == $a->id ? 'selected' : '' }}>
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
                            <label for="observacion" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-file-contract text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Base Legal / Observación en Certificación</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('observacion') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="observacion"
                                   name="observacion"
                                   value="{{ old('observacion', 'Equivalencia según Circular N° 01 del MPPE (enero 2024)') }}"
                                   placeholder="Cita oficial que saldrá en la certificación">
                            <small class="text-muted">Si hubo conversión, las notas oficiales deben citar esta circular.</small>
                            @error('observacion')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estado Activo --}}
                        <div class="col-12 mb-2">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input mr-2 me-2" type="checkbox" id="status_crear" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }} style="cursor: pointer;">
                                <label class="form-check-label fw-bold font-weight-bold" for="status_crear" style="cursor: pointer;">
                                    Regla activa para conversiones
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
                        <i class="fas fa-save mr-2 me-2"></i> Guardar Regla
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    function ajustarCamposOrigenes(tipo, prefijo) {
        const bloque = document.getElementById('bloqueOrigenes' + (prefijo === 'crear' ? 'Crear' : ''));
        const fila2 = document.getElementById('filaOrigen2' + (prefijo === 'crear' ? 'Crear' : ''));
        const ayuda = document.getElementById('ayudaOrigenes' + (prefijo === 'crear' ? 'Crear' : ''));

        if (!bloque) return;

        if (tipo === 'exonerada' || tipo === 'no_cuenta') {
            bloque.style.display = 'none';
        } else if (tipo === 'directa') {
            bloque.style.display = 'block';
            if (fila2) fila2.style.display = 'none';
            if (ayuda) ayuda.innerText = 'Seleccione la única materia de origen para homologación directa 1 a 1.';
        } else if (tipo === 'promedio') {
            bloque.style.display = 'block';
            if (fila2) fila2.style.display = 'flex';
            if (ayuda) ayuda.innerText = 'Seleccione las dos o más materias de origen que se promediarán para calcular la nota final.';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const selectTipo = document.getElementById('tipo_regla');
        if (selectTipo) {
            ajustarCamposOrigenes(selectTipo.value, 'crear');
        }
    });
</script>


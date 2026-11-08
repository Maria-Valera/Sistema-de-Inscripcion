<div class="modal fade" id="modalEditar{{ $institucion->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">

            {{-- Cabecera Compacta en Índigo (sin naranja) --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Editar Datos del Plantel</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">{{ $institucion->nombre }}</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.institucion.update', $institucion) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body-create p-4">
                    <div class="row g-3">
                        {{-- Código del Plantel --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="codigo_plantel_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-barcode text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Código del Plantel</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern"
                                   style="height: 46px; border-radius: 10px;"
                                   id="codigo_plantel_{{ $institucion->id }}"
                                   name="codigo_plantel"
                                   value="{{ old('codigo_plantel', $institucion->codigo_plantel) }}"
                                   required>
                        </div>

                        {{-- Nombre de la Institución --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="nombre_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-university text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Nombre de la Institución</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern"
                                   style="height: 46px; border-radius: 10px;"
                                   id="nombre_{{ $institucion->id }}"
                                   name="nombre"
                                   value="{{ old('nombre', $institucion->nombre) }}"
                                   required>
                        </div>

                        {{-- Dirección --}}
                        <div class="col-md-12 form-group-modern mb-3">
                            <label for="direccion_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-map-marker-alt text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Dirección</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern"
                                   style="height: 46px; border-radius: 10px;"
                                   id="direccion_{{ $institucion->id }}"
                                   name="direccion"
                                   value="{{ old('direccion', $institucion->direccion) }}">
                        </div>

                        {{-- Teléfono --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="telefono_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-phone text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Teléfono</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern"
                                   style="height: 46px; border-radius: 10px;"
                                   id="telefono_{{ $institucion->id }}"
                                   name="telefono"
                                   value="{{ old('telefono', $institucion->telefono) }}">
                        </div>

                        {{-- Localidad --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="localidad_id_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-globe-americas text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Parroquia / Localidad</span>
                            </label>
                            <select class="form-control-modern"
                                    style="height: 46px; border-radius: 10px;"
                                    id="localidad_id_{{ $institucion->id }}"
                                    name="localidad_id">
                                <option value="">Seleccione Localidad...</option>
                                @foreach($localidades as $loc)
                                    <option value="{{ $loc->id }}" {{ old('localidad_id', $institucion->localidad_id) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->nombre_localidad }} ({{ $loc->municipio->nombre_municipio ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Zona Educativa --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="zona_educativa_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-compass text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Zona Educativa</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern"
                                   style="height: 46px; border-radius: 10px;"
                                   id="zona_educativa_{{ $institucion->id }}"
                                   name="zona_educativa"
                                   value="{{ old('zona_educativa', $institucion->zona_educativa) }}">
                        </div>

                        {{-- Director (Persona) --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="director_persona_id_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-user-tie text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Director(a)</span>
                            </label>
                            <select class="form-control-modern"
                                    style="height: 46px; border-radius: 10px;"
                                    id="director_persona_id_{{ $institucion->id }}"
                                    name="director_persona_id">
                                <option value="">Seleccione Director(a)...</option>
                                @foreach($directores as $persona)
                                    <option value="{{ $persona->id }}" {{ old('director_persona_id', $institucion->director_persona_id) == $persona->id ? 'selected' : '' }}>
                                        {{ $persona->nombre_completo }} (CI: {{ $persona->numero_documento }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Director Encargado Checkbox --}}
                        <div class="col-md-4 form-group-modern mb-3 d-flex align-items-center pt-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="director_encargado_{{ $institucion->id }}"
                                       name="director_encargado"
                                       value="1"
                                       {{ old('director_encargado', $institucion->director_encargado) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold font-weight-bold ml-2 ms-2 text-dark" for="director_encargado_{{ $institucion->id }}">
                                    ¿Director(a) Encargado(a)?
                                </label>
                            </div>
                        </div>

                        {{-- CDCEE --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="cdcee_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-sitemap text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">CDCEE</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern"
                                   style="height: 46px; border-radius: 10px;"
                                   id="cdcee_{{ $institucion->id }}"
                                   name="cdcee"
                                   value="{{ old('cdcee', $institucion->cdcee) }}">
                        </div>

                        {{-- Dependencia --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="dependencia_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-building text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Dependencia</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern"
                                   style="height: 46px; border-radius: 10px;"
                                   id="dependencia_{{ $institucion->id }}"
                                   name="dependencia"
                                   value="{{ old('dependencia', $institucion->dependencia) }}">
                        </div>

                        {{-- Logo actual y nuevo --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="logo_{{ $institucion->id }}" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-image text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Logo Institucional</span>
                            </label>
                            <input type="file"
                                   class="form-control-modern"
                                   style="height: 46px; border-radius: 10px; padding: 0.45rem 0.75rem;"
                                   id="logo_{{ $institucion->id }}"
                                   name="logo"
                                   accept="image/*">
                            @php
                                $logoExiste = !empty($institucion->logo_ruta) && file_exists(public_path($institucion->logo_ruta));
                            @endphp
                            @if($logoExiste)
                                <div class="mt-2 d-flex align-items-center">
                                    <img src="{{ asset($institucion->logo_ruta) }}" alt="Logo" class="rounded mr-2 shadow-sm" style="max-height: 42px; max-width: 42px; object-fit: contain; border: 1px solid #e5e7eb; padding: 2px; background: #fff;">
                                    <small class="text-muted ml-2 ms-2">Logo actual cargado</small>
                                </div>
                            @endif
                        </div>

                        {{-- Activo --}}
                        <div class="col-md-4 form-group-modern mb-3 d-flex align-items-center pt-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="activo_{{ $institucion->id }}"
                                       name="activo"
                                       value="1"
                                       {{ old('activo', $institucion->activo) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold font-weight-bold ml-2 ms-2 text-primary" for="activo_{{ $institucion->id }}">
                                    Plantel Activo del Sistema
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pie de Modal con botones en Índigo y Gris --}}
                <div class="modal-footer-create py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                    <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-modal-create px-4 py-2" style="background: var(--purple, #5b6ee1); border-color: var(--purple, #5b6ee1); border-radius: 8px; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(91, 110, 225, 0.3);">
                        <i class="fas fa-save mr-1 me-1"></i> Actualizar Plantel
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

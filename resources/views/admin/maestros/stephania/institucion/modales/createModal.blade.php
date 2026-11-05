<div class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">

            {{-- Cabecera Compacta en Índigo --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-school"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Registrar Datos del Plantel</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Configuración institucional de la sede educativa</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.institucion.store') }}" method="POST" enctype="multipart/form-data" id="formCrearInstitucion">
                @csrf
                <div class="modal-body-create p-4">
                    <div class="row g-3">
                        {{-- Código del Plantel --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="codigo_plantel" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-barcode text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Código del Plantel</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('codigo_plantel') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="codigo_plantel"
                                   name="codigo_plantel"
                                   value="{{ old('codigo_plantel') }}"
                                   placeholder="Ej: OD05241808"
                                   required>
                            @error('codigo_plantel')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nombre de la Institución --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="nombre" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-university text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Nombre de la Institución</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('nombre') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="nombre"
                                   name="nombre"
                                   value="{{ old('nombre') }}"
                                   placeholder="Ej: Liceo General Juan Guillermo Iribarren"
                                   required>
                            @error('nombre')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Dirección --}}
                        <div class="col-md-12 form-group-modern mb-3">
                            <label for="direccion" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-map-marker-alt text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Dirección</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('direccion') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="direccion"
                                   name="direccion"
                                   value="{{ old('direccion') }}"
                                   placeholder="Av., Calle, Sector...">
                            @error('direccion')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="telefono" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-phone text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Teléfono</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('telefono') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="telefono"
                                   name="telefono"
                                   value="{{ old('telefono') }}"
                                   placeholder="Ej: 0255-6641234">
                            @error('telefono')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Localidad --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="localidad_id" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-globe-americas text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Parroquia / Localidad</span>
                            </label>
                            <select class="form-control-modern @error('localidad_id') is-invalid @enderror"
                                    style="height: 46px; border-radius: 10px;"
                                    id="localidad_id"
                                    name="localidad_id">
                                <option value="">Seleccione Localidad...</option>
                                @foreach($localidades as $loc)
                                    <option value="{{ $loc->id }}" {{ old('localidad_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->nombre_localidad }} ({{ $loc->municipio->nombre_municipio ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('localidad_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Zona Educativa --}}
                        <div class="col-md-4 form-group-modern mb-3">
                            <label for="zona_educativa" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-compass text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Zona Educativa</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('zona_educativa') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="zona_educativa"
                                   name="zona_educativa"
                                   value="{{ old('zona_educativa') }}"
                                   placeholder="Ej: Portuguesa">
                            @error('zona_educativa')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Director (Persona) --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="director_persona_id" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-user-tie text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Director(a)</span>
                            </label>
                            <select class="form-control-modern @error('director_persona_id') is-invalid @enderror"
                                    style="height: 46px; border-radius: 10px;"
                                    id="director_persona_id"
                                    name="director_persona_id">
                                <option value="">Seleccione Director(a)...</option>
                                @foreach($directores as $persona)
                                    <option value="{{ $persona->id }}" {{ old('director_persona_id') == $persona->id ? 'selected' : '' }}>
                                        {{ $persona->nombre_completo }} (CI: {{ $persona->numero_documento }})
                                    </option>
                                @endforeach
                            </select>
                            @error('director_persona_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Director Encargado Checkbox --}}
                        <div class="col-md-4 form-group-modern mb-3 d-flex align-items-center pt-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="director_encargado"
                                       name="director_encargado"
                                       value="1"
                                       {{ old('director_encargado') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold font-weight-bold ml-2 ms-2 text-dark" for="director_encargado">
                                    ¿Director(a) Encargado(a)?
                                </label>
                            </div>
                        </div>

                        {{-- CDCEE --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="cdcee" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-sitemap text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">CDCEE</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('cdcee') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="cdcee"
                                   name="cdcee"
                                   value="{{ old('cdcee') }}"
                                   placeholder="Centro de Desarrollo de la Calidad Educativa">
                            @error('cdcee')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Dependencia --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="dependencia" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-building text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Dependencia</span>
                            </label>
                            <input type="text"
                                   class="form-control-modern @error('dependencia') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="dependencia"
                                   name="dependencia"
                                   value="{{ old('dependencia') }}"
                                   placeholder="Ej: Pública Nacional">
                            @error('dependencia')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Logo --}}
                        <div class="col-md-8 form-group-modern mb-3">
                            <label for="logo" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-image text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Logo Institucional</span>
                            </label>
                            <input type="file"
                                   class="form-control-modern @error('logo') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px; padding: 0.45rem 0.75rem;"
                                   id="logo"
                                   name="logo"
                                   accept="image/*">
                            <small class="text-muted">Formatos: PNG, JPG, SVG. Máx 2MB.</small>
                            @error('logo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Activo --}}
                        <div class="col-md-4 form-group-modern mb-3 d-flex align-items-center pt-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="activo"
                                       name="activo"
                                       value="1"
                                       checked>
                                <label class="form-check-label fw-bold font-weight-bold ml-2 ms-2 text-primary" for="activo">
                                    Definir como Plantel Activo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pie de Modal con botones estilizados en Índigo / Gris --}}
                <div class="modal-footer-create py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                    <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-modal-create px-4 py-2" id="btnGuardarPlantel" style="background: var(--purple, #5b6ee1); border-color: var(--purple, #5b6ee1); border-radius: 8px; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(91, 110, 225, 0.3);">
                        <i class="fas fa-save mr-1 me-1"></i> Guardar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

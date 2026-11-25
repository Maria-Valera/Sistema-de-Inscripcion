<div wire:ignore.self class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-map-pin" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalCrearLabel" style="color: #1E293B; font-size: 1.1rem;">
                            Nueva Localidad
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Registro de localidad, sector o parroquia</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form wire:submit.prevent="store" id="formCrearLocalidad" class="m-0">
                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div id="contenedorAlertaCrear"></div>

                    <div class="mb-3">
                        <label for="pais_id" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-globe-americas text-primary me-1"></i>
                            <span>País</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="pais_id" wire:model.live="pais_id" id="pais_id" class="form-select"
                                style="height: 42px; border-radius: 10px; font-size: 0.95rem;" required>
                            <option value="">Seleccione un país</option>
                            @foreach ($paises as $pais)
                                <option value="{{ $pais->id }}">{{ $pais->nameES }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="estado_id" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-map-marked-alt text-primary me-1"></i>
                            <span>Estado</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="estado_id" wire:model.live="estado_id" id="estado_id" @disabled(!$pais_id)
                                class="form-select" style="height: 42px; border-radius: 10px; font-size: 0.95rem;" required>
                            <option value="">Seleccione un estado</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                            @endforeach
                        </select>
                        @error('estado_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="municipio_id" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-city text-primary me-1"></i>
                            <span>Municipio</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="municipio_id" wire:model.live="municipio_id" id="municipio_id"
                                @disabled(!$estado_id) class="form-select" style="height: 42px; border-radius: 10px; font-size: 0.95rem;" required>
                            <option value="">Seleccione un municipio</option>
                            @foreach ($municipios as $municipio)
                                <option value="{{ $municipio->id }}">{{ $municipio->nombre_municipio }}</option>
                            @endforeach
                        </select>
                        @error('municipio_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nombre_localidad_crear" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-map-pin text-primary me-1"></i>
                            <span>Nombre de la Localidad</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nombre_localidad') is-invalid @enderror"
                               style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                               id="nombre_localidad_crear"
                               wire:model.defer="nombre_localidad"
                               inputmode="text"
                               maxlength="100"
                               placeholder="Ingrese el nombre de la localidad"
                               required>
                        @error('nombre_localidad')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Footer Alineado a la Derecha -->
                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="btn-primary-modern">
                        <i class="fas fa-save me-1"></i>
                        <span>Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

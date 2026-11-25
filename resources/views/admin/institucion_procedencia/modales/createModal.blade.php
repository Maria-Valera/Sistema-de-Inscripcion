<!-- Modal Crear Institución de Procedencia -->
<div wire:ignore.self class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">

            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-school" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalCrearLabel" style="color: #1E293B; font-size: 1.1rem;">
                            Nueva Institución
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Registro de institución educativa de procedencia</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form wire:submit.prevent="store" id="formCrearInstitucionProcedencia" class="m-0">
                <div class="modal-body p-4" style="background-color: #FFFFFF;">
                    {{-- Contenedor para alertas --}}
                    <div id="contenedorAlertaCrear"></div>

                    <!-- Fila 1: País y Estado (2 columnas) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="floating-group mb-0">
                            <label for="pais_id">País <span class="text-danger">*</span></label>
                            <select name="pais_id"
                                wire:model.live="pais_id"
                                id="pais_id"
                                class="form-select @error('pais_id') is-invalid @enderror"
                                placeholder=" "
                                required>
                                <option value="">Seleccione un país</option>
                                @foreach ($paises as $pais)
                                    <option value="{{ $pais->id }}">{{ $pais->nameES }}</option>
                                @endforeach
                            </select>
                            @error('pais_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="floating-group mb-0">
                            <label for="estado_id">Estado <span class="text-danger">*</span></label>
                            <select name="estado_id"
                                wire:model.live="estado_id"
                                id="estado_id"
                                @disabled(!$pais_id)
                                class="form-select @error('estado_id') is-invalid @enderror"
                                placeholder=" "
                                required>
                                <option value="">Seleccione un estado</option>
                                @foreach ($estadosCrear as $estado)
                                    <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                                @endforeach
                            </select>
                            @error('estado_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Fila 2: Municipio y Localidad (2 columnas) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="floating-group mb-0">
                            <label for="municipio_id">Municipio <span class="text-danger">*</span></label>
                            <select name="municipio_id"
                                wire:model.live="municipio_id"
                                id="municipio_id"
                                @disabled(!$estado_id)
                                class="form-select @error('municipio_id') is-invalid @enderror"
                                placeholder=" "
                                required>
                                <option value="">Seleccione un municipio</option>
                                @foreach ($municipios as $municipio)
                                    <option value="{{ $municipio->id }}">{{ $municipio->nombre_municipio }}</option>
                                @endforeach
                            </select>
                            @error('municipio_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="floating-group mb-0">
                            <label for="localidad_id">Localidad <span class="text-danger">*</span></label>
                            <select name="localidad_id"
                                wire:model.live="localidad_id"
                                id="localidad_id"
                                @disabled(!$municipio_id)
                                class="form-select @error('localidad_id') is-invalid @enderror"
                                placeholder=" "
                                required>
                                <option value="">Seleccione una localidad</option>
                                @foreach ($localidades as $localidad)
                                    <option value="{{ $localidad->id }}">{{ $localidad->nombre_localidad }}</option>
                                @endforeach
                            </select>
                            @error('localidad_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Fila 3: Nombre de la Institución (100%) -->
                    <div class="mb-3 floating-group">
                        <label for="nombre_institucion">Nombre de la Institución <span class="text-danger">*</span></label>
                        <input type="text"
                            id="nombre_institucion"
                            class="form-control @error('nombre_institucion') is-invalid @enderror"
                            wire:model.defer="nombre_institucion"
                            maxlength="150"
                            placeholder=" "
                            required>
                        @error('nombre_institucion')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
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

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('cerrarModal', () => {
            const modalEl = document.getElementById('modalCrear');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
        });
    });
</script>

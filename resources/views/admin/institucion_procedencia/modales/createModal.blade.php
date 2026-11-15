<!-- Modal Crear Institución de Procedencia -->
<div wire:ignore.self class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
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
                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    {{-- Contenedor para alertas --}}
                    <div id="contenedorAlertaCrear"></div>

                    {{-- Select Estado --}}
                    <div class="mb-3">
                        <label for="estado_id" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-map-marker-alt text-primary me-1"></i>
                            <span>Estado</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="estado_id"
                            wire:model.live="estado_id"
                            id="estado_id"
                            class="form-select"
                            style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                            required>
                            <option value="">Seleccione un estado</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                            @endforeach
                        </select>
                        @error('estado_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Select Municipio --}}
                    <div class="mb-3">
                        <label for="municipio_id" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-city text-primary me-1"></i>
                            <span>Municipio</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="municipio_id"
                            wire:model.live="municipio_id"
                            id="municipio_id"
                            class="form-select"
                            style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                            required>
                            <option value="">Seleccione un municipio</option>
                            @foreach ($municipios as $municipio)
                                <option value="{{ $municipio->id }}">{{ $municipio->nombre_municipio }}</option>
                            @endforeach
                        </select>
                        @error('municipio_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Select Localidad --}}
                    <div class="mb-3">
                        <label for="localidad_id" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-map-pin text-primary me-1"></i>
                            <span>Localidad</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="localidad_id"
                            wire:model.live="localidad_id"
                            id="localidad_id"
                            class="form-select"
                            style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                            required>
                            <option value="">Seleccione una localidad</option>
                            @foreach ($localidades as $localidad)
                                <option value="{{ $localidad->id }}">{{ $localidad->nombre_localidad }}</option>
                            @endforeach
                        </select>
                        @error('localidad_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nombre de la Institución --}}
                    <div class="mb-3">
                        <label for="nombre_institucion" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-school text-primary me-1"></i>
                            <span>Nombre de la Institución</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            id="nombre_institucion"
                            class="form-control"
                            style="height: 42px; border-radius: 10px; font-size: 0.95rem;"
                            wire:model.defer="nombre_institucion"
                            maxlength="150"
                            placeholder="Ej: U.E. Colegio Andrés Bello"
                            required>
                        @error('nombre_institucion')
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

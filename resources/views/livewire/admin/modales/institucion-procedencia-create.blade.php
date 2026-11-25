<div>
    <!-- Modal Crear Institución de Procedencia -->
    <div wire:ignore.self class="modal fade" id="modalCrearInstitucionInscripcion" data-bs-backdrop="static"
        data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearInstitucionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
            <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; background: #FFFFFF;">

                {{-- Cabecera del modal --}}
                <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 38px; height: 38px; border-radius: 10px; background-color: #EEF2FF; color: #4338CA; font-size: 1rem;">
                            <i class="fas fa-school"></i>
                        </div>
                        <div>
                            <h5 class="modal-title m-0 fw-bold" id="modalCrearInstitucionLabel" style="color: #1E293B; font-size: 1.05rem;">Nueva Institución de Procedencia</h5>
                            <p class="text-muted m-0" style="font-size: 0.8rem;">Registrar institución educativa de origen</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                {{-- Cuerpo del modal --}}
                <div class="modal-body p-4" style="background-color: #FFFFFF;">
                    <form wire:submit.prevent="store" id="formCrearInstitucionProcedencia">
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Fila 1 (Ubicación - 2 columnas): País y Estado -->
                        <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="floating-group mb-0">
                                <label for="pais_id">País <span class="text-danger">*</span></label>
                                <select name="pais_id" wire:model.live="pais_id" id="pais_id" placeholder=" "
                                    class="form-select @error('pais_id') is-invalid @enderror" required>
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
                                <select name="estado_id" wire:model.live="estado_id" id="estado_id" placeholder=" "
                                    @disabled(!$pais_id)
                                    class="form-select @error('estado_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un estado</option>
                                    @foreach ($estados as $estado)
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

                        <!-- Fila 2 (Ubicación - 2 columnas): Municipio y Localidad -->
                        <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="floating-group mb-0">
                                <label for="municipio_id">Municipio <span class="text-danger">*</span></label>
                                <select name="municipio_id" wire:model.live="municipio_id" id="municipio_id" placeholder=" "
                                    @disabled(!$estado_id)
                                    class="form-select @error('municipio_id') is-invalid @enderror" required>
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
                                <select name="localidad_id" wire:model.live="localidad_id" id="localidad_id" placeholder=" "
                                    @disabled(!$municipio_id)
                                    class="form-select @error('localidad_id') is-invalid @enderror" required>
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

                        <!-- Fila 3 (Nombre - Ancho completo) -->
                        <div class="mb-3 floating-group">
                            <label for="nombre_institucion">Nombre de la Institución <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @error('nombre_institucion') is-invalid @enderror"
                                id="nombre_institucion"
                                name="nombre_institucion"
                                wire:model.defer="nombre_institucion" maxlength="150"
                                placeholder=" " required>
                            @error('nombre_institucion')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Botones --}}
                        <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-0 pt-3 pb-0 border-top mt-3" style="border-top-color: #F1F5F9 !important;">
                            <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-primary-modern" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="store">
                                    <i class="fas fa-save me-1"></i> Guardar
                                </span>
                                <span wire:loading wire:target="store">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Guardando...
                                </span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        function limpiarBackdropModalInstitucion() {
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 300);
        }

        function cerrarModalInstitucion() {
            const modalEl = document.getElementById('modalCrearInstitucionInscripcion');
            if (!modalEl) {
                limpiarBackdropModalInstitucion();
                return;
            }

            if (window.$ && typeof $(modalEl).modal === 'function') {
                $(modalEl).modal('hide');
            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
            }

            limpiarBackdropModalInstitucion();
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('cerrarModalCrearInstitucion', () => {
                cerrarModalInstitucion();
            });

            Livewire.on('cerrarModalDespuesDe', (data) => {
                let delay = 300;
                if (data) {
                    if (typeof data === 'number') delay = data;
                    else if (data.delay) delay = data.delay;
                    else if (Array.isArray(data) && data[0] && data[0].delay) delay = data[0].delay;
                }
                setTimeout(() => {
                    cerrarModalInstitucion();
                }, delay);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Limpieza inmediata al cargar
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            const modalEl = document.getElementById('modalCrearInstitucionInscripcion');
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function () {
                    limpiarBackdropModalInstitucion();
                });
            }
        });
    </script>
@endpush

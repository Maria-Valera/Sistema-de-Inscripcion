<div>
    <div wire:ignore.self class="modal fade" id="modalCrearEstado" data-bs-backdrop="static"
        data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
            <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; background: #FFFFFF;">
                <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 38px; height: 38px; border-radius: 10px; background-color: #EEF2FF; color: #4338CA; font-size: 1rem;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h5 class="modal-title m-0 fw-bold" style="color: #1E293B; font-size: 1.05rem;">Nuevo Estado</h5>
                            <p class="text-muted m-0" style="font-size: 0.8rem;">Registrar nuevo estado en el sistema</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4" style="background-color: #FFFFFF;">
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

                    <form wire:submit.prevent="store">
                        <!-- Fila 1: País y Nombre del Estado (50% y 50%) -->
                        <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="floating-group mb-0">
                                <label for="pais_id_estado">País <span class="text-danger">*</span></label>
                                <select wire:model.live="pais_id" id="pais_id_estado"
                                    class="form-select @error('pais_id') is-invalid @enderror" placeholder=" " required>
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
                                <label for="nombre_estado">Nombre del Estado <span class="text-danger">*</span></label>
                                <input type="text"
                                    wire:model.defer="nombre_estado" id="nombre_estado"
                                    class="form-control @error('nombre_estado') is-invalid @enderror"
                                    placeholder=" " required>
                                @error('nombre_estado')
                                    <div class="invalid-feedback-modern">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

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
        function limpiarBackdropModalEstado() {
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 300);
        }

        function cerrarModalEstado() {
            const modalEl = document.getElementById('modalCrearEstado');
            if (!modalEl) {
                limpiarBackdropModalEstado();
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

            limpiarBackdropModalEstado();
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('cerrarModalCrearEstado', () => {
                cerrarModalEstado();
            });

            Livewire.on('cerrarModalDespuesDe', (data) => {
                let delay = 300;
                if (data) {
                    if (typeof data === 'number') delay = data;
                    else if (data.delay) delay = data.delay;
                    else if (Array.isArray(data) && data[0] && data[0].delay) delay = data[0].delay;
                }
                setTimeout(() => {
                    cerrarModalEstado();
                }, delay);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            const modalEl = document.getElementById('modalCrearEstado');
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function () {
                    limpiarBackdropModalEstado();
                });
            }
        });
    </script>
@endpush

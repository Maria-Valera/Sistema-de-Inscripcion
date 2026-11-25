<div class="modal fade" id="modalCrearAnioEscolar" data-bs-backdrop="static" data-backdrop="static" data-bs-keyboard="false" data-keyboard="false" tabindex="-1"
    aria-labelledby="modalCrearAnioEscolarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">

            {{-- Cabecera con Degradado Azul / Morado (Estilo Lapsos / Autoridades Firmantes) --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none;">
                <div class="modal-icon-create" style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.15rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" id="modalCrearAnioEscolarLabel" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Nuevo Año Escolar</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Apertura o registro de un período lectivo</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.85; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-create p-4">
                <form id="formAnioEscolar" action="{{ route('admin.anio_escolar.modales.store') }}" method="POST"
                    class="rd-prevent-double-submit">
                    @csrf
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="alert alert-info d-flex align-items-center mb-3"
                        style="font-size: 0.875rem; padding: 0.75rem; border-radius: 8px;">
                        <i class="fas fa-info-circle mr-2 me-2"></i>
                        <span>Las fechas sugeridas abarcan el ciclo escolar de septiembre a junio. Puede ajustarlas según el calendario oficial.</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="inicio_anio_escolar" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-alt text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Inicio</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="date" class="form-control-modern @error('inicio_anio_escolar') is-invalid @enderror"
                                style="height: 46px; border-radius: 10px;"
                                id="inicio_anio_escolar"
                                name="inicio_anio_escolar" required>
                            @error('inicio_anio_escolar')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle mr-1 me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="cierre_anio_escolar" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-check text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Cierre</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="date" class="form-control-modern @error('cierre_anio_escolar') is-invalid @enderror"
                                style="height: 46px; border-radius: 10px;"
                                id="cierre_anio_escolar"
                                name="cierre_anio_escolar" required>
                            @error('cierre_anio_escolar')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle mr-1 me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div id="error_validacion_fechas" class="alert alert-danger py-2 px-3 small border-0 d-none" style="border-radius: 8px;">
                        <i class="fas fa-exclamation-triangle mr-1 me-1"></i>
                        <span id="texto_error_validacion"></span>
                    </div>

                    <div id="info_duracion" class="alert alert-success py-2 px-3 small border-0 d-none"
                        style="background-color: #ecfdf5; color: #047857; border-radius: 8px; margin-top: 0.5rem;">
                        <i class="fas fa-clock mr-1 me-1"></i>
                        <strong>Duración estimada:</strong> <span id="duracion_texto"></span>
                    </div>

                    {{-- Pie de Modal (Estilo Lapsos / Autoridades Firmantes) --}}
                    <div class="modal-footer-create py-3 px-4 d-flex justify-content-end align-items-center border-top mt-3" style="background: #f8fafc; gap: 0.75rem; margin: 1.5rem -1.5rem -1.5rem -1.5rem;">
                        <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-times mr-1 me-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary btn-modal-save rd-submit-btn px-4 py-2" id="btn_guardar_anio" style="border-radius: 8px; font-weight: 600; background: var(--purple, #5b6ee1); border-color: var(--purple, #5b6ee1);">
                            <i class="fas fa-save mr-1 me-1"></i>
                            Guardar Año Escolar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalCrearAnioEscolar');
        if (!modal) return;

        const inputInicio = modal.querySelector('#inicio_anio_escolar');
        const inputCierre = modal.querySelector('#cierre_anio_escolar');
        const errorDiv = modal.querySelector('#error_validacion_fechas');
        const textoError = modal.querySelector('#texto_error_validacion');
        const infoDuracion = modal.querySelector('#info_duracion');
        const duracionTexto = modal.querySelector('#duracion_texto');
        const btnGuardar = modal.querySelector('#btn_guardar_anio');
        const formAnio = modal.querySelector('#formAnioEscolar');

        // Garantizar token CSRF antes de enviar
        if (formAnio) {
            formAnio.addEventListener('submit', function (e) {
                const tokenInputs = formAnio.querySelectorAll('input[name="_token"]');
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                const csrfValor = metaToken ? metaToken.getAttribute('content') : null;

                if (csrfValor) {
                    tokenInputs.forEach(input => {
                        if (!input.value) {
                            input.value = csrfValor;
                        }
                    });
                }
            });
        }

        function prellenarFechas() {
            const hoy = new Date();
            const anioActual = hoy.getFullYear();
            const mesActual = hoy.getMonth();
            const anioInicio = mesActual >= 7 ? anioActual : anioActual - 1;
            const fechaInicio = `${anioInicio}-09-01`;
            const fechaCierre = `${anioInicio + 1}-06-30`;
            if (inputInicio && !inputInicio.value) inputInicio.value = fechaInicio;
            if (inputCierre && !inputCierre.value) inputCierre.value = fechaCierre;
            validarFechas();
        }

        modal.addEventListener('show.bs.modal', prellenarFechas);
        modal.addEventListener('shown.bs.modal', prellenarFechas);

        if (typeof window.$ !== 'undefined' && typeof window.$.fn.modal !== 'undefined') {
            window.$(modal).on('show.bs.modal', prellenarFechas);
        }

        function validarFechas() {
            if (!inputInicio || !inputCierre) return;

            const inicio = new Date(inputInicio.value + 'T00:00:00');
            const cierre = new Date(inputCierre.value + 'T00:00:00');
            if (errorDiv) errorDiv.classList.add('d-none');
            inputInicio.classList.remove('is-invalid');
            inputCierre.classList.remove('is-invalid');
            if (btnGuardar) btnGuardar.disabled = false;

            if (!inputInicio.value || !inputCierre.value) {
                if (infoDuracion) infoDuracion.classList.add('d-none');
                return;
            }

            if (cierre <= inicio) {
                mostrarError('La fecha de cierre debe ser posterior a la fecha de inicio.');
                inputCierre.classList.add('is-invalid');
                if (btnGuardar) btnGuardar.disabled = true;
                return;
            }

            const diffTiempo = cierre - inicio;
            const dias = Math.floor(diffTiempo / (1000 * 60 * 60 * 24));
            const meses = Math.floor(dias / 30);

            if (meses < 6) {
                mostrarError('El Año Escolar debe tener una duración mínima de 6 meses.');
                inputCierre.classList.add('is-invalid');
                if (btnGuardar) btnGuardar.disabled = true;
                return;
            }

            if (duracionTexto) duracionTexto.textContent = `${meses} meses (${dias} días)`;
            if (infoDuracion) infoDuracion.classList.remove('d-none');
        }

        function mostrarError(mensaje) {
            if (textoError) textoError.textContent = mensaje;
            if (errorDiv) errorDiv.classList.remove('d-none');
            if (infoDuracion) infoDuracion.classList.add('d-none');
        }

        if (inputInicio) inputInicio.addEventListener('change', validarFechas);
        if (inputCierre) inputCierre.addEventListener('change', validarFechas);

        modal.addEventListener('hidden.bs.modal', function() {
            if (formAnio) formAnio.reset();
            if (errorDiv) errorDiv.classList.add('d-none');
            if (infoDuracion) infoDuracion.classList.add('d-none');
            if (inputInicio) inputInicio.classList.remove('is-invalid');
            if (inputCierre) inputCierre.classList.remove('is-invalid');
            if (btnGuardar) btnGuardar.disabled = false;
        });
    });
</script>

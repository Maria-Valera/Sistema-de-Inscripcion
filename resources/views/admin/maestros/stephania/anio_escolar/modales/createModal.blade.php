<div class="modal fade" id="modalCrearAnioEscolar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">

            {{-- Cabecera Compacta en Índigo --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Nuevo Año Escolar</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Apertura o registro de un período lectivo</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.anio-escolar.store') }}" method="POST" id="formCrearAnioEscolar">
                @csrf
                <div class="modal-body-create p-4">
                    <div class="row g-3">
                        {{-- Inicio Año Escolar --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="inicio_anio_escolar" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-alt text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Inicio</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('inicio_anio_escolar') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="inicio_anio_escolar"
                                   name="inicio_anio_escolar"
                                   value="{{ old('inicio_anio_escolar') }}"
                                   required>
                            @error('inicio_anio_escolar')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cierre Año Escolar --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="cierre_anio_escolar" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-check text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Cierre</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('cierre_anio_escolar') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="cierre_anio_escolar"
                                   name="cierre_anio_escolar"
                                   value="{{ old('cierre_anio_escolar') }}"
                                   required>
                            @error('cierre_anio_escolar')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Extensión Año Escolar --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="extencion_anio_escolar" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-week text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Extensión</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('extencion_anio_escolar') is-invalid @enderror"
                                   style="height: 46px; border-radius: 10px;"
                                   id="extencion_anio_escolar"
                                   name="extencion_anio_escolar"
                                   value="{{ old('extencion_anio_escolar') }}">
                            <small class="text-muted d-block mt-1">Opcional. Aplica en prórrogas del calendario.</small>
                            @error('extencion_anio_escolar')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estado --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="status" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-toggle-on text-primary mr-2 me-2"></i>
                                <span class="fw-bold font-weight-bold">Estado</span>
                                <span class="text-danger ml-1 ms-1">*</span>
                            </label>
                            <select class="form-control-modern form-select @error('status') is-invalid @enderror"
                                    style="height: 46px; border-radius: 10px;"
                                    id="status"
                                    name="status"
                                    required>
                                <option value="Inactivo" {{ old('status') === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                <option value="Activo" {{ old('status', 'Activo') === 'Activo' ? 'selected' : '' }}>Activo</option>
                                <option value="Extendido" {{ old('status') === 'Extendido' ? 'selected' : '' }}>Extendido</option>
                            </select>
                            <small class="text-info d-block mt-1">
                                <i class="fas fa-info-circle mr-1"></i>Si seleccionas <strong>Activo</strong>, los demás años pasarán a <strong>Inactivo</strong> automáticamente.
                            </small>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Contenedores de Validación Dinámica y Duración --}}
                        <div class="col-12">
                            <div id="error_validacion_fechas_crear" class="alert alert-danger py-2 px-3 small border-0 d-none" style="border-radius: 8px;">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <span id="texto_error_fechas_crear"></span>
                            </div>
                            <div id="info_duracion_crear" class="alert alert-success py-2 px-3 small border-0 d-none" style="background-color: #ecfdf5; color: #047857; border-radius: 8px;">
                                <i class="fas fa-clock mr-1"></i>
                                <strong>Duración estimada:</strong> <span id="texto_duracion_crear"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pie de Modal --}}
                <div class="modal-footer-create py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f8fafc; gap: 0.75rem;">
                    <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-modal-save px-4 py-2" id="btn_guardar_anio_crear" style="border-radius: 8px; font-weight: 600; background: var(--purple, #5b6ee1); border-color: var(--purple, #5b6ee1);">
                        <i class="fas fa-save mr-1 me-1"></i>
                        Guardar Año Escolar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalCrear = document.getElementById('modalCrearAnioEscolar');
        if (!modalCrear) return;

        const inputInicio = modalCrear.querySelector('#inicio_anio_escolar');
        const inputCierre = modalCrear.querySelector('#cierre_anio_escolar');
        const errorDiv = modalCrear.querySelector('#error_validacion_fechas_crear');
        const textoError = modalCrear.querySelector('#texto_error_fechas_crear');
        const infoDuracion = modalCrear.querySelector('#info_duracion_crear');
        const textoDuracion = modalCrear.querySelector('#texto_duracion_crear');
        const btnGuardar = modalCrear.querySelector('#btn_guardar_anio_crear');

        function validarFechasCrear() {
            if (!inputInicio || !inputCierre) return;

            errorDiv.classList.add('d-none');
            inputInicio.classList.remove('is-invalid');
            inputCierre.classList.remove('is-invalid');
            if (btnGuardar) btnGuardar.disabled = false;

            if (!inputInicio.value || !inputCierre.value) {
                if (infoDuracion) infoDuracion.classList.add('d-none');
                return;
            }

            const inicio = new Date(inputInicio.value + 'T00:00:00');
            const cierre = new Date(inputCierre.value + 'T00:00:00');

            if (cierre <= inicio) {
                textoError.textContent = 'La fecha de cierre debe ser posterior a la fecha de inicio.';
                errorDiv.classList.remove('d-none');
                if (infoDuracion) infoDuracion.classList.add('d-none');
                inputCierre.classList.add('is-invalid');
                if (btnGuardar) btnGuardar.disabled = true;
                return;
            }

            const diffTiempo = cierre - inicio;
            const dias = Math.floor(diffTiempo / (1000 * 60 * 60 * 24));
            const meses = Math.floor(dias / 30);
            textoDuracion.textContent = `${meses} meses (${dias} días)`;
            if (infoDuracion) infoDuracion.classList.remove('d-none');
        }

        if (inputInicio) inputInicio.addEventListener('change', validarFechasCrear);
        if (inputCierre) inputCierre.addEventListener('change', validarFechasCrear);

        // Al abrir modal, si están vacías, sugerir fechas estándar del ciclo venezolano (Septiembre a Junio)
        const modalTriggerEvents = ['show.bs.modal'];
        modalTriggerEvents.forEach(evt => {
            modalCrear.addEventListener(evt, function () {
                if (!inputInicio.value && !inputCierre.value) {
                    const hoy = new Date();
                    const anioActual = hoy.getFullYear();
                    const mesActual = hoy.getMonth();
                    const anioInicio = mesActual >= 7 ? anioActual : anioActual - 1;
                    inputInicio.value = `${anioInicio}-09-01`;
                    inputCierre.value = `${anioInicio + 1}-06-30`;
                }
                validarFechasCrear();
            });
        });
    });
</script>


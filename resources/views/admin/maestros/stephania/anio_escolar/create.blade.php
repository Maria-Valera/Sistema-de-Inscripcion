@extends('adminlte::page')

@section('title', 'Nuevo año escolar · Configuración')

@section('classes_body', 'sidebar-collapse')

@section('css')
    @include('asistente_anio._estilos')
@stop

@section('content')
    <div class="asistente">
        {{-- Stepper Superior: (1) Año escolar [Activo], (2) Lapsos, (3) Calendario académico --}}
        @include('asistente_anio._pasos', ['pasoActual' => 1])

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2 me-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Tarjeta Principal --}}
        <div class="asistente-tarjeta">
            {{-- Cabecera Morada / Azul con Subtítulo Dinámico --}}
            <div class="asistente-cabecera">
                <h1><i class="fas fa-calendar-plus me-2 mr-2"></i> Año escolar</h1>
                <p id="subtitulo_fechas">Del 01/09/{{ date('Y') }} al 30/06/{{ date('Y') + 1 }}. Configura las fechas de apertura y cierre del nuevo período.</p>
            </div>

            {{-- Cuerpo del Formulario --}}
            <div class="asistente-cuerpo">
                {{-- Alerta Informativa --}}
                <div class="nota-info">
                    <i class="fas fa-info-circle me-1 mr-1"></i>
                    Configura las fechas del nuevo año escolar. En el siguiente paso podrás definir los lapsos y cortes.
                </div>

                <form action="{{ route('admin.anio-escolar.store') }}" method="POST" id="form-crear-anio-asistente">
                    @csrf

                    <div class="row g-3">
                        {{-- Fecha de Inicio --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="inicio_anio_escolar" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-alt text-primary me-2 mr-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Inicio</span>
                                <span class="text-danger ms-1 ml-1">*</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('inicio_anio_escolar') is-invalid campo-invalido @enderror"
                                   id="inicio_anio_escolar"
                                   name="inicio_anio_escolar"
                                   value="{{ old('inicio_anio_escolar') }}"
                                   required>
                            @error('inicio_anio_escolar')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Fecha de Cierre --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="cierre_anio_escolar" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-check text-primary me-2 mr-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Cierre</span>
                                <span class="text-danger ms-1 ml-1">*</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('cierre_anio_escolar') is-invalid campo-invalido @enderror"
                                   id="cierre_anio_escolar"
                                   name="cierre_anio_escolar"
                                   value="{{ old('cierre_anio_escolar') }}"
                                   required>
                            @error('cierre_anio_escolar')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Fecha de Extensión --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="extencion_anio_escolar" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-week text-primary me-2 mr-2"></i>
                                <span class="fw-bold font-weight-bold">Fecha de Extensión</span>
                            </label>
                            <input type="date"
                                   class="form-control-modern @error('extencion_anio_escolar') is-invalid campo-invalido @enderror"
                                   id="extencion_anio_escolar"
                                   name="extencion_anio_escolar"
                                   value="{{ old('extencion_anio_escolar') }}">
                            <small class="text-muted d-block mt-1">Opcional. Aplica en prórrogas del calendario.</small>
                            @error('extencion_anio_escolar')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Estado --}}
                        <div class="col-md-6 form-group-modern mb-3">
                            <label for="status" class="form-label-modern d-flex align-items-center mb-1">
                                <i class="fas fa-toggle-on text-primary me-2 mr-2"></i>
                                <span class="fw-bold font-weight-bold">Estado</span>
                                <span class="text-danger ms-1 ml-1">*</span>
                            </label>
                            <select class="form-control-modern @error('status') is-invalid campo-invalido @enderror"
                                    id="status"
                                    name="status"
                                    required>
                                <option value="Activo" {{ old('status', 'Activo') === 'Activo' ? 'selected' : '' }}>Activo</option>
                                <option value="Inactivo" {{ old('status') === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                <option value="Extendido" {{ old('status') === 'Extendido' ? 'selected' : '' }}>Extendido</option>
                            </select>
                            <small class="text-info d-block mt-1">
                                <i class="fas fa-info-circle me-1 mr-1"></i>Si seleccionas <strong>Activo</strong>, los demás años pasarán a <strong>Inactivo</strong> automáticamente.
                            </small>
                            @error('status')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Duración / Validación dinámica --}}
                        <div class="col-12">
                            <div id="error_validacion_fechas" class="alert alert-danger py-2 px-3 small border-0 d-none" style="border-radius: 8px;">
                                <i class="fas fa-exclamation-triangle me-1 mr-1"></i>
                                <span id="texto_error_fechas"></span>
                            </div>
                            <div id="info_duracion" class="alert alert-success py-2 px-3 small border-0 d-none" style="background-color: #ecfdf5; color: #047857; border-radius: 8px;">
                                <i class="fas fa-clock me-1 mr-1"></i>
                                <strong>Duración estimada:</strong> <span id="texto_duracion"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Botón de Acción --}}
                    <div class="acciones">
                        <a href="{{ route('admin.anio-escolar.seleccionar') }}" class="btn btn-light border" style="border-radius: 10px; padding: 0.75rem 1.25rem; font-weight: 600;">
                            <i class="fas fa-arrow-left me-1 mr-1"></i> Volver a selección
                        </a>
                        <button type="submit" class="btn-asistente" id="btn-guardar-seguir">
                            Guardar y continuar a Lapsos <i class="fas fa-arrow-right ms-1 ml-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputInicio = document.getElementById('inicio_anio_escolar');
        const inputCierre = document.getElementById('cierre_anio_escolar');
        const subtitulo = document.getElementById('subtitulo_fechas');
        const errorDiv = document.getElementById('error_validacion_fechas');
        const textoError = document.getElementById('texto_error_fechas');
        const infoDuracion = document.getElementById('info_duracion');
        const textoDuracion = document.getElementById('texto_duracion');
        const btnGuardar = document.getElementById('btn-guardar-seguir');

        // Prellenar con fechas estándar si están vacías
        if (inputInicio && inputCierre && !inputInicio.value && !inputCierre.value) {
            const hoy = new Date();
            const anioActual = hoy.getFullYear();
            const mesActual = hoy.getMonth();
            const anioInicio = mesActual >= 7 ? anioActual : anioActual - 1;
            inputInicio.value = `${anioInicio}-09-01`;
            inputCierre.value = `${anioInicio + 1}-06-30`;
        }

        function formatearFecha(cadenaFecha) {
            if (!cadenaFecha) return '';
            const partes = cadenaFecha.split('-');
            if (partes.length === 3) {
                return `${partes[2]}/${partes[1]}/${partes[0]}`;
            }
            return cadenaFecha;
        }

        function actualizarSubtituloYValidacion() {
            if (!inputInicio || !inputCierre) return;

            const valInicio = inputInicio.value;
            const valCierre = inputCierre.value;

            // Actualizar subtítulo dinámico de la cabecera
            if (valInicio && valCierre && subtitulo) {
                subtitulo.textContent = `Del ${formatearFecha(valInicio)} al ${formatearFecha(valCierre)}. Configura las fechas de apertura y cierre del nuevo período.`;
            }

            if (errorDiv) errorDiv.classList.add('d-none');
            inputInicio.classList.remove('campo-invalido', 'is-invalid');
            inputCierre.classList.remove('campo-invalido', 'is-invalid');
            if (btnGuardar) btnGuardar.disabled = false;

            if (!valInicio || !valCierre) {
                if (infoDuracion) infoDuracion.classList.add('d-none');
                return;
            }

            const inicio = new Date(valInicio + 'T00:00:00');
            const cierre = new Date(valCierre + 'T00:00:00');

            if (cierre <= inicio) {
                if (textoError) textoError.textContent = 'La fecha de cierre debe ser posterior a la fecha de inicio.';
                if (errorDiv) errorDiv.classList.remove('d-none');
                if (infoDuracion) infoDuracion.classList.add('d-none');
                inputCierre.classList.add('campo-invalido', 'is-invalid');
                if (btnGuardar) btnGuardar.disabled = true;
                return;
            }

            const diffTiempo = cierre - inicio;
            const dias = Math.floor(diffTiempo / (1000 * 60 * 60 * 24));
            const meses = Math.floor(dias / 30);

            if (meses < 6) {
                if (textoError) textoError.textContent = 'El año escolar debe tener una duración mínima recomendada de 6 meses.';
                if (errorDiv) errorDiv.classList.remove('d-none');
            }

            if (textoDuracion) textoDuracion.textContent = `${meses} meses (${dias} días)`;
            if (infoDuracion) infoDuracion.classList.remove('d-none');
        }

        if (inputInicio) inputInicio.addEventListener('input', actualizarSubtituloYValidacion);
        if (inputCierre) inputCierre.addEventListener('input', actualizarSubtituloYValidacion);
        if (inputInicio) inputInicio.addEventListener('change', actualizarSubtituloYValidacion);
        if (inputCierre) inputCierre.addEventListener('change', actualizarSubtituloYValidacion);

        actualizarSubtituloYValidacion();
    });
</script>
@stop


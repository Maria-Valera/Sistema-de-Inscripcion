@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Paso 1: Crear Docente')

@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row align-items-center justify-content-between text-center text-md-start gap-3">
            <div class="header-title d-flex flex-column flex-md-row align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Paso 1 de 2: Crear Nuevo Docente</h1>
                    <p class="title-subtitle mb-0">Registra la información personal y laboral del docente</p>
                </div>
            </div>
            <a href="{{ route('admin.docente.index') }}" class="btn-hero-white w-100 w-md-auto justify-content-center mt-3 mt-md-0" title="Volver al listado">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @if ($errors->any())
            <div class="alerts-container mb-3">
                <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Errores de Validación</h4>
                        <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                            @foreach ($errors->all() as $error)
                                <li style="font-size: 0.875rem;">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="card-modern shadow-sm">
            <div class="card-header-modern border-bottom px-4 py-3 bg-white" style="border-bottom: 1px solid #E2E8F0 !important;">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Formulario de Registro</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">Complete los datos del docente para continuar al Paso 2</p>
                    </div>
                </div>
            </div> 

            <div class="card-body-modern p-4">
                <form id="docenteForm" action="{{ route('admin.docente.store') }}" method="POST" class="form-modern">
                    @csrf

                    <!-- 1. Datos de Identificación -->
                    <div class="section-title-modern mb-3" style="font-size: 0.95rem; font-weight: 700; color: #1E293B;">
                        <i class="fas fa-id-badge me-2" style="color: #4A6FC1;"></i> Datos de Identificación
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-id-card-alt text-primary me-1"></i>
                                Tipo de Documento <span class="text-danger">*</span>
                            </label>
                            <select name="tipo_documento_id" id="tipo_documento_id"
                                class="form-select @error('tipo_documento_id') is-invalid @enderror" required>
                                <option value="" selected disabled>Seleccione</option>
                                @foreach ($tipoDocumentos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('tipo_documento_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_documento_id')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern text-muted">
                                <i class="fas fa-info-circle"></i> Seleccione V, E
                            </small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-id-card text-primary me-1"></i>
                                Cédula <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="numero_documento" inputmode="numeric" id="numero_documento"
                                pattern="[0-9]+" maxlength="8" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                required class="form-control @error('numero_documento') is-invalid @enderror"
                                value="{{ old('numero_documento') }}" placeholder="12345678">
                            @error('numero_documento')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern text-muted">
                                <i class="fas fa-info-circle"></i> Solo números, máximo 8 dígitos
                            </small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-hashtag text-primary me-1"></i>
                                Código
                            </label>
                            <input type="text" name="codigo" inputmode="numeric" pattern="[0-9]+" maxlength="9"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                class="form-control @error('codigo') is-invalid @enderror"
                                value="{{ old('codigo') }}" placeholder="79322403">
                            @error('codigo')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern text-muted">
                                <i class="fas fa-info-circle"></i> Código interno opcional
                            </small>
                        </div>
                    </div>

                    <!-- Alerta de Persona Encontrada en el Sistema -->
                    <div id="alerta-persona-encontrada" class="alert alert-info d-none align-items-center mt-2 mb-3" role="alert" style="border-left: 4px solid #0284c7; background-color: #f0f9ff; color: #0369a1; border-radius: 10px;">
                        <i class="fas fa-info-circle fa-lg me-2" style="color: #0284c7;"></i>
                        <div>
                            <strong>Persona encontrada en el sistema.</strong>
                            <span>Los datos personales han sido cargados para evitar inconsistencias.</span>
                        </div>
                    </div>

                    <!-- 2. Datos Personales con Desbloqueo Progresivo -->
                    <div class="section-title-modern mb-3 mt-4" style="font-size: 0.95rem; font-weight: 700; color: #1E293B;">
                        <i class="fas fa-user me-2" style="color: #4A6FC1;"></i> Datos Personales
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-user text-primary me-1"></i>
                                Primer nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="primer_nombre" id="primer_nombre"
                                class="text-capitalize form-control @error('primer_nombre') is-invalid @enderror"
                                value="{{ old('primer_nombre') }}" placeholder="Ej: Juan" required>
                            @error('primer_nombre')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-user text-muted me-1"></i>
                                Segundo nombre
                            </label>
                            <input type="text" name="segundo_nombre" id="segundo_nombre"
                                class="text-capitalize form-control @error('segundo_nombre') is-invalid @enderror"
                                value="{{ old('segundo_nombre') }}" placeholder="Ej: Carlos" disabled>
                            @error('segundo_nombre')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern text-muted">
                                <i class="fas fa-lock me-1"></i> Se desbloquea con el primer nombre
                            </small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-user text-muted me-1"></i>
                                Tercer nombre
                            </label>
                            <input type="text" name="tercer_nombre" id="tercer_nombre"
                                class="text-capitalize form-control @error('tercer_nombre') is-invalid @enderror"
                                value="{{ old('tercer_nombre') }}" placeholder="Opcional" disabled>
                            @error('tercer_nombre')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern text-muted">
                                <i class="fas fa-lock me-1"></i> Se desbloquea con el segundo nombre
                            </small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-user-tag text-primary me-1"></i>
                                Primer apellido <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="primer_apellido" id="primer_apellido"
                                class="text-capitalize form-control @error('primer_apellido') is-invalid @enderror"
                                value="{{ old('primer_apellido') }}" placeholder="Ej: Pérez" required>
                            @error('primer_apellido')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-user-tag text-muted me-1"></i>
                                Segundo apellido
                            </label>
                            <input type="text" name="segundo_apellido" id="segundo_apellido"
                                class="text-capitalize form-control @error('segundo_apellido') is-invalid @enderror"
                                value="{{ old('segundo_apellido') }}" placeholder="Ej: Gómez">
                            @error('segundo_apellido')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-venus-mars text-primary me-1"></i>
                                Género <span class="text-danger">*</span>
                            </label>
                            <select name="genero" id="genero"
                                class="form-select @error('genero') is-invalid @enderror" required>
                                <option value="">Seleccione</option>
                                @foreach ($generos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('genero') == $item->id ? 'selected' : '' }}>
                                        {{ $item->genero }}
                                    </option>
                                @endforeach
                            </select>
                            @error('genero')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- 3. Información de Contacto y Dependencia -->
                    <div class="section-title-modern mb-3 mt-4" style="font-size: 0.95rem; font-weight: 700; color: #1E293B;">
                        <i class="fas fa-address-book me-2" style="color: #4A6FC1;"></i> Contacto y Dependencia
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-calendar-alt text-primary me-1"></i>
                                Fecha de nacimiento <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                                class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                value="{{ old('fecha_nacimiento') }}" max="{{ date('Y-m-d') }}" required>
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern text-muted">
                                <i class="fas fa-info-circle"></i> Mayor de 18 años
                            </small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-phone text-primary me-1"></i>
                                Teléfono Principal <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <select name="prefijo_id" id="prefijo_id"
                                    class="form-select @error('prefijo_id') is-invalid @enderror" style="max-width: 105px; border-radius: 10px 0 0 10px;" required>
                                    <option value="" selected disabled>Prefijo</option>
                                    @foreach ($prefijos as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('prefijo_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->prefijo }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="primer_telefono" id="primer_telefono" inputmode="numeric"
                                    pattern="[0-9]*" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                    required class="form-control @error('primer_telefono') is-invalid @enderror"
                                    style="border-radius: 0 10px 10px 0;"
                                    value="{{ old('primer_telefono') }}" placeholder="1234567">
                            </div>
                            @error('prefijo_id')
                                <div class="invalid-feedback-modern d-block">{{ $message }}</div>
                            @enderror
                            @error('primer_telefono')
                                <div class="invalid-feedback-modern d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-phone text-muted me-1"></i>
                                Segundo Teléfono
                            </label>
                            <div class="input-group">
                                <select name="prefijo_dos_id" id="prefijo_dos_id"
                                    class="form-select @error('prefijo_dos_id') is-invalid @enderror" style="max-width: 105px; border-radius: 10px 0 0 10px;">
                                    <option value="" selected disabled>Prefijo</option>
                                    @foreach ($prefijos as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('prefijo_dos_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->prefijo }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="telefono_dos" id="telefono_dos" inputmode="numeric"
                                    pattern="[0-9]*" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                    class="form-control @error('telefono_dos') is-invalid @enderror"
                                    style="border-radius: 0 10px 10px 0;"
                                    value="{{ old('telefono_dos') }}" placeholder="1234567">
                            </div>
                            @error('prefijo_dos_id')
                                <div class="invalid-feedback-modern d-block">{{ $message }}</div>
                            @enderror
                            @error('telefono_dos')
                                <div class="invalid-feedback-modern d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-envelope text-primary me-1"></i>
                                Correo electrónico
                            </label>
                            <input type="email" name="correo" id="correo"
                                class="form-control @error('correo') is-invalid @enderror"
                                value="{{ old('correo') }}" placeholder="ejemplo@correo.com">
                            @error('correo')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern text-muted">
                                <i class="fas fa-info-circle"></i> Para notificaciones
                            </small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-building text-primary me-1"></i>
                                Dependencia
                            </label>
                            <input type="text" name="dependencia" id="dependencia" required
                                class="text-capitalize form-control @error('dependencia') is-invalid @enderror"
                                value="{{ old('dependencia') }}" placeholder="Ej: Dpto. de Matemáticas">
                            @error('dependencia')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern text-muted">
                                <i class="fas fa-info-circle"></i> Unidad o área de trabajo
                            </small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-clock text-primary me-1"></i>
                                Horas académicas disponibles
                            </label>
                            <input type="number" name="horas_academicas" id="horas_academicas" required min="1" max="60"
                                class="form-control @error('horas_academicas') is-invalid @enderror"
                                value="{{ old('horas_academicas', old('hora_academica')) }}" placeholder="Ej: 5">
                            @error('horas_academicas')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern text-muted">
                                <i class="fas fa-info-circle"></i> Carga máxima por semana
                            </small>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label-modern fw-semibold" style="color: #1E293B;">
                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                Dirección
                            </label>
                            <textarea
                                class="text-capitalize form-control @error('direccion') is-invalid @enderror"
                                name="direccion" id="direccion" rows="2" required placeholder="Ingrese la dirección de domicilio completa">{{ old('direccion') }}</textarea>
                            @error('direccion')
                                <div class="invalid-feedback-modern d-block">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Wizard Navigation Buttons -->
                    <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top" style="border-top-color: #F1F5F9 !important;">
                        <a href="{{ route('admin.docente.index') }}" class="btn-secondary-modern">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                        <button type="submit" class="btn-primary-modern">
                            <span>Siguiente</span> <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Configurar la URL para verificación de cédula
        window.verificarCedulaUrl = "{{ route('admin.docente.verificar_cedula') }}";

        // TAREA 1: Lógica de Desbloqueo Progresivo de Nombres
        document.addEventListener('DOMContentLoaded', function () {
            const primerNombre = document.getElementById('primer_nombre');
            const segundoNombre = document.getElementById('segundo_nombre');
            const tercerNombre = document.getElementById('tercer_nombre');

            if (!primerNombre || !segundoNombre || !tercerNombre) return;

            function actualizarNombres() {
                const val1 = primerNombre.value.trim();
                const val2 = segundoNombre.value.trim();

                // Desbloqueo de Segundo Nombre
                if (val1 !== '') {
                    segundoNombre.disabled = false;
                } else {
                    segundoNombre.disabled = true;
                    segundoNombre.value = '';

                    tercerNombre.disabled = true;
                    tercerNombre.value = '';
                    return;
                }

                // Desbloqueo de Tercer Nombre
                if (val2 !== '') {
                    tercerNombre.disabled = false;
                } else {
                    tercerNombre.disabled = true;
                    tercerNombre.value = '';
                }
            }

            primerNombre.addEventListener('input', actualizarNombres);
            segundoNombre.addEventListener('input', actualizarNombres);

            // Ejecutar al cargar la página (por si viene de old() con valores tras error de validación)
            actualizarNombres();

            // Antes de enviar el form, habilitar los campos para que Laravel los reciba si el navegador los excluye
            const form = document.getElementById('docenteForm');
            if (form) {
                form.addEventListener('submit', function () {
                    segundoNombre.disabled = false;
                    tercerNombre.disabled = false;
                });
            }
        });
    </script>
    <script src="{{ asset('js/validations/docente.js') }}"></script>
@stop

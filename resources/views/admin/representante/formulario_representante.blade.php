@extends('adminlte::page')

@section('title', 'Gestión de Representantes')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/representante.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @livewireStyles
@stop

@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">
                        {{ isset($representante) ? 'Editar Representante' : 'Registrar Representante' }}
                    </h1>
                    <p class="title-subtitle mb-0">Formulario de registro de representantes</p>
                </div>
            </div>

            @if ($from === 'inscripcion')
                <a href="{{ route('admin.transacciones.inscripcion.create') }}" class="btn-hero-white w-100 w-md-auto justify-content-center">
                    <i class="fas fa-arrow-left me-2"></i>
                    <span>Volver</span>
                </a>
            @else
                <a href="{{ route('representante.index') }}" class="btn-hero-white w-100 w-md-auto justify-content-center">
                    <i class="fas fa-arrow-left me-2"></i>
                    <span>Volver</span>
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="main-container">
        <!-- Mensajes de validación -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>¡Error!</strong> Por favor, corrija los siguientes errores:
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="col-12 px-0 w-100">
                <div class="alert alert-info-modern d-flex align-items-center mb-4 p-3" style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 12px; color: #1E40AF;">
                    <i class="fas fa-info-circle fs-5 me-3" style="color: #3B82F6;"></i>
                    <div>
                        <strong style="color: #1E3A8A;">Información importante:</strong>
                        <span class="d-block d-md-inline ms-md-1" style="color: #1E40AF;">Los campos marcados con <span class="text-danger fw-bold">*</span> son obligatorios. Asegúrese de completar toda la información solicitada.</span>
                    </div>
                </div>

                <form id="representante-form" action="/representante/save" method="POST" class="needs-validation w-100" novalidate>
                    <input type="hidden" name="from" value="{{ $from }}">
                    @csrf

                    <!-- ==========================================
                         1. SECCIÓN DE LA MADRE (Card Clásica: Header Rosa + Body Blanco)
                         ========================================== -->
                    <div class="card card-modern form-card-modern card-section-madre mb-4 w-100" id="seccion-madre" style="border: 1px solid #FBCFE8 !important; border-radius: 12px !important; overflow: hidden !important; background-color: #FFFFFF !important;">
                <div class="card-header-modern card-header-madre d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 p-3 px-md-4" style="background-color: #FDF2F8 !important; border-bottom: 1px solid #FBCFE8 !important; border-radius: 12px 12px 0 0 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon-badge badge-madre">
                            <i class="fas fa-female"></i>
                        </div>
                        <div class="text-start">
                            <h3 class="card-title-modern mb-0" style="color: #1E293B; font-weight: 700; font-size: 1.15rem;">
                                Datos de la Madre
                            </h3>
                            <p class="text-muted mb-0 small text-start">Información personal, ubicación y contacto de la madre</p>
                        </div>
                    </div>

                    <!-- Estado de la madre (Presente / Ausente) en el encabezado a la derecha -->
                    <div class="d-flex flex-column align-items-sm-end ms-sm-auto">
                        <div class="d-flex align-items-center gap-2">
                            <label class="state-pill-item mb-0" for="Presente_madre">
                                <input class="visually-hidden" type="radio" value="Presente" name="estado_madre" id="Presente_madre" required>
                                <span class="state-pill-btn pill-presente">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Presente</span>
                                </span>
                            </label>
                            <label class="state-pill-item mb-0" for="Ausente_madre">
                                <input class="visually-hidden" type="radio" value="Ausente" name="estado_madre" id="Ausente_madre" required>
                                <span class="state-pill-btn pill-ausente">
                                    <i class="fas fa-times-circle"></i>
                                    <span>Ausente</span>
                                </span>
                            </label>
                        </div>
                        <small id="estado_madre-error" class="text-danger mt-1"></small>
                    </div>
                </div>

                <div class="card-body p-0" style="background-color: #FFFFFF !important; border-radius: 0 0 12px 12px !important;">
                    <div id="campos-madre" class="campos-representante p-4" style="display: {{ old('estado_madre', isset($representante->madre) ? 'Presente' : '') === 'Presente' ? 'block' : 'none' }};">
                        <!-- Sub-bloque 1: Datos Personales de la Madre -->
                    <div class="form-section-header header-madre text-start mt-0">
                        <i class="fas fa-id-card"></i>
                        <h5>Datos Personales de la Madre</h5>
                    </div>

                    <!-- FILA 1: Identificación (Grid 1fr 1.5fr 1.5fr 1fr) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1.5fr 1.5fr 1fr; gap: 16px;">
                        <div class="floating-group">
                            <label for="tipo-ci">Tipo Doc <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="tipo-ci" name="tipo-ci" required>
                                <option value="">Doc</option>
                                @foreach ($tipoDocumentos as $tipoDoc)
                                    <option value="{{ $tipoDoc->id }}">{{ $tipoDoc->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Por favor seleccione el tipo de documento.</div>
                            <small id="tipo-ci-error" class="text-danger"></small>
                        </div>
                        <div class="floating-group">
                            <label for="numero_documento">Número de Cédula <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero_documento"
                                name="numero_documento" maxlength="8" pattern="\d{6,8}"
                                placeholder=" " required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <div class="invalid-feedback">Número de cédula válido (6-8 dígitos).</div>
                        </div>
                        <div class="floating-group">
                            <label for="fechaNacimiento">Fecha de Nacimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" placeholder=" " required>
                            <div class="invalid-feedback">Por favor ingrese una fecha válida.</div>
                            <small id="error-edad" class="text-danger d-none">Edad entre 10 y 17 años</small>
                        </div>
                        <div class="floating-group">
                            <label for="sexo">Género <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="sexo" name="sexo" required>
                                <option value="">Seleccione</option>
                                @foreach ($generos as $genero)
                                    <option value="{{ $genero->id }}">{{ $genero->genero }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Por favor seleccione un género.</div>
                        </div>
                    </div>

                    <!-- FILA 2: Nombres (Grid 3 columnas iguales) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div class="floating-group">
                            <label for="primer-nombre">Primer Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="primer-nombre"
                                name="primer-nombre" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" required>
                            <div class="invalid-feedback">Solo letras y espacios.</div>
                        </div>
                        <div class="floating-group">
                            <label for="segundo-nombre">Segundo Nombre</label>
                            <input type="text" class="form-control" id="segundo-nombre"
                                name="segundo-nombre" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]*">
                        </div>
                        <div class="floating-group">
                            <label for="tercer-nombre">Tercer Nombre</label>
                            <input type="text" class="form-control" id="tercer-nombre"
                                name="tercer-nombre" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]*">
                        </div>
                    </div>

                    <!-- FILA 3: Apellidos (Grid 2 columnas iguales) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                        <div class="floating-group">
                            <label for="primer-apellido">Primer Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="primer-apellido"
                                name="primer-apellido" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" required>
                            <div class="invalid-feedback">Solo letras y espacios.</div>
                        </div>
                        <div class="floating-group">
                            <label for="segundo-apellido">Segundo Apellido</label>
                            <input type="text" class="form-control" id="segundo-apellido"
                                name="segundo-apellido" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]*">
                        </div>
                    </div>

                    <!-- Sub-bloque 2: Dirección y Ubicación de la Madre -->
                    <div class="form-section-header header-madre text-start">
                        <i class="fas fa-map-marker-alt"></i>
                        <h5>Dirección y Ubicación</h5>
                    </div>

                    <!-- Fila 1: País, Estado, Municipio (Grid 3 columnas iguales) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div class="floating-group">
                            <label for="idPais">País <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="idPais"
                                name="idPais" aria-label="Seleccione un país" required>
                                <option value="" disabled selected>Seleccione un país</option>
                                @php
                                    $paisesUnicos = $paises->unique('id')->sortBy('nameES');
                                @endphp
                                @foreach ($paisesUnicos as $pais)
                                    <option value="{{ $pais->id }}" @if (old('idPais') == $pais->id) selected @endif>
                                        {{ $pais->nameES }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                        </div>

                        <div class="floating-group">
                            <label for="idEstado">Estado <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="idEstado"
                                name="idEstado" aria-label="Seleccione un estado" required>
                                <option value="" disabled selected>Seleccione un estado</option>
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                            <small id="idEstado-error" class="text-danger"></small>
                        </div>

                        <div class="floating-group">
                            <label for="idMunicipio">Municipio <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="idMunicipio"
                                name="idMunicipio" aria-label="Seleccione un municipio" required>
                                <option value="" disabled selected>Seleccione un municipio</option>
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                            <small id="idMunicipio-error" class="text-danger"></small>
                        </div>
                    </div>

                    <!-- Fila 2: Parroquia, Lugar de Nacimiento (Grid 1fr 1fr) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="floating-group">
                            <label for="idparroquia">Localidad / Parroquia <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="idparroquia"
                                name="idparroquia" aria-label="Seleccione una parroquia" required>
                                <option value="" disabled selected>Seleccione una parroquia</option>
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                            <small id="idparroquia-error" class="text-danger"></small>
                        </div>

                        <div class="floating-group">
                            <label for="lugar-nacimiento">Lugar de Nacimiento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lugar-nacimiento"
                                name="lugar-nacimiento" placeholder=" "
                                pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" maxlength="100" required>
                            <div class="invalid-feedback">Lugar válido requerido.</div>
                        </div>
                    </div>

                    <!-- Fila 3: Dirección de Habitación (100% ancho) -->
                    <div class="mb-3">
                        <div class="floating-group">
                            <label for="direccion">Dirección de Habitación <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="direccion"
                                name="direccion" placeholder=" " required>
                            <div class="invalid-feedback">Dirección requerida.</div>
                        </div>
                    </div>

                    <!-- Sub-bloque 3: Contactos Telefónicos (Grid 1fr 2fr 1fr 2fr) -->
                    <div class="form-section-header header-madre text-start">
                        <i class="fas fa-phone"></i>
                        <h5>Contactos Telefónicos</h5>
                    </div>

                    <div class="mb-3" style="display: grid; grid-template-columns: 1fr 2fr 1fr 2fr; gap: 16px;">
                        <div class="floating-group">
                            <label for="prefijo">Prefijo 1 <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="prefijo"
                                name="prefijo" required>
                                <option value="" disabled selected>Prefijo</option>
                                @foreach ($prefijos_telefono as $prefijo)
                                    <option value="{{ $prefijo->id }}">{{ $prefijo->prefijo }}
                                        ({{ $prefijo->tipo_linea }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Prefijo requerido.</div>
                            <small id="prefijo-error" class="text-danger"></small>
                        </div>

                        <div class="floating-group">
                            <label for="telefono">Teléfono 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="telefono"
                                name="telefono" pattern="[0-9]+" maxlength="11"
                                title="Ingrese solo números (máximo 11 dígitos)" placeholder=" " required>
                            <div class="invalid-feedback">Número telefónico válido requerido.</div>
                            <small id="telefono-error" class="text-danger"></small>
                        </div>

                        <div class="floating-group">
                            <label for="prefijo_dos">Prefijo 2</label>
                            <select class="form-select select-modern w-100" id="prefijo_dos"
                                name="prefijo_dos">
                                <option value="" disabled selected>Prefijo</option>
                                @foreach ($prefijos_telefono as $prefijo)
                                    <option value="{{ $prefijo->id }}">{{ $prefijo->prefijo }}</option>
                                @endforeach
                            </select>
                            <small id="prefijo_dos-error" class="text-danger"></small>
                        </div>

                        <div class="floating-group">
                            <label for="telefono_dos">Teléfono 2</label>
                            <input type="text" class="form-control" id="telefono_dos"
                                name="telefono_dos" pattern="[0-9]+" maxlength="11"
                                title="Ingrese solo números (máximo 11 dígitos)" placeholder=" ">
                            <small id="telefono_dos-error" class="text-danger"></small>
                        </div>
                    </div>

                    <!-- Sub-bloque 4: Relación Familiar de la Madre -->
                    <div class="form-section-header header-madre text-start">
                        <i class="fas fa-users"></i>
                        <h5>Relación Familiar</h5>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="floating-group mb-0">
                                <label for="ocupacion-madre">
                                    <i class="fas fa-briefcase"></i> Ocupación <span class="text-danger">*</span>
                                </label>
                                <select name="ocupacion-madre" id="ocupacion-madre"
                                    class="form-select select-modern w-100" required>
                                    <option value="" disabled selected>Seleccione una ocupación</option>
                                    @foreach ($ocupaciones as $ocupacion)
                                        <option value="{{ $ocupacion->id }}">
                                            {{ $ocupacion->nombre_ocupacion }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Seleccione una ocupación.</div>
                                <small id="ocupacion-madre-error" class="text-danger"></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="convive-si" style="font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 4px;">
                                <i class="fas fa-home"></i> ¿Convive con el Estudiante? <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex align-items-center gap-4" style="height: 56px;">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" id="convive-si"
                                        name="convive" value="si">
                                    <label class="form-check-label cursor-pointer" for="convive-si">Sí</label>
                                </div>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" id="convive-no"
                                        name="convive" value="no">
                                    <label class="form-check-label cursor-pointer" for="convive-no">No</label>
                                </div>
                            </div>
                            <small id="convive-error" class="error-message text-danger"></small>
                            <small id="representante_legal-error" class="error-message text-danger"></small>
                        </div>

                        <div class="col-12 d-none" id="otra-ocupacion-container">
                            <div class="floating-group">
                                <label for="otra-ocupacion">
                                    <i class="fas fa-briefcase"></i> Otra Ocupación <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="otra-ocupacion"
                                    name="otra-ocupacion" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                                    placeholder=" ">
                                <div class="invalid-feedback">Especifique la ocupación.</div>
                                <small id="otra-ocupacion-error" class="text-danger"></small>
                            </div>
                        </div>
                    </div>
                    </div><!-- Fin campos-madre -->
                </div><!-- Fin card-body Madre -->
            </div><!-- Fin card Madre -->

            <!-- ==========================================
                 2. SECCIÓN DEL PADRE (Card Clásica: Header Azul + Body Blanco)
                 ========================================== -->
            <div class="card card-modern form-card-modern card-section-padre mb-4 w-100" id="seccion-padre" style="border: 1px solid #BAE6FD !important; border-radius: 12px !important; overflow: hidden !important; background-color: #FFFFFF !important;">
                <div class="card-header-modern card-header-padre d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 p-3 px-md-4" style="background-color: #F0F9FF !important; border-bottom: 1px solid #BAE6FD !important; border-radius: 12px 12px 0 0 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon-badge badge-padre">
                            <i class="fas fa-male"></i>
                        </div>
                        <div class="text-start">
                            <h3 class="card-title-modern mb-0" style="color: #1E293B; font-weight: 700; font-size: 1.15rem;">
                                Datos del Padre
                            </h3>
                            <p class="text-muted mb-0 small text-start">Información personal, ubicación y contacto del padre</p>
                        </div>
                    </div>

                    <!-- Estado del padre (Presente / Ausente) en el encabezado a la derecha -->
                    <div class="d-flex flex-column align-items-sm-end ms-sm-auto">
                        <div class="d-flex align-items-center gap-2">
                            <label class="state-pill-item mb-0" for="Presente_padre">
                                <input class="visually-hidden" type="radio" value="Presente" name="estado_padre" id="Presente_padre" required>
                                <span class="state-pill-btn pill-presente">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Presente</span>
                                </span>
                            </label>
                            <label class="state-pill-item mb-0" for="Ausente_padre">
                                <input class="visually-hidden" type="radio" value="Ausente" name="estado_padre" id="Ausente_padre">
                                <span class="state-pill-btn pill-ausente">
                                    <i class="fas fa-times-circle"></i>
                                    <span>Ausente</span>
                                </span>
                            </label>
                        </div>
                        <small id="estado_padre-error" class="text-danger mt-1"></small>
                    </div>
                </div>

                <div class="card-body p-0" style="background-color: #FFFFFF !important; border-radius: 0 0 12px 12px !important;">
                    <div id="campos-padre" class="campos-representante p-4" style="display: {{ old('estado_padre', isset($representante->padre) ? 'Presente' : '') === 'Presente' ? 'block' : 'none' }};">
                        <!-- Sub-bloque 1: Datos Personales del Padre -->
                    <div class="form-section-header header-padre text-start mt-0">
                        <i class="fas fa-id-card"></i>
                        <h5>Datos Personales del Padre</h5>
                    </div>

                    <!-- Fila 1: Identificación (Grid 1fr 1.5fr 1.5fr 1fr) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1.5fr 1.5fr 1fr; gap: 16px;">
                        <div class="floating-group">
                            <label for="tipo-ci-padre">Tipo Doc <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="tipo-ci-padre" name="tipo-ci-padre" required>
                                <option value="">Doc</option>
                                @foreach ($tipoDocumentos as $tipoDoc)
                                    <option value="{{ $tipoDoc->id }}">{{ $tipoDoc->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                        </div>

                        <div class="floating-group">
                            <label for="numero_documento-padre">Número de Cédula <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero_documento-padre"
                                name="numero_documento-padre" maxlength="8" placeholder=" " required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <div class="invalid-feedback">Número válido requerido.</div>
                        </div>

                        <div class="floating-group">
                            <label for="fecha-nacimiento-padre">Fecha de Nacimiento <span class="text-danger">*</span></label>
                            <input type="date" id="fecha-nacimiento-padre" name="fecha-nacimiento-padre"
                                class="form-control" placeholder=" " required>
                            <div class="invalid-feedback">Fecha válida requerida.</div>
                            <small id="error-edad-padre" class="text-danger d-none">La edad debe ser mayor de 18 años</small>
                        </div>

                        <div class="floating-group">
                            <label for="sexo-padre">Género <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="sexo-padre" name="sexo-padre" required>
                                <option value="">Seleccione</option>
                                @foreach ($generos as $genero)
                                    <option value="{{ $genero->id }}">{{ $genero->genero }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Seleccione un género.</div>
                        </div>
                    </div>

                    <!-- Fila 2: Nombres (Grid 3 columnas iguales) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div class="floating-group">
                            <label for="primer-nombre-padre">Primer Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="primer-nombre-padre"
                                name="primer-nombre-padre" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" required>
                            <div class="invalid-feedback">Solo letras y espacios.</div>
                        </div>

                        <div class="floating-group">
                            <label for="segundo-nombre-padre">Segundo Nombre</label>
                            <input type="text" class="form-control" id="segundo-nombre-padre"
                                name="segundo-nombre-padre" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]*">
                        </div>

                        <div class="floating-group">
                            <label for="tercer-nombre-padre">Tercer Nombre</label>
                            <input type="text" class="form-control" id="tercer-nombre-padre"
                                name="tercer-nombre-padre" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]*">
                        </div>
                    </div>

                    <!-- Fila 3: Apellidos (Grid 2 columnas iguales) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                        <div class="floating-group">
                            <label for="primer-apellido-padre">Primer Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="primer-apellido-padre"
                                name="primer-apellido-padre" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" required>
                            <div class="invalid-feedback">Solo letras y espacios.</div>
                        </div>

                        <div class="floating-group">
                            <label for="segundo-apellido-padre">Segundo Apellido</label>
                            <input type="text" class="form-control" id="segundo-apellido-padre"
                                name="segundo-apellido-padre" placeholder=" " pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]*">
                        </div>
                    </div>

                    <!-- Sub-bloque 2: Dirección y Ubicación del Padre -->
                    <div class="form-section-header header-padre text-start">
                        <i class="fas fa-map-marker-alt"></i>
                        <h5>Dirección y Ubicación</h5>
                    </div>

                    <!-- Fila 1: País, Estado, Municipio (Grid 3 columnas) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div class="floating-group">
                            <label for="idPais-padre">País <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="idPais-padre"
                                name="idPais-padre" aria-label="Seleccione un país" required>
                                <option value="" disabled selected>Seleccione un país</option>
                                @php
                                    $paisesUnicosPadre = $paises->unique('id')->sortBy('nameES');
                                @endphp
                                @foreach ($paisesUnicosPadre as $pais)
                                    <option value="{{ $pais->id }}" @if (old('idPais-padre') == $pais->id) selected @endif>
                                        {{ $pais->nameES }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                        </div>

                        <div class="floating-group">
                            <label for="idEstado-padre">Estado <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="idEstado-padre"
                                name="idEstado-padre" aria-label="Seleccione un estado" required>
                                <option value="" disabled selected>Seleccione un estado</option>
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                        </div>

                        <div class="floating-group">
                            <label for="idMunicipio-padre">Municipio <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="idMunicipio-padre"
                                name="idMunicipio-padre" aria-label="Seleccione un municipio" required>
                                <option value="" disabled selected>Seleccione un municipio</option>
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                        </div>
                    </div>

                    <!-- Fila 2: Parroquia, Lugar de Nacimiento (Grid 1fr 1fr) -->
                    <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="floating-group">
                            <label for="idparroquia-padre">Localidad / Parroquia <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="idparroquia-padre"
                                name="idparroquia-padre" aria-label="Seleccione una parroquia" required>
                                <option value="" disabled selected>Seleccione una parroquia</option>
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                        </div>

                        <div class="floating-group">
                            <label for="lugar-nacimiento-padre">Lugar de Nacimiento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lugar-nacimiento-padre"
                                name="lugar-nacimiento-padre" placeholder=" "
                                pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s,]+" maxlength="100" required>
                            <div class="invalid-feedback">Lugar requerido.</div>
                            <small id="lugar-nacimiento-padre-error" class="text-danger"></small>
                        </div>
                    </div>

                    <!-- Fila 3: Dirección de Habitación (100% ancho) -->
                    <div class="mb-3">
                        <div class="floating-group">
                            <label for="direccion-padre">Dirección de Vivienda <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="direccion-padre"
                                name="direccion-padre" placeholder=" " required>
                            <div class="invalid-feedback">Dirección requerida.</div>
                        </div>
                    </div>

                    <!-- Sub-bloque 3: Contactos Telefónicos del Padre (Grid 1fr 2fr 1fr 2fr) -->
                    <div class="form-section-header header-padre text-start">
                        <i class="fas fa-phone"></i>
                        <h5>Contactos Telefónicos</h5>
                    </div>

                    <div class="mb-3" style="display: grid; grid-template-columns: 1fr 2fr 1fr 2fr; gap: 16px;">
                        <div class="floating-group">
                            <label for="prefijo-padre">Prefijo 1 <span class="text-danger">*</span></label>
                            <select class="form-select select-modern w-100" id="prefijo-padre" name="prefijo-padre"
                                required>
                                <option value="" disabled selected>Prefijo</option>
                                @foreach ($prefijos_telefono as $prefijo)
                                    <option value="{{ $prefijo->id }}">{{ $prefijo->prefijo }}
                                        ({{ $prefijo->tipo_linea }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Requerido.</div>
                        </div>

                        <div class="floating-group">
                            <label for="telefono-padre">Teléfono 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="telefono-padre"
                                name="telefono-padre" pattern="[0-9]+" maxlength="11"
                                title="Ingrese solo números (máximo 11 dígitos)" placeholder=" " required>
                            <div class="invalid-feedback">Número telefónico válido requerido.</div>
                        </div>

                        <div class="floating-group">
                            <label for="prefijo_dos_padre">Prefijo 2</label>
                            <select class="form-select select-modern w-100" id="prefijo_dos_padre"
                                name="prefijo_dos_padre">
                                <option value="" disabled selected>Prefijo</option>
                                @foreach ($prefijos_telefono as $prefijo)
                                    <option value="{{ $prefijo->id }}">{{ $prefijo->prefijo }}</option>
                                @endforeach
                            </select>
                            <small id="prefijo_dos_padre-error" class="text-danger"></small>
                        </div>

                        <div class="floating-group">
                            <label for="telefono_dos_padre">Teléfono 2</label>
                            <input type="text" class="form-control" id="telefono_dos_padre"
                                name="telefono_dos_padre" pattern="[0-9]+" maxlength="11"
                                title="Ingrese solo números (máximo 11 dígitos)" placeholder=" ">
                        </div>
                    </div>

                    <!-- Sub-bloque 4: Relación Familiar del Padre -->
                    <div class="form-section-header header-padre text-start">
                        <i class="fas fa-users"></i>
                        <h5>Relación Familiar</h5>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="floating-group mb-0">
                                <label for="ocupacion-padre">
                                    <i class="fas fa-briefcase"></i> Ocupación <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select-modern w-100" id="ocupacion-padre"
                                    name="ocupacion-padre" required>
                                    <option value="" disabled selected>Seleccione una ocupación</option>
                                    @foreach ($ocupaciones as $ocupacion)
                                        <option value="{{ $ocupacion->id }}">{{ $ocupacion->nombre_ocupacion }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Seleccione una ocupación.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="convive-si-padre" style="font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 4px;">
                                <i class="fas fa-home"></i> ¿Convive con el Estudiante? <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex align-items-center gap-4" style="height: 56px;">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" name="convive-padre"
                                        id="convive-si-padre" value="si" required>
                                    <label class="form-check-label cursor-pointer" for="convive-si-padre">Sí</label>
                                </div>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" name="convive-padre"
                                        id="convive-no-padre" value="no" required>
                                    <label class="form-check-label cursor-pointer" for="convive-no-padre">No</label>
                                </div>
                            </div>
                            <small id="convive-padre-error" class="error-message text-danger"></small>
                            <small id="representante_legal-padre-error" class="error-message text-danger"></small>
                        </div>

                        <div class="col-12 d-none" id="otra-ocupacion-padre-container">
                            <label class="form-label" for="otra-ocupacion-padre">
                                <i class="fas fa-briefcase"></i> Especifique Ocupación <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="otra-ocupacion-padre"
                                name="otra-ocupacion-padre" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                                placeholder="Especifique la ocupación">
                            <div class="invalid-feedback">Ingrese una ocupación válida.</div>
                        </div>
                    </div>
                    </div><!-- Fin campos-padre -->
                </div><!-- Fin card-body Padre -->
            </div><!-- Fin card Padre -->

            <!-- ==========================================
                 3. SECCIÓN DEL REPRESENTANTE LEGAL (Card Clásica: Header Verde + Body Blanco)
                 ========================================== -->
            <div class="card card-modern form-card-modern card-section-representante mb-4 w-100" id="seccion-representante-legal" style="border: 1px solid #BBF7D0 !important; border-radius: 12px !important; overflow: hidden !important; background-color: #FFFFFF !important;">
                <div class="card-header-modern card-header-legal d-flex justify-content-between align-items-center gap-3 p-3 px-md-4" style="background-color: #F0FDF4 !important; border-bottom: 1px solid #BBF7D0 !important; border-radius: 12px 12px 0 0 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon-badge badge-representante">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="text-start">
                            <h3 class="card-title-modern mb-0" style="color: #1E293B; font-weight: 700; font-size: 1.15rem;">
                                Datos del Representante Legal
                            </h3>
                            <p class="text-muted mb-0 small text-start">Información de quien ejercerá la representación legal del estudiante</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4" style="background-color: #FFFFFF !important; border-radius: 0 0 12px 12px !important;">
                    <div class="col-12 px-0">
                        <!-- Selector de Tipo de Representante (Alerta Superior) -->
                        <div class="tipo-rep-alert mb-3 text-start">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-info-circle text-primary" style="color: #4A6FC1 !important;"></i>
                                <strong style="color: #1E293B; font-size: 0.95rem;">Tipo de Representante <span class="text-danger">*</span></strong>
                            </div>
                            <p class="text-muted small mb-3">Seleccione quién ejercerá la representación legal del estudiante:</p>
                            <div class="d-flex flex-column flex-md-row gap-3">
                                <div class="form-check tipo-rep-option flex-fill d-flex align-items-center mb-0">
                                    <input class="form-check-input me-2 mt-0" type="radio" name="tipo_representante" id="solo_representante"
                                        value="solo_representante" required>
                                    <label class="form-check-label mb-0 w-100 cursor-pointer" for="solo_representante">
                                        <i class="fas fa-user-tag me-1 text-muted"></i> Solo Representante Legal
                                    </label>
                                </div>
                                <div class="form-check tipo-rep-option flex-fill d-flex align-items-center mb-0">
                                    <input class="form-check-input me-2 mt-0" type="radio" name="tipo_representante"
                                        id="progenitor_padre_representante" value="progenitor_padre_representante">
                                    <label class="form-check-label mb-0 w-100 cursor-pointer" for="progenitor_padre_representante">
                                        <i class="fas fa-male me-1 text-muted"></i> Padre como Representante
                                    </label>
                                </div>
                                <div class="form-check tipo-rep-option flex-fill d-flex align-items-center mb-0">
                                    <input class="form-check-input me-2 mt-0" type="radio" name="tipo_representante"
                                        id="progenitor_madre_representante" value="progenitor_madre_representante">
                                    <label class="form-check-label mb-0 w-100 cursor-pointer" for="progenitor_madre_representante">
                                        <i class="fas fa-female me-1 text-muted"></i> Madre como Representante
                                    </label>
                                </div>
                            </div>
                            <!-- Error Inline cuando el progenitor seleccionado está ausente -->
                            <div id="error-tipo-representante" class="text-danger mt-2" style="display: none; font-size: 13px;">
                                <i class="fas fa-exclamation-circle me-1"></i> 
                                <span id="error-tipo-representante-texto">La madre está marcada como ausente. Debe cambiarla a presente o elegir otra opción.</span>
                            </div>
                            <div class="invalid-feedback">
                                Por favor seleccione el tipo de representante.
                            </div>
                        </div>

                        <!-- Contenedor de campos del Representante Legal -->
                        <div id="campos-legal" class="campos-representante">
                            <div id="datos-representante">
                                <!-- Sub-bloque 1: Datos Personales del Representante -->
                            <div class="form-section-header header-representante text-start mt-0">
                                <i class="fas fa-id-card"></i>
                                <h5>Datos Personales del Representante</h5>
                            </div>

                            <!-- Fila 1: Identificación (Grid 1fr 1.5fr 1.5fr 1fr) -->
                            <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1.5fr 1.5fr 1fr; gap: 16px;">
                                <div class="floating-group">
                                    <label for="tipo-ci-representante">Tipo Doc <span class="text-danger">*</span></label>
                                    <select class="form-select select-modern w-100" id="tipo-ci-representante"
                                        name="tipo-ci-representante" required>
                                        <option value="">Doc</option>
                                        @foreach ($tipoDocumentos as $tipoDoc)
                                            <option value="{{ $tipoDoc->id }}">{{ $tipoDoc->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Requerido.</div>
                                    <small id="tipo-ci-representante-error" class="text-danger"></small>
                                </div>

                                <div class="floating-group">
                                    <label for="numero_documento-representante">Número de Cédula <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="numero_documento-representante"
                                        name="numero_documento-representante" maxlength="8" required
                                        placeholder=" " oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <input type="hidden" id="persona-id-representante" name="persona-id-representante">
                                    <input type="hidden" id="representante-id" name="representante-id">
                                    <div class="invalid-feedback">Cédula requerida.</div>
                                    <small id="numero_documento-representante-error" class="text-danger"></small>
                                </div>

                                <div class="floating-group">
                                    <label for="fecha-nacimiento-representante">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                    <input type="date" id="fecha-nacimiento-representante"
                                        name="fecha-nacimiento-representante" class="form-control" placeholder=" " required>
                                    <div class="invalid-feedback">Fecha válida requerida.</div>
                                </div>

                                <div class="floating-group">
                                    <label for="sexo-representante">Género <span class="text-danger">*</span></label>
                                    <select class="form-select select-modern w-100" id="sexo-representante" name="sexo-representante"
                                        required>
                                        <option value="">Seleccione</option>
                                        @foreach ($generos as $genero)
                                            <option value="{{ $genero->id }}">{{ $genero->genero }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Seleccione un género.</div>
                                    <small id="sexo-representante-error" class="text-danger"></small>
                                </div>
                            </div>

                            <!-- Fila 2: Nombres (Grid 3 columnas iguales) -->
                            <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                                <div class="floating-group">
                                    <label for="primer-nombre-representante">Primer Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="primer-nombre-representante"
                                        name="primer-nombre-representante" placeholder=" "
                                        pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" required>
                                    <div class="invalid-feedback">Solo letras y espacios.</div>
                                    <small class="text-danger" id="primer-nombre-representante-error"></small>
                                </div>

                                <div class="floating-group">
                                    <label for="segundo-nombre-representante">Segundo Nombre</label>
                                    <input type="text" class="form-control" id="segundo-nombre-representante"
                                        name="segundo-nombre-representante" placeholder=" "
                                        pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]*">
                                    <small class="text-danger" id="segundo-nombre-representante-error"></small>
                                </div>

                                <div class="floating-group">
                                    <label for="tercer-nombre-representante">Tercer Nombre</label>
                                    <input type="text" class="form-control" id="tercer-nombre-representante"
                                        name="tercer-nombre-representante" placeholder=" "
                                        pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]*">
                                    <small class="text-danger" id="tercer-nombre-representante-error"></small>
                                </div>
                            </div>

                            <!-- Fila 3: Apellidos (Grid 2 columnas iguales) -->
                            <div class="mb-3" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                                <div class="floating-group">
                                    <label for="primer-apellido-representante">Primer Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="primer-apellido-representante"
                                        name="primer-apellido-representante" placeholder=" "
                                        pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" required>
                                    <div class="invalid-feedback">Solo letras y espacios.</div>
                                    <small class="text-danger" id="primer-apellido-representante-error"></small>
                                </div>

                                <div class="floating-group">
                                    <label for="segundo-apellido-representante">Segundo Apellido</label>
                                    <input type="text" class="form-control" id="segundo-apellido-representante"
                                        name="segundo-apellido-representante" placeholder=" "
                                        pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]*">
                                    <small class="text-danger" id="segundo-apellido-representante-error"></small>
                                </div>
                            </div>

                        <!-- Alerta de Persona Encontrada en el Sistema (Representante) -->
                        <div id="alerta-persona-encontrada-representante" class="alert alert-info d-none align-items-center mt-2 mb-3" role="alert" style="border-left: 4px solid #0284c7; background-color: #f0f9ff; color: #0369a1;">
                            <i class="fas fa-info-circle fa-lg me-2" style="color: #0284c7;"></i>
                            <div>
                                <strong>Persona encontrada en el sistema.</strong>
                                <span>Los datos personales han sido bloqueados para evitar inconsistencias.</span>
                            </div>
                        </div>

                        <!-- Sub-bloque 2: Dirección y Ubicación del Representante -->
                        <div class="form-section-header header-representante text-start">
                            <i class="fas fa-map-marker-alt"></i>
                            <h5>Dirección y Ubicación</h5>
                        </div>

                        <!-- Fila 1: País, Estado, Municipio (Grid 3 columnas) -->
                        <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                            <div class="floating-group">
                                <label for="idPais-representante">País <span class="text-danger">*</span></label>
                                <select class="form-select select-modern w-100" id="idPais-representante"
                                    name="idPais-representante" aria-label="Seleccione un país" required>
                                    <option value="" disabled selected>Seleccione un país</option>
                                    @php
                                        $paisesUnicosRepresentante = $paises->unique('id')->sortBy('nameES');
                                    @endphp
                                    @foreach ($paisesUnicosRepresentante as $pais)
                                        <option value="{{ $pais->id }}"
                                            @if (old('idPais-representante') == $pais->id) selected @endif>
                                            {{ $pais->nameES }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Requerido.</div>
                            </div>

                            <div class="floating-group">
                                <label for="idEstado-representante">Estado <span class="text-danger">*</span></label>
                                <select class="form-select select-modern w-100" id="idEstado-representante"
                                    name="idEstado-representante" aria-label="Seleccione un estado" required>
                                    <option value="" disabled selected>Seleccione un estado</option>
                                </select>
                                <div class="invalid-feedback">Requerido.</div>
                                <small id="idEstado-error" class="text-danger"></small>
                            </div>

                            <div class="floating-group">
                                <label for="idMunicipio-representante">Municipio <span class="text-danger">*</span></label>
                                <select class="form-select select-modern w-100" id="idMunicipio-representante"
                                    name="idMunicipio-representante" aria-label="Seleccione un municipio" required>
                                    <option value="" disabled selected>Seleccione un municipio</option>
                                </select>
                                <div class="invalid-feedback">Requerido.</div>
                                <small id="idMunicipio-representante-error" class="text-danger"></small>
                            </div>
                        </div>

                        <!-- Fila 2: Parroquia, Lugar de Nacimiento (Grid 1fr 1fr) -->
                        <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="floating-group">
                                <label for="idparroquia-representante">Localidad / Parroquia <span class="text-danger">*</span></label>
                                <select class="form-select select-modern w-100" id="idparroquia-representante"
                                    name="idparroquia-representante" aria-label="Seleccione una parroquia" required>
                                    <option value="" disabled selected>Seleccione una parroquia</option>
                                </select>
                                <div class="invalid-feedback">Requerido.</div>
                                <small id="idparroquia-representante-error" class="text-danger"></small>
                            </div>

                            <div class="floating-group">
                                <label for="lugar-nacimiento-representante">Lugar de Nacimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lugar-nacimiento-representante"
                                    name="lugar-nacimiento-representante" placeholder=" "
                                    pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s,]+" maxlength="100" required>
                                <div class="invalid-feedback">Lugar de nacimiento requerido.</div>
                                <small id="lugar-nacimiento-representante-error" class="text-danger"></small>
                            </div>
                        </div>

                        <!-- Fila 3: Dirección de Habitación (100% ancho) -->
                        <div class="mb-3">
                            <div class="floating-group">
                                <label for="direccion-representante">Dirección de Residencia <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="direccion-representante"
                                    name="direccion-representante" placeholder=" " required>
                                <div class="invalid-feedback">Dirección requerida.</div>
                            </div>
                        </div>

                        <!-- Sub-bloque 3: Contactos Telefónicos del Representante (Grid 1fr 2fr 1fr 2fr) -->
                        <div class="form-section-header header-representante text-start">
                            <i class="fas fa-phone"></i>
                            <h5>Contactos Telefónicos</h5>
                        </div>

                        <div class="mb-3" style="display: grid; grid-template-columns: 1fr 2fr 1fr 2fr; gap: 16px;">
                            <div class="floating-group">
                                <label for="prefijo-representante">Prefijo 1 <span class="text-danger">*</span></label>
                                <select class="form-select select-modern w-100" id="prefijo-representante"
                                    name="prefijo-representante" required>
                                    <option value="" disabled selected>Prefijo</option>
                                    @foreach ($prefijos_telefono as $prefijo)
                                        <option value="{{ $prefijo->id }}">{{ $prefijo->prefijo }}
                                            ({{ $prefijo->tipo_linea }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Prefijo requerido.</div>
                                <small id="prefijo-representante-error" class="text-danger"></small>
                            </div>

                            <div class="floating-group">
                                <label for="telefono-representante">Teléfono 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="telefono-representante"
                                    name="telefono-representante" pattern="[0-9]+" maxlength="11"
                                    title="Ingrese solo números (máximo 11 dígitos)" placeholder=" " required>
                                <div class="invalid-feedback">Teléfono válido requerido.</div>
                                <small id="telefono-representante-error" class="text-danger"></small>
                            </div>

                            <div class="floating-group">
                                <label for="prefijo_dos-representante">Prefijo 2</label>
                                <select class="form-select select-modern w-100" id="prefijo_dos-representante"
                                    name="prefijo_dos-representante">
                                    <option value="" disabled selected>Prefijo</option>
                                    @foreach ($prefijos_telefono as $prefijo)
                                        <option value="{{ $prefijo->id }}">{{ $prefijo->prefijo }}</option>
                                    @endforeach
                                </select>
                                <small id="prefijo_dos-representante-error" class="text-danger"></small>
                            </div>

                            <div class="floating-group">
                                <label for="telefono_dos-representante">Teléfono 2</label>
                                <input type="text" class="form-control" id="telefono_dos-representante"
                                    name="telefono_dos-representante" pattern="[0-9]+" maxlength="11"
                                    title="Ingrese solo números (máximo 11 dígitos)" placeholder=" ">
                                <small id="telefono_dos-representante-error" class="text-danger"></small>
                            </div>
                        </div>

                        <!-- Sub-bloque 4: Relación Familiar y Ocupación -->
                        <div class="form-section-header header-representante text-start">
                            <i class="fas fa-users"></i>
                            <h5>Relación Familiar y Ocupación</h5>
                        </div>

                        <!-- Fila de 3 columnas para Ocupación, Parentesco y Convive -->
                        <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                            <!-- Ocupación -->
                            <div class="floating-group mb-0">
                                <label for="ocupacion-representante">
                                    <i class="fas fa-briefcase"></i> Ocupación <span class="text-danger">*</span>
                                </label>
                                <select name="ocupacion-representante" id="ocupacion-representante"
                                    class="form-select select-modern w-100" required>
                                    <option value="" disabled selected>Seleccione una ocupación</option>
                                    @foreach ($ocupaciones as $ocupacion)
                                        <option value="{{ $ocupacion->id }}">{{ $ocupacion->nombre_ocupacion }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Seleccione una ocupación.</div>
                                <small id="ocupacion-representante-error" class="text-danger"></small>
                            </div>

                            <!-- Parentesco -->
                            <div class="floating-group mb-0">
                                <label for="parentesco">
                                    <i class="fas fa-user-tag"></i> Parentesco <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select-modern w-100" id="parentesco" name="parentesco"
                                    required>
                                    <option value="" disabled selected>Seleccione el parentesco</option>
                                    <option value="Papá">Papá</option>
                                    <option value="Mamá">Mamá</option>
                                    <option value="Hermano(a)">Hermano(a)</option>
                                    <option value="Abuelo(a)">Abuelo(a)</option>
                                    <option value="Tío(a)">Tío(a)</option>
                                    <option value="Primo(a)">Primo(a)</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <input type="hidden" id="parentesco_hidden" name="parentesco_hidden" value="">
                                <div class="invalid-feedback">Seleccione el parentesco.</div>
                            </div>

                            <!-- Convive -->
                            <div>
                                <label class="form-label" for="convive-si-representante" style="font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 4px;">
                                    <i class="fas fa-home"></i> ¿Convive con el Estudiante? <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex align-items-center gap-3" style="height: 42px;">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="radio" id="convive-si-representante"
                                            name="convive-representante" value="si">
                                        <label class="form-check-label cursor-pointer" for="convive-si-representante">Sí</label>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="radio" id="convive-no-representante"
                                            name="convive-representante" value="no">
                                        <label class="form-check-label cursor-pointer" for="convive-no-representante">No</label>
                                    </div>
                                </div>
                                <small id="convive-representante-error" class="text-danger"></small>
                            </div>
                        </div>

                        <!-- Contenedores condicionales si selecciona Otra Ocupación -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 d-none" id="otra-ocupacion-representante-container">
                                <div class="floating-group">
                                    <label for="otra-ocupacion-representante">
                                        <i class="fas fa-briefcase"></i> Otra Ocupación <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="otra-ocupacion-representante"
                                        name="otra-ocupacion-representante" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                                        placeholder=" ">
                                    <div class="invalid-feedback">Especifique la ocupación.</div>
                                    <small id="otra-ocupacion-representante-error" class="text-danger"></small>
                                </div>
                            </div>

                            <div class="col-md-6 d-none" id="otra-ocupacion-container">
                                <div class="floating-group">
                                    <label for="otra-ocupacion-representante-extra">
                                        <i class="fas fa-briefcase"></i> Especifique Ocupación <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="otra-ocupacion-representante-extra"
                                        name="otra-ocupacion-representante" maxlength="50"
                                        title="Solo se permiten letras y espacios, no se aceptan números"
                                        pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" placeholder=" ">
                                    <small id="otra-ocupacion-error-representante" class="text-danger"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Sub-bloque 5: Conectividad y Participación -->
                        <div class="form-section-header header-representante text-start">
                            <i class="fas fa-wifi"></i>
                            <h5>Conectividad y Participación Comunitaria</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="floating-group">
                                    <label for="correo-representante">
                                        <i class="fas fa-envelope"></i>
                                        Correo Electrónico <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="correo-representante"
                                        name="correo-representante" required placeholder=" ">
                                    <div class="invalid-feedback">Correo electrónico válido requerido.</div>
                                    <small id="correo-representante-error" class="text-danger"></small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label-modern">
                                        <i class="fas fa-users"></i>
                                        ¿Pertenece a Organización Política / Comunitaria?
                                    </label>
                                    <div class="d-flex align-items-center gap-3 pt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="organizacion-si"
                                                name="organizacion-representante" value="si"
                                                {{ isset($representante->legal->pertenece_a_organizacion_representante) && $representante->legal->pertenece_a_organizacion_representante ? 'checked' : '' }}
                                                required>
                                            <label class="form-check-label cursor-pointer" for="organizacion-si">Sí</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="organizacion-no"
                                                name="organizacion-representante" value="no"
                                                {{ isset($representante->legal->pertenece_a_organizacion_representante) && !$representante->legal->pertenece_a_organizacion_representante ? 'checked' : (!isset($representante->legal->pertenece_a_organizacion_representante) ? 'checked' : '') }}
                                                required>
                                            <label class="form-check-label cursor-pointer" for="organizacion-no">No</label>
                                        </div>
                                    </div>
                                    <small id="organizacion-representante-error" class="text-danger"></small>
                                </div>
                            </div>

                            <div class="col-md-12" id="especifique-organizacion-container" style="display: none;">
                                <div class="floating-group">
                                    <label for="especifique-organizacion">
                                        <i class="fas fa-edit"></i>
                                        Especifique Organización
                                    </label>
                                    <input type="text" class="form-control" id="especifique-organizacion"
                                        name="especifique-organizacion" placeholder=" ">
                                    <small id="especifique-organizacion-error" class="text-danger"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Sub-bloque 6: Identificación Familiar y Datos de Cuenta -->
                        <div class="form-section-header header-representante text-start">
                            <i class="fas fa-credit-card"></i>
                            <h5>Identificación Familiar y Datos Bancarios</h5>
                        </div>

                        <div class="mb-3" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px;">
                            <!-- 1. Carnet Patria Afiliado -->
                            <div class="floating-group mb-0">
                                <label for="carnet-patria-afiliado">
                                    <i class="fas fa-id-card"></i>
                                    Carnet Patria Afiliado
                                </label>
                                <select class="form-select select-modern w-100" id="carnet-patria-afiliado"
                                    name="carnet-patria-afiliado">
                                    <option value="">Seleccione...</option>
                                    <option value="madre">Madre</option>
                                    <option value="padre">Padre</option>
                                    <option value="otro">Otro</option>
                                </select>
                                <small id="carnet-patria-afiliado-error" class="text-danger"></small>
                            </div>

                            <!-- 2. Código Carnet Patria -->
                            <div class="floating-group mb-0">
                                <label for="codigo-patria">
                                    <i class="fas fa-barcode"></i>
                                    Código Carnet Patria
                                </label>
                                <input type="text" class="form-control" id="codigo-patria"
                                    name="codigo-patria" placeholder=" " pattern="[0-9]{10,15}"
                                    inputmode="numeric" maxlength="15" min="10"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                <small id="codigo-patria-error" class="text-danger"></small>
                            </div>

                            <!-- 3. Serial Carnet Patria -->
                            <div class="floating-group mb-0">
                                <label for="serial-patria">
                                    <i class="fas fa-hashtag"></i>
                                    Serial Carnet Patria
                                </label>
                                <input type="text" class="form-control" id="serial-patria"
                                    name="serial-patria" placeholder=" " pattern="[0-9]{10,15}"
                                    inputmode="numeric" maxlength="15" min="10"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                <small id="serial-patria-error" class="text-danger"></small>
                            </div>

                            <!-- 4. Tipo de Cuenta -->
                            <div class="floating-group mb-0">
                                <label for="tipo-cuenta">
                                    <i class="fas fa-credit-card"></i>
                                    Tipo de Cuenta
                                </label>
                                <select class="form-select select-modern w-100" id="tipo-cuenta" name="tipo-cuenta">
                                    <option value="">Seleccione tipo de cuenta...</option>
                                    <option value="Ahorro">Cuenta de Ahorro</option>
                                    <option value="Corriente">Cuenta Corriente</option>
                                </select>
                                <small id="tipo-cuenta-error" class="text-danger"></small>
                            </div>

                            <!-- 5. Banco -->
                            <div class="floating-group mb-0">
                                <label for="banco_id">
                                    <i class="fas fa-university"></i>
                                    Banco
                                </label>
                                <select class="form-select select-modern w-100" id="banco_id" name="banco_id">
                                    <option value="" disabled selected>Seleccione un banco</option>
                                    @foreach ($bancos as $banco)
                                        <option value="{{ $banco->id }}">{{ $banco->nombre_banco }}</option>
                                    @endforeach
                                </select>
                                <small id="banco-error" class="text-danger"></small>
                            </div>
                        </div>

                        <!-- Sub-bloque 7: Dirección de Habitación del Representante -->
                        <div class="form-section-header header-representante text-start">
                            <i class="fas fa-home"></i>
                            <h5>Dirección de Habitación Detallada</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="floating-group">
                                    <label for="direccion-habitacion">
                                        <i class="fas fa-home"></i>
                                        Dirección Completa <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="direccion-habitacion" name="direccion-habitacion" rows="3"
                                        required placeholder=" "></textarea>
                                    <div class="invalid-feedback">Dirección válida requerida.</div>
                                    <small id="direccion-habitacion-error" class="text-danger"></small>
                                </div>
                            </div>
                        </div>
                    </div><!-- Fin datos-representante -->
                    </div><!-- Fin campos-legal -->
                </div><!-- Fin col-12 px-0 Representante Legal -->
            </div><!-- Fin card-body Representante Legal -->
            </div><!-- Fin card Representante Legal -->

            <!-- Botones de Acción Footer -->
            <div class="d-flex flex-column flex-sm-row justify-content-end gap-3 pt-3 pb-4 border-top mt-3 w-100">
                @if ($from === 'inscripcion')
                    <a href="{{ route('admin.transacciones.inscripcion.create') }}" class="btn-secondary-modern w-100 w-sm-auto justify-content-center">
                        <i class="fas fa-arrow-left me-2"></i> Cancelar y Volver
                    </a>
                @else
                    <a href="{{ route('representante.index') }}" class="btn-secondary-modern w-100 w-sm-auto justify-content-center">
                        <i class="fas fa-arrow-left me-2"></i> Cancelar y Volver
                    </a>
                @endif
                <button type="submit" class="btn-primary-modern w-100 w-sm-auto justify-content-center">
                    <i class="fas fa-save me-2"></i> {{ isset($representante) ? 'Actualizar' : 'Guardar' }} Representante
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Shim no-op para erradicar Bootstrap Selectpicker y trabajar 100% con selects nativos
        if (typeof jQuery !== 'undefined') {
            jQuery.fn.selectpicker = function(action, ...args) {
                if (action === 'val') {
                    if (args.length > 0) {
                        return this.each(function() { this.value = args[0]; });
                    }
                    return this.val();
                }
                return this;
            };
        }
        // Datos cargados desde Blade
        const ubicacionesData = @json($estados);
        const ocupaciones = @json(\App\Models\Ocupacion::all());
        const prefijosTelefono = @json($prefijos_telefono);

        // [NUEVA FUNCIÓN] Diagnóstico completo de selectpickers
        function diagnosticarSelectpicker(selectId) {
            const select = document.getElementById(selectId);
            if (!select) {
                console.log(`[DIAGNÓSTICO] ${selectId}: Elemento no encontrado`);
                return;
            }

            console.log(`=== DIAGNÓSTICO COMPLETO DE ${selectId} ===`);
            
            // 1. Inspeccionar HTML nativo
            const optionsNativas = Array.from(select.options);
            console.log(`[HTML NATIVO] Total opciones: ${optionsNativas.length}`);
            optionsNativas.forEach((opt, i) => {
                console.log(`  ${i}: value="${opt.value}", text="${opt.text}", display="${opt.style.display}"`);
            });

            // 2. Inspeccionar Bootstrap-Select
            const $select = $(select);
            const selectpickerData = $select.data('selectpicker');
            
            if (selectpickerData) {
                console.log(`[BOOTSTRAP-SELECT] Selectpicker inicializado: true`);
                
                // 3. Inspeccionar elementos DOM del selectpicker
                const bootstrapSelect = select.nextElementSibling;
                if (bootstrapSelect && bootstrapSelect.classList.contains('bootstrap-select')) {
                    const dropdownItems = bootstrapSelect.querySelectorAll('.dropdown-item');
                    console.log(`[BOOTSTRAP-SELECT] Items en dropdown: ${dropdownItems.length}`);
                    
                    dropdownItems.forEach((item, i) => {
                        const text = item.textContent.trim();
                        const hidden = item.style.display === 'none';
                        const disabled = item.classList.contains('disabled');
                        console.log(`  ${i}: "${text}" (hidden: ${hidden}, disabled: ${disabled})`);
                    });
                    
                    // 4. Verificar duplicados en el dropdown
                    const textos = Array.from(dropdownItems).map(item => item.textContent.trim());
                    const duplicados = textos.filter((text, index) => textos.indexOf(text) !== index);
                    if (duplicados.length > 0) {
                        console.log(`[BOOTSTRAP-SELECT] DUPLICADOS ENCONTRADOS:`, duplicados);
                    } else {
                        console.log(`[BOOTSTRAP-SELECT] Sin duplicados en dropdown`);
                    }
                } else {
                    console.log(`[BOOTSTRAP-SELECT] No se encontró elemento bootstrap-select`);
                }
            } else {
                console.log(`[BOOTSTRAP-SELECT] Selectpicker NO inicializado`);
            }
            
            console.log(`=== FIN DIAGNÓSTICO DE ${selectId} ===`);
        }

        // [NUEVA FUNCIÓN] Diagnóstico de todos los selects del representante
        function diagnosticarTodosLosSelects() {
            const selectIds = [
                'prefijo-representante',
                'prefijo_dos-representante',
                'ocupacion-representante',
                'parentesco',
                'idPais-representante',
                'idEstado-representante',
                'idMunicipio-representante',
                'idparroquia-representante'
            ];
            
            console.log('\n🔍 INICIANDO DIAGNÓSTICO COMPLETO DE TODOS LOS SELECTS 🔍\n');
            
            selectIds.forEach(id => {
                diagnosticarSelectpicker(id);
                console.log(''); // Espacio entre diagnósticos
            });
        }
        function limpiarSelectPickerNuclear(selectElement, selectId) {
            if (!selectElement) return;
            
            console.log(`[LIMPIEZA NUCLEAR] Iniciando limpieza nuclear para ${selectId}`);
            
            try {
                const $select = $(selectElement);
                
                // PASO 1: Guardar HTML original la primera vez
                if (!selectElement.hasAttribute('data-original-html')) {
                    const originalHTML = selectElement.outerHTML;
                    selectElement.setAttribute('data-original-html', originalHTML);
                    console.log(`[LIMPIEZA NUCLEAR] HTML original guardado para ${selectId}`);
                }
                
                // PASO 2: Eliminar COMPLETAMENTE el selectpicker del DOM
                if ($select.data('selectpicker')) {
                    try {
                        $select.selectpicker('destroy');
                    } catch (e) {
                        console.warn(`[LIMPIEZA NUCLEAR] Error destruyendo selectpicker ${selectId}:`, e);
                    }
                }
                
                // PASO 3: Eliminar el selectpicker del jQuery data
                $select.removeData('selectpicker');
                
                // PASO 4: Restaurar HTML original si está corrupto
                const originalHTML = selectElement.getAttribute('data-original-html');
                if (originalHTML) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = originalHTML;
                    const newSelect = tempDiv.querySelector('select');
                    
                    if (newSelect && newSelect.id === selectId) {
                        // Reemplazar el select actual con el original
                        selectElement.parentNode.replaceChild(newSelect, selectElement);
                        selectElement = newSelect;
                        console.log(`[LIMPIEZA NUCLEAR] HTML original restaurado para ${selectId}`);
                    }
                }
                
                // PASO 5: Limpiar selección del select restaurado
                selectElement.value = '';
                selectElement.selectedIndex = -1;
                
                // PASO 6: Re-inicializar selectpicker con delay
                setTimeout(() => {
                    try {
                        const $newSelect = $(selectElement);
                        $newSelect.selectpicker({
                            liveSearch: true,
                            size: 8,
                            noneResultsText: 'No hay resultados para {0}',
                            selectOnTab: false,
                            showSubtext: false,
                            showIcon: true,
                            width: 'auto'
                        });
                        
                        // PASO 7: Forzar limpieza completa del estado
                        $newSelect.selectpicker('val', '');
                        $newSelect.selectpicker('deselectAll');
                        $newSelect.selectpicker('render');
                        $newSelect.selectpicker('refresh');
                        
                        console.log(`[LIMPIEZA NUCLEAR] ${selectId}: Selectpicker recreado con HTML original`);
                        
                    } catch (initError) {
                        console.error(`[LIMPIEZA NUCLEAR] Error inicializando ${selectId}:`, initError);
                    }
                }, 50);
                
            } catch (error) {
                console.error(`[LIMPIEZA NUCLEAR] Error crítico en ${selectId}:`, error);
            }
        }

        // [NUEVA FUNCIÓN] Limpieza ultra-agresiva para selectpickers (combina todos los métodos)
        function limpiarSelectPickerUltra(selectElement, selectId) {
            if (!selectElement) return;
            
            console.log(`[LIMPIEZA ULTRA] Iniciando limpieza ultra-agresiva para ${selectId}`);
            
            try {
                const $select = $(selectElement);
                
                // MÉTODO 1: Limpieza básica del select nativo
                selectElement.value = '';
                selectElement.selectedIndex = -1;
                
                // MÉTODO 2: Limpieza agresiva si el selectpicker está inicializado
                if ($select.data('selectpicker')) {
                    console.log(`[LIMPIEZA ULTRA] Selectpicker detectado, aplicando limpieza completa...`);
                    
                    // Paso 1: Limpiar valor y deseleccionar todo
                    $select.selectpicker('val', '');
                    $select.selectpicker('deselectAll');
                    
                    // Paso 2: Forzar render para limpiar texto visual
                    $select.selectpicker('render');
                    
                    // Paso 3: Refresh para actualizar estado interno
                    $select.selectpicker('refresh');
                    
                    // Paso 4: Destruir para eliminar estado residual
                    $select.selectpicker('destroy');
                    
                    // Paso 5: Recrear con configuración limpia (solo para selects estáticos)
                    if (selectId.includes('prefijo') || selectId.includes('ocupacion') || selectId.includes('parentesco') || selectId.includes('idPais')) {
                        // El HTML original ya está intacto, solo re-inicializar
                        $select.selectpicker({
                            liveSearch: true,
                            size: 8,
                            noneResultsText: 'No hay resultados para {0}',
                            selectOnTab: false,
                            showSubtext: false,
                            showIcon: true,
                            width: 'auto'
                        });
                        console.log(`[LIMPIEZA ULTRA] ${selectId}: Selectpicker estático recreado`);
                    } else {
                        // Para selects dinámicos, usar limpiarSelectCompleto
                        limpiarSelectCompleto(selectElement);
                        console.log(`[LIMPIEZA ULTRA] ${selectId}: Selectpicker dinámico limpiado con limpiarSelectCompleto`);
                    }
                } else {
                    console.log(`[LIMPIEZA ULTRA] ${selectId}: Selectpicker no inicializado, limpieza básica aplicada`);
                }
                
                console.log(`[LIMPIEZA ULTRA] ${selectId}: Limpieza completada exitosamente`);
                
            } catch (error) {
                console.error(`[LIMPIEZA ULTRA] Error en ${selectId}:`, error);
                // Fallback de emergencia
                try {
                    selectElement.value = '';
                    selectElement.selectedIndex = -1;
                    const $select = $(selectElement);
                    if ($select.data('selectpicker')) {
                        $select.selectpicker('val', '');
                        $select.selectpicker('refresh');
                    }
                } catch (fallbackError) {
                    console.error(`[LIMPIEZA ULTRA] Error en fallback para ${selectId}:`, fallbackError);
                }
            }
        }

        // [NUEVA FUNCIÓN] Limpieza ultra segura para selects estáticos (ocupación, prefijos)
        function limpiarSelectPickerEstatico(selectElement, placeholderText = 'Seleccione') {
            if (!selectElement) return;

            const $select = $(selectElement);
            const selectId = selectElement.id;

            // Evitar limpieza duplicada del mismo select
            if (selectElement.hasAttribute('data-limpiando')) {
                console.log(`[LIMPIEZA ESTATICO] Omitiendo limpieza duplicada para ${selectId}`);
                return;
            }

            try {
                console.log(`[LIMPIEZA ESTATICO] Iniciando limpieza para ${selectId}`);
                
                // Marcar como en proceso de limpieza
                selectElement.setAttribute('data-limpiando', 'true');

                // Guardar datos originales la primera vez
                if (!selectElement.hasAttribute('data-original-options')) {
                    const opcionesOriginales = [];
                    Array.from(selectElement.options).forEach(option => {
                        if (option.value !== '') { // No guardar placeholder
                            opcionesOriginales.push({
                                value: option.value,
                                text: option.text,
                                disabled: option.disabled
                            });
                        }
                    });
                    selectElement.setAttribute('data-original-options', JSON.stringify(opcionesOriginales));
                    console.log(`[LIMPIEZA ESTATICO] Opciones originales guardadas para ${selectId}:`, opcionesOriginales.length);
                }

                // Destruir selectpicker si existe
                if ($select.data('selectpicker')) {
                    $select.selectpicker('destroy');
                    console.log(`[LIMPIEZA ESTATICO] Selectpicker destruido para ${selectId}`);
                }

                // Reconstruir HTML con opciones originales
                let html = `<option value="">${placeholderText}</option>`;
                const opcionesOriginales = JSON.parse(selectElement.getAttribute('data-original-options') || '[]');
                opcionesOriginales.forEach(option => {
                    html += `<option value="${option.value}"${option.disabled ? ' disabled' : ''}>${option.text}</option>`;
                });
                
                selectElement.innerHTML = html;
                
                // Limpiar selección
                selectElement.value = '';
                selectElement.selectedIndex = -1;

                // Recrear selectpicker con configuración limpia
                $select.selectpicker({
                    liveSearch: true,
                    size: 8,
                    noneResultsText: 'No hay resultados para {0}',
                    selectOnTab: false,
                    showSubtext: false,
                    showIcon: true,
                    width: 'auto'
                });

                // Forzar refresh y disparar evento
                $select.selectpicker('refresh');
                const event = new Event('change', { bubbles: true });
                selectElement.dispatchEvent(event);

                // Limpiar marca de proceso de limpieza
                selectElement.removeAttribute('data-limpiando');

                console.log(`[LIMPIEZA ESTATICO] Limpieza estática completada para ${selectId}`);
            } catch (error) {
                console.error(`[LIMPIEZA ESTATICO] Error en limpieza para ${selectId}:`, error);
                // Fallback - usar limpiarSelectCompleto que ya existe
                limpiarSelectCompleto(selectElement);
            }
        }

        // Función unificada para cargar selects anidados
        function cargarSelectAnidado(tipo, parentId, targetSelectId, clearSelectId = null) {
            // BLOQUEO DE COPIA: No ejecutar si estamos copiando datos
            if (window.copiandoDatosProgenitor) {
                console.log(`[CARGAR_SELECT] ⚠️ Modo copia activo, omitiendo carga de ${targetSelectId}`);
                return;
            }
            
            const targetSelect = document.getElementById(targetSelectId);
            const clearSelect = clearSelectId ? document.getElementById(clearSelectId) : null;

            if (!targetSelect) {
                console.error(`[CARGAR_SELECT] Target select no encontrado: ${targetSelectId}`);
                return;
            }

            console.log(`[CARGAR_SELECT] Iniciando carga: tipo=${tipo}, parentId=${parentId}, target=${targetSelectId}`);

            // Limpiar selects
            limpiarSelectCompleto(targetSelect);
            if (clearSelect) limpiarSelectCompleto(clearSelect);
            
            // Verificación adicional: asegurar que los selects estén completamente vacíos
            if (targetSelect.options.length > 1) {
                console.warn(`[CARGAR_SELECT] El select ${targetSelectId} aún tiene ${targetSelect.options.length} opciones después de limpiar, forzando limpieza completa`);
                console.warn(`[CARGAR_SELECT] Opciones existentes:`, Array.from(targetSelect.options).map(o => ({value: o.value, text: o.text})));
                targetSelect.innerHTML = '<option value="">Seleccione un ' + tipo + '</option>';
            }
            if (clearSelect && clearSelect.options.length > 1) {
                console.warn(`[CARGAR_SELECT] El select ${clearSelectId} aún tiene ${clearSelect.options.length} opciones después de limpiar, forzando limpieza completa`);
                console.warn(`[CARGAR_SELECT] Opciones existentes en clearSelect:`, Array.from(clearSelect.options).map(o => ({value: o.value, text: o.text})));
                clearSelect.innerHTML = '<option value="">Seleccione</option>';
            }

            if (!parentId) {
                console.warn(`[CARGAR_SELECT] parentId vacío, saliendo`);
                return;
            }

            let options = '<option value="">Seleccione un ' + tipo + '</option>';
            let datos = [];
            let datosUnicos = [];

            if (tipo === 'estado') {
                datos = ubicacionesData.filter(e => String(e.pais_id) === String(parentId));
                console.log(`[CARGAR_SELECT] Estados encontrados para país ${parentId}:`, datos.length);
                
                // Usar Set para eliminar duplicados por ID
                const idsVistos = new Set();
                datosUnicos = datos.filter(item => {
                    if (idsVistos.has(item.id)) {
                        console.warn(`[CARGAR_SELECT] Estado duplicado detectado y eliminado:`, item);
                        return false;
                    }
                    idsVistos.add(item.id);
                    return true;
                });
                
                datosUnicos.forEach(item => {
                    options += `<option value="${item.id}">${item.nombre_estado}</option>`;
                });
                console.log(`[CARGAR_SELECT] Estados únicos después de filtrar:`, datosUnicos.length);
                
            } else if (tipo === 'municipio') {
                const estado = ubicacionesData.find(e => e.id == parentId);
                if (estado && estado.municipio) {
                    datos = estado.municipio;
                    console.log(`[CARGAR_SELECT] Municipios encontrados para estado ${parentId}:`, datos.length);
                    
                    // Usar Set para eliminar duplicados por ID
                    const idsVistos = new Set();
                    datosUnicos = datos.filter(item => {
                        if (idsVistos.has(item.id)) {
                            console.warn(`[CARGAR_SELECT] Municipio duplicado detectado y eliminado:`, item);
                            return false;
                        }
                        idsVistos.add(item.id);
                        return true;
                    });
                    
                    datosUnicos.forEach(item => {
                        options += `<option value="${item.id}">${item.nombre_municipio}</option>`;
                    });
                    console.log(`[CARGAR_SELECT] Municipios únicos después de filtrar:`, datosUnicos.length);
                }
            } else if (tipo === 'localidad') {
                for (const estado of ubicacionesData) {
                    if (estado.municipio) {
                        const municipio = estado.municipio.find(m => m.id == parentId);
                        if (municipio && municipio.localidades) {
                            datos = municipio.localidades;
                            console.log(`[CARGAR_SELECT] Localidades encontradas para municipio ${parentId}:`, datos.length);
                            
                            // Usar Set para eliminar duplicados por ID
                            const idsVistos = new Set();
                            datosUnicos = datos.filter(item => {
                                if (idsVistos.has(item.id)) {
                                    console.warn(`[CARGAR_SELECT] Localidad duplicada detectada y eliminada:`, item);
                                    return false;
                                }
                                idsVistos.add(item.id);
                                return true;
                            });
                            
                            datosUnicos.forEach(item => {
                                options += `<option value="${item.id}">${item.nombre_localidad}</option>`;
                            });
                            console.log(`[CARGAR_SELECT] Localidades únicas después de filtrar:`, datosUnicos.length);
                            break;
                        }
                    }
                }
            }

            // Validar datos antes de generar opciones
            if (datosUnicos.length === 0) {
                console.warn(`[CARGAR_SELECT] No se encontraron ${tipo} únicos para parentId=${parentId}`);
            }

            // Guardar el valor actual antes de modificar
            const valorActual = targetSelect.value;
            const valorGuardado = targetSelect.getAttribute('data-valor-guardado');
            
            console.log(`[CARGAR_SELECT] Valores guardados - actual: "${valorActual}", guardado: "${valorGuardado}"`);
            
            targetSelect.innerHTML = options;
            
            // Re-inicializar selectpicker solo si el elemento lo usa
            const $targetSelect = $(targetSelect);
            if ($targetSelect.hasClass('selectpicker')) {
                try {
                    $targetSelect.selectpicker('destroy');
                    $targetSelect.selectpicker({
                        liveSearch: true,
                        size: 8,
                        noneResultsText: 'No hay resultados para {0}',
                        selectOnTab: false,
                        showSubtext: false,
                        showIcon: true,
                        width: 'auto'
                    });
                    
                    // Restaurar el valor si existía previamente
                    if (valorGuardado) {
                        $targetSelect.selectpicker('val', valorGuardado);
                        console.log(`[CARGAR_SELECT] Valor restaurado desde data-valor-guardado: "${valorGuardado}"`);
                    } else if (valorActual) {
                        $targetSelect.selectpicker('val', valorActual);
                        console.log(`[CARGAR_SELECT] Valor restaurado desde value: "${valorActual}"`);
                    }
                } catch (e) {
                    console.error(`[CARGAR_SELECT] Error al inicializar selectpicker:`, e);
                    $targetSelect.selectpicker({
                        liveSearch: true,
                        size: 8,
                        noneResultsText: 'No hay resultados para {0}',
                        selectOnTab: false,
                        showSubtext: false,
                        showIcon: true,
                        width: 'auto'
                    });
                    
                    // Restaurar el valor si existía previamente
                    if (valorGuardado) {
                        $targetSelect.selectpicker('val', valorGuardado);
                    } else if (valorActual) {
                        $targetSelect.selectpicker('val', valorActual);
                    }
                }
            } else {
                // Select nativo con diseño moderno
                if (valorGuardado) {
                    targetSelect.value = valorGuardado;
                } else if (valorActual) {
                    targetSelect.value = valorActual;
                }
            }

            console.log(`[CARGAR_SELECT] Carga completada para ${targetSelectId}`);
        }

        // Versión promise-based de cargarSelectAnidado para carga secuencial
        function cargarSelectAnidadoPromise(tipo, parentId, targetSelectId, clearSelectId = null) {
            return new Promise((resolve, reject) => {
                // BLOQUEO DE COPIA: No ejecutar si estamos copiando datos
                if (window.copiandoDatosProgenitor) {
                    console.log(`[CARGAR_SELECT_PROMISE] ⚠️ Modo copia activo, omitiendo carga de ${targetSelectId}`);
                    resolve(); // Resolver la promise para no romper la cadena
                    return;
                }
                
                // BLOQUEO DE COPIA: Verificación adicional antes de ejecutar cualquier cosa
                if (window.copiandoDatosProgenitor) {
                    console.log(`[CARGAR_SELECT_PROMISE] ⚠️ Verificación adicional: Modo copia activo, omitiendo ${targetSelectId}`);
                    resolve();
                    return;
                }
                
                try {
                    const targetSelect = document.getElementById(targetSelectId);
                    const clearSelect = clearSelectId ? document.getElementById(clearSelectId) : null;

                    if (!targetSelect) {
                        reject(new Error(`Select target no encontrado: ${targetSelectId}`));
                        return;
                    }

                    console.log(`[CARGAR_SELECT_PROMISE] Iniciando carga: tipo=${tipo}, parentId=${parentId}, target=${targetSelectId}`);

                    // Limpiar selects con método SIMPLE (sin selectpicker)
                    const limpiarSelectSimple = (selectElement) => {
                        if (selectElement) {
                            // Solo limpiar HTML, NO tocar selectpicker
                            const placeholderOption = selectElement.querySelector('option[value=""]');
                            const placeholderHTML = placeholderOption ? placeholderOption.outerHTML : `<option value="">Seleccione un ${tipo}</option>`;
                            selectElement.innerHTML = placeholderHTML;
                            console.log(`[CARGAR_SELECT_PROMISE] Select ${selectElement.id} limpiado con método simple`);
                        }
                    };
                    
                    limpiarSelectSimple(targetSelect);
                    if (clearSelect) limpiarSelectSimple(clearSelect);
                    
                    // Verificación adicional: asegurar que los selects estén completamente vacíos
                    if (targetSelect.options.length > 1) {
                        console.warn(`[CARGAR_SELECT_PROMISE] El select ${targetSelectId} aún tiene ${targetSelect.options.length} opciones después de limpiar, forzando limpieza completa`);
                        console.warn(`[CARGAR_SELECT_PROMISE] Opciones existentes:`, Array.from(targetSelect.options).map(o => ({value: o.value, text: o.text})));
                        targetSelect.innerHTML = '<option value="">Seleccione un ' + tipo + '</option>';
                    }
                    if (clearSelect && clearSelect.options.length > 1) {
                        console.warn(`[CARGAR_SELECT_PROMISE] El select ${clearSelectId} aún tiene ${clearSelect.options.length} opciones después de limpiar, forzando limpieza completa`);
                        console.warn(`[CARGAR_SELECT_PROMISE] Opciones existentes en clearSelect:`, Array.from(clearSelect.options).map(o => ({value: o.value, text: o.text})));
                        clearSelect.innerHTML = '<option value="">Seleccione</option>';
                    }

                    if (!parentId) {
                        console.log(`[CARGAR_SELECT_PROMISE] parentId vacío, resolviendo con null`);
                        resolve(null);
                        return;
                    }

                    let options = '<option value="">Seleccione un ' + tipo + '</option>';
                    let datos = [];
                    let datosUnicos = [];

                    if (tipo === 'estado') {
                        datos = ubicacionesData.filter(e => String(e.pais_id) === String(parentId));
                        console.log(`[CARGAR_SELECT_PROMISE] Estados encontrados para país ${parentId}:`, datos.length);
                        
                        // Usar Set para eliminar duplicados por ID
                        const idsVistos = new Set();
                        datosUnicos = datos.filter(item => {
                            if (idsVistos.has(item.id)) {
                                console.warn(`[CARGAR_SELECT_PROMISE] Estado duplicado detectado y eliminado:`, item);
                                return false;
                            }
                            idsVistos.add(item.id);
                            return true;
                        });
                        
                        datosUnicos.forEach(item => {
                            options += `<option value="${item.id}">${item.nombre_estado}</option>`;
                        });
                        console.log(`[CARGAR_SELECT_PROMISE] Estados únicos después de filtrar:`, datosUnicos.length);
                        
                    } else if (tipo === 'municipio') {
                        const estado = ubicacionesData.find(e => e.id == parentId);
                        if (estado && estado.municipio) {
                            datos = estado.municipio;
                            console.log(`[CARGAR_SELECT_PROMISE] Municipios encontrados para estado ${parentId}:`, datos.length);
                            
                            // Usar Set para eliminar duplicados por ID
                            const idsVistos = new Set();
                            datosUnicos = datos.filter(item => {
                                if (idsVistos.has(item.id)) {
                                    console.warn(`[CARGAR_SELECT_PROMISE] Municipio duplicado detectado y eliminado:`, item);
                                    return false;
                                }
                                idsVistos.add(item.id);
                                return true;
                            });
                            
                            datosUnicos.forEach(item => {
                                options += `<option value="${item.id}">${item.nombre_municipio}</option>`;
                            });
                            console.log(`[CARGAR_SELECT_PROMISE] Municipios únicos después de filtrar:`, datosUnicos.length);
                        }
                    } else if (tipo === 'localidad') {
                        for (const estado of ubicacionesData) {
                            if (estado.municipio) {
                                const municipio = estado.municipio.find(m => m.id == parentId);
                                if (municipio && municipio.localidades) {
                                    datos = municipio.localidades;
                                    console.log(`[CARGAR_SELECT_PROMISE] Localidades encontradas para municipio ${parentId}:`, datos.length);
                                    
                                    // Usar Set para eliminar duplicados por ID
                                    const idsVistos = new Set();
                                    datosUnicos = datos.filter(item => {
                                        if (idsVistos.has(item.id)) {
                                            console.warn(`[CARGAR_SELECT_PROMISE] Localidad duplicada detectada y eliminada:`, item);
                                            return false;
                                        }
                                        idsVistos.add(item.id);
                                        return true;
                                    });
                                    
                                    datosUnicos.forEach(item => {
                                        options += `<option value="${item.id}">${item.nombre_localidad}</option>`;
                                    });
                                    console.log(`[CARGAR_SELECT_PROMISE] Localidades únicas después de filtrar:`, datosUnicos.length);
                                    break;
                                }
                            }
                        }
                    }

                    // Validar datos antes de generar opciones
                    if (datosUnicos.length === 0) {
                        console.warn(`[CARGAR_SELECT_PROMISE] No se encontraron ${tipo} únicos para parentId=${parentId}`);
                    }

                    // Guardar el valor actual antes de modificar
                    const valorActual = targetSelect.value;
                    const valorGuardado = targetSelect.getAttribute('data-valor-guardado');
                    
                    console.log(`[CARGAR_SELECT_PROMISE] Valores guardados - actual: "${valorActual}", guardado: "${valorGuardado}"`);
                    
                    targetSelect.innerHTML = options;
                    
                    // Actualizar selectpicker con método SIMPLE (sin destroy)
                    const $targetSelect = $(targetSelect);
                    try {
                        // Solo refrescar el selectpicker existente, NO destruirlo
                        if ($targetSelect.data('selectpicker')) {
                            $targetSelect.selectpicker('refresh');
                            console.log(`[CARGAR_SELECT_PROMISE] Selectpicker refrescado para ${targetSelectId}`);
                        } else {
                            // Inicializar solo si no existe
                            $targetSelect.selectpicker({
                                liveSearch: true,
                                size: 8,
                                noneResultsText: 'No hay resultados para {0}',
                                selectOnTab: false,
                                showSubtext: false,
                                showIcon: true,
                                width: 'auto'
                            });
                            console.log(`[CARGAR_SELECT_PROMISE] Selectpicker inicializado para ${targetSelectId}`);
                        }
                        
                        // Restaurar el valor si existía previamente (usando método simple)
                        if (valorGuardado) {
                            targetSelect.value = valorGuardado;
                            console.log(`[CARGAR_SELECT_PROMISE] Valor restaurado desde data-valor-guardado: "${valorGuardado}"`);
                        } else if (valorActual) {
                            targetSelect.value = valorActual;
                            console.log(`[CARGAR_SELECT_PROMISE] Valor restaurado desde value: "${valorActual}"`);
                        }

                        // Esperar un poco para que se complete la actualización
                        setTimeout(() => {
                            resolve({
                                select: targetSelect,
                                datos: datosUnicos,
                                valorGuardado: valorGuardado || valorActual
                            });
                        }, 200);
                    } catch (e) {
                        console.error(`[CARGAR_SELECT_PROMISE] Error al inicializar selectpicker:`, e);
                        $targetSelect.selectpicker({
                            liveSearch: true,
                            size: 8,
                            noneResultsText: 'No hay resultados para {0}',
                            selectOnTab: false,
                            showSubtext: false,
                            showIcon: true,
                            width: 'auto'
                        });
                        
                        setTimeout(() => {
                            resolve({
                                select: targetSelect,
                                datos: datosUnicos,
                                valorGuardado: valorGuardado || valorActual
                            });
                        }, 200);
                    }
                } catch (error) {
                    console.error(`[CARGAR_SELECT_PROMISE] Error general:`, error);
                    reject(error);
                }
            });
        }
        function mostrarAlertaAusencia(tipo) {
            const nombre = tipo === 'madre' ? 'la madre' : 'el padre';

            // Crear modal de alerta moderna SaaS
            const alertaHTML = `
                <div class="modal fade" id="alertaAusenciaModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content modal-modern" style="border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; background-color: #FFFFFF;">
                            <div class="modal-header border-bottom p-4" style="background-color: #FFFFFF;">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #FEF2F2; color: #EF4444; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <h5 class="modal-title fw-bold text-dark mb-0" style="color: #1E293B !important; font-size: 1.15rem;">Confirmar Ausencia</h5>
                                        <p class="text-muted small mb-0">Acción requerida para continuar</p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6; color: #475569;">
                                    Marcar como <strong style="color: #DC2626;">AUSENTE</strong> a ${nombre} bloqueará la edición de esta sección en este formulario. ¿Desea continuar con esta acción?
                                </p>
                            </div>
                            <div class="modal-footer border-top p-3 px-4 d-flex justify-content-end gap-2" style="background-color: #F8FAFC;">
                                <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal" id="btnCancelarAusencia">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </button>
                                <button type="button" class="btn-danger-modern" id="btnConfirmarAusencia">
                                    <i class="fas fa-check me-2"></i>Confirmar Ausencia
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Eliminar modal anterior si existe
            const modalAnterior = document.getElementById('alertaAusenciaModal');
            if (modalAnterior) modalAnterior.remove();

            // Agregar nuevo modal al body
            document.body.insertAdjacentHTML('beforeend', alertaHTML);

            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('alertaAusenciaModal'));
            modal.show();

            // Manejar confirmación
            document.getElementById('btnConfirmarAusencia').addEventListener('click', function() {
                modal.hide();
                setTimeout(() => {
                    document.getElementById('alertaAusenciaModal')?.remove();
                }, 500);
            });

            // Manejar cancelación
            document.getElementById('btnCancelarAusencia').addEventListener('click', function() {
                modal.hide();
                setTimeout(() => {
                    document.getElementById('alertaAusenciaModal')?.remove();
                }, 500);
            });
        }

        function validarSoloNumeros(input) {
            input.addEventListener('input', function() {
                // Eliminar cualquier caracter que no sea número
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Limitar a 15 dígitos máximo
                if (this.value.length > 15) {
                    this.value = this.value.slice(0, 15);
                }
            });

            // Prevenir pegar texto no numérico
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const texto = e.clipboardData.getData('text');
                const numeros = texto.replace(/[^0-9]/g, '').slice(0, 15);
                document.execCommand('insertText', false, numeros);
            });
            
            // Validar longitud mínima al perder el foco
            input.addEventListener('blur', function() {
                if (this.value.length > 0 && this.value.length < 10) {
                    this.setCustomValidity('El campo debe tener entre 10 y 15 dígitos');
                } else {
                    this.setCustomValidity('');
                }
            });
        }

        // Aplicar validación a campos numéricos
        document.addEventListener('DOMContentLoaded', function() {
            const codigoInput = document.getElementById('codigo-patria');
            const serialInput = document.getElementById('serial-patria');

            if (codigoInput) validarSoloNumeros(codigoInput);
            if (serialInput) validarSoloNumeros(serialInput);

            // Configurar eventos de ubicación después de la inicialización
            setTimeout(() => {
                configurarEventosRepresentante();
            }, 200);
        });

        // Inicialización de tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Asegurar que solo los selects que NO son selectpicker tengan la clase form-select
            const selectElements = document.querySelectorAll('select:not(.selectpicker)');
            selectElements.forEach(select => {
                select.classList.add('form-select');
                select.style.width = '100%';

                // Eliminar atributos de Select2 si existen
                select.removeAttribute('data-select2-id');
                select.removeAttribute('data-toggle');
                select.removeAttribute('data-allow-clear');
                select.removeAttribute('data-placeholder');

                // Eliminar cualquier elemento creado por Select2
                const select2Container = select.nextElementSibling;
                if (select2Container && select2Container.classList.contains('select2-container')) {
                    select2Container.remove();
                }
            });

            // Prevenir la inicialización de Select2 en caso de que esté presente
            if (typeof $.fn.select2 !== 'undefined') {
                $.fn.select2 = undefined;
            }
        });

        // Validación del formulario
        (function() {
            'use strict'

            // Obtener todos los formularios que necesitan validación
            var forms = document.querySelectorAll('.needs-validation')

            // Bucle sobre los formularios y evitar el envío
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    // Habilitar temporalmente todos los campos readOnly para que se envíen
                    const readonlyFields = form.querySelectorAll('[readonly]');
                    readonlyFields.forEach(field => {
                        field.readOnly = false;
                    });
                    
                    // Ejecutar validación personalizada primero
                    if (!validarFormularioCompleto()) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Restaurar readonly después de validación fallida
                        readonlyFields.forEach(field => {
                            field.readOnly = true;
                        });
                        
                        return false;
                    }
                    
                    console.log('=== VALIDACIÓN PERSONALIZADA PASÓ ===');
                    
                    // Luego ejecutar validación de Bootstrap para campos requeridos
                    console.log('=== VERIFICANDO VALIDACIÓN BOOTSTRAP ===');
                    console.log('Form checkValidity ANTES:', form.checkValidity());
                    
                    // Remover required de campos en secciones deshabilitadas temporalmente
                    const camposEnSeccionesDeshabilitadas = [];
                    
                    // Buscar todos los campos que están deshabilitados Y requeridos
                    const camposDeshabilitadosRequeridos = form.querySelectorAll(':disabled[required]');
                    console.log('Campos deshabilitados y requeridos encontrados:', camposDeshabilitadosRequeridos.length);
                    camposDeshabilitadosRequeridos.forEach(campo => {
                        console.log('Campo deshabilitado y requerido:', campo.id, campo.name, campo.type);
                        camposEnSeccionesDeshabilitadas.push({campo, required: true});
                        campo.removeAttribute('required');
                    });
                    
                    // También verificar campos con readonly que son requeridos
                    const camposReadonlyRequeridos = form.querySelectorAll('[readonly][required]');
                    console.log('Campos readonly y requeridos encontrados:', camposReadonlyRequeridos.length);
                    camposReadonlyRequeridos.forEach(campo => {
                        console.log('Campo readonly y requerido:', campo.id, campo.name, campo.type);
                        camposEnSeccionesDeshabilitadas.push({campo, required: true});
                        campo.removeAttribute('required');
                    });
                    
                    // Verificar selects específicos que podrían estar deshabilitados
                    const selectsRequeridos = form.querySelectorAll('select[required]');
                    selectsRequeridos.forEach(select => {
                        if (select.disabled || select.readOnly) {
                            console.log('Select requerido deshabilitado:', select.id, select.name);
                            camposEnSeccionesDeshabilitadas.push({campo: select, required: true});
                            select.removeAttribute('required');
                        }
                    });
                    
                    // Verificar campos "otra ocupación" que están vacíos pero requeridos
                    const camposOtraOcupacion = form.querySelectorAll('[id*="otra-ocupacion"]');
                    camposOtraOcupacion.forEach(campo => {
                        // Verificar explícitamente si tiene el atributo required
                        const tieneRequired = campo.hasAttribute('required');
                        const estaVacio = !campo.value.trim();
                        
                        console.log(`Analizando campo ${campo.id}: required=${tieneRequired}, value="${campo.value}", vacio=${estaVacio}`);
                        
                        if (tieneRequired && estaVacio) {
                            console.log(`Campo ${campo.id} vacío pero requerido, removiendo required`);
                            camposEnSeccionesDeshabilitadas.push({campo, required: true});
                            campo.removeAttribute('required');
                        }
                    });
                    
                    console.log('Total de campos modificados:', camposEnSeccionesDeshabilitadas.length);
                    console.log('Form checkValidity DESPUÉS:', form.checkValidity());
                    
                    // Debug exhaustivo para encontrar el problema
                    console.log('=== DEBUG EXHAUSTIVO ===');
                    console.log('Formulario:', form);
                    console.log('Form length:', form.length);
                    console.log('Form elements:', form.elements.length);
                    
                    // Verificar cada elemento individualmente
                    for (let i = 0; i < form.elements.length; i++) {
                        const element = form.elements[i];
                        console.log(`Elemento ${i}:`, {
                            name: element.name,
                            id: element.id,
                            type: element.type,
                            value: element.value,
                            required: element.required,
                            disabled: element.disabled,
                            readOnly: element.readOnly,
                            willValidate: element.willValidate,
                            validity: element.validity,
                            validationMessage: element.validationMessage
                        });
                        
                        if (element.willValidate && !element.validity.valid) {
                            console.log('*** ELEMENTO INVÁLIDO ENCONTRADO ***', element);
                        }
                    }
                    
                    if (!form.checkValidity()) {
                        console.log('=== VALIDACIÓN BOOTSTRAP FALLÓ ===');
                        console.log('Form validity:', form.checkValidity());
                        
                        // Mostrar qué campos están inválidos
                        const invalidFields = form.querySelectorAll(':invalid');
                        console.log('Campos inválidos:', invalidFields);
                        invalidFields.forEach(field => {
                            console.log('Campo inválido:', field.id, field.name, field.type, field.value, 'validationMessage:', field.validationMessage);
                        });
                        
                        // Restaurar atributos required antes de salir
                        camposEnSeccionesDeshabilitadas.forEach(({campo, required}) => {
                            if (required) campo.setAttribute('required', '');
                        });
                        console.log('Atributos required restaurados después de validación fallida');
                        
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Restaurar readonly después de validación fallida
                        readonlyFields.forEach(field => {
                            field.readOnly = true;
                        });
                        
                        form.classList.add('was-validated');
                        return false;
                    }
                    
                    // Restaurar atributos required antes de enviar
                    camposEnSeccionesDeshabilitadas.forEach(({campo, required}) => {
                        if (required) campo.setAttribute('required', '');
                    });
                    console.log('Atributos required restaurados antes de enviar');
                    
                    form.classList.add('was-validated');
                    
                    console.log('=== FORMULARIO ENVIÁNDOSE ===');
                    console.log('Action:', form.action);
                    console.log('Method:', form.method);
                    console.log('Form data:', new FormData(form));
                });
            });
        })()
        // Función centralizada para inicializar selectpicker con configuración consistente
        function inicializarSelectPickerConBuscador($selectElement) {
            // BLOQUEO DE COPIA: No ejecutar si estamos copiando datos
            if (window.copiandoDatosProgenitor) {
                console.log(`[INICIALIZAR_SELECT] ⚠️ Modo copia activo, omitiendo inicialización de selectpicker`);
                return;
            }
            
            if (!$selectElement || !$selectElement.hasClass('selectpicker')) {
                return;
            }

            try {
                // Destruir si ya estaba inicializado
                if ($selectElement.data('selectpicker')) {
                    $selectElement.selectpicker('destroy');
                }

                // Inicializar con configuración completa que incluye el buscador
                $selectElement.selectpicker({
                    liveSearch: true,
                    size: 5, // Limitar a 5 opciones visibles
                    noneResultsText: 'No hay resultados para {0}',
                    selectOnTab: false,
                    showSubtext: false,
                    showIcon: true,
                    width: 'auto',
                    dropupAuto: true, // Permitir que se despliegue hacia arriba automáticamente
                    container: 'body' // Evitar problemas de z-index
                });

                console.log('Selectpicker inicializado con buscador para:', $selectElement.attr('id'));
            } catch (e) {
                console.error('Error al inicializar selectpicker:', e);
            }
        }

        // Función para refrescar selectpicker manteniendo el buscador
        function refrescarSelectPickerConBuscador(selectElement) {
            if (!selectElement) return;

            const $select = $(selectElement);
            if ($select.hasClass('selectpicker')) {
                try {
                    $select.selectpicker('refresh');

                    // Verificar que el buscador esté activo y re-inicializar si es necesario
                    setTimeout(() => {
                        if (!$select.data('selectpicker') || !$select.data('selectpicker').options.liveSearch) {
                            console.log('Re-activando buscador para:', $select.attr('id'));
                            inicializarSelectPickerConBuscador($select);
                        }
                    }, 50);
                } catch (e) {
                    console.error('Error al refrescar selectpicker:', e);
                    inicializarSelectPickerConBuscador($select);
                }
            }
        }

        // Función para limpiar completamente un select con selectpicker
        function limpiarSelectCompleto(selectElement) {
            if (!selectElement) return;

            const $select = $(selectElement);
            const selectId = selectElement.id;
            
            console.log(`[LIMPIAR_SELECT] Iniciando limpieza para ${selectId}, opciones actuales: ${selectElement.options.length}`);
            console.log(`[LIMPIAR_SELECT] Opciones antes de limpiar:`, Array.from(selectElement.options).map(o => ({value: o.value, text: o.text})));
            
            try {
                // NO destruir el selectpicker - solo limpiar opciones para evitar errores
                if ($select.data('selectpicker')) {
                    console.log(`[LIMPIAR_SELECT] Limpiando selectpicker sin destruir para ${selectId}`);
                    
                    // LIMPIEZA AGRESIVA: Eliminar todas las opciones excepto placeholder EXISTENTE
                    const placeholderExistente = selectElement.querySelector('option[value=""]');
                    if (placeholderExistente) {
                        // Mantener solo el placeholder existente
                        $select.find('option:not([value=""])').remove();
                    } else {
                        // Solo crear placeholder si no existe
                        const placeholderText = selectId.includes('pais') ? 'país' : selectId.includes('estado') ? 'estado' : selectId.includes('municipio') ? 'municipio' : selectId.includes('parroquia') ? 'parroquia' : selectId.includes('ocupacion') ? 'ocupación' : 'opción';
                        $select.empty().append(`<option value="">Seleccione un ${placeholderText}</option>`);
                    }
                    
                    // LIMPIEZA DE TEXTO VISUAL: Limpiar el texto mostrado en el selectpicker
                    $select.selectpicker('val', '');
                    $select.selectpicker('render');
                    
                    // LIMPIEZA ADICIONAL: Forzar limpieza del DOM interno del selectpicker
                    $select.selectpicker('destroy');
                    
                    // Reconstruir con el placeholder ya existente
                    // Re-inicializar selectpicker limpio
                    $select.selectpicker({
                        liveSearch: true,
                        size: 8,
                        noneResultsText: 'No hay resultados para {0}',
                        selectOnTab: false,
                        showSubtext: false,
                        showIcon: true,
                        width: 'auto'
                    });
                    
                    console.log(`[LIMPIAR_SELECT] Selectpicker limpiado agresivamente para ${selectId}`);
                    console.log(`[LIMPIAR_SELECT] Opciones después de limpiar: ${selectElement.options.length}`);
                    return;
                }
                
                // Si no hay selectpicker inicializado, limpiar normalmente
                const placeholderExistente = selectElement.querySelector('option[value=""]');
                if (placeholderExistente) {
                    // Mantener solo el placeholder existente, eliminar el resto
                    const opciones = Array.from(selectElement.options);
                    opciones.forEach(option => {
                        if (option.value !== '') {
                            option.remove();
                        }
                    });
                } else {
                    // Solo crear placeholder si no existe
                    let placeholderText = 'Seleccione';
                    
                    if (selectId.includes('pais')) placeholderText = 'Seleccione un país';
                    else if (selectId.includes('estado')) placeholderText = 'Seleccione un estado';
                    else if (selectId.includes('municipio')) placeholderText = 'Seleccione un municipio';
                    else if (selectId.includes('parroquia')) placeholderText = 'Seleccione una parroquia';
                    else if (selectId.includes('prefijo')) placeholderText = 'Seleccione';
                    else if (selectId.includes('ocupacion')) placeholderText = 'Seleccione una ocupación';
                    
                    selectElement.innerHTML = `<option value="">${placeholderText}</option>`;
                }
                console.log(`[LIMPIAR_SELECT] Select limpiado para ${selectId}`);
                console.log(`[LIMPIAR_SELECT] Opciones después de limpiar: ${selectElement.options.length}`);
                
                // Inicializar selectpicker solo si no existe
                if (!$select.data('selectpicker')) {
                    $select.selectpicker({
                        liveSearch: true,
                        size: 8,
                        noneResultsText: 'No hay resultados para {0}',
                        selectOnTab: false,
                        showSubtext: false,
                        showIcon: true,
                        width: 'auto'
                    });
                    console.log(`[LIMPIAR_SELECT] Selectpicker inicializado para ${selectId}`);
                }
            } catch (error) {
                console.error('Error en limpiarSelectCompleto:', error);
                try {
                    // Fallback: limpiar directamente las opciones
                    $select.find('option:not([value=""])').remove();
                    $select.selectpicker('refresh');
                } catch (recoveryError) {
                    console.error('Error en recuperación de limpiarSelectCompleto:', recoveryError);
                    try {
                        selectElement.value = '';
                    } catch {}
                }
            }
        }

        // Función ULTRA simplificada para establecer valores en selects (evita duplicación)
        function establecerValorSelectUltraSimple(selectElement, valor, delay = 50) {
            return new Promise((resolve, reject) => {
                if (!selectElement) {
                    reject(new Error('Elemento select no encontrado'));
                    return;
                }

                const selectId = selectElement.id || 'unknown';
                const $select = $(selectElement);
                
                // Validación: no procesar valores vacíos o nulos
                if (!valor || valor === '' || valor === 'null' || valor === null) {
                    console.log(`[${selectId}] [ULTRA] Valor vacío o nulo detectado: "${valor}", omitiendo procesamiento`);
                    resolve(null);
                    return;
                }
                
                console.log(`[${selectId}] [ULTRA] Estableciendo valor: "${valor}"`);
                
                try {
                    // PASO 1: Verificar si el selectpicker está inicializado y tiene elementos
                    const selectpickerInstance = $select.data('selectpicker');
                    if (!selectpickerInstance) {
                        console.warn(`[${selectId}] [ULTRA] Selectpicker no inicializado, inicializando...`);
                        // Inicializar selectpicker si no existe
                        $select.selectpicker({
                            liveSearch: true,
                            size: 8,
                            noneResultsText: 'No hay resultados para {0}',
                            selectOnTab: false,
                            showSubtext: false,
                            showIcon: true,
                            width: 'auto'
                        });
                    }
                    
                    // PASO 2: Verificar que hay opciones disponibles
                    const $options = $select.find('option');
                    if ($options.length === 0) {
                        console.warn(`[${selectId}] [ULTRA] No hay opciones disponibles en el select`);
                        reject(new Error('No hay opciones disponibles en el select'));
                        return;
                    }
                    
                    // PASO 3: Desactivar liveSearch temporalmente si está activo
                    const liveSearchOriginal = $select.data('selectpicker')?.options?.liveSearch;
                    if (liveSearchOriginal) {
                        $select.selectpicker('setOptions', { liveSearch: false });
                        console.log(`[${selectId}] [ULTRA] LiveSearch desactivado temporalmente`);
                    }
                    
                    // PASO 4: Limpiar completamente el estado residual
                    try {
                        // Verificación adicional: asegurar que el selectpicker está completamente inicializado
                        if ($select.data('selectpicker') && $select.data('selectpicker').$element) {
                            $select.selectpicker('val', '');
                            $select.selectpicker('deselectAll');
                            selectElement.value = '';
                            selectElement.selectedIndex = -1;
                        } else {
                            console.warn(`[${selectId}] [ULTRA] Selectpicker no completamente inicializado, usando limpieza básica`);
                            selectElement.value = '';
                            selectElement.selectedIndex = -1;
                        }
                    } catch (cleanError) {
                        console.error(`[${selectId}] [ULTRA] Error en limpieza inicial:`, cleanError);
                        // Fallback ultra seguro
                        try {
                            selectElement.value = '';
                            selectElement.selectedIndex = -1;
                        } catch (fallbackError) {
                            console.error(`[${selectId}] [ULTRA] Error en fallback de limpieza:`, fallbackError);
                        }
                    }
                    
                    // PASO 5: Forzar limpieza de texto del selectpicker
                    const $button = $select.data('selectpicker')?.$button;
                    if ($button && $button.length > 0) {
                        const $title = $button.find('.filter-option');
                        if ($title.length > 0) {
                            $title.text(''); // Limpiar texto residual del title
                            console.log(`[${selectId}] [ULTRA] Texto residual del title limpiado`);
                        }
                    }
                    
                    // PASO 6: Esperar a que se complete la limpieza
                    setTimeout(() => {
                        try {
                            // PASO 7: Establecer el nuevo valor directamente
                            try {
                                // Validación final: asegurar que el select está en estado válido
                                if (selectElement && selectElement.options && selectElement.options.length > 0) {
                                    // Verificar que el valor existe en las opciones
                                    const valorExiste = Array.from(selectElement.options).some(option => option.value === valor);
                                    if (valorExiste || valor === '') {
                                        selectElement.value = valor;
                                        console.log(`[${selectId}] [ULTRA] Valor establecido directamente: "${valor}"`);
                                    } else {
                                        console.warn(`[${selectId}] [ULTRA] Valor "${valor}" no existe en las opciones disponibles`);
                                        selectElement.value = '';
                                    }
                                } else {
                                    console.warn(`[${selectId}] [ULTRA] Select no válido para establecer valor`);
                                    selectElement.value = valor; // Intento de último recurso
                                }
                            } catch (setValueError) {
                                console.error(`[${selectId}] [ULTRA] Error al establecer valor:`, setValueError);
                                // Fallback ultra seguro
                                try {
                                    selectElement.value = valor;
                                } catch (fallbackError) {
                                    console.error(`[${selectId}] [ULTRA] Error fatal al establecer valor:`, fallbackError);
                                    reject(fallbackError);
                                    return;
                                }
                            }
                            
                            // PASO 8: Refrescar el selectpicker con validación segura
                            try {
                                // Verificación robusta: asegurar que el selectpicker y sus elementos DOM existen
                                const selectpickerInstance = $select.data('selectpicker');
                                if (selectpickerInstance && selectpickerInstance.$element && selectpickerInstance.$element.length > 0) {
                                    // Verificación adicional: asegurar que el elemento DOM principal existe
                                    const mainElement = selectpickerInstance.$element[0];
                                    if (mainElement && document.contains(mainElement)) {
                                        try {
                                            $select.selectpicker('refresh');
                                            console.log(`[${selectId}] [ULTRA] Selectpicker refrescado exitosamente`);
                                        } catch (refreshError) {
                                            console.error(`[${selectId}] [ULTRA] Error en refresh:`, refreshError);
                                            // Intentar recuperación: destruir y recrear
                                            try {
                                                $select.selectpicker('destroy');
                                                $select.selectpicker({
                                                    liveSearch: true,
                                                    size: 8,
                                                    noneResultsText: 'No hay resultados para {0}',
                                                    selectOnTab: false,
                                                    showSubtext: false,
                                                    showIcon: true,
                                                    width: 'auto'
                                                });
                                                selectElement.value = valor;
                                                console.log(`[${selectId}] [ULTRA] Selectpicker recuperado después de error`);
                                            } catch (recoveryError) {
                                                console.error(`[${selectId}] [ULTRA] Error en recuperación:`, recoveryError);
                                                // Último recurso: establecer valor sin selectpicker
                                                selectElement.value = valor;
                                                console.log(`[${selectId}] [ULTRA] Valor establecido sin selectpicker como último recurso`);
                                            }
                                        }
                                    } else {
                                        console.warn(`[${selectId}] [ULTRA] Elemento DOM principal no existe, re-inicializando...`);
                                        $select.selectpicker('destroy');
                                        $select.selectpicker({
                                            liveSearch: true,
                                            size: 8,
                                            noneResultsText: 'No hay resultados para {0}',
                                            selectOnTab: false,
                                            showSubtext: false,
                                            showIcon: true,
                                            width: 'auto'
                                        });
                                        selectElement.value = valor;
                                    }
                                } else {
                                    console.warn(`[${selectId}] [ULTRA] Selectpicker destruido o incompleto, re-inicializando...`);
                                    $select.selectpicker({
                                        liveSearch: true,
                                        size: 8,
                                        noneResultsText: 'No hay resultados para {0}',
                                        selectOnTab: false,
                                        showSubtext: false,
                                        showIcon: true,
                                        width: 'auto'
                                    });
                                    selectElement.value = valor;
                                }
                            } catch (refreshError) {
                                console.error(`[${selectId}] [ULTRA] Error en refresh:`, refreshError);
                                // Intentar recuperación: destruir y recrear
                                try {
                                    $select.selectpicker('destroy');
                                    $select.selectpicker({
                                        liveSearch: true,
                                        size: 8,
                                        noneResultsText: 'No hay resultados para {0}',
                                        selectOnTab: false,
                                        showSubtext: false,
                                        showIcon: true,
                                        width: 'auto'
                                    });
                                    selectElement.value = valor;
                                } catch (recoveryError) {
                                    console.error(`[${selectId}] [ULTRA] Error en recuperación:`, recoveryError);
                                    reject(recoveryError);
                                    return;
                                }
                            }
                            
                            // PASO 9: Forzar actualización visual del texto
                            setTimeout(() => {
                                const $button = $select.data('selectpicker')?.$button;
                                if ($button && $button.length > 0) {
                                    const $title = $button.find('.filter-option');
                                    const selectedText = $select.find('option:selected').text();
                                    if ($title.length > 0 && selectedText) {
                                        $title.text(selectedText); // Forzar texto limpio
                                        console.log(`[${selectId}] [ULTRA] Texto forzado: "${selectedText}"`);
                                    }
                                }
                            }, 50);
                            
                            // PASO 10: Esperar y verificar resultado
                            setTimeout(() => {
                                try {
                                    const valorActual = $select.selectpicker('val');
                                    const textoActual = $select.find('option:selected').text();
                                    
                                    console.log(`[${selectId}] [ULTRA] Resultado - Valor: "${valorActual}", Texto: "${textoActual}"`);
                                    
                                    // PASO 11: Validar consistencia (ID numérico vs texto)
                                    if (valor && !isNaN(valor)) {
                                        // Para valores numéricos (como prefijos), el texto no debe contener el número repetido
                                        const patronDuplicacion = new RegExp(`${valor}.*${valor}`);
                                        if (patronDuplicacion.test(textoActual)) {
                                            console.warn(`[${selectId}] [ULTRA] ❌ Duplicación detectada en texto numérico: "${textoActual}"`);
                                            
                                            // Corrección forzada: limpiar y establecer de nuevo
                                            try {
                                                if ($select.data('selectpicker') && $select.data('selectpicker').$element) {
                                                    $select.selectpicker('val', '');
                                                    selectElement.value = valor;
                                                    $select.selectpicker('refresh');
                                                } else {
                                                    console.warn(`[${selectId}] [ULTRA] Selectpicker no completamente inicializado, usando método nativo`);
                                                    selectElement.value = valor;
                                                }
                                            } catch (correctionError) {
                                                console.error(`[${selectId}] [ULTRA] Error en corrección:`, correctionError);
                                                selectElement.value = valor;
                                            }
                                            
                                            // Forzar texto limpio después de corrección
                                            setTimeout(() => {
                                                const $button = $select.data('selectpicker')?.$button;
                                                if ($button && $button.length > 0) {
                                                    const $title = $button.find('.filter-option');
                                                    const textoCorregido = $select.find('option:selected').text();
                                                    if ($title.length > 0) {
                                                        $title.text(textoCorregido);
                                                    }
                                                }
                                                console.log(`[${selectId}] [ULTRA] Texto después de corrección: "${textoCorregido}"`);
                                            }, 100);
                                        } else {
                                            console.log(`[${selectId}] [ULTRA] ✅ Sin duplicación en texto numérico`);
                                        }
                                    } else {
                                        // Para valores de texto, verificar que no esté concatenado
                                        if (textoActual && textoActual.includes('Seleccione')) {
                                            console.warn(`[${selectId}] [ULTRA] ❌ Concatenación con placeholder detectada: "${textoActual}"`);
                                            
                                            // Corrección forzada
                                            setTimeout(() => {
                                                const $button = $select.data('selectpicker')?.$button;
                                                if ($button && $button.length > 0) {
                                                    const $title = $button.find('.filter-option');
                                                    if ($title.length > 0) {
                                                        $title.text(textoActual.replace(/Seleccione.*?(?=[A-Z])/, '').trim());
                                                    }
                                                }
                                            }, 50);
                                        } else {
                                            console.log(`[${selectId}] [ULTRA] ✅ Sin concatenación con placeholder`);
                                        }
                                    }
                                    
                                    // PASO 12: Reactivar liveSearch si estaba activo
                                    if (liveSearchOriginal) {
                                        $select.selectpicker('setOptions', { liveSearch: true });
                                        console.log(`[${selectId}] [ULTRA] LiveSearch reactivado`);
                                    }
                                    
                                    // PASO 13: Disparar evento change
                                    const event = new Event('change', { bubbles: true });
                                    selectElement.dispatchEvent(event);
                                    
                                    console.log(`[${selectId}] [ULTRA] ✅ Valor establecido exitosamente`);
                                    resolve(valorActual);
                                    
                                } catch (error) {
                                    console.error(`[${selectId}] [ULTRA] Error en verificación:`, error);
                                    reject(error);
                                }
                            }, 100);
                            
                        } catch (error) {
                            console.error(`[${selectId}] [ULTRA] Error al establecer valor:`, error);
                            reject(error);
                        }
                    }, delay);
                    
                } catch (error) {
                    console.error(`[${selectId}] [ULTRA] Error inicial:`, error);
                    reject(error);
                }
            });
        }

        // Función para activar/desactivar modo fallback de selects nativos
        function toggleModoFallback(activo = false) {
            window.usarSelectsNativos = activo;
            console.log(`[FALLBACK] Modo de selects nativos: ${activo ? 'ACTIVADO' : 'DESACTIVADO'}`);
            
            if (activo) {
                // Destruir todos los selectpickers para usar selects nativos
                $('.selectpicker').each(function() {
                    const $this = $(this);
                    if ($this.data('selectpicker')) {
                        try {
                            $this.selectpicker('destroy');
                            console.log(`[FALLBACK] Selectpicker destruido para: ${this.id}`);
                        } catch (e) {
                            console.warn(`[FALLBACK] Error al destruir selectpicker:`, e);
                        }
                    }
                });
                console.log('[FALLBACK] Todos los selectpickers han sido destruidos');
            } else {
                // Recrear todos los selectpickers
                $('.selectpicker').each(function() {
                    const $this = $(this);
                    try {
                        $this.selectpicker({
                            liveSearch: true,
                            size: 8,
                            noneResultsText: 'No hay resultados para {0}',
                            selectOnTab: false,
                            showSubtext: false,
                            showIcon: true,
                            width: 'auto'
                        });
                        console.log(`[FALLBACK] Selectpicker recreado para: ${this.id}`);
                    } catch (e) {
                        console.error(`[FALLBACK] Error al recrear selectpicker:`, e);
                    }
                });
                console.log('[FALLBACK] Todos los selectpickers han sido recreados');
            }
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            // Establecer valores guardados si estamos editando un representante
            @if(isset($representante))
                // Valores para la madre (todos en tabla persona)
                document.getElementById('idPais').setAttribute('data-valor-guardado', '{{ $representante->persona->pais_id ?? '' }}');
                document.getElementById('idEstado').setAttribute('data-valor-guardado', '{{ $representante->persona->estado_id ?? '' }}');
                document.getElementById('idMunicipio').setAttribute('data-valor-guardado', '{{ $representante->persona->municipio_id ?? '' }}');
                document.getElementById('idparroquia').setAttribute('data-valor-guardado', '{{ $representante->persona->localidad_id ?? '' }}');
                
                // Valores para el padre (todos en tabla persona)
                document.getElementById('idPais-padre').setAttribute('data-valor-guardado', '{{ $representante->persona->pais_id ?? '' }}');
                document.getElementById('idEstado-padre').setAttribute('data-valor-guardado', '{{ $representante->persona->estado_id ?? '' }}');
                document.getElementById('idMunicipio-padre').setAttribute('data-valor-guardado', '{{ $representante->persona->municipio_id ?? '' }}');
                document.getElementById('idparroquia-padre').setAttribute('data-valor-guardado', '{{ $representante->persona->localidad_id ?? '' }}');
                
                // Valores para el representante (país, estado, municipio en tabla representantes; parroquia en persona)
                document.getElementById('idPais-representante').setAttribute('data-valor-guardado', '{{ $representante->pais_id ?? '' }}');
                document.getElementById('idEstado-representante').setAttribute('data-valor-guardado', '{{ $representante->estado_id ?? '' }}');
                document.getElementById('idMunicipio-representante').setAttribute('data-valor-guardado', '{{ $representante->municipio_id ?? '' }}');
                document.getElementById('idparroquia-representante').setAttribute('data-valor-guardado', '{{ $representante->parroquia_id ?? '' }}');
                
                // Inicializar la carga en cascada para la madre
                setTimeout(() => {
                    const paisMadre = document.getElementById('idPais').getAttribute('data-valor-guardado');
                    if (paisMadre) {
                        // Limpiar valores anteriores antes de cargar
                        limpiarSelectCompleto(document.getElementById('idEstado'));
                        limpiarSelectCompleto(document.getElementById('idMunicipio'));
                        limpiarSelectCompleto(document.getElementById('idparroquia'));
                        
                        document.getElementById('idPais').value = paisMadre;
                        cargarSelectAnidado('estado', paisMadre, 'idEstado', 'idMunicipio');
                        
                        setTimeout(() => {
                            const estadoMadre = document.getElementById('idEstado').getAttribute('data-valor-guardado');
                            if (estadoMadre) {
                                cargarSelectAnidado('municipio', estadoMadre, 'idMunicipio', 'idparroquia');
                                
                                setTimeout(() => {
                                    const municipioMadre = document.getElementById('idMunicipio').getAttribute('data-valor-guardado');
                                    if (municipioMadre) {
                                        cargarSelectAnidado('localidad', municipioMadre, 'idparroquia');
                                    }
                                }, 200);
                            }
                        }, 200);
                    }
                }, 100);
                
                // Inicializar la carga en cascada para el padre
                setTimeout(() => {
                    const paisPadre = document.getElementById('idPais-padre').getAttribute('data-valor-guardado');
                    if (paisPadre) {
                        // Limpiar valores anteriores antes de cargar
                        limpiarSelectCompleto(document.getElementById('idEstado-padre'));
                        limpiarSelectCompleto(document.getElementById('idMunicipio-padre'));
                        limpiarSelectCompleto(document.getElementById('idparroquia-padre'));
                        
                        document.getElementById('idPais-padre').value = paisPadre;
                        cargarSelectAnidado('estado', paisPadre, 'idEstado-padre', 'idMunicipio-padre');
                        
                        setTimeout(() => {
                            const estadoPadre = document.getElementById('idEstado-padre').getAttribute('data-valor-guardado');
                            if (estadoPadre) {
                                cargarSelectAnidado('municipio', estadoPadre, 'idMunicipio-padre', 'idparroquia-padre');
                                
                                setTimeout(() => {
                                    const municipioPadre = document.getElementById('idMunicipio-padre').getAttribute('data-valor-guardado');
                                    if (municipioPadre) {
                                        cargarSelectAnidado('localidad', municipioPadre, 'idparroquia-padre');
                                    }
                                }, 200);
                            }
                        }, 200);
                    }
                }, 100);
                
                // Inicializar la carga en cascada para el representante
                setTimeout(() => {
                    const paisRepresentante = document.getElementById('idPais-representante').getAttribute('data-valor-guardado');
                    if (paisRepresentante) {
                        // Limpiar valores anteriores antes de cargar
                        limpiarSelectValor(document.getElementById('idEstado-representante'));
                        limpiarSelectValor(document.getElementById('idMunicipio-representante'));
                        limpiarSelectValor(document.getElementById('idparroquia-representante'));
                        
                        // Usar función ultra simple para evitar duplicación de texto en selectpicker
                        const paisRepresentanteSelect = document.getElementById('idPais-representante');
                        if (paisRepresentanteSelect) {
                            establecerValorSelectUltraSimple(paisRepresentanteSelect, paisRepresentante, 100);
                        }
                        cargarSelectAnidadoSimple('estado', paisRepresentante, 'idEstado-representante', 'idMunicipio-representante');
                        
                        setTimeout(() => {
                            const estadoRepresentante = document.getElementById('idEstado-representante').getAttribute('data-valor-guardado');
                            if (estadoRepresentante) {
                                cargarSelectAnidadoSimple('municipio', estadoRepresentante, 'idMunicipio-representante', 'idparroquia-representante');
                                
                                setTimeout(() => {
                                    const municipioRepresentante = document.getElementById('idMunicipio-representante').getAttribute('data-valor-guardado');
                                    if (municipioRepresentante) {
                                        cargarSelectAnidadoSimple('localidad', municipioRepresentante, 'idparroquia-representante');
                                    }
                                }, 200);
                            }
                        }, 200);
                    }
                }, 100);
            @endif
        }); // Cierre del DOMContentLoaded anterior
        
        // Nuevo DOMContentLoaded para los event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Eventos para MADRE
            const paisMadre = document.getElementById('idPais');
            if (paisMadre) {
                paisMadre.addEventListener('change', function() {
                    cargarSelectAnidado('estado', this.value, 'idEstado', 'idMunicipio');
                    // También limpiar la parroquia cuando cambia el país
                    limpiarSelectCompleto(document.getElementById('idparroquia'));
                    inicializarSelectPickerConBuscador($(this));
                });
            }

            const estadoMadre = document.getElementById('idEstado');
            if (estadoMadre) {
                estadoMadre.addEventListener('change', function() {
                    cargarSelectAnidado('municipio', this.value, 'idMunicipio', 'idparroquia');
                });
            }

            const municipioMadre = document.getElementById('idMunicipio');
            if (municipioMadre) {
                municipioMadre.addEventListener('change', function() {
                    cargarSelectAnidado('localidad', this.value, 'idparroquia');
                });
            }

            // Eventos para PADRE
            const paisPadre = document.getElementById('idPais-padre');
            if (paisPadre) {
                paisPadre.addEventListener('change', function() {
                    cargarSelectAnidado('estado', this.value, 'idEstado-padre', 'idMunicipio-padre');
                    // También limpiar la parroquia cuando cambia el país
                    limpiarSelectCompleto(document.getElementById('idparroquia-padre'));
                    inicializarSelectPickerConBuscador($(this));
                });
            }

            const estadoPadre = document.getElementById('idEstado-padre');
            if (estadoPadre) {
                estadoPadre.addEventListener('change', function() {
                    cargarSelectAnidado('municipio', this.value, 'idMunicipio-padre', 'idparroquia-padre');
                });
            }

            const municipioPadre = document.getElementById('idMunicipio-padre');
            if (municipioPadre) {
                municipioPadre.addEventListener('change', function() {
                    cargarSelectAnidado('localidad', this.value, 'idparroquia-padre');
                });
            }

            // Eventos para REPRESENTANTE - Se adjuntarán después de la reconstrucción
            function configurarEventosRepresentante() {
                console.log('[EVENTOS REPRESENTANTE] Configurando eventos...');
                
                const paisRepresentante = document.getElementById('idPais-representante');
                if (paisRepresentante) {
                    // Remover eventos anteriores para evitar duplicados
                    paisRepresentante.removeEventListener('change', manejarCambioPaisRepresentante);
                    // Agregar nuevo evento
                    paisRepresentante.addEventListener('change', manejarCambioPaisRepresentante);
                    console.log('[EVENTOS REPRESENTANTE] Evento change configurado para idPais-representante');
                }

                const estadoRepresentante = document.getElementById('idEstado-representante');
                if (estadoRepresentante) {
                    estadoRepresentante.removeEventListener('change', manejarCambioEstadoRepresentante);
                    estadoRepresentante.addEventListener('change', manejarCambioEstadoRepresentante);
                    console.log('[EVENTOS REPRESENTANTE] Evento change configurado para idEstado-representante');
                }

                const municipioRepresentante = document.getElementById('idMunicipio-representante');
                if (municipioRepresentante) {
                    municipioRepresentante.removeEventListener('change', manejarCambioMunicipioRepresentante);
                    municipioRepresentante.addEventListener('change', manejarCambioMunicipioRepresentante);
                    console.log('[EVENTOS REPRESENTANTE] Evento change configurado para idMunicipio-representante');
                }
            }

            // Funciones manejadoras de eventos
            function manejarCambioPaisRepresentante() {
                console.log('[PAIS REPRESENTANTE] Cambio detectado, valor:', this.value);
                if (!copiandoUbicacion && !reseteandoRepresentante && !window.copiandoDatosProgenitor) {
                    cargarSelectAnidadoSimple('estado', this.value, 'idEstado-representante', 'idMunicipio-representante');
                    // Solo limpiar valor, NO destruir opciones
                    limpiarSelectValor(document.getElementById('idparroquia-representante'));
                }
            }

            function manejarCambioEstadoRepresentante() {
                console.log('[ESTADO REPRESENTANTE] Cambio detectado, valor:', this.value);
                if (!copiandoUbicacion && !reseteandoRepresentante && !window.copiandoDatosProgenitor) {
                    cargarSelectAnidadoSimple('municipio', this.value, 'idMunicipio-representante', 'idparroquia-representante');
                }
            }

            function manejarCambioMunicipioRepresentante() {
                console.log('[MUNICIPIO REPRESENTANTE] Cambio detectado, valor:', this.value);
                if (!copiandoUbicacion && !reseteandoRepresentante && !window.copiandoDatosProgenitor) {
                    cargarSelectAnidadoSimple('localidad', this.value, 'idparroquia-representante');
                }
            }

            // Configurar eventos iniciales
            configurarEventosRepresentante();

            // Reconfigurar eventos después de cada reconstrucción
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Verificar si los selects de representante fueron reconstruidos
                        const paisSelect = document.getElementById('idPais-representante');
                        if (paisSelect && !paisSelect.hasAttribute('data-eventos-configurados')) {
                            setTimeout(() => {
                                configurarEventosRepresentante();
                                paisSelect.setAttribute('data-eventos-configurados', 'true');
                            }, 500);
                        }
                    }
                });
            });

            // Observar cambios en el contenedor del representante
            const representanteContainer = document.getElementById('datos-representante');
            if (representanteContainer) {
                observer.observe(representanteContainer, {
                    childList: true,
                    subtree: true
                });
            }

            // Función simplificada para cargar selects anidados sin selectpicker
            function cargarSelectAnidadoSimple(tipo, parentId, targetSelectId, clearSelectId = null) {
                console.log('[CARGAR SIMPLE] Tipo:', tipo, 'Parent:', parentId, 'Target:', targetSelectId);
                
                const targetSelect = document.getElementById(targetSelectId);
                if (!targetSelect) {
                    console.error('[CARGAR SIMPLE] No se encontró el select:', targetSelectId);
                    return;
                }

                // Limpiar select si se especifica
                if (clearSelectId) {
                    const clearSelect = document.getElementById(clearSelectId);
                    if (clearSelect) {
                        // Destruir selectpicker si existe
                        if ($(clearSelect).data('selectpicker')) {
                            $(clearSelect).selectpicker('destroy');
                        }
                        clearSelect.innerHTML = '<option value="">Seleccione...</option>';
                    }
                }

                // Destruir selectpicker del target si existe
                if ($(targetSelect).data('selectpicker')) {
                    $(targetSelect).selectpicker('destroy');
                }

                // Filtrar datos según el tipo
                let datos = [];
                let placeholderText = '';
                
                if (tipo === 'estado') {
                    datos = ubicacionesData.filter(e => String(e.pais_id) === String(parentId));
                    console.log('[CARGAR SIMPLE] Estados encontrados:', datos.length);
                    console.log('[CARGAR SIMPLE] Datos completos:', datos);
                    placeholderText = 'Seleccione un estado';
                    
                } else if (tipo === 'municipio') {
                    const estado = ubicacionesData.find(e => e.id == parentId);
                    if (estado && estado.municipio) {
                        datos = estado.municipio;
                        console.log('[CARGAR SIMPLE] Municipios encontrados:', datos.length);
                        placeholderText = 'Seleccione un municipio';
                    }
                } else if (tipo === 'localidad') {
                    for (const estado of ubicacionesData) {
                        if (estado.municipio) {
                            const municipio = estado.municipio.find(m => m.id == parentId);
                            if (municipio && municipio.localidades) {
                                datos = municipio.localidades;
                                console.log('[CARGAR SIMPLE] Localidades encontradas:', datos.length);
                                placeholderText = 'Seleccione una localidad';
                                break;
                            }
                        }
                    }
                }

                // Limpiar completamente el select
                targetSelect.innerHTML = '';

                // Agregar placeholder
                const placeholderOption = new Option(placeholderText, '', false, false);
                targetSelect.add(placeholderOption);

                // Agregar opciones usando DOM manipulation (no innerHTML +=)
                datos.forEach((item, index) => {
                    let nombre = '';
                    let id = '';
                    
                    if (tipo === 'estado') {
                        nombre = item.nombre_estado ? item.nombre_estado.trim() : '';
                        id = item.id;
                        console.log(`[CARGAR SIMPLE] Estado ${index}:`, {
                            id: item.id,
                            nombre_original: item.nombre_estado,
                            nombre_limpiado: nombre
                        });
                    } else if (tipo === 'municipio') {
                        nombre = item.nombre_municipio ? item.nombre_municipio.trim() : '';
                        id = item.id;
                    } else if (tipo === 'localidad') {
                        nombre = item.nombre_localidad ? item.nombre_localidad.trim() : '';
                        id = item.id;
                    }
                    
                    if (nombre && id) {
                        const option = new Option(nombre, id, false, false);
                        targetSelect.add(option);
                    }
                });

                // Re-inicializar selectpicker
                try {
                    $(targetSelect).selectpicker({
                        liveSearch: true,
                        size: 8,
                        noneResultsText: 'No hay resultados para {0}',
                        selectOnTab: false,
                        showSubtext: false,
                        showIcon: true,
                        width: 'auto',
                        container: 'body'
                    });
                    
                    console.log('[CARGAR SIMPLE] Selectpicker reconstruido completamente');
                } catch (e) {
                    console.log('[CARGAR SIMPLE] Error al reconstruir selectpicker:', e.message);
                }
                
                // Reconfigurar eventos después de cargar datos (importante para selects reconstruidos)
                setTimeout(() => {
                    configurarEventosRepresentante();
                }, 100);
            }

            // Funciones globales para compatibilidad
            window.cargarMunicipiosInputFormulario = (estadoId, municipioId, localidadId) => cargarSelectAnidado('municipio', estadoId, municipioId, localidadId);
            window.cargarParroquiasInputFormulario = (municipioId, localidadId) => cargarSelectAnidado('localidad', municipioId, localidadId);


            // Bandera para evitar múltiples ejecuciones
            let actualizandoParentesco = false;
            
            // Bandera para evitar eventos de ubicación durante la copia
            let copiandoUbicacion = false;
            
            // Bandera para evitar eventos durante reseteo de representante
            let reseteandoRepresentante = false;

            // Función para actualizar las opciones de parentesco según el estado de los progenitores
            function actualizarOpcionesParentesco() {
                // Evitar ejecuciones múltiples
                if (actualizandoParentesco) {
                    return;
                }

                actualizandoParentesco = true;
                
                try {
                    const parentescoSelect = document.getElementById('parentesco');
                    if (!parentescoSelect) return;

                    const estadoMadre = document.querySelector('input[name="estado_madre"]:checked')?.value;
                    const estadoPadre = document.querySelector('input[name="estado_padre"]:checked')?.value;
                    const tipoRepresentante = document.querySelector('input[name="tipo_representante"]:checked')?.value;

                    // Obtener todas las opciones del select
                    const opciones = parentescoSelect.options;

                    // Recorrer las opciones y habilitar/deshabilitar según corresponda
                    for (let i = 0; i < opciones.length; i++) {
                        const opcion = opciones[i];

                        if (opcion.value === 'Mamá') {
                            // Deshabilitar opción "Mamá" si la madre está ausente o si se seleccionó "Solo Representante Legal"
                            const debeDeshabilitar = (estadoMadre !== 'Presente' || tipoRepresentante === 'solo_representante');
                            opcion.disabled = debeDeshabilitar;

                            // Si la opción está seleccionada y se deshabilita, limpiar la selección
                            if (opcion.selected && debeDeshabilitar) {
                                parentescoSelect.value = '';
                                const event = new Event('change');
                                parentescoSelect.dispatchEvent(event);
                            }
                        } else if (opcion.value === 'Papá') {
                            // Deshabilitar opción "Papá" si el padre está ausente o si se seleccionó "Solo Representante Legal"
                            const debeDeshabilitar = (estadoPadre !== 'Presente' || tipoRepresentante === 'solo_representante');
                            opcion.disabled = debeDeshabilitar;

                            // Si la opción está seleccionada y se deshabilita, limpiar la selección
                            if (opcion.selected && debeDeshabilitar) {
                                parentescoSelect.value = '';
                                const event = new Event('change');
                                parentescoSelect.dispatchEvent(event);
                            }
                        }
                    }

                    // Actualizar selectpicker si está en uso
                    if (typeof $ !== 'undefined' && $.fn.selectpicker) {
                        const $parentescoSelect = $(parentescoSelect);
                        
                        // Destruir el selectpicker primero para evitar conflictos
                        $parentescoSelect.selectpicker('destroy');
                        
                        // Limpiar completamente el select
                        $parentescoSelect.empty();
                        
                        // Opciones base sin duplicación (todas las opciones del HTML original)
                        const opcionesBase = [
                            { value: '', text: 'Seleccione un parentesco', disabled: false, selected: true },
                            { value: 'Papá', text: 'Papá', disabled: (estadoPadre !== 'Presente' || tipoRepresentante === 'solo_representante'), selected: false },
                            { value: 'Mamá', text: 'Mamá', disabled: (estadoMadre !== 'Presente' || tipoRepresentante === 'solo_representante'), selected: false },
                            { value: 'Hermano(a)', text: 'Hermano(a)', disabled: false, selected: false },
                            { value: 'Abuelo(a)', text: 'Abuelo(a)', disabled: false, selected: false },
                            { value: 'Tío(a)', text: 'Tío(a)', disabled: false, selected: false },
                            { value: 'Primo(a)', text: 'Primo(a)', disabled: false, selected: false },
                            { value: 'Otro', text: 'Otro', disabled: false, selected: false }
                        ];
                        
                        // Agregar opciones limpias sin duplicación
                        opcionesBase.forEach(opcion => {
                            const $newOption = $('<option>', {
                                value: opcion.value,
                                text: opcion.text,
                                disabled: opcion.disabled,
                                selected: opcion.selected
                            });
                            $parentescoSelect.append($newOption);
                        });
                        
                        // Reconstruir el selectpicker
                        $parentescoSelect.selectpicker();
                    }

                    // Actualizar Select2 si está en uso
                    if (typeof $.fn.select2 === 'function' && $(parentescoSelect).hasClass(
                            'select2-hidden-accessible')) {
                        $(parentescoSelect).trigger('change.select2');
                    }
                
                } catch (error) {
                    console.error('Error en actualizarOpcionesParentesco:', error);
                } finally {
                    // Resetear la bandera después de completar
                    actualizandoParentesco = false;
                }
            }

            // Función para validar el código del carnet de la patria
            function validarCodigoCarnet(input) {
                const value = input.value.trim();
                const errorElement = document.getElementById('codigo-error') ||
                    document.createElement('div');

                // Si no existe el elemento de error, lo creamos
                if (!errorElement.id) {
                    errorElement.id = 'codigo-error';
                    errorElement.className = 'invalid-feedback';
                    input.parentNode.appendChild(errorElement);
                }

                // Validar que solo contenga números y tenga máximo 10 dígitos
                if (value && !/^\d{1,10}$/.test(value)) {
                    input.classList.add('is-invalid');
                    errorElement.textContent = 'El código debe contener solo números (máx. 10 dígitos)';
                    return false;
                }

                // Si el campo está vacío, mostramos un mensaje de error si es requerido
                const carnetAfiliado = document.getElementById('carnet-patria');
                if (carnetAfiliado && carnetAfiliado.value && carnetAfiliado.value !== '0' && !value) {
                    input.classList.add('is-invalid');
                    errorElement.textContent = 'Este campo es obligatorio cuando el carnet está afiliado';
                    return false;
                }

                // Si pasa todas las validaciones
                input.classList.remove('is-invalid');
                errorElement.textContent = '';
                return true;
            }

            // Función para actualizar las opciones del Carnet de la Patria según el estado de los padres
            function actualizarOpcionesCarnetPatria() {
                const carnetPatriaSelect = document.getElementById('carnet-patria');
                const codigoInput = document.getElementById('codigo');

                // Si se selecciona una opción distinta a vacío, validar el código
                if (carnetPatriaSelect && carnetPatriaSelect.value && codigoInput) {
                    validarCodigoCarnet(codigoInput);
                }
                if (!carnetPatriaSelect) return;

                const estadoMadre = document.querySelector('input[name="estado_madre"]:checked')?.value;
                const estadoPadre = document.querySelector('input[name="estado_padre"]:checked')?.value;

                // Obtener todas las opciones del select
                const opciones = carnetPatriaSelect.options;

                // Verificar que opciones exista y tenga elementos
                if (!opciones || opciones.length === 0) return;

                // Recorrer las opciones y habilitar/deshabilitar según corresponda
                for (let i = 0; i < opciones.length; i++) {
                    const opcion = opciones[i];

                    if (opcion.value === '1') { // Opción "Padre"
                        // Solo deshabilitar opción "Padre" si el padre está ausente
                        opcion.disabled = (estadoPadre !== 'Presente');

                        // Si la opción está seleccionada y se deshabilita, limpiar la selección
                        if (opcion.selected && opcion.disabled) {
                            carnetPatriaSelect.value = '';
                            // Disparar evento change para actualizar validaciones
                            const event = new Event('change');
                            carnetPatriaSelect.dispatchEvent(event);
                        }
                    } else if (opcion.value === '2') { // Opción "Madre"
                        // Solo deshabilitar opción "Madre" si la madre está ausente
                        opcion.disabled = (estadoMadre !== 'Presente');

                        // Si la opción está seleccionada y se deshabilita, limpiar la selección
                        if (opcion.selected && opcion.disabled) {
                            carnetPatriaSelect.value = '';
                            // Disparar evento change para actualizar validaciones
                            const event = new Event('change');
                            carnetPatriaSelect.dispatchEvent(event);
                        }
                    } else if (opcion.value === '3') { // Opción "Otro familiar"
                        // Siempre habilitar la opción "Otro familiar"
                        opcion.disabled = false;
                    }
                }

                // Actualizar selectpicker si está en uso
                if (typeof $ !== 'undefined' && $.fn.selectpicker) {
                    $(carnetPatriaSelect).selectpicker('destroy');
                    $(carnetPatriaSelect).selectpicker();
                }

                // Actualizar Select2 si está en uso
                if (typeof $.fn.select2 === 'function' && $(carnetPatriaSelect).hasClass(
                        'select2-hidden-accessible')) {
                    $(carnetPatriaSelect).trigger('change.select2');
                }
            }

            // Función para mostrar validación en línea cuando un progenitor está ausente
            function mostrarErrorTipoRepresentante(progenitor) {
                const esMadre = progenitor === 'madre';
                const texto = esMadre
                    ? 'La madre está marcada como ausente. Debe cambiarla a presente o elegir otra opción.'
                    : 'El padre está marcado como ausente. Debe cambiarlo a presente o elegir otra opción.';

                const errorDiv = document.getElementById('error-tipo-representante');
                const textoSpan = document.getElementById('error-tipo-representante-texto');
                if (errorDiv) {
                    if (textoSpan) {
                        textoSpan.textContent = texto;
                    } else {
                        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> ${texto}`;
                    }
                    errorDiv.style.display = 'block';
                }
            }

            function ocultarErrorTipoRepresentante() {
                const errorDiv = document.getElementById('error-tipo-representante');
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }
            }

            // Alias para compatibilidad interna
            const mostrarAlertaProgenitorAusente = mostrarErrorTipoRepresentante;
            const ocultarAlertaProgenitorAusente = ocultarErrorTipoRepresentante;

            // Función para actualizar la disponibilidad de los radios de representante legal
            function actualizarRadiosRepresentante() {
                const estadoMadre = document.querySelector('input[name="estado_madre"]:checked')?.value;
                const estadoPadre = document.querySelector('input[name="estado_padre"]:checked')?.value;

                const radioMadreRepresentante = document.getElementById('progenitor_madre_representante');
                const radioPadreRepresentante = document.getElementById('progenitor_padre_representante');
                const radioSoloRepresentante = document.getElementById('solo_representante');

                // Deshabilitar radio de madre como representante si la madre está ausente
                if (radioMadreRepresentante) {
                    const madreAusente = (estadoMadre !== 'Presente');
                    radioMadreRepresentante.disabled = madreAusente;
                    if (madreAusente && radioMadreRepresentante.checked) {
                        radioSoloRepresentante.checked = true;
                        mostrarAlertaProgenitorAusente('madre');
                        radioSoloRepresentante.dispatchEvent(new Event('change'));
                    }
                }

                // Deshabilitar radio de padre como representante si el padre está ausente
                if (radioPadreRepresentante) {
                    const padreAusente = (estadoPadre !== 'Presente');
                    radioPadreRepresentante.disabled = padreAusente;
                    if (padreAusente && radioPadreRepresentante.checked) {
                        radioSoloRepresentante.checked = true;
                        mostrarAlertaProgenitorAusente('padre');
                        radioSoloRepresentante.dispatchEvent(new Event('change'));
                    }
                }

                // Si ambos padres están ausentes, asegurarse de que solo se pueda seleccionar "Solo Representante Legal"
                if ((estadoMadre !== 'Presente' && estadoPadre !== 'Presente') && radioSoloRepresentante) {
                    radioSoloRepresentante.checked = true;
                }

                // Si el tipo actual seleccionado es válido o presente, ocultar el aviso inline
                const tipoActual = document.querySelector('input[name="tipo_representante"]:checked')?.value;
                if (tipoActual === 'solo_representante' ||
                    (tipoActual === 'progenitor_madre_representante' && estadoMadre === 'Presente') ||
                    (tipoActual === 'progenitor_padre_representante' && estadoPadre === 'Presente')) {
                    ocultarAlertaProgenitorAusente();
                }

                // Actualizar las opciones de parentesco y carnet de la patria
                actualizarOpcionesParentesco();
                actualizarOpcionesCarnetPatria();
            }

            // Configurar validación en tiempo real para el código del carnet
            document.addEventListener('DOMContentLoaded', function() {
                const codigoInput = document.getElementById('codigo');
                const carnetSelect = document.getElementById('carnet-patria');

                // Validar al cambiar el valor
                if (codigoInput) {
                    codigoInput.addEventListener('input', function() {
                        validarCodigoCarnet(this);
                    });

                    codigoInput.addEventListener('blur', function() {
                        validarCodigoCarnet(this);
                    });
                }

                // Validar cuando cambia la selección del carnet
                if (carnetSelect) {
                    carnetSelect.addEventListener('change', function() {
                        if (codigoInput) {
                            validarCodigoCarnet(codigoInput);
                        }
                    });
                }
            });

            // Event listener consolidado para tipo_representante (eliminamos listeners duplicados)
            document.querySelectorAll('input[name="tipo_representante"]').forEach(radio => {
                radio.addEventListener('change', async function() {
                    const tipo = this.value;
                    console.log(`[TIPO REPRESENTANTE] Cambiando a: ${tipo}`);

                    // Resetear bandera de copia para permitir nuevas copias (MÚLTIPLES INTENTOS)
                    console.log('[TIPO REPRESENTANTE] Estado ANTES de resetear copiandoDatosProgenitor:', window.copiandoDatosProgenitor);
                    
                    // FORZAR RESETEO MÚLTIPLE VECES para asegurar que se resetee
                    for (let i = 0; i < 3; i++) {
                        window.copiandoDatosProgenitor = false;
                        await new Promise(resolve => setTimeout(resolve, 10));
                    }
                    
                    console.log('[TIPO REPRESENTANTE] Bandera copiandoDatosProgenitor RESETEADA MÚLTIPLES VECES');
                    console.log('[TIPO REPRESENTANTE] Estado DESPUÉS de resetear copiandoDatosProgenitor:', window.copiandoDatosProgenitor);

                    // Actualizar opciones de parentesco con manejo de errores
                    try {
                        actualizarOpcionesParentesco();
                    } catch (error) {
                        console.warn('[TIPO REPRESENTANTE] Error en actualizarOpcionesParentesco (ignorado):', error);
                    }

                    // Mostrar la sección de representante
                    toggleSeccionRepresentante(true);

                    // Limpiar campos según el tipo seleccionado
                    if (tipo !== 'solo_representante') {
                        await resetearCamposRepresentante();
                    }
                    
                    // SIEMPRE ejecutar limpieza completa cuando se selecciona "solo_representante"
                    if (tipo === 'solo_representante') {
                        ocultarErrorTipoRepresentante();
                        // Para "solo representante legal", realizar limpieza SIMPLE (sin duplicación)
                        toggleCamposRepresentante(false);
                        
                        // FORZAR: Regenerar opciones originales limpias antes de la limpieza
                        console.log('[LIMPIEZA COMPLETA] Regenerando opciones originales limpias...');
                        guardarOpcionesOriginales();
                        
                        // Limpieza COMPLETA para SELECTPICKERS del representante (elimina duplicados)
                        const selectpickersRepresentante = [
                            'prefijo-representante',
                            'prefijo_dos-representante',
                            'ocupacion-representante',
                            'parentesco',
                            'idPais-representante',
                            'idEstado-representante',
                            'idMunicipio-representante',
                            'idparroquia-representante'
                        ];

                        for (const id of selectpickersRepresentante) {
                            const select = document.getElementById(id);
                            if (select) {
                                try {
                                    // SIEMPRE usar opciones originales guardadas, nunca generar nuevas
                                    let opcionesOriginales = null;
                                    if (select.dataset.opcionesOriginales) {
                                        try {
                                            opcionesOriginales = JSON.parse(select.dataset.opcionesOriginales);
                                            console.log(`[LIMPIEZA COMPLETA] Usando opciones originales guardadas para ${id}: ${opcionesOriginales.length} opciones`);
                                        } catch (e) {
                                            console.warn(`[LIMPIEZA COMPLETA] Error parseando opciones originales para ${id}:`, e);
                                            opcionesOriginales = null;
                                        }
                                    }
                                    
                                    // Si no hay opciones originales, NO generar nuevas - solo limpiar el select existente
                                    if (!opcionesOriginales || opcionesOriginales.length === 0) {
                                        console.warn(`[LIMPIEZA COMPLETA] ⚠️ No hay opciones originales para ${id}, limpiando select existente sin reconstruir`);
                                        // Solo limpiar el select existente sin reconstruir
                                        const $select = $(select);
                                        $select.val('');
                                        $select.selectpicker('refresh');
                                        continue; // Saltar al siguiente select
                                    }
                                    
                                    // Destruir selectpicker completamente
                                    const $select = $(select);
                                    if ($select.data('selectpicker')) {
                                        $select.selectpicker('destroy');
                                    }

                                    // Limpiar HTML completamente
                                    select.innerHTML = '';

                                    // Restaurar opciones limpias desde originales
                                    opcionesOriginales.forEach(opt => {
                                        const option = new Option(opt.text, opt.value, false, false);
                                        if (opt.disabled) option.disabled = true;
                                        select.add(option);
                                    });

                                    // Resetear selección
                                    select.value = '';
                                    select.selectedIndex = 0;

                                    // Reconstruir selectpicker
                                    $select.selectpicker({
                                        liveSearch: true,
                                        noneResultsText: 'No hay resultados'
                                    });

                                    // Forzar refresh
                                    $select.selectpicker('refresh');
                                    
                                    console.log(`[LIMPIEZA COMPLETA] Selectpicker ${id} restaurado con ${opcionesOriginales.length} opciones limpias`);
                                } catch (error) {
                                    console.warn(`[LIMPIEZA COMPLETA] Error limpiando ${id}:`, error);
                                }
                            }
                        }
                        
                        // DIAGNÓSTICO: Verificar qué está pasando después de la limpieza
                        setTimeout(() => {
                            console.log('\n🔍 DIAGNÓSTICO DESPUÉS DE LIMPIEZA 🔍');
                            diagnosticarSelectpicker('prefijo-representante');
                        }, 500);
                    }

                    // Resetear bandera NUEVAMENTE después del reseteo de campos (por si los selectpickers la afectaron)
                    window.copiandoDatosProgenitor = false;
                    console.log('[TIPO REPRESENTANTE] Bandera reseteada DESPUÉS de resetearCamposRepresentante');

                    // Manejar validación de cédula del representante según el tipo
                    const cedulaRepresentanteInput = document.getElementById('numero_documento-representante');
                    if (cedulaRepresentanteInput) {
                        if (tipo === 'progenitor_padre_representante' || tipo === 'progenitor_madre_representante') {
                            limpiarError(cedulaRepresentanteInput);
                        } else {
                            // Para otros tipos, validar la cédula
                            validarnumero_documento(cedulaRepresentanteInput);
                        }
                    }

                    if (tipo === 'solo_representante') {
                        // Para "Solo Representante Legal", reactivar selectpickers y habilitar campos
                        console.log('[TIPO REPRESENTANTE] Modo: Solo Representante Legal - reactivando selectpickers');
                        
                        // REACTIVAR SELECTPICKERS: Convertir selects normales de vuelta a selectpickers
                        const selectIdsParaReactivar = [
                            'prefijo-representante',
                            'prefijo_dos-representante',
                            'ocupacion-representante',
                            'parentesco',
                            'idPais-representante',
                            'idEstado-representante',
                            'idMunicipio-representante',
                            'idparroquia-representante'
                        ];
                        
                        selectIdsParaReactivar.forEach((id, index) => {
                            setTimeout(() => {
                                const select = document.getElementById(id);
                                if (select && !select.classList.contains('selectpicker')) {
                                    console.log(`[REACTIVACIÓN] Reactivando selectpicker para ${id}`);
                                    
                                    // Agregar clase selectpicker
                                    select.classList.add('selectpicker');
                                    
                                    // Inicializar selectpicker
                                    try {
                                        $(select).selectpicker({
                                            liveSearch: true,
                                            size: 8,
                                            noneResultsText: 'No hay resultados para {0}',
                                            selectOnTab: false,
                                            showSubtext: false,
                                            showIcon: true,
                                            width: 'auto'
                                        });
                                        
                                        // Refresh para asegurar que se muestre el valor actual
                                        $(select).selectpicker('refresh');
                                        
                                        console.log(`[REACTIVACIÓN] ✅ ${id} reactivado correctamente`);
                                        
                                    } catch (error) {
                                        console.error(`[REACTIVACIÓN] Error reactivando ${id}:`, error);
                                    }
                                } else if (select && select.classList.contains('selectpicker')) {
                                    // Si ya tiene selectpicker, solo hacer refresh
                                    try {
                                        $(select).selectpicker('refresh');
                                        console.log(`[REACTIVACIÓN] ${id} ya tenía selectpicker, solo refresh`);
                                    } catch (error) {
                                        console.error(`[REACTIVACIÓN] Error en refresh de ${id}:`, error);
                                    }
                                }

                                // Configurar eventos de ubicación después de reactivar el último select
                                if (id === 'idparroquia-representante') {
                                    setTimeout(() => {
                                        console.log('[REACTIVACIÓN] Configurando eventos de ubicación después de reactivación...');
                                        configurarEventosRepresentante();
                                    }, 200);
                                }
                            }, index * 100); // Stagger para evitar conflictos
                        });
                        
                        // Los campos ya están habilitados arriba
                    } else if (tipo === 'progenitor_padre_representante') {
                        // Para "Padre como Representante Legal"
                        console.log('[TIPO REPRESENTANTE] Modo: Padre como Representante Legal');
                        
                        // Verificar estado del padre y copiar si es presente
                        const padrePresente = document.querySelector('input[name="estado_padre"]:checked')?.value === 'Presente';
                        if (padrePresente) {
                            ocultarAlertaProgenitorAusente();
                            // Esperar más tiempo a que los selects se estabilicen después del reseteo
                            await new Promise(resolve => setTimeout(resolve, 1000));
                            
                            // Resetear bandera UNA VEZ MÁS antes de copiar
                            window.copiandoDatosProgenitor = false;
                            console.log('[TIPO REPRESENTANTE] Bandera reseteada ANTES de copiar padre');
                            
                            // Establecer parentesco como "Papá" y copiar datos del padre
                            await establecerParentesco(false);
                            await copiarDatosPadreARepresentante();
                        } else {
                            mostrarAlertaProgenitorAusente('padre');
                            document.getElementById('solo_representante').checked = true;
                            document.getElementById('solo_representante').dispatchEvent(new Event('change'));
                            return;
                        }
                    } else if (tipo === 'progenitor_madre_representante') {
                        // Para "Madre como Representante Legal"
                        console.log('[TIPO REPRESENTANTE] Modo: Madre como Representante Legal');
                        
                        // Verificar estado de la madre y copiar si es presente
                        const madrePresente = document.querySelector('input[name="estado_madre"]:checked')?.value === 'Presente';
                        if (madrePresente) {
                            ocultarAlertaProgenitorAusente();
                            // Esperar más tiempo a que los selects se estabilicen después del reseteo
                            await new Promise(resolve => setTimeout(resolve, 1000));
                            
                            // Resetear bandera UNA VEZ MÁS antes de copiar
                            window.copiandoDatosProgenitor = false;
                            console.log('[TIPO REPRESENTANTE] Bandera reseteada ANTES de copiar madre');
                            
                            // Establecer parentesco como "Mamá" y copiar datos de la madre
                            await establecerParentesco(true);
                            await copiarDatosMadreARepresentante();
                        } else {
                            mostrarAlertaProgenitorAusente('madre');
                            document.getElementById('solo_representante').checked = true;
                            document.getElementById('solo_representante').dispatchEvent(new Event('change'));
                            return;
                        }
                    }
                });
            });

            // Controlar clics en opciones de representante para evitar seleccionar progenitores ausentes
            document.querySelectorAll('.tipo-rep-option').forEach(option => {
                option.addEventListener('click', function(e) {
                    const radio = this.querySelector('input[type="radio"]');
                    if (!radio) return;

                    const estadoMadre = document.querySelector('input[name="estado_madre"]:checked')?.value;
                    const estadoPadre = document.querySelector('input[name="estado_padre"]:checked')?.value;

                    if (radio.id === 'progenitor_madre_representante' && (radio.disabled || estadoMadre !== 'Presente')) {
                        e.preventDefault();
                        e.stopPropagation();
                        radio.checked = false;
                        mostrarErrorTipoRepresentante('madre');
                        const soloRep = document.getElementById('solo_representante');
                        if (soloRep && !document.querySelector('input[name="tipo_representante"]:checked')) {
                            soloRep.checked = true;
                        }
                    } else if (radio.id === 'progenitor_padre_representante' && (radio.disabled || estadoPadre !== 'Presente')) {
                        e.preventDefault();
                        e.stopPropagation();
                        radio.checked = false;
                        mostrarErrorTipoRepresentante('padre');
                        const soloRep = document.getElementById('solo_representante');
                        if (soloRep && !document.querySelector('input[name="tipo_representante"]:checked')) {
                            soloRep.checked = true;
                        }
                    }
                });
            });

            // Listener directo en los radio buttons de tipo de representante
            document.querySelectorAll('input[name="tipo_representante"]').forEach(radio => {
                radio.addEventListener('click', function(e) {
                    const estadoMadre = document.querySelector('input[name="estado_madre"]:checked')?.value;
                    const estadoPadre = document.querySelector('input[name="estado_padre"]:checked')?.value;

                    if (this.id === 'progenitor_madre_representante' && (this.disabled || estadoMadre !== 'Presente')) {
                        e.preventDefault();
                        this.checked = false;
                        mostrarErrorTipoRepresentante('madre');
                        const soloRep = document.getElementById('solo_representante');
                        if (soloRep) soloRep.checked = true;
                    } else if (this.id === 'progenitor_padre_representante' && (this.disabled || estadoPadre !== 'Presente')) {
                        e.preventDefault();
                        this.checked = false;
                        mostrarErrorTipoRepresentante('padre');
                        const soloRep = document.getElementById('solo_representante');
                        if (soloRep) soloRep.checked = true;
                    } else {
                        ocultarErrorTipoRepresentante();
                    }
                });
            });

            // Actualizar cuando cambie el estado de la madre
            document.querySelectorAll('input[name="estado_madre"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'Presente') {
                        ocultarErrorTipoRepresentante();
                    }
                    actualizarOpcionesParentesco();
                    actualizarRadiosRepresentante();
                    actualizarOpcionesCarnetPatria();
                });
            });

            // Actualizar cuando cambie el estado del padre
            document.querySelectorAll('input[name="estado_padre"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'Presente') {
                        ocultarErrorTipoRepresentante();
                    }
                    actualizarOpcionesParentesco();
                    actualizarRadiosRepresentante();
                    actualizarOpcionesCarnetPatria();
                });
            });

            // Guardar opciones originales ANTES de cualquier inicialización
            guardarOpcionesOriginales();
            
            // Ejecutar al cargar la página
            document.addEventListener('DOMContentLoaded', function() {
                actualizarRadiosRepresentante();
                actualizarOpcionesParentesco();
                actualizarOpcionesCarnetPatria(); // Asegurarse de que las opciones del Carnet de la Patria estén actualizadas
                
                // Guardar opciones originales limpias de todos los selects antes de cualquier manipulación
                setTimeout(() => {
                    guardarOpcionesOriginales();
                }, 100);
            });

            // Función para guardar las opciones originales limpias de todos los selects
            function guardarOpcionesOriginales() {
                console.log('[OPCIONES ORIGINALES] Iniciando guardado de opciones originales...');
                
                const selectIds = [
                    'prefijo-representante',
                    'prefijo_dos-representante',
                    'ocupacion-representante',
                    'parentesco',
                    'idPais-representante',
                    'idEstado-representante',
                    'idMunicipio-representante',
                    'idparroquia-representante',
                    'prefijo-padre',
                    'prefijo_dos-padre',
                    'ocupacion-padre',
                    'idPais-padre',
                    'idEstado-padre',
                    'idMunicipio-padre',
                    'idparroquia-padre',
                    'prefijo-madre',
                    'prefijo_dos-madre',
                    'ocupacion-madre',
                    'idPais-madre',
                    'idEstado-madre',
                    'idMunicipio-madre',
                    'idparroquia-madre'
                ];

                let guardados = 0;
                selectIds.forEach(id => {
                    const select = document.getElementById(id);
                    if (select) {
                        try {
                            // Forzar limpieza de opciones duplicadas antes de guardar
                            const opcionesUnicas = [];
                            const valoresVistos = new Set();
                            
                            Array.from(select.options).forEach(opt => {
                                if (!valoresVistos.has(opt.value)) {
                                    valoresVistos.add(opt.value);
                                    opcionesUnicas.push({
                                        value: opt.value,
                                        text: opt.text,
                                        disabled: opt.disabled,
                                        selected: opt.selected
                                    });
                                }
                            });
                            
                            select.dataset.opcionesOriginales = JSON.stringify(opcionesUnicas);
                            guardados++;
                            console.log(`[OPCIONES ORIGINALES] ✅ Guardadas para ${id}: ${opcionesUnicas.length} opciones únicas`);
                        } catch (error) {
                            console.warn(`[OPCIONES ORIGINALES] ❌ Error guardando ${id}:`, error);
                        }
                    } else {
                        console.warn(`[OPCIONES ORIGINALES] ⚠️ Select no encontrado: ${id}`);
                    }
                });
                
                console.log(`[OPCIONES ORIGINALES] 📊 Resumen: ${guardados}/${selectIds.length} selects procesados`);
                return guardados > 0;
            }

            // Función para crear opciones limpias basadas en el tipo de select
            function crearOpcionesLimpias(selectId) {
                const opciones = [];
                
                if (selectId.includes('prefijo')) {
                    // Prefijos telefónicos
                    opciones.push({ value: "", text: "Seleccione", disabled: false, selected: false });
                    opciones.push({ value: "1", text: "0412 ()", disabled: false, selected: false });
                    opciones.push({ value: "5", text: "0414 ()", disabled: false, selected: false });
                    opciones.push({ value: "2", text: "0416 ()", disabled: false, selected: false });
                    opciones.push({ value: "6", text: "0422 ()", disabled: false, selected: false });
                    opciones.push({ value: "3", text: "0424 ()", disabled: false, selected: false });
                    opciones.push({ value: "4", text: "0426 ()", disabled: false, selected: false });
                } else if (selectId.includes('ocupacion')) {
                    // Ocupaciones - agregar placeholder
                    opciones.push({ value: "", text: "Seleccione una ocupación", disabled: false, selected: false });
                    
                    // Usar datos globales si están disponibles
                    if (typeof ocupaciones !== 'undefined' && ocupaciones.length > 0) {
                        ocupaciones.forEach(ocup => {
                            opciones.push({ 
                                value: ocup.id.toString(), 
                                text: ocup.nombre || ocup.descripcion || 'Ocupación', 
                                disabled: false, 
                                selected: false 
                            });
                        });
                    }
                } else if (selectId.includes('parentesco')) {
                    // Parentesco
                    opciones.push({ value: "", text: "Seleccione un parentesco", disabled: false, selected: false });
                    opciones.push({ value: "Mamá", text: "Mamá", disabled: false, selected: false });
                    opciones.push({ value: "Papá", text: "Papá", disabled: false, selected: false });
                    opciones.push({ value: "Otro", text: "Otro", disabled: false, selected: false });
                    opciones.push({ value: "Representante Legal", text: "Representante Legal", disabled: false, selected: false });
                } else if (selectId.includes('idPais')) {
                    // Países
                    opciones.push({ value: "", text: "Seleccione un país", disabled: false, selected: false });
                    if (typeof paises !== 'undefined' && paises.length > 0) {
                        paises.forEach(pais => {
                            opciones.push({ 
                                value: pais.id.toString(), 
                                text: pais.nombre, 
                                disabled: false, 
                                selected: false 
                            });
                        });
                    }
                } else {
                    // Para selects de ubicación (estado, municipio, parroquia) - solo placeholder
                    if (selectId.includes('idEstado')) {
                        opciones.push({ value: "", text: "Seleccione un estado", disabled: false, selected: false });
                    } else if (selectId.includes('idMunicipio')) {
                        opciones.push({ value: "", text: "Seleccione un municipio", disabled: false, selected: false });
                    } else if (selectId.includes('idparroquia')) {
                        opciones.push({ value: "", text: "Seleccione una parroquia", disabled: false, selected: false });
                    } else {
                        opciones.push({ value: "", text: "Seleccione", disabled: false, selected: false });
                    }
                }
                
                console.log(`[OPCIONES LIMPIAS] Creadas ${opciones.length} opciones para ${selectId}`);
                return opciones;
            }

            // ================================
            // VALIDACIÓN EN TIEMPO REAL CÉDULAS
            // ================================

            const verificarnumero_documentoUrl = "{{ route('representante.verificar_numero_documento') }}";
            const buscarnumero_documentoUrl = "{{ route('representante.buscar_numero_documento') }}";

            function marcarnumero_documentoError(input, mensaje) {
                input.classList.add('is-invalid');
                const errorId = input.id + '-error';
                let errorElement = document.getElementById(errorId);
                if (!errorElement) {
                    errorElement = document.createElement('small');
                    errorElement.className = 'text-danger';
                    errorElement.id = errorId;
                    const contenedor = input.closest('.form-group, .input-group') || input;
                    contenedor.after(errorElement);
                }
                errorElement.textContent = mensaje;
            }

            function limpiarnumero_documentoError(input) {
                input.classList.remove('is-invalid');
                const errorId = input.id + '-error';
                const errorElement = document.getElementById(errorId);
                if (errorElement) {
                    errorElement.textContent = '';
                }
            }

            function numero_documentoSeRepiteEnFormulario(valor, idActual) {
                if (!valor) return false;

                // Si es el campo de cédula del representante y está seleccionado "Progenitor como Representante", no marcar como duplicado
                if (idActual === 'numero_documento-representante') {
                    const progenitorRep = document.querySelector(
                        'input[name="tipo_representante"][value="progenitor_representante"]:checked');
                    if (progenitorRep) {
                        return false; // No marcar como duplicado si es progenitor representante
                    }
                }

                const ids = ['numero_documento', 'numero_documento-padre', 'numero_documento-representante'];
                let contador = 0;

                ids.forEach(id => {
                    // No comparar consigo mismo
                    if (id === idActual) return;

                    const campo = document.getElementById(id);
                    if (campo && campo.value && campo.value === valor) {
                        // Si es el campo de cédula del representante y está seleccionado "Progenitor como Representante", no contar como duplicado
                        if (id === 'numero_documento-representante') {
                            const progenitorRep = document.querySelector(
                                'input[name="tipo_representante"][value="progenitor_representante"]:checked'
                            );
                            if (progenitorRep) {
                                return; // No contar este campo si es progenitor representante
                            }
                        }
                        contador++;
                    }
                });

                return contador >
                    0; // Cambiado a > 0 para marcar como duplicado si se encuentra en cualquier otro campo
            }

            function verificarnumero_documentoCampo(selector, personaIdSelector) {
                const input = document.querySelector(selector);
                if (!input) return;

                input.addEventListener('blur', function() {
                    const valor = this.value.trim();
                    limpiarnumero_documentoError(this);
                    if (!valor) return;

                    // Verificar si es el campo de cédula del representante y está seleccionado "Progenitor como Representante"
                    const esProgenitorRepresentante = document.querySelector(
                        'input[name="tipo_representante"][value="progenitor_representante"]:checked'
                    ) !== null;

                    if (this.id === 'numero_documento-representante' && esProgenitorRepresentante) {
                        // No validar cédula duplicada si es progenitor representante
                        limpiarnumero_documentoError(this);
                        return;
                    }

                    // Verificar repetición dentro del mismo formulario
                    if (numero_documentoSeRepiteEnFormulario(valor, this.id)) {
                        marcarnumero_documentoError(this,
                            'Esta cédula ya se está usando en otro bloque del formulario');
                        return;
                    }

                    // Verificar contra la base de datos
                    const personaId = personaIdSelector ? document.querySelector(personaIdSelector)?.value :
                        '';

                    fetch(
                            `${verificarnumero_documentoUrl}?numero_documento=${valor}&persona_id=${personaId}`
                        )
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw err;
                                });
                            }
                            return response.json();
                        })
                        .then(resp => {
                            console.log('numero_documento OK', selector, resp);
                            limpiarnumero_documentoError(input);

                            // Si es el campo de representante y se detectó una persona existente
                            if (selector === '#numero_documento-representante') {
                                const alerta = document.getElementById('alerta-persona-encontrada-representante');
                                if (resp && resp.status === 'persona_existente' && resp.persona) {
                                    if (alerta) {
                                        alerta.classList.remove('d-none');
                                        alerta.classList.add('d-flex');
                                    }
                                    autocompletarYBloquearRepresentante(resp.persona);
                                } else {
                                    if (alerta) {
                                        alerta.classList.add('d-none');
                                        alerta.classList.remove('d-flex');
                                    }
                                    desbloquearCamposRepresentante();
                                }
                            }
                        })
                        .catch(error => {
                            if (selector === '#numero_documento-representante') {
                                const alerta = document.getElementById('alerta-persona-encontrada-representante');
                                if (alerta) {
                                    alerta.classList.add('d-none');
                                    alerta.classList.remove('d-flex');
                                }
                                desbloquearCamposRepresentante();
                            }
                            if (error.message) {
                                marcarnumero_documentoError(input, error.message);
                            } else {
                                console.error('Error al verificar cédula', error);
                            }
                        });
                });
            }

            function autocompletarYBloquearRepresentante(persona) {
                if (!persona) return;

                const personaIdHidden = document.getElementById('persona-id-representante');
                if (personaIdHidden && persona.id) {
                    personaIdHidden.value = persona.id;
                }

                const mapeo = [
                    { id: 'primer-nombre-representante', val: persona.primer_nombre },
                    { id: 'segundo-nombre-representante', val: persona.segundo_nombre },
                    { id: 'tercer-nombre-representante', val: persona.tercer_nombre },
                    { id: 'primer-apellido-representante', val: persona.primer_apellido },
                    { id: 'segundo-apellido-representante', val: persona.segundo_apellido },
                    { id: 'fecha-nacimiento-representante', val: persona.fecha_nacimiento },
                    { id: 'correo-representante', val: persona.email },
                    { id: 'telefono-representante', val: persona.telefono },
                    { id: 'telefono_dos-representante', val: persona.telefono_dos },
                    { id: 'direccion-habitacion', val: persona.direccion }
                ];

                mapeo.forEach(item => {
                    const el = document.getElementById(item.id);
                    if (el) {
                        if (item.val !== undefined && item.val !== null && item.val !== '') {
                            el.value = item.val;
                        }
                        el.readOnly = true;
                        el.classList.add('bg-light');
                    }
                });

                if (persona.genero_id) {
                    const gen = document.getElementById('sexo-representante');
                    if (gen) {
                        gen.value = persona.genero_id;
                        gen.style.pointerEvents = 'none';
                        gen.classList.add('bg-light');
                    }
                }

                if (persona.tipo_documento_id) {
                    const tipoDoc = document.getElementById('tipo-ci-representante');
                    if (tipoDoc) {
                        tipoDoc.value = persona.tipo_documento_id;
                        tipoDoc.style.pointerEvents = 'none';
                        tipoDoc.classList.add('bg-light');
                    }
                }

                if (persona.prefijo_id) {
                    const pref = document.getElementById('prefijo-representante');
                    if (pref) {
                        pref.value = persona.prefijo_id;
                        pref.style.pointerEvents = 'none';
                        pref.classList.add('bg-light');
                    }
                }
            }

            function desbloquearCamposRepresentante() {
                const campos = [
                    'primer-nombre-representante',
                    'segundo-nombre-representante',
                    'tercer-nombre-representante',
                    'primer-apellido-representante',
                    'segundo-apellido-representante',
                    'fecha-nacimiento-representante',
                    'correo-representante',
                    'telefono-representante',
                    'telefono_dos-representante',
                    'direccion-habitacion'
                ];

                campos.forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.readOnly) {
                        el.readOnly = false;
                        el.classList.remove('bg-light');
                    }
                });

                ['sexo-representante', 'tipo-ci-representante', 'prefijo-representante'].forEach(id => {
                    const select = document.getElementById(id);
                    if (select) {
                        select.style.pointerEvents = '';
                        select.classList.remove('bg-light');
                    }
                });
            }

            // Aplicar validación en tiempo real a las tres cédulas principales
            verificarnumero_documentoCampo('#numero_documento', null); // Madre
            verificarnumero_documentoCampo('#numero_documento-padre', null); // Padre
            verificarnumero_documentoCampo('#numero_documento-representante',
                '#persona-id-representante'); // Representante legal

            // ================================
            // BLOQUEO / DESBLOQUEO Y COLAPSO SUAVE DE SECCIONES
            // ================================

            // Función para actualizar los estilos visuales de los botones pill Presente / Ausente (TAREA 3)
            function actualizarEstilosPillsEstado(nombreEstado) {
                const radios = document.querySelectorAll(`input[name="${nombreEstado}"]`);
                radios.forEach(radio => {
                    const pillItem = radio.closest('.state-pill-item');
                    const pillBtn = pillItem ? pillItem.querySelector('.state-pill-btn') : null;
                    if (pillBtn) {
                        if (radio.checked) {
                            pillBtn.classList.add('active');
                        } else {
                            pillBtn.classList.remove('active');
                        }
                    }
                });
            }

            function toggleSeccionPorEstado(nombreEstado, contenedorCampos, excepcionesNames) {
                const radios = document.querySelectorAll(`input[name="${nombreEstado}"]`);
                if (radios.length === 0 || !contenedorCampos) return;

                function aplicarEstado(animar = true) {
                    const radioChecked = Array.from(radios).find(r => r.checked);
                    const valor = radioChecked?.value;
                    const esPresente = valor === 'Presente';

                    // Actualizar estilos dinámicos de los botones de estado (Verde = Presente, Rojo = Ausente)
                    actualizarEstilosPillsEstado(nombreEstado);

                    // Habilitar o deshabilitar todos los inputs, selects y textareas dentro del contenedor (TAREA 2)
                    const inputs = contenedorCampos.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        const name = input.name;
                        if (name && excepcionesNames && excepcionesNames.includes(name)) {
                            return; // no tocar radios de estado
                        }

                        if (esPresente) {
                            input.disabled = false;
                            const wasRequired = input.dataset.wasRequired;
                            if (wasRequired === 'true') {
                                input.required = true;
                            }
                        } else {
                            if (input.required) {
                                input.dataset.wasRequired = 'true';
                            }
                            input.required = false;
                            input.disabled = true;
                        }
                    });

                    // Deshabilitar / habilitar selectpickers si existen
                    if (typeof $ !== 'undefined' && $.fn.selectpicker) {
                        $(contenedorCampos).find('select.selectpicker').each(function() {
                            $(this).prop('disabled', !esPresente);
                            try {
                                $(this).selectpicker('refresh');
                            } catch (e) {}
                        });
                    }

                    // TAREA 2: Animación suave de colapsar / desplegar (slideUp / slideDown)
                    if (typeof $ !== 'undefined') {
                        if (animar) {
                            if (esPresente) {
                                $(contenedorCampos).stop(true, true).slideDown(350);
                            } else {
                                $(contenedorCampos).stop(true, true).slideUp(350);
                            }
                        } else {
                            if (esPresente) {
                                $(contenedorCampos).show();
                            } else {
                                $(contenedorCampos).hide();
                            }
                        }
                    } else {
                        contenedorCampos.style.display = esPresente ? 'block' : 'none';
                    }
                }

                // Estado inicial al cargar la página (sin animación para evitar saltos visuales)
                aplicarEstado(false);

                // Escuchar cambios en los botones Presente / Ausente
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        aplicarEstado(true);
                    });
                });
            }

            // Madre: Contenedor de campos de la Madre (#campos-madre)
            const camposMadre = document.getElementById('campos-madre') || document.getElementById('Presente_madre')?.closest('.card')?.querySelector('.card-body');
            toggleSeccionPorEstado('estado_madre', camposMadre, ['estado_madre']);

            // Refuerzo específico: asegurar bloqueo de ocupación y convive de la madre cuando está Ausente
            function aplicarBloqueoCamposMadre() {
                const estadoMadreVal = document.querySelector('input[name="estado_madre"]:checked')?.value;
                const esPresenteMadre = estadoMadreVal === 'Presente';

                const ocupacionMadre = document.getElementById('ocupacion-madre');
                const otraOcupacionMadre = document.getElementById('otra-ocupacion');
                const conviveSiMadre = document.getElementById('convive-si');
                const conviveNoMadre = document.getElementById('convive-no');

                if (esPresenteMadre) {
                    if (ocupacionMadre) {
                        ocupacionMadre.disabled = false;
                        ocupacionMadre.required = true;
                    }
                    const otraOcupacionContainer = document.getElementById('otra-ocupacion-container');
                    if (otraOcupacionMadre && otraOcupacionContainer) {
                        otraOcupacionMadre.disabled = !otraOcupacionContainer.classList.contains('d-none');
                        otraOcupacionMadre.required = !otraOcupacionContainer.classList.contains('d-none');
                    }
                    if (conviveSiMadre) conviveSiMadre.disabled = false;
                    if (conviveNoMadre) conviveNoMadre.disabled = false;
                } else {
                    if (ocupacionMadre) {
                        ocupacionMadre.disabled = true;
                        ocupacionMadre.required = false;
                    }
                    if (otraOcupacionMadre) {
                        otraOcupacionMadre.disabled = true;
                        otraOcupacionMadre.required = false;
                    }
                    if (conviveSiMadre) conviveSiMadre.disabled = true;
                    if (conviveNoMadre) conviveNoMadre.disabled = true;
                }
            }

            // Aplicar una vez al cargar y cada vez que cambie el estado de la madre
            aplicarBloqueoCamposMadre();
            document.querySelectorAll('input[name="estado_madre"]').forEach(radio => {
                radio.addEventListener('change', aplicarBloqueoCamposMadre);
            });

            // Padre: Contenedor de campos del Padre (#campos-padre)
            const camposPadre = document.getElementById('campos-padre') || document.getElementById('Presente_padre')?.closest('.card')?.querySelector('.card-body');
            toggleSeccionPorEstado('estado_padre', camposPadre, ['estado_padre']);

            // ================================
            // REPRESENTANTE LEGAL COMO PROGENITOR
            // ================================

            function rellenarRepresentanteDesdeRespuesta(resp) {
                if (!resp || !resp.data || !resp.data.persona) return;

                const persona = resp.data.persona;
                const representante = resp.data;
                const legal = resp.data.legal || {};

                document.getElementById('persona-id-representante').value = persona.id || '';
                document.getElementById('representante-id').value = representante.id || '';

                document.getElementById('primer-nombre-representante').value = persona.primer_nombre || '';
                document.getElementById('segundo-nombre-representante').value = persona.segundo_nombre || '';
                document.getElementById('tercer-nombre-representante').value = persona.tercer_nombre || '';
                document.getElementById('primer-apellido-representante').value = persona.primer_apellido || '';
                document.getElementById('segundo-apellido-representante').value = persona.segundo_apellido || '';
                document.getElementById('fechaNacimiento-representante').value = persona.fecha_nacimiento || '';

                const sexoRepresentante = document.getElementById('sexo-representante');
                if (sexoRepresentante) {
                    sexoRepresentante.value = persona.genero_id || '';
                }

                // Otros campos opcionales si existen
                const telefonoRepresentante = document.getElementById('telefono-representante');
                const lugarNacimientoRepresentante = document.getElementById('lugar-nacimiento-representante');
                if (telefonoRepresentante) telefonoRepresentante.value = persona.telefono || '';
                if (lugarNacimientoRepresentante) lugarNacimientoRepresentante.value = persona.direccion || '';

                // Correo: preferir el correo del representante legal, si no, el email de la persona
                const correo = legal.correo_representante || persona.email || '';
                document.getElementById('correo-representante').value = correo;
            }

            // Función segura para obtener un elemento por ID
            const getElement = (id) => document.getElementById(id);

            // Función segura para establecer un valor en un elemento
            const setValue = (id, value) => {
                const element = getElement(id);
                if (element) {
                    console.log(`=== SETVALUE: Estableciendo valor en ${id}:`, value);
                    
                    // Para selects con selectpicker, usar un enfoque más simple sin destruir
                    if (typeof $ !== 'undefined' && $(element).hasClass('selectpicker')) {
                        try {
                            // Verificar si el elemento ya tiene el valor para evitar recargas innecesarias
                            if (element.value === value.toString()) {
                                console.log(`=== SETVALUE: El valor ${value} ya está establecido en ${id}, omitiendo`);
                                return;
                            }
                            
                            console.log(`=== SETVALUE: Estableciendo valor en selectpicker ${id}`);
                            // Establecer el valor directamente sin destruir
                            $(element).selectpicker('val', value);
                            $(element).selectpicker('refresh');
                            console.log(`=== SETVALUE: Valor establecido en selectpicker ${id}:`, value);
                        } catch (error) {
                            console.error(`=== SETVALUE: Error al manejar selectpicker ${id}:`, error);
                            // Fallback: establecer el valor directamente
                            element.value = value || '';
                        }
                    } else {
                        // Para elementos normales, también verificar si ya tienen el valor
                        if (element.value !== value.toString()) {
                            element.value = value || '';
                            console.log(`=== SETVALUE: Valor establecido directamente en ${id}:`, value);
                        } else {
                            console.log(`=== SETVALUE: El valor ${value} ya está establecido en ${id}, omitiendo`);
                        }
                    }
                    
                    // Disparar evento change para notificar el cambio
                    setTimeout(() => {
                        const event = new Event('change');
                        element.dispatchEvent(event);
                        console.log(`=== SETVALUE: Evento change disparado para ${id}`);
                    }, 50);
                } else {
                    console.log(`=== SETVALUE: Elemento ${id} no encontrado`);
                }
            };

            // Función para establecer valores en selects dependientes
            function setSelectValue(selectId, value, callback) {
                const select = document.getElementById(selectId);
                if (!select) return;

                select.value = value;
                if (select.value === value) {
                    // Disparar evento change para actualizar selects dependientes
                    select.dispatchEvent(new Event('change'));
                    if (callback) callback();
                } else {
                    // Si el valor no se establece de inmediato, esperar un momento y volver a intentar
                    setTimeout(() => {
                        setSelectValue(selectId, value, callback);
                    }, 100);
                }
            }

            function copiarDesdeMadreOPadreSiCoincide(numero_documento) {
                if (!numero_documento || !numero_documento.trim()) return false;

                // Limpiar errores previos
                const errorElement = getElement('error-copia-datos');
                if (errorElement) errorElement.remove();

                // Función para copiar ubicación
                const copiarUbicacion = (prefijoOrigen, prefijoDestino) => {
                    // Evitar recargas múltiples si ya se está procesando
                    if (window.copiandoUbicacion) {
                        console.log('Ya se está copiando ubicación, omitiendo...');
                        return;
                    }
                    window.copiandoUbicacion = true;
                    
                    try {
                        console.log('=== INICIANDO COPIA DE UBICACIÓN ===');
                        console.log('Prefijo origen:', prefijoOrigen);
                        console.log('Prefijo destino:', prefijoDestino);
                        
                        const estadoEl = getElement(`id${prefijoOrigen === '' ? 'Estado' : 'Estado-padre'}`);
                        const municipioEl = getElement(`id${prefijoOrigen === '' ? 'Municipio' : 'Municipio-padre'}`);
                        const parroquiaEl = getElement(
                            `id${prefijoOrigen === '' ? 'parroquia' : 'parroquia-padre'}`);

                        console.log('Elementos encontrados:', {
                            estado: estadoEl ? 'Sí' : 'No',
                            municipio: municipioEl ? 'Sí' : 'No', 
                            parroquia: parroquiaEl ? 'Sí' : 'No'
                        });

                        if (estadoEl && estadoEl.value) {
                            const estado = estadoEl.value;
                            console.log('Copiando estado:', estado);
                            
                            // Para el estado, NO disparar evento change para evitar recargas
                            const estadoSelect = getElement('idEstado-representante');
                            if (estadoSelect) {
                                // Verificar si el elemento ya tiene el valor para evitar recargas innecesarias
                                if (estadoSelect.value === estado.toString()) {
                                    console.log(`=== SETVALUE: El valor ${estado} ya está establecido en idEstado-representante, omitiendo`);
                                    return; // Salir completamente de la función callback
                                }
                                
                                console.log(`=== SETVALUE: Destruyendo selectpicker para idEstado-representante`);
                                // Desactivar temporalmente el selectpicker para evitar errores
                                $(estadoSelect).selectpicker('destroy');
                                
                                // Establecer el valor directamente en el select
                                estadoSelect.value = estado || '';
                                console.log(`=== SETVALUE: Valor establecido directamente en idEstado-representante:`, estado);
                                
                                // Reactivar el selectpicker después de un pequeño retraso
                                setTimeout(() => {
                                    try {
                                        $(estadoSelect).selectpicker({
                                            liveSearch: true,
                                            size: 8,
                                            noneResultsText: 'No hay resultados para {0}',
                                            title: 'Seleccione una opción',
                                            showIcon: true,
                                            width: 'auto'
                                        });
                                        $(estadoSelect).selectpicker('val', estado);
                                        $(estadoSelect).selectpicker('refresh');
                                        console.log(`=== SETVALUE: Selectpicker reactivado para idEstado-representante con valor:`, estado);
                                    } catch (e) {
                                        console.log(`=== SETVALUE: No se pudo reactivar selectpicker para idEstado-representante, manteniendo valor directo`);
                                    }
                                }, 300);
                            }

                            // Cargar municipios usando la función unificada
                            if (estado) {
                                console.log('Cargando municipios para el representante...');
                                cargarSelectAnidado('municipio', estado, 'idMunicipio-representante', 'idparroquia-representante');

                                // Esperar a que carguen los municipios antes de copiar el valor
                                setTimeout(() => {
                                    if (municipioEl && municipioEl.value) {
                                        const municipio = municipioEl.value;
                                        console.log('Copiando municipio después de cargar:', municipio);
                                        
                                        // Verificar que el municipio exista en las opciones antes de establecerlo
                                        const municipioSelect = document.getElementById('idMunicipio-representante');
                                        if (municipioSelect) {
                                            console.log('Select de municipio encontrado, opciones disponibles:', municipioSelect.options.length);
                                            
                                            // Esperar más tiempo para que el selectpicker termine de reactivarse
                                            setTimeout(() => {
                                                const opciones = municipioSelect.querySelectorAll('option');
                                                console.log('Total de opciones encontradas:', opciones.length);
                                                const municipioExiste = Array.from(opciones).some(opt => opt.value === municipio);
                                                console.log('¿Municipio existe?', municipioExiste, 'Valor buscado:', municipio);
                                                
                                                if (municipioExiste) {
                                                    console.log('Estableciendo municipio en representante...');
                                                    setValue('idMunicipio-representante', municipio);
                                                    console.log('Municipio establecido correctamente:', municipio);
                                                    
                                                    if (municipio) {
                                                        console.log('Cargando localidades para el representante...');
                                                        cargarSelectAnidado('localidad', municipio, 'idparroquia-representante');

                                                        // Esperar a que carguen las localidades
                                                        setTimeout(() => {
                                                            if (parroquiaEl && parroquiaEl.value) {
                                                                console.log('Copiando parroquia:', parroquiaEl.value);
                                                                setValue('idparroquia-representante',
                                                                    parroquiaEl.value);
                                                                console.log('Ubicación copiada completamente');
                                                            } else {
                                                                console.log('No se encontró parroquia o no tiene valor');
                                                            }
                                                        }, 500);
                                                    }
                                                } else {
                                                    console.log('El municipio no existe en las opciones:', municipio);
                                                    console.log('Opciones disponibles:', Array.from(opciones).map(opt => ({value: opt.value, text: opt.text})));
                                                }
                                            }, 600); // Aumentar tiempo de espera para selectpicker
                                        } else {
                                            console.log('No se encontró el select de municipio del representante');
                                        }
                                    } else {
                                        console.log('No se encontró municipio o no tiene valor');
                                    }
                                }, 1200); // Aumentar tiempo de espera principal
                            }
                        } else {
                            console.log('No se encontró elemento de estado o no tiene valor');
                        }
                    } catch (error) {
                        console.error('Error al copiar ubicación:', error);
                    } finally {
                        // Resetear la bandera después de completar
                        setTimeout(() => {
                            window.copiandoUbicacion = false;
                        }, 2000);
                    }
                };

                // Función para copiar teléfono y prefijo
                const copiarTelefonoYPrefijo = (prefijoOrigen) => {
                    try {
                        const telefonoEl = getElement(`${prefijoOrigen}telefono`);
                        const prefijoEl = getElement(`${prefijoOrigen}prefijo`);

                        if (telefonoEl && telefonoEl.value) setValue('telefono-representante', telefonoEl
                            .value);
                        if (prefijoEl && prefijoEl.value) setValue('prefijo-representante', prefijoEl.value);
                    } catch (error) {
                        console.error('Error al copiar teléfono y prefijo:', error);
                    }
                };

                // Función para copiar lugar de nacimiento
                const copiarLugarNacimiento = (prefijoOrigen) => {
                    try {
                        const lugarNacimientoEl = getElement(`lugar-nacimiento${prefijoOrigen}`);
                        if (lugarNacimientoEl && lugarNacimientoEl.value) {
                            setValue('lugar-nacimiento-representante', lugarNacimientoEl.value);
                        }
                    } catch (error) {
                        console.error('Error al copiar lugar de nacimiento:', error);
                    }
                };

                // Función para copiar ocupación
                const copiarOcupacion = (prefijoOrigen) => {
                    try {
                        const ocupacionEl = getElement(`ocupacion-${prefijoOrigen}`);
                        const ocupacionRepresentanteEl = getElement('ocupacion-representante');

                        if (ocupacionEl && ocupacionEl.value && ocupacionRepresentanteEl) {
                            ocupacionRepresentanteEl.value = ocupacionEl.value;
                        }
                    } catch (error) {
                        console.error('Error al copiar ocupación:', error);
                    }
                };

                // Función para copiar convivencia
                const copiarConvivencia = (prefijoOrigen) => {
                    try {
                        // Construir el nombre del campo correctamente
                        const nombreCampo = prefijoOrigen ? `convive${prefijoOrigen}` : 'convive';
                        const convive = document.querySelector(`input[name="${nombreCampo}"]:checked`);
                        const conviveSiRepresentante = document.querySelector(
                            'input[name="convive-representante"][value="si"]');
                        const conviveNoRepresentante = document.querySelector(
                            'input[name="convive-representante"][value="no"]');

                        console.log('=== DEPURACIÓN COPIAR CONVIVENCIA ===');
                        console.log('Prefijo origen:', prefijoOrigen);
                        console.log('Nombre campo:', nombreCampo);
                        console.log('Elemento convivencia encontrado:', convive);
                        console.log('Valor de convivencia:', convive ? convive.value : 'null');
                        console.log('Radio Sí representante:', conviveSiRepresentante);
                        console.log('Radio No representante:', conviveNoRepresentante);

                        if (convive && conviveSiRepresentante && conviveNoRepresentante) {
                            if (convive.value === 'si') {
                                conviveSiRepresentante.checked = true;
                                console.log('Marcando Sí en representante');
                            } else if (convive.value === 'no') {
                                conviveNoRepresentante.checked = true;
                                console.log('Marcando No en representante');
                            }
                        } else {
                            console.log('No se encontraron todos los elementos necesarios');
                        }
                    } catch (error) {
                        console.error('Error al copiar convivencia:', error);
                    }
                };

                // Función para copiar datos personales
                const copiarDatosPersonales = (prefijoOrigen, esMadre = true) => {
                    try {
                        // Mapeo de campos para padre/madre
                        const campos = [{
                                origen: 'primer-nombre',
                                destino: 'primer-nombre-representante'
                            },
                            {
                                origen: 'segundo-nombre',
                                destino: 'segundo-nombre-representante'
                            },
                            {
                                origen: 'tercer-nombre',
                                destino: 'tercer-nombre-representante'
                            },
                            {
                                origen: 'primer-apellido',
                                destino: 'primer-apellido-representante'
                            },
                            {
                                origen: 'segundo-apellido',
                                destino: 'segundo-apellido-representante'
                            }
                        ];

                        // Copiar cada campo si existe
                        campos.forEach(campo => {
                            const valor = getElement(`${campo.origen}${prefijoOrigen}`)?.value;
                            if (valor !== undefined) {
                                setValue(campo.destino, valor);
                            }
                        });

                        // Copiar fecha de nacimiento (manejar tanto con prefijo como sin él)
                        const fechaNacimiento = getElement(`fechaNacimiento${prefijoOrigen}`)?.value ||
                            getElement('fecha-nacimiento' + (esMadre ? '' : '-padre'))?.value ||
                            getElement('fecha-nacimiento' + prefijoOrigen.toLowerCase())?.value;

                        if (fechaNacimiento) {
                            setValue('fecha-nacimiento-representante', fechaNacimiento);
                        }

                        // Copiar teléfono y prefijo
                        const telefono = getElement(`telefono${prefijoOrigen}`)?.value;
                        const prefijo = getElement(`prefijo${prefijoOrigen}`)?.value;
                        if (telefono) setValue('telefono-representante', telefono);
                        if (prefijo) setValue('prefijo-representante', prefijo);

                        // Copiar género (usando el campo correcto para padre)
                        const generoId = esMadre ?
                            getElement('sexo')?.value :
                            getElement('sexo-padre')?.value;

                        if (generoId) {
                            setValue('sexo-representante', generoId);
                            // Disparar evento change para actualizar selects dependientes si es necesario
                            const selectGenero = getElement('sexo-representante');
                            if (selectGenero) {
                                selectGenero.dispatchEvent(new Event('change'));
                            }
                        }

                        // Copiar ubicación
                        const prefijoUbicacion = esMadre ? '-madre' : '-padre';
                        const estado = getElement(`idEstado${prefijoUbicacion}`)?.value;
                        const municipio = getElement(`idMunicipio${prefijoUbicacion}`)?.value;
                        const parroquia = getElement(`idparroquia${prefijoUbicacion}`)?.value;
                        const lugarNacimiento = getElement(`lugar-nacimiento${prefijoUbicacion}`)?.value;

                        // Establecer lugar de nacimiento
                        if (lugarNacimiento) {
                            setValue('lugar-nacimiento-representante', lugarNacimiento);
                        }

                        // Establecer ubicación (estado, municipio, parroquia)
                        if (estado) {
                            // Usar setTimeout para asegurar que los selects se hayan cargado
                            setTimeout(() => {
                                setSelectValue('idEstado-representante', estado, () => {
                                    if (municipio) {
                                        setTimeout(() => {
                                            setSelectValue('idMunicipio-representante',
                                                municipio, () => {
                                                    if (parroquia) {
                                                        setTimeout(() => {
                                                            setSelectValue(
                                                                'idparroquia-representante',
                                                                parroquia
                                                            );
                                                        }, 500);
                                                    }
                                                });
                                        }, 500);
                                    }
                                });
                            }, 100);
                        }

                        return true;
                    } catch (error) {
                        console.error('Error al copiar datos personales:', error);
                        return false;
                    }
                };

                // MADRE
                try {
                    const numero_documentoMadre = getElement('numero_documento');
                    if (numero_documentoMadre?.value === numero_documento) {
                        // Copiar datos personales
                        copiarDatosPersonales('', true);

                        // Copiar datos adicionales
                        copiarLugarNacimiento('');
                        copiarTelefonoYPrefijo('');
                        copiarUbicacion('', '');
                        copiarOcupacion('madre');
                        // copiarConvivencia(''); // ELIMINADO - Usando sistema ultra optimizado

                        return true;
                    }
                } catch (error) {
                    console.error('Error al copiar datos de la madre:', error);
                }

                // PADRE
                try {
                    const numero_documentoPadre = getElement('numero_documento-padre');
                    if (numero_documentoPadre?.value === numero_documento) {
                        // Copiar datos personales
                        copiarDatosPersonales('-padre', false);

                        // Copiar datos adicionales
                        copiarLugarNacimiento('-padre');
                        copiarTelefonoYPrefijo('padre-');
                        copiarUbicacion('padre-', 'padre-');
                        copiarOcupacion('padre');
                        // copiarConvivencia('-padre'); // ELIMINADO - Usando sistema ultra optimizado

                        return true;
                    }
                } catch (error) {
                    console.error('Error al copiar datos del padre:', error);
                }

                // Si no coincide con ninguno, mostrar mensaje
                try {
                    const seccionRep = getElement('datos-representante');
                    if (seccionRep) {
                        // Remover mensaje anterior si existe
                        const mensajeAnterior = getElement('error-copia-datos');
                        if (mensajeAnterior) mensajeAnterior.remove();

                        // Crear y mostrar nuevo mensaje
                        const errorDiv = document.createElement('div');
                        errorDiv.id = 'error-copia-datos';
                        errorDiv.className = 'alert alert-warning mt-3';
                        errorDiv.textContent =
                            'No se encontró un progenitor con esta cédula. Complete los datos manualmente.';

                        // Insertar después del campo de cédula
                        const numero_documentoField = getElement('numero_documento-representante');
                        if (numero_documentoField?.parentNode) {
                            numero_documentoField.parentNode.insertBefore(errorDiv, numero_documentoField
                                .nextSibling);
                        } else {
                            seccionRep.insertBefore(errorDiv, seccionRep.firstChild);
                        }
                    }

                    // Limpiar campos de representante excepto la cédula
                    const campos = document.querySelectorAll(
                        '#datos-representante input[type="text"], #datos-representante input[type="email"], #datos-representante input[type="tel"], #datos-representante select'
                    );
                    campos.forEach(campo => {
                        if (campo.id !== 'numero_documento-representante') {
                            campo.value = '';
                        }
                    });
                } catch (error) {
                    console.error('Error al mostrar mensaje de error:', error);
                }

                return false;
            }

            // Función para mostrar/ocultar el campo de organización
            function toggleOrganizacion() {
                const mostrar = document.querySelector('input[name="organizacion-representante"]:checked')
                    ?.value ===
                    'si';
                const campoOrganizacion = document.getElementById('especifique-organizacion-container');
                const inputOrganizacion = document.getElementById('especifique-organizacion');

                if (campoOrganizacion) {
                    campoOrganizacion.style.display = mostrar ? 'block' : 'none';
                }
                if (inputOrganizacion) {
                    inputOrganizacion.required = mostrar;
                    if (!mostrar) {
                        inputOrganizacion.value = '';
                    }
                }
            }

            // Manejar cambios en la opción de organización
            document.querySelectorAll('input[name="organizacion-representante"]').forEach(radio => {
                radio.addEventListener('change', toggleOrganizacion);
            });
            toggleOrganizacion(); // Estado inicial

            // Función para validar Carnet Patria Afiliado basado en estado de madre/padre
            function validarCarnetPatriaAfiliado() {
                const selectCarnet = document.getElementById('carnet-patria-afiliado');
                const estadoMadre = document.querySelector('input[name="estado_madre"]:checked')?.value;
                const estadoPadre = document.querySelector('input[name="estado_padre"]:checked')?.value;

                if (!selectCarnet) return;

                // Guardar selección actual
                const seleccionActual = selectCarnet.value;

                // Limpiar opciones
                selectCarnet.innerHTML = '<option value="">Seleccione...</option>';

                // Agregar opciones según disponibilidad y estado
                if (!estadoMadre) {
                    // Madre no seleccionada - opción bloqueada
                    selectCarnet.innerHTML += '<option value="madre" disabled>Madre (seleccione estado)</option>';
                } else if (estadoMadre !== 'Ausente') {
                    // Madre presente
                    selectCarnet.innerHTML += '<option value="madre">Madre</option>';
                }

                if (!estadoPadre) {
                    // Padre no seleccionado - opción bloqueada
                    selectCarnet.innerHTML += '<option value="padre" disabled>Padre (seleccione estado)</option>';
                } else if (estadoPadre !== 'Ausente') {
                    // Padre presente
                    selectCarnet.innerHTML += '<option value="padre">Padre</option>';
                }

                // "Otro" siempre está disponible
                selectCarnet.innerHTML += '<option value="otro">Otro</option>';

                // Restaurar selección si aún es válida
                if (seleccionActual && (seleccionActual === 'otro' ||
                        (seleccionActual === 'madre' && estadoMadre && estadoMadre !== 'Ausente') ||
                        (seleccionActual === 'padre' && estadoPadre && estadoPadre !== 'Ausente'))) {
                    selectCarnet.value = seleccionActual;
                } else if (seleccionActual && (seleccionActual === 'madre' || seleccionActual === 'padre')) {
                    // Si la selección actual ya no es válida, limpiarla
                    selectCarnet.value = '';
                }
            }

            // Manejar cambios en estado de madre/padre
            document.querySelectorAll('input[name="estado_madre"], input[name="estado_padre"]').forEach(radio => {
                radio.addEventListener('change', validarCarnetPatriaAfiliado);
            });
            validarCarnetPatriaAfiliado(); // Estado inicial

            // Variable global para la sección de representante
            let seccionRep = null;

            // Función para habilitar/deshabilitar la sección de representante legal
            function toggleSeccionRepresentante(habilitar) {
                // Inicializar seccionRep si aún no está definida
                if (!seccionRep) {
                    seccionRep = document.getElementById('datos-representante');
                    if (!seccionRep) return;
                }

                // Mostrar/ocultar la sección con colapso suave
                if (typeof $ !== 'undefined') {
                    if (habilitar) {
                        seccionRep.classList.remove('d-none');
                        $(seccionRep).stop(true, true).slideDown(350);
                    } else {
                        $(seccionRep).stop(true, true).slideUp(350, function() {
                            seccionRep.classList.add('d-none');
                        });
                    }
                } else {
                    if (habilitar) {
                        seccionRep.classList.remove('d-none');
                        seccionRep.classList.add('d-block');
                    } else {
                        seccionRep.classList.add('d-none');
                        seccionRep.classList.remove('d-block');
                    }
                }

                // Obtener todos los campos del formulario dentro de la sección
                const formControls = seccionRep.querySelectorAll('input, select, textarea, button');

                // Habilitar/deshabilitar todos los campos
                formControls.forEach(control => {
                    if (control.type === 'button' || control.type === 'submit') {
                        // Para botones, solo deshabilitar si es necesario
                        control.disabled = !habilitar;
                    } else if (control.type !== 'hidden') {
                        // Para otros controles de formulario
                        control.disabled = !habilitar;
                        control.classList.toggle('bg-light', !habilitar);
                        control.readOnly = !habilitar;

                        // Si es un select, forzar la actualización del estilo
                        if (control.tagName === 'SELECT' && typeof $.fn.select2 === 'function') {
                            $(control).select2({
                                disabled: !habilitar
                            });
                        }
                    }
                });

                // Si se deshabilita, limpiar validaciones
                if (!habilitar) {
                    const errores = seccionRep.querySelectorAll('.is-invalid, .invalid-feedback');
                    errores.forEach(el => el.remove());
                }

                // Forzar actualización de Select2
                if (typeof $.fn.select2 === 'function') {
                    $('select').select2();
                }
            }

            // Inicialización cuando el DOM esté listo
            document.addEventListener('DOMContentLoaded', function() {
                // Obtener referencia a la sección
                seccionRep = document.getElementById('datos-representante');

                // Ocultar la sección por defecto
                if (seccionRep) {
                    seccionRep.classList.add('d-none');
                    seccionRep.classList.remove('d-block');
                }

                // Verificar si hay un radio button seleccionado por defecto
                const radioSeleccionado = document.querySelector(
                    'input[name="tipo_representante"]:checked');
                if (radioSeleccionado) {
                    toggleSeccionRepresentante(true);
                }
            });

            // Función para habilitar/deshabilitar campos del representante
            function toggleCamposRepresentante(deshabilitar = true) {
                const campos = [
                    'prefijo-representante',
                    'tipo-ci-representante',
                    'numero_documento-representante',
                    'primer-nombre-representante',
                    'segundo-nombre-representante',
                    'tercer-nombre-representante',
                    'primer-apellido-representante',
                    'segundo-apellido-representante',
                    'sexo-representante',
                    'fecha-nacimiento-representante',
                    'lugar-nacimiento-representante',
                    'idPais-representante',
                    'idEstado-representante',
                    'idMunicipio-representante',
                    'idparroquia-representante',
                    'direccion-representante',
                    'telefono-representante',
                    'prefijo_dos-representante',
                    'telefono_dos-representante',
                    'ocupacion-representante',
                    'otra-ocupacion-representante',
                    'parentesco'
                ];

                // Campos que nunca deben ser deshabilitados
                const camposExcluidos = ['correo-representante'];

                // NOTA: Los radio buttons de convivencia fueron removidos completamente del toggle
                // para que no sean procesados por esta función

                // Aplicar a todos los campos excepto los excluidos
                campos.forEach(id => {
                    if (camposExcluidos.includes(id)) {
                        console.log(`[TOGGLE] Campo ${id} excluido de deshabilitación`);
                        return; // Saltar este campo
                    }

                    const campo = document.getElementById(id);
                    if (campo) {
                        campo.disabled = deshabilitar;
                        campo.readOnly = deshabilitar;

                        // Manejar estilos para campos deshabilitados
                        if (deshabilitar) {
                            campo.classList.add('bg-light');
                            campo.classList.add('text-muted');
                        } else {
                            campo.classList.remove('bg-light');
                            campo.classList.remove('text-muted');
                        }

                        // Manejar select2 si está presente
                        if (typeof $.fn.select2 === 'function' && $(campo).hasClass(
                                'select2-hidden-accessible')) {
                            try {
                                if (deshabilitar) {
                                    $(campo).select2('disable');
                                } else {
                                    $(campo).select2('enable');
                                }
                            } catch (error) {
                                console.warn('Error al manejar select2:', error);
                            }
                        }

                        // Manejar selectpicker si está presente
                        if (typeof $ !== 'undefined' && $.fn.selectpicker && $(campo).hasClass('selectpicker')) {
                            try {
                                // NUEVO ENFOQUE: Serializar reconstrucciones para evitar conflictos
                                if (id.includes('ocupacion') || id.includes('prefijo') || 
                                    id.includes('Pais') || id.includes('Estado') || 
                                    id.includes('Municipio') || id.includes('parroquia')) {
                                    
                                    console.log(`[TOGGLE] Agregando ${id} a la cola de reconstrucción`);
                                    
                                    // Agregar a la cola global de reconstrucciones
                                    if (!window.reconstruccionCola) {
                                        window.reconstruccionCola = [];
                                        window.reconstruyendo = false;
                                    }
                                    
                                    window.reconstruccionCola.push({
                                        campo: campo,
                                        id: id,
                                        deshabilitar: deshabilitar
                                    });
                                    
                                    // Iniciar procesamiento de la cola si no está activo
                                    if (!window.reconstruyendoSelectpickers) {
                                        procesarColaReconstruccion();
                                    }
                                } else {
                                    // Comportamiento normal para otros selects
                                    if (deshabilitar) {
                                        $(campo).prop('disabled', true);
                                        $(campo).selectpicker('refresh');
                                    } else {
                                        $(campo).prop('disabled', false);
                                        $(campo).selectpicker('refresh');
                                    }
                                }
                            } catch (error) {
                                console.warn('Error al manejar selectpicker:', error);
                            }
                        }

                        // Función para procesar la cola de reconstrucciones con BLOQUEO GLOBAL
                        function procesarColaReconstruccion() {
                            // BLOQUEO DE COPIA: No procesar si estamos copiando datos
                            if (window.copiandoDatosProgenitor) {
                                console.log('[TOGGLE] ⚠️ Modo copia activo, omitiendo procesamiento de cola de reconstrucción');
                                return;
                            }
                            
                            // BLOQUEO DE SEGURIDAD: Esperar un tiempo después de copia reciente
                            if (window.ultimaCopiaDatos && Date.now() - window.ultimaCopiaDatos < 1000) {
                                console.log('[TOGGLE] ⚠️ Copia reciente detectada, esperando antes de procesar cola');
                                return;
                            }
                            
                            // Usar bloqueo global para evitar conflictos con otros procesos
                            if (window.reconstruyendoSelectpickers || window.reconstruccionCola.length === 0) {
                                return;
                            }
                            
                            window.reconstruyendoSelectpickers = true;
                            const item = window.reconstruccionCola.shift();
                            
                            console.log(`[TOGGLE] Reconstruyendo ${item.id} (cola: ${window.reconstruccionCola.length} restantes)`);
                            
                            try {
                                // PASO 1: Destruir completamente
                                $(item.campo).selectpicker('destroy');
                                
                                // PASO 2: Limpiar elementos residuales PERO MANTENER clase selectpicker
                                const bootstrapSelect = item.campo.nextElementSibling;
                                if (bootstrapSelect && bootstrapSelect.classList.contains('bootstrap-select')) {
                                    bootstrapSelect.remove();
                                }
                                
                                // ASEGURAR que la clase selectpicker esté presente
                                if (!item.campo.classList.contains('selectpicker')) {
                                    item.campo.classList.add('selectpicker');
                                }
                                
                                // PASO 3: Re-inicializar con delay mayor y forzamiento de DOM
                                setTimeout(() => {
                                    console.log(`[TOGGLE] ${item.id}: Iniciando inicialización forzada...`);
                                    
                                    // Asegurar que el elemento esté visible y no oculto
                                    item.campo.style.display = '';
                                    item.campo.style.visibility = 'visible';
                                    
                                    // FORZAR: Destruir completamente cualquier instancia previa
                                    try {
                                        $(item.campo).selectpicker('destroy');
                                    } catch (e) {
                                        // Ignorar si no existe
                                    }
                                    
                                    // FORZAR: Limpiar completamente el DOM alrededor
                                    const elementosResiduales = item.campo.parentNode.querySelectorAll('.bootstrap-select');
                                    elementosResiduales.forEach(el => el.remove());
                                    
                                    // FORZAR: Re-agregar clase si falta
                                    if (!item.campo.classList.contains('selectpicker')) {
                                        item.campo.classList.add('selectpicker');
                                    }
                                    
                                    // FORZAR: Inicialización con verificación
                                    try {
                                        $(item.campo).selectpicker({
                                            liveSearch: true,
                                            size: 8,
                                            noneResultsText: 'No hay resultados para {0}',
                                            selectOnTab: false,
                                            showSubtext: false,
                                            showIcon: true,
                                            width: 'auto'
                                        });
                                        
                                        console.log(`[TOGGLE] ${item.id}: selectpicker() ejecutado`);
                                        
                                        // FORZAR: Verificación inmediata de creación DOM
                                        setTimeout(() => {
                                            const bootstrapSelectElement = item.campo.nextElementSibling;
                                            
                                            if (!bootstrapSelectElement || !bootstrapSelectElement.classList.contains('bootstrap-select')) {
                                                console.error(`[TOGGLE] ${item.id}: ❌ DOM no creado, intentando método alternativo...`);
                                                
                                                // MÉTODO ALTERNATIVO: Forzar recreación completa
                                                try {
                                                    // Destruir y limpiar
                                                    $(item.campo).selectpicker('destroy');
                                                    
                                                    // Clonar el elemento para limpiar estado
                                                    const originalSelect = item.campo;
                                                    const clonedSelect = originalSelect.cloneNode(true);
                                                    
                                                    // Reemplazar en el DOM
                                                    originalSelect.parentNode.replaceChild(clonedSelect, originalSelect);
                                                    
                                                    // Actualizar referencia
                                                    item.campo = clonedSelect;
                                                    
                                                    // Re-inicializar con el clon
                                                    $(item.campo).selectpicker({
                                                        liveSearch: true,
                                                        size: 8,
                                                        noneResultsText: 'No hay resultados para {0}',
                                                        selectOnTab: false,
                                                        showSubtext: false,
                                                        showIcon: true,
                                                        width: 'auto'
                                                    });
                                                    
                                                    console.log(`[TOGGLE] ${item.id}: ⚠️ Método alternativo aplicado`);
                                                    
                                                } catch (altError) {
                                                    console.error(`[TOGGLE] ${item.id}: ❌ Método alternativo falló:`, altError);
                                                }
                                            }
                                            
                                            // Verificación final
                                            const finalBootstrapElement = item.campo.nextElementSibling;
                                            if (finalBootstrapElement && finalBootstrapElement.classList.contains('bootstrap-select')) {
                                                console.log(`[TOGGLE] Selectpicker ${item.id} ✅ DOM creado correctamente`);
                                            } else {
                                                console.error(`[TOGGLE] Selectpicker ${item.id} ❌ Error: DOM no creado después de todos los intentos`);
                                            }
                                            
                                            console.log(`[TOGGLE] Selectpicker ${item.id} reconstruido correctamente`);
                                            
                                        }, 100);
                                        
                                    } catch (initError) {
                                        console.error(`[TOGGLE] ${item.id}: Error en inicialización:`, initError);
                                    }
                                    
                                    // PASO 4: Aplicar estado
                                    if (item.deshabilitar) {
                                        $(item.campo).prop('disabled', true);
                                    } else {
                                        $(item.campo).prop('disabled', false);
                                    }
                                    
                                    $(item.campo).selectpicker('refresh');
                                    
                                    // Liberar bloqueo y continuar con el siguiente
                                    window.reconstruyendoSelectpickers = false;
                                    setTimeout(() => procesarColaReconstruccion(), 150);
                                    
                                }, 300); // Aumentado a 300ms
                            } catch (error) {
                                console.error(`[TOGGLE] Error reconstruyendo ${item.id}:`, error);
                                // Liberar bloqueo en caso de error
                                window.reconstruyendoSelectpickers = false;
                                setTimeout(() => procesarColaReconstruccion(), 150);
                            }
                        }

                        console.log(`[TOGGLE] Campo ${id}: ${deshabilitar ? 'deshabilitado' : 'habilitado'}`);
                    } else {
                        console.warn(`[TOGGLE] Campo ${id} no encontrado`);
                    }
                });

                // Asegurar que los campos excluidos siempre permanezcan habilitados
                camposExcluidos.forEach(id => {
                    const campo = document.getElementById(id);
                    if (campo) {
                        campo.disabled = false;
                        campo.readOnly = false;
                        campo.classList.remove('bg-light', 'text-muted');
                        
                        // Manejar selectpicker si está presente en campos excluidos
                        if (typeof $ !== 'undefined' && $.fn.selectpicker && $(campo).hasClass('selectpicker')) {
                            try {
                                $(campo).prop('disabled', false);
                                $(campo).selectpicker('refresh');
                            } catch (error) {
                                console.warn('Error al manejar selectpicker en campo excluido:', error);
                            }
                        }
                        
                        console.log(`[TOGGLE] Campo excluido ${id}: forzado a permanecer habilitado`);
                    }
                });
            }

            // Función para establecer el parentesco según el tipo de representante
            async function establecerParentesco(esMadre = false) {
                // Evitar ejecución durante reseteo para prevenir duplicaciones
                if (reseteandoRepresentante) {
                    console.log('[PARENTESCO] Omitiendo establecimiento durante reseteo para evitar duplicaciones');
                    return;
                }
                
                console.log(`[PARENTESCO] Estableciendo parentesco: ${esMadre ? 'Mamá' : 'Papá'}`);
                
                const parentescoSelect = document.getElementById('parentesco');
                const parentescoHidden = document.getElementById('parentesco_hidden');
                if (parentescoSelect) {
                    // Mostrar opciones disponibles para depuración
                    console.log('[PARENTESCO] Opciones disponibles:');
                    Array.from(parentescoSelect.options).forEach((opt, index) => {
                        console.log(`  ${index}: value="${opt.value}", text="${opt.text}", disabled=${opt.disabled}`);
                    });
                    
                    const valorParentesco = esMadre ? 'Mamá' : 'Papá';
                    console.log(`[PARENTESCO] Valor a establecer: "${valorParentesco}"`);

                    // Verificar que la opción exista
                    const opcion = Array.from(parentescoSelect.options).find(opt => opt.value === valorParentesco);
                    if (!opcion) {
                        console.error(`[PARENTESCO] ERROR: No se encontró la opción con valor "${valorParentesco}"`);
                        return;
                    }
                    
                    if (opcion.disabled) {
                        console.error(`[PARENTESCO] ERROR: La opción "${valorParentesco}" está deshabilitada`);
                        return;
                    }
                    
                    console.log(`[PARENTESCO] Opción encontrada y habilitada, estableciendo valor...`);

                    // MODO COPIA O PARENTESCO FIJO: Desactivar selectpicker y usar select nativo
                    if (window.copiandoDatosProgenitor || valorParentesco === 'Mamá' || valorParentesco === 'Papá') {
                        const esModoCopia = window.copiandoDatosProgenitor;
                        const esParentescoFijo = valorParentesco === 'Mamá' || valorParentesco === 'Papá';
                        
                        if (esModoCopia) {
                            console.log('[PARENTESCO] Modo copia: usando select nativo SIN reactivar selectpicker...');
                        } else if (esParentescoFijo) {
                            console.log(`[PARENTESCO] Parentesco fijo "${valorParentesco}": manteniendo select nativo sin selectpicker...`);
                        }
                        
                        // Desactivar selectpicker completamente
                        const $parentesco = $(parentescoSelect);
                        if ($parentesco.data('selectpicker')) {
                            $parentesco.selectpicker('destroy');
                            $parentesco.removeData('selectpicker');
                            parentescoSelect.classList.remove('selectpicker');
                            
                            // Eliminar elementos DOM residuales de bootstrap-select
                            const bootstrapSelect = parentescoSelect.nextElementSibling;
                            if (bootstrapSelect && bootstrapSelect.classList.contains('bootstrap-select')) {
                                bootstrapSelect.remove();
                            }
                            
                            console.log('[PARENTESCO] Selectpicker destruido completamente');
                        }
                        
                        // Establecer valor directamente en select nativo
                        parentescoSelect.value = valorParentesco;
                        console.log(`[PARENTESCO] Valor establecido en select nativo: "${valorParentesco}"`);
                        
                        // Actualizar campo oculto
                        if (parentescoHidden) {
                            parentescoHidden.value = valorParentesco;
                            console.log(`[PARENTESCO] Campo oculto actualizado: "${parentescoHidden.value}"`);
                        }
                        
                        // Deshabilitar el select nativo con estilo visual
                        parentescoSelect.disabled = true;
                        parentescoSelect.classList.add('bg-light', 'text-muted');
                        
                        if (esModoCopia) {
                            console.log('[PARENTESCO] Select nativo deshabilitado (permanente durante copia)');
                        } else if (esParentescoFijo) {
                            console.log(`[PARENTESCO] Select nativo deshabilitado (permanente para parentesco "${valorParentesco}")`);
                        }
                        
                        // Disparar evento change
                        const event = new Event('change');
                        parentescoSelect.dispatchEvent(event);
                        console.log('[PARENTESCO] Evento change disparado en select nativo');
                        
                    } else {
                        // MODO NORMAL: Usar selectpicker con fallback
                        try {
                            await establecerValorSelectUltraSimple(parentescoSelect, valorParentesco, 100);
                            console.log(`[PARENTESCO] Selectpicker actualizado correctamente`);
                        } catch (error) {
                            console.warn(`[PARENTESCO] Error en selectpicker, usando método nativo:`, error);
                            // Fallback: establecer valor directamente en el select nativo
                            parentescoSelect.value = valorParentesco;
                            console.log(`[PARENTESCO] Valor establecido con método nativo: "${valorParentesco}"`);
                        }
                        
                        // Actualizar campo oculto para que se envíe cuando el select está deshabilitado
                        if (parentescoHidden) {
                            parentescoHidden.value = valorParentesco;
                            console.log(`[PARENTESCO] Campo oculto actualizado: "${parentescoHidden.value}"`);
                        }

                        // Deshabilitar el select
                        parentescoSelect.disabled = true;
                        parentescoSelect.classList.add('bg-light', 'text-muted');
                        console.log('[PARENTESCO] Select deshabilitado');

                        // Disparar evento change para actualizar validaciones
                        try {
                            const event = new Event('change');
                            parentescoSelect.dispatchEvent(event);
                            console.log('[PARENTESCO] Evento change disparado');
                        } catch (error) {
                            console.warn('[PARENTESCO] Error al disparar evento change:', error);
                        }

                        // Actualizar Select2 si está en uso
                        try {
                            if (typeof $.fn.select2 === 'function' && $(parentescoSelect).hasClass(
                                    'select2-hidden-accessible')) {
                                $(parentescoSelect).val(valorParentesco).trigger('change');
                                console.log('[PARENTESCO] Select2 actualizado');
                            }
                        } catch (error) {
                            console.warn('[PARENTESCO] Error al actualizar Select2:', error);
                        }
                    }
                    
                    console.log(`[PARENTESCO] Parentesco establecido exitosamente: "${valorParentesco}"`);
                } else {
                    console.error('[PARENTESCO] ERROR: No se encontró el select de parentesco');
                }
            }

            // Función para restablecer el campo de parentesco
            function resetearParentesco() {
                const parentescoSelect = document.getElementById('parentesco');
                const parentescoHidden = document.getElementById('parentesco_hidden');
                if (parentescoSelect) {
                    parentescoSelect.disabled = false;
                    parentescoSelect.value = '';
                    parentescoSelect.classList.remove('bg-light', 'text-muted');
                    
                    // Limpiar campo oculto
                    if (parentescoHidden) {
                        parentescoHidden.value = '';
                    }

                    // Disparar evento change para actualizar validaciones
                    const event = new Event('change');
                    parentescoSelect.dispatchEvent(event);

                    // Actualizar Select2 si está en uso
                    if (typeof $.fn.select2 === 'function' && $(parentescoSelect).hasClass(
                            'select2-hidden-accessible')) {
                        $(parentescoSelect).val('').trigger('change');
                    }
                }
            }

            // Event listener para sincronizar campo oculto cuando el usuario cambia manualmente el parentesco
            document.addEventListener('DOMContentLoaded', function() {
                const parentescoSelect = document.getElementById('parentesco');
                const parentescoHidden = document.getElementById('parentesco_hidden');
                
                if (parentescoSelect && parentescoHidden) {
                    parentescoSelect.addEventListener('change', function() {
                        // Solo actualizar el campo oculto si el select no está deshabilitado
                        if (!this.disabled) {
                            parentescoHidden.value = this.value;
                        }
                    });
                }
            });

            // Función para copiar datos de un progenitor al representante (versión con watchdog y manejo de errores reforzado)
            async function copiarDatosProgenitorARepresentante(esMadre = false) {
                console.log('[COPIA WATCHDOG] Iniciando función. Estado actual de copiandoDatosProgenitor:', window.copiandoDatosProgenitor);
                
                // CORRECCIÓN: Resetear bandera múltiples veces al inicio para asegurar que esté limpia
                for (let i = 0; i < 5; i++) {
                    window.copiandoDatosProgenitor = false;
                    await new Promise(resolve => setTimeout(resolve, 10));
                }
                console.log('[COPIA WATCHDOG] Bandera reseteada múltiples veces al inicio');
                
                // Evitar múltiples ejecuciones simultáneas
                if (window.copiandoDatosProgenitor) {
                    console.log('[COPIA WATCHDOG] Ya se está copiando datos del progenitor, omitiendo...');
                    return Promise.resolve();
                }
                window.copiandoDatosProgenitor = true;
                
                // CORRECCIÓN: Timeout de seguridad (watchdog) para resetear la bandera si la función se atasca
                const watchdogTimeout = setTimeout(() => {
                    if (window.copiandoDatosProgenitor) {
                        console.error('[EMERGENCIA] Detectada bandera atascada, reseteando automáticamente...');
                        window.copiandoDatosProgenitor = false;
                    }
                }, 10000); // 10 segundos de timeout
                
                try {
                    const prefijo = esMadre ? '' : '-padre';
                    const mensajeExito = esMadre ? 'de la madre' : 'del padre';

                    console.log(`[COPIA WATCHDOG] Iniciando copia para ${esMadre ? 'madre' : 'padre'}`);
                    
                    // Limpieza SUAVE - Solo limpiar valores seleccionados sin afectar opciones
                    const selectsRepresentante = [
                        'prefijo-representante', 'prefijo_dos-representante', 'ocupacion-representante',
                        'idPais-representante', 'idEstado-representante', 'idMunicipio-representante', 'idparroquia-representante'
                    ];
                    for (const id of selectsRepresentante) {
                        const select = document.getElementById(id);
                        if (select && select.parentNode) { // VALIDACIÓN: Verificar que existe en el DOM
                            try {
                                const $select = $(select);
                                if ($select.length && $select[0] && $select.data('selectpicker')) {
                                    // SOLO limpiar selección, NO destruir ni refrescar agresivamente
                                    $select.selectpicker('val', '');
                                    // NO hacer refresh aquí para evitar perder opciones
                                    console.log(`[LIMPIEZA SUAVE] ${id} selección limpiada (opciones preservadas)`);
                                } else {
                                    select.value = '';
                                    select.selectedIndex = -1;
                                }
                            } catch (error) {
                                console.error(`[LIMPIEZA SUAVE] Error al limpiar ${id}:`, error);
                                // Intentar limpieza básica como fallback
                                try {
                                    select.value = '';
                                    select.selectedIndex = -1;
                                } catch (fallbackError) {
                                    console.error(`[LIMPIEZA SUAVE] Error en fallback para ${id}:`, fallbackError);
                                }
                            }
                        } else {
                            console.warn(`[LIMPIEZA SUAVE] Elemento ${id} no encontrado o no está en el DOM`);
                        }
                    }

                    console.log(`=== INICIANDO COPIA ULTRA OPTIMIZADA ${mensajeExito.toUpperCase()} ===`);

                    // Establecer el parentesco según corresponda
                    await establecerParentesco(esMadre);

                    // Habilitar temporalmente los campos para poder copiar los valores
                    toggleCamposRepresentante(false);

                    // Función para preparar todos los selects (limpieza suave)
                    const prepararSelectsSuave = () => {
                        console.log('PREPARACIÓN SUAVE: Limpiando solo selección de selects...');
                        const selectsPreparar = [
                            'prefijo-representante',
                            'prefijo_dos-representante',
                            'ocupacion-representante'
                            // NO incluir selects de ubicación para preservar sus opciones cargadas
                        ];

                        selectsPreparar.forEach(selectId => {
                            const select = document.getElementById(selectId);
                            if (select) {
                                const $select = $(select);
                                try {
                                    // Verificar si el selectpicker está inicializado antes de operar
                                    if ($select.data('selectpicker')) {
                                        // Solo limpiar selección, NO destruir ni deselectAll
                                        $select.selectpicker('val', '');
                                        console.log(`[PREP SUAVE] ${selectId}: Selección limpiada (opciones preservadas)`);
                                    } else {
                                        // Si no está inicializado, limpiar solo el select nativo
                                        select.value = '';
                                        select.selectedIndex = -1;
                                        console.log(`[PREP SUAVE] ${selectId}: Limpieza básica (selectpicker no inicializado)`);
                                    }
                                } catch (error) {
                                    console.error(`[PREP SUAVE] ${selectId}: Error durante limpieza:`, error);
                                    // Intentar limpieza básica como fallback
                                    try {
                                        select.value = '';
                                        select.selectedIndex = -1;
                                    } catch (fallbackError) {
                                        console.error(`[PREP SUAVE] ${selectId}: Error en fallback:`, fallbackError);
                                    }
                                }
                            } else {
                                console.warn(`[PREP SUAVE] ${selectId}: Elemento no encontrado`);
                            }
                        });
                    };

                    // Función auxiliar para copiar campos simples
                    const copiarCampoSimple = (origenId, destinoId) => {
                        const origen = document.getElementById(origenId);
                        const destino = document.getElementById(destinoId);
                        if (origen && destino) {
                            // Limpiar completamente antes de asignar para evitar concatenación
                            const valorOrigen = origen.value;
                            destino.value = '';
                            
                            // Asignar el nuevo valor
                            destino.value = valorOrigen;
                            
                            // Disparar eventos
                            destino.dispatchEvent(new Event('input'));
                            destino.dispatchEvent(new Event('change'));
                            console.log(`[SIMPLE] ${origenId} -> ${destinoId} = "${valorOrigen}"`);
                            return true;
                        }
                        console.warn(`[SIMPLE] No se pudo copiar: ${origenId} -> ${destinoId}`);
                        return false;
                    };

                    // PASO 0: Preparación suave (preservar opciones de ubicación)
                    prepararSelectsSuave();
                    await new Promise(resolve => setTimeout(resolve, 200)); // Reducir tiempo de espera

                    // 1. Copiar campos personales primero
                    console.log('PASO 1: Copiando campos personales...');
                    const camposPersonales = [
                        [`prefijo${prefijo}`, 'prefijo-representante'],
                        [`tipo-ci${prefijo}`, 'tipo-ci-representante'],
                        [`numero_documento${prefijo}`, 'numero_documento-representante'],
                        [`primer-nombre${prefijo}`, 'primer-nombre-representante'],
                        [`segundo-nombre${prefijo}`, 'segundo-nombre-representante'],
                        [`tercer-nombre${prefijo}`, 'tercer-nombre-representante'],
                        [`primer-apellido${prefijo}`, 'primer-apellido-representante'],
                        [`segundo-apellido${prefijo}`, 'segundo-apellido-representante'],
                        [`sexo${prefijo}`, 'sexo-representante'],
                        [esMadre ? 'fechaNacimiento' : 'fecha-nacimiento-padre', 'fecha-nacimiento-representante'],
                        [`lugar-nacimiento${prefijo}`, 'lugar-nacimiento-representante']
                    ];

                    for (const [origenId, destinoId] of camposPersonales) {
                        copiarCampoSimple(origenId, destinoId);
                        await new Promise(resolve => setTimeout(resolve, 100));
                    }

                    // 2. Copiar prefijos telefónicos con método NATIVO PURO (sin Bootstrap-Select)
                    console.log('PASO 2: Copiando prefijos telefónicos con método nativo puro...');
                    
                    const copiarSelectNativoPuro = (origenId, destinoId) => {
                        const origen = document.getElementById(origenId);
                        const destino = document.getElementById(destinoId);
                        
                        if (origen && destino && origen.value) {
                            console.log(`[NATIVO PURO] ${origenId} -> ${destinoId} = "${origen.value}"`);
                            
                            try {
                                // PASO 1: Desactivar COMPLETAMENTE Bootstrap-Select durante la copia
                                const $destino = $(destino);
                                const selectpickerData = $destino.data('selectpicker');
                                
                                if (selectpickerData) {
                                    // Destruir Bootstrap-Select completamente
                                    $destino.selectpicker('destroy');
                                    $destino.removeData('selectpicker');
                                    destino.classList.remove('selectpicker');
                                    
                                    // Eliminar elementos DOM residuales
                                    const bootstrapSelect = destino.nextElementSibling;
                                    if (bootstrapSelect && bootstrapSelect.classList.contains('bootstrap-select')) {
                                        bootstrapSelect.remove();
                                    }
                                    
                                    console.log(`[NATIVO PURO] Bootstrap-Select completamente desactivado para ${destinoId}`);
                                }
                                
                                // PASO 2: Asignación DIRECTA al select nativo (ahora es un select normal)
                                // Limpiar completamente antes de asignar para evitar concatenación
                                destino.value = '';
                                destino.selectedIndex = -1;
                                
                                // Asignar el nuevo valor
                                destino.value = origen.value;
                                destino.selectedIndex = Array.from(destino.options).findIndex(opt => opt.value === origen.value);
                                
                                console.log(`[NATIVO PURO] Valor asignado a select normal ${destinoId}: "${origen.value}"`);
                                return true;
                                
                            } catch (error) {
                                console.warn(`[NATIVO PURO] Error copiando ${origenId}:`, error);
                                return false;
                            }
                        }
                        return false;
                    };
                    
                    copiarSelectNativoPuro(`prefijo${prefijo}`, 'prefijo-representante');
                    copiarSelectNativoPuro(`prefijo_dos${esMadre ? '' : '_padre'}`, 'prefijo_dos-representante');

                    // 3. Copiar ubicación con método NATIVO PURO (sin Bootstrap-Select)
                    console.log('PASO 3: Copiando ubicación con método nativo puro...');
                    
                    // Activar bandera para evitar eventos change durante la copia
                    copiandoUbicacion = true;
                    
                    // Copiar país
                    if (copiarSelectNativoPuro(`idPais${prefijo}`, 'idPais-representante')) {
                        const paisOrigen = document.getElementById(`idPais${prefijo}`);
                        console.log(`[UBICACIÓN NATIVA] País copiado: ${paisOrigen.value}, cargando estados...`);
                        await new Promise(resolve => setTimeout(resolve, 300));
                        
                        // Cargar estados para el país copiado
                        await cargarSelectAnidadoSimple('estado', paisOrigen.value, 'idEstado-representante', 'idMunicipio-representante');
                        await new Promise(resolve => setTimeout(resolve, 300));
                        
                        // Copiar estado
                        if (copiarSelectNativoPuro(`idEstado${prefijo}`, 'idEstado-representante')) {
                            const estadoOrigen = document.getElementById(`idEstado${prefijo}`);
                            console.log(`[UBICACIÓN NATIVA] Estado copiado: ${estadoOrigen.value}, cargando municipios...`);
                            await new Promise(resolve => setTimeout(resolve, 300));
                            
                            // Cargar municipios para el estado copiado
                            await cargarSelectAnidadoSimple('municipio', estadoOrigen.value, 'idMunicipio-representante', 'idparroquia-representante');
                            await new Promise(resolve => setTimeout(resolve, 300));
                            
                            // Copiar municipio
                            if (copiarSelectNativoPuro(`idMunicipio${prefijo}`, 'idMunicipio-representante')) {
                                const municipioOrigen = document.getElementById(`idMunicipio${prefijo}`);
                                console.log(`[UBICACIÓN NATIVA] Municipio copiado: ${municipioOrigen.value}, cargando localidades...`);
                                await new Promise(resolve => setTimeout(resolve, 300));
                                
                                // Cargar localidades para el municipio copiado
                                await cargarSelectAnidadoSimple('localidad', municipioOrigen.value, 'idparroquia-representante');
                                await new Promise(resolve => setTimeout(resolve, 300));
                                
                                // Copiar parroquia
                                copiarSelectNativoPuro(`idparroquia${prefijo}`, 'idparroquia-representante');
                            }
                        }
                    }
                    
                    // Desactivar bandera para permitir eventos change normales
                    copiandoUbicacion = false;
                    console.log('[UBICACIÓN SIMPLE] Copia de ubicación completada, bandera desactivada');

                    // 4. Copiar campos de contacto
                    console.log('PASO 4: Copiando campos de contacto...');
                    const camposContacto = [
                        [`direccion${prefijo}`, 'direccion-representante'],
                        [`telefono${prefijo}`, 'telefono-representante'],
                        [`telefono_dos${esMadre ? '' : '_padre'}`, 'telefono_dos-representante'],
                        [`email${prefijo}`, 'correo-representante']
                    ];

                    for (const [origenId, destinoId] of camposContacto) {
                        copiarCampoSimple(origenId, destinoId);
                        await new Promise(resolve => setTimeout(resolve, 100));
                    }

                    // 5. Copiar ocupación con método NATIVO PURO (sin Bootstrap-Select)
                    console.log('PASO 5: Copiando ocupación con método nativo puro...');
                    
                    copiarSelectNativoPuro(esMadre ? 'ocupacion-madre' : 'ocupacion-padre', 'ocupacion-representante');

                    // 6. Manejar campo "otra ocupación"
                    console.log('PASO 6: Manejando campo otra ocupación...');
                    const ocupacionOrigen = document.getElementById(esMadre ? 'ocupacion-madre' : 'ocupacion-padre');
                    const otraOcupacionOrigen = document.getElementById(esMadre ? 'otra-ocupacion' : 'otra-ocupacion-padre');
                    const otraOcupacionDestino = document.getElementById('otra-ocupacion-representante');
                    const otraOcupacionContainer = document.getElementById('otra-ocupacion-container');
                    
                    if (ocupacionOrigen && ocupacionOrigen.value === 'otro' && otraOcupacionOrigen && otraOcupacionDestino) {
                        if (otraOcupacionContainer) {
                            otraOcupacionContainer.classList.remove('d-none');
                            otraOcupacionContainer.classList.add('d-block');
                        }
                        otraOcupacionDestino.value = otraOcupacionOrigen.value;
                        otraOcupacionDestino.dispatchEvent(new Event('input'));
                    } else if (otraOcupacionContainer) {
                        otraOcupacionContainer.classList.add('d-none');
                        otraOcupacionContainer.classList.remove('d-block');
                    }

                    // 6. Copiar convivencia de forma simple
                    const conviveNombreCampo = esMadre ? 'convive' : 'convive-padre';
                    const convive = document.querySelector(`input[name="${conviveNombreCampo}"]:checked`);
                    
                    if (convive) {
                        const valorConvivencia = convive.value;
                        const conviveRepresentante = document.querySelector(`input[name="convive-representante"][value="${valorConvivencia}"]`);
                        
                        if (conviveRepresentante) {
                            conviveRepresentante.checked = true;
                        }
                    }

                    // 7. Validación final ultra completa...
                    console.log('PASO 7: Validación final ultra completa...');
                    
                    // PASO FINAL: Validación simple y rápida
                    console.log('PASO FINAL: Validación final de duplicaciones...');
                    const selectsValidar = [
                        'prefijo-representante',
                        'prefijo_dos-representante',
                        'idPais-representante',
                        'idEstado-representante',
                        'idMunicipio-representante',
                        'idparroquia-representante'
                    ];

                    let duplicacionDetectada = false;
                    selectsValidar.forEach(selectId => {
                        const select = document.getElementById(selectId);
                        if (select) {
                            const texto = select.options[select.selectedIndex]?.text || '';
                            if (texto.includes('()') || /\d.*\d/.test(texto)) {
                                duplicacionDetectada = true;
                            }
                        }
                    });

                    if (duplicacionDetectada) {
                        console.warn('[VALIDACIÓN] ⚠️ Duplicación detectada');
                    } else {
                        console.log('[VALIDACIÓN] ✅ Sin duplicación');
                    }

                    // Aplicar estilos de solo lectura (excluyendo radios de convivencia)
                    document.querySelectorAll('[id$="-representante"]').forEach(elemento => {
                        // Excluir radios de convivencia para no afectarlos
                        if (elemento.id === 'convive-si-representante' || elemento.id === 'convive-no-representante') {
                            return; // Saltar radios de convivencia
                        }
                        
                        if (!elemento.disabled && elemento.tagName !== 'SELECT') {
                            elemento.classList.add('bg-light', 'text-muted');
                            elemento.readOnly = true;
                        }
                    });

                    // Deshabilitar los campos después de copiar los datos
                    toggleCamposRepresentante(true);
                    
                    // Registrar timestamp de la copia para bloqueo de seguridad
                    window.ultimaCopiaDatos = Date.now();
                    
                    console.log(`=== COPIA WATCHDOG ${mensajeExito.toUpperCase()} COMPLETADA ===`);
                        
                } catch (error) {
                    console.error('[COPIA WATCHDOG] Error durante la copia:', error);
                    // CORRECCIÓN: Forzar reset inmediato en caso de error
                    window.copiandoDatosProgenitor = false;
                    throw error; // Re-lanzar para que se maneje arriba si es necesario
                } finally {
                    // CORRECCIÓN: Esperar un momento antes de resetear la bandera para evitar conflictos con la cola de reconstrucción
                    clearTimeout(watchdogTimeout); // Limpiar el timeout para evitar resets innecesarios
                    
                    // Esperar a que termine cualquier procesamiento de la cola de reconstrucción
                    setTimeout(() => {
                        window.copiandoDatosProgenitor = false;
                        console.log('[COPIA WATCHDOG] ✅ Bandera reseteada después de esperar procesamiento de cola');
                        
                        // VERIFICACIÓN FINAL: Asegurar que los selects copiados estén correctamente inicializados
                        setTimeout(() => {
                            const selectsParaVerificar = [
                                'prefijo-representante',
                                'prefijo_dos-representante', 
                                'ocupacion-representante',
                                'idPais-representante',
                                'idEstado-representante',
                                'idMunicipio-representante',
                                'idparroquia-representante'
                            ];
                            
                            selectsParaVerificar.forEach(selectId => {
                                const select = document.getElementById(selectId);
                                if (select && select.value) {
                                    const $select = $(select);
                                    if ($select.hasClass('selectpicker') && !$select.data('selectpicker')) {
                                        console.log(`[VERIFICACIÓN FINAL] Re-inicializando ${selectId}...`);
                                        try {
                                            $select.selectpicker({
                                                liveSearch: true,
                                                size: 8,
                                                noneResultsText: 'No hay resultados para {0}',
                                                selectOnTab: false,
                                                showSubtext: false,
                                                showIcon: true,
                                                width: 'auto'
                                            });
                                            $select.selectpicker('refresh');
                                            console.log(`[VERIFICACIÓN FINAL] ✅ ${selectId} re-inicializado`);
                                        } catch (error) {
                                            console.error(`[VERIFICACIÓN FINAL] ❌ Error re-inicializando ${selectId}:`, error);
                                        }
                                    }
                                }
                            });
                        }, 200); // Esperar 200ms adicionales para verificación
                    }, 500); // Esperar 500ms para asegurar que la cola termine
                }
            }

            // Función para copiar datos del padre al representante
            async function copiarDatosPadreARepresentante() {
                console.log('[COPIA PADRE] Iniciando. Estado actual de copiandoDatosProgenitor:', window.copiandoDatosProgenitor);
                // NO resetear bandera aquí para mantener el modo copia activo y el parentesco establecido
                console.log('[COPIA PADRE] Manteniendo bandera copiandoDatosProgenitor para preservar parentesco');
                return await copiarDatosProgenitorARepresentante(false);
            }

            // Función para copiar datos de la madre al representante
            async function copiarDatosMadreARepresentante() {
                console.log('[COPIA MADRE] Iniciando. Estado actual de copiandoDatosProgenitor:', window.copiandoDatosProgenitor);
                // NO resetear bandera aquí para mantener el modo copia activo y el parentesco establecido
                console.log('[COPIA MADRE] Manteniendo bandera copiandoDatosProgenitor para preservar parentesco');
                return await copiarDatosProgenitorARepresentante(true);
            }

            // Función para restablecer los campos del representante con limpieza ultra
            function resetearCamposRepresentante() {
                console.log('[RESET ULTRA] INICIO - resetearCamposRepresentante() llamado');

                // Activar bandera para evitar eventos change durante reseteo
                reseteandoRepresentante = true;

                // Habilitar todos los campos primero
                toggleCamposRepresentante(false);

                // Restablecer el campo de parentesco
                resetearParentesco();

                // Lista de campos a limpiar (excluyendo radios de convivencia para no perder valores copiados)
                const campos = [
                    'prefijo-representante',
                    'tipo-ci-representante',
                    'numero_documento-representante',
                    'primer-nombre-representante',
                    'segundo-nombre-representante',
                    'tercer-nombre-representante',
                    'primer-apellido-representante',
                    'segundo-apellido-representante',
                    'sexo-representante',
                    'fecha-nacimiento-representante',
                    'lugar-nacimiento-representante',
                    'idPais-representante',
                    'idEstado-representante',
                    'idMunicipio-representante',
                    'idparroquia-representante',
                    'direccion-representante',
                    'telefono-representante',
                    'prefijo_dos-representante',
                    'telefono_dos-representante',
                    'correo-representante',
                    'ocupacion-representante',
                    'otra-ocupacion-representante',
                    'parentesco'
                ];

                // Limpiar campos con manejo especial para selectpicker
                campos.forEach(id => {
                    const campo = document.getElementById(id);
                    if (campo) {
                        // Limpiar clases y atributos
                        campo.classList.remove('bg-light', 'text-muted');
                        campo.readOnly = false;
                        campo.disabled = false;

                        // Manejo especial para selects con selectpicker
                        if ($(campo).hasClass('selectpicker')) {
                            // Usar limpieza NUCLEAR para todos los selectpickers
                            limpiarSelectPickerNuclear(campo, id);
                        } else {
                            // Para otros campos, limpiar normalmente
                            campo.value = '';
                        }

                        // Limpiar errores asociados
                        limpiarError(campo);
                    }
                });

                // Desactivar bandera para permitir eventos change normales
                reseteandoRepresentante = false;
                console.log('[RESET ULTRA] FIN - resetearCamposRepresentante() completado, bandera desactivada');
            }

            // NOTA: Event listener duplicado eliminado - ahora toda la lógica está consolidada en el listener principal (línea 3778)

            // NOTA: No se necesita la verificación final redundante
            // ya que cada caso maneja sus campos correctamente
            // El evento blur fue eliminado para evitar múltiples llamadas
            // document.getElementById('numero_documento-representante')?.addEventListener('blur', function() {
            //     const tipo = document.querySelector('input[name="tipo_representante"]:checked')?.value;
            //     const numero_documento = this.value;

            //     if (tipo !== 'progenitor_representante' || !numero_documento) {
            //         return;
            //     }

            //     // 1) Intentar copiar desde los datos ya cargados en este formulario (madre/padre)
            //     const copiadoLocal = copiarDesdeMadreOPadreSiCoincide(numero_documento);
            //     if (copiadoLocal) {
            //         return; // no hace falta ir a BD
            //     }

            //     // 2) Si no coincide con madre/padre, buscar en BD
            //     fetch(`${buscarnumero_documentoUrl}?numero_documento=${numero_documento}`)
            //         .then(response => response.json())
            //         .then(resp => {
            //             if (resp && resp.status === 'success') {
            //                 rellenarRepresentanteDesdeRespuesta(resp);
            //             }
            //         })
            //         .catch(error => {
            //             console.error('Error al buscar cédula para representante legal como progenitor',
            //                 error);
            //         });
            // });// });

            // Otras funciones
            document.querySelectorAll('input[name*="telefono"]').forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });

            document.querySelectorAll('input[name*="numero_documento"]').forEach(input => {
                input.addEventListener('input', function() {
                    // Limpiar caracteres no numéricos y limitar longitud
                    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 8);
                    
                    // Validar en tiempo real la longitud mínima
                    const valor = this.value.trim();
                    if (valor.length > 0 && valor.length < 7) {
                        mostrarError(this, 'La cédula debe tener al menos 7 dígitos');
                    } else if (valor.length >= 7) {
                        limpiarError(this);
                    }
                });
                
                // Validar al salir del campo (blur)
                input.addEventListener('blur', function() {
                    validarnumero_documento(this);
                });
            });
        });

        // Función para mostrar errores
        function mostrarError(elemento, mensaje) {
            if (!elemento) return;

            const grupo = elemento.closest('.form-group') || elemento.closest('.input-group') || elemento.closest('.mb-3') || elemento.closest('[class*="col-"]') || elemento.parentElement;
            if (!grupo) return;

            let errorElement = grupo.querySelector('.invalid-feedback');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'invalid-feedback d-block';
                grupo.appendChild(errorElement);
            }
            errorElement.textContent = mensaje;

            // Añadir clases de error
            elemento.classList.add('is-invalid');
            // if (elemento.tagName === 'SELECT') {
            //     elemento.classList.add('border-danger'); // ELIMINADO - No aplicar borde rojo a selects
            // }
        }

        // Función para limpiar solo el valor de un select sin destruir sus opciones
        function limpiarSelectValor(selectElement) {
            if (!selectElement) return;
            
            try {
                const $select = $(selectElement);
                if ($select.hasClass('selectpicker') && $select.data('selectpicker')) {
                    // Solo limpiar selección, preservar opciones
                    $select.selectpicker('val', '');
                    console.log(`[LIMPIAR VALOR] ${selectElement.id}: Valor limpiado, opciones preservadas`);
                } else {
                    // Para selects normales
                    selectElement.value = '';
                    selectElement.selectedIndex = -1;
                    console.log(`[LIMPIAR VALOR] ${selectElement.id}: Valor limpiado (select normal)`);
                }
            } catch (error) {
                console.error(`[LIMPIAR VALOR] Error limpiando ${selectElement?.id}:`, error);
                // Fallback básico
                if (selectElement) {
                    selectElement.value = '';
                    selectElement.selectedIndex = -1;
                }
            }
        }

            // Función para limpiar errores
        function limpiarError(elemento) {
            if (!elemento) return;

            const grupo = elemento.closest('.form-group') || elemento.closest('.input-group') || elemento.closest('.mb-3') || elemento.closest('[class*="col-"]') || elemento.parentElement;
            if (!grupo) return;

            const errorElement = grupo.querySelector('.invalid-feedback');
            if (errorElement) {
                errorElement.remove();
            }

            elemento.classList.remove('is-invalid');
            // if (elemento.tagName === 'SELECT') {
            //     elemento.classList.remove('border-danger'); // ELIMINADO - No aplicar borde rojo a selects
            // }
        }

        // Validar campo requerido
        function validarRequerido(input) {
            if (!input) return false;
            if (!input.value.trim()) {
                mostrarError(input, 'Este campo es obligatorio');
                return false;
            }
            limpiarError(input);
            return true;
        }

        // Validar selección de opción
        function validarSeleccion(select) {
            if (!select) return false;
            if (!select.value) {
                mostrarError(select, 'Debe seleccionar una opción');
                return false;
            }
            limpiarError(select);
            return true;
        }

        // Función para verificar si se ha seleccionado "Progenitor como Representante"
        function esProgenitorRepresentante() {
            return document.querySelector('input[name="tipo_representante"][value="progenitor_representante"]:checked') !==
                null;
        }

        // Validar cédula (verifica que no esté vacío y tenga al menos 7 dígitos)
        function validarnumero_documento(input) {
            if (!input) return true; // No hay input, no validar

            const numero_documento = input.value.trim();
            if (input.required && !numero_documento) {
                mostrarError(input, 'La cédula es obligatoria');
                return false;
            }

            // Validar que tenga al menos 7 dígitos
            if (numero_documento && numero_documento.length < 7) {
                mostrarError(input, 'La cédula debe tener al menos 7 dígitos');
                return false;
            }

            // Validar que solo contenga números (opcional, pero recomendado)
            if (numero_documento && !/^\d+$/.test(numero_documento)) {
                mostrarError(input, 'La cédula solo debe contener números');
                return false;
            }

            limpiarError(input);
            return true;
        }

        // Validar teléfono
        function validarTelefono(input) {
            if (!input) return false;
            const telefono = input.value.trim();
            if (!telefono) {
                mostrarError(input, 'El teléfono es obligatorio');
                return false;
            }
            if (!/^\d{7,11}$/.test(telefono)) {
                mostrarError(input, 'El teléfono debe tener entre 7 y 11 dígitos');
                return false;
            }
            limpiarError(input);
            return true;
        }

        // Validar fecha de nacimiento
        function validarFechaNacimiento(input) {
            if (!input) return false;
            if (!input.value) {
                mostrarError(input, 'La fecha de nacimiento es obligatoria');
                return false;
            }

            const fechaNacimiento = new Date(input.value);
            const hoy = new Date();
            const edad = hoy.getFullYear() - fechaNacimiento.getFullYear();

            if (fechaNacimiento > hoy) {
                mostrarError(input, 'La fecha no puede ser futura');
                return false;
            }
            if (edad < 18) {
                mostrarError(input, 'El representante debe ser mayor de edad');
                return false;
            }

            limpiarError(input);
            return true;
        }

        // Configurar validación en tiempo real para un campo
        function configurarValidacion(input, validacion) {
            if (!input) return;

            // Validar al perder el foco
            input.addEventListener('blur', function() {
                // No validar cédula de representante si es "Progenitor como Representante"
                if (this.id === 'numero_documento-representante' && esProgenitorRepresentante()) {
                    limpiarError(this);
                    return;
                }
                validacion(this);
            });

            // Limpiar errores al escribir
            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    limpiarError(this);
                }
            });

            // Si es el campo de cédula del representante, agregar evento para el cambio de tipo de representante
            // NOTA: Event listener duplicado eliminado - ya está manejado en el listener principal (línea 3778)
            if (input.id === 'numero_documento-representante') {
                // La lógica de limpiar error/validar ya está manejada en el event listener principal
                console.log('[VALIDACIÓN] Campo cédula representante configurado - listener duplicado eliminado');
            }
        }

        // Configurar validación para selects
        function configurarValidacionSelect(select) {
            if (!select) return;

            select.addEventListener('change', function() {
                if (this.value) {
                    limpiarError(this);
                } else {
                    validarSeleccion(this);
                }
            });
        }

        // Configurar validaciones para la madre
        function configurarValidacionesMadre() {
            const camposMadre = {
                // Datos personales
                'primer-nombre': validarRequerido,
                'primer-apellido': validarRequerido,
                'numero_documento': validarnumero_documento,
                'fechaNacimiento': validarFechaNacimiento,
                'sexo': validarSeleccion,
                'lugar-nacimiento': validarRequerido,
                // Ubicación
                'idEstado': validarSeleccion,
                'idMunicipio': validarSeleccion,
                'idparroquia': validarSeleccion,
                // Contacto
                'prefijo': validarSeleccion,
                'telefono': validarTelefono,
                // Relación familiar
                'ocupacion-madre': validarRequerido,
                'convive': validarSeleccion
            };

            Object.entries(camposMadre).forEach(([id, validacion]) => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    if (elemento.tagName === 'SELECT') {
                        configurarValidacionSelect(elemento);
                    } else if (elemento.type === 'radio' || elemento.type === 'checkbox') {
                        // Para radios, configurar en todos los del mismo nombre
                        const radioName = elemento.name;
                        const radioGroup = document.querySelectorAll(`input[name="${radioName}"]`);
                        if (radioGroup.length > 0) {
                            radioGroup.forEach(radio => {
                                radio.addEventListener('change', () => {
                                    const selected = document.querySelector(
                                        `input[name="${radioName}"]:checked`);
                                    if (!selected) {
                                        mostrarError(radio, 'Debe seleccionar una opción');
                                    } else {
                                        limpiarError(radio);
                                        // Limpiar errores de otros radios del mismo grupo
                                        radioGroup.forEach(r => limpiarError(r));
                                    }
                                });
                            });
                        }
                    } else {
                        configurarValidacion(elemento, validacion);
                    }
                }
            });
        }

        // Configurar validaciones para el padre
        function configurarValidacionesPadre() {
            const camposPadre = {
                // Datos personales
                'primer-nombre-padre': validarRequerido,
                'primer-apellido-padre': validarRequerido,
                'numero_documento-padre': validarnumero_documento,
                'fechaNacimiento-padre': validarFechaNacimiento,
                'genero-padre': validarSeleccion,
                'lugar-nacimiento-padre': validarRequerido,
                // Ubicación
                'idEstado-padre': validarSeleccion,
                'idMunicipio-padre': validarSeleccion,
                'idparroquia-padre': validarSeleccion,
                // Contacto
                'prefijo-padre': validarSeleccion,
                'telefono-padre': validarTelefono,
                // Relación familiar
                'ocupacion-padre': validarRequerido,
                'convive-padre': validarSeleccion
            };

            Object.entries(camposPadre).forEach(([id, validacion]) => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    if (elemento.tagName === 'SELECT') {
                        configurarValidacionSelect(elemento);
                    } else if (elemento.type === 'radio' || elemento.type === 'checkbox') {
                        const radioName = elemento.name;
                        const radioGroup = document.querySelectorAll(`input[name="${radioName}"]`);
                        if (radioGroup.length > 0) {
                            radioGroup.forEach(radio => {
                                radio.addEventListener('change', () => {
                                    const selected = document.querySelector(
                                        `input[name="${radioName}"]:checked`);
                                    if (!selected) {
                                        mostrarError(radio, 'Debe seleccionar una opción');
                                    } else {
                                        limpiarError(radio);
                                        // Limpiar errores de otros radios del mismo grupo
                                        radioGroup.forEach(r => limpiarError(r));
                                    }
                                });
                            });
                        }
                    } else {
                        configurarValidacion(elemento, validacion);
                    }
                }
            });
        }

        // Configurar validaciones para el representante legal
        function configurarValidacionesRepresentante() {
            const camposRepresentante = {
                // Datos personales
                'primer-nombre-representante': validarRequerido,
                'primer-apellido-representante': validarRequerido,
                'numero_documento-representante': validarnumero_documento,
                'fechaNacimiento-representante': validarFechaNacimiento,
                'sexo-representante': validarSeleccion,
                'lugar-nacimiento-representante': validarRequerido,
                // Ubicación
                'idEstado-representante': validarSeleccion,
                'idMunicipio-representante': validarSeleccion,
                'idparroquia-representante': validarSeleccion,
                // Contacto
                'prefijo-representante': validarSeleccion,
                'telefono-representante': validarTelefono,
                // Relación familiar
                'ocupacion-representante': validarRequerido,
                'convive-representante': validarSeleccion,
                // Datos legales
                'parentesco': validarSeleccion,
                'tipo-cuenta': validarSeleccion,
                'banco-representante': validarSeleccion
            };

            Object.entries(camposRepresentante).forEach(([id, validacion]) => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    if (elemento.tagName === 'SELECT') {
                        configurarValidacionSelect(elemento);
                    } else if (elemento.type === 'radio' || elemento.type === 'checkbox') {
                        const radioName = elemento.name;
                        const radioGroup = document.querySelectorAll(`input[name="${radioName}"]`);
                        if (radioGroup.length > 0) {
                            radioGroup.forEach(radio => {
                                radio.addEventListener('change', () => {
                                    const selected = document.querySelector(
                                        `input[name="${radioName}"]:checked`);
                                    if (!selected) {
                                        mostrarError(radio, 'Debe seleccionar una opción');
                                    } else {
                                        limpiarError(radio);
                                        // Limpiar errores de otros radios del mismo grupo
                                        radioGroup.forEach(r => limpiarError(r));
                                    }
                                });
                            });
                        }
                    } else {
                        configurarValidacion(elemento, validacion);
                    }
                }
            });
        }

        // Función para inicializar las validaciones
        function inicializarValidaciones() {
            // Configurar validaciones
            configurarValidacionesMadre();
            configurarValidacionesPadre();
            configurarValidacionesRepresentante();
        }

        // Función para validar un campo individual con validaciones específicas
        function validarCampo(campo, mensajeError) {
            if (!campo) return false;

            const valor = campo.value.trim();
            let esValido = true;
            let mensaje = '';

            // Validar campo requerido
            const esRequerido = campo.required || campo.getAttribute('required') !== null;

            // Campos que siempre son obligatorios independientemente del atributo required
            const camposObligatorios = [
                'sexo-representante',
                'lugar-nacimiento-representante',
                'carnet-patria',
                'codigo-carnet',
                'serial-carnet'
            ];

            // Verificar si el campo es obligatorio
            const esCampoObligatorio = camposObligatorios.includes(campo.id) ||
                (campo.id.includes('lugar-nacimiento') && !campo.id.includes('lugar-nacimiento-madre') && !campo.id
                    .includes('lugar-nacimiento-padre'));

            // Forzar el atributo required si es un campo obligatorio
            if (esCampoObligatorio) {
                campo.required = true;
            }

            if ((esRequerido || esCampoObligatorio) && valor === '') {
                esValido = false;
                mensaje = 'Este campo es obligatorio';
            }

            // Validaciones específicas por tipo de campo
            if (esValido && valor !== '') {
                // Validar nombres (solo letras, espacios y caracteres especiales comunes en nombres)
                if (campo.id.includes('nombre') || campo.id.includes('apellido') ||
                    campo.name.includes('nombre') || campo.name.includes('apellido')) {
                    const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'-]+$/;
                    if (!nombreRegex.test(valor)) {
                        esValido = false;
                        mensaje = 'Solo se permiten letras y espacios en este campo';
                    }
                }
                // Validar ubicaciones (estado, municipio, parroquia, lugar de nacimiento)
                else if (campo.id.includes('lugar-nacimiento') ||
                    campo.id.includes('idEstado') ||
                    campo.id.includes('idMunicipio') ||
                    campo.id.includes('idparroquia')) {
                    if (valor === '' || valor === 'Seleccione') {
                        esValido = false;
                        mensaje = 'Este campo es obligatorio';

                        // Asegurarse de que el campo se marque como inválido visualmente
                        if (campo.id === 'lugar-nacimiento-representante') {
                            campo.classList.add('is-invalid');
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback';
                            errorDiv.textContent = mensaje;

                            // Insertar el mensaje de error después del campo
                            const parent = campo.parentNode;
                            if (parent && !parent.querySelector('.invalid-feedback')) {
                                parent.appendChild(errorDiv);
                            }
                        }
                    }
                }
                // Validar Carnet de la Patria
                else if (campo.id === 'carnet-patria') {
                    if (valor === '' || valor === 'Seleccione') {
                        esValido = false;
                        mensaje = 'Debe seleccionar una opción';
                    } else if (valor !== '0') { // Si no es 'No aplica'
                        // Validar campos de código y serial si está afiliado
                        const codigoCarnet = document.getElementById('codigo');
                        const serialCarnet = document.getElementById('serial');

                        if (codigoCarnet && codigoCarnet.value.trim() === '') {
                            mostrarError(codigoCarnet, 'El código es obligatorio cuando el carnet está afiliado');
                            esValido = false;
                        }
                        if (serialCarnet && serialCarnet.value.trim() === '') {
                            mostrarError(serialCarnet, 'El serial es obligatorio cuando el carnet está afiliado');
                            esValido = false;
                        }
                    }
                }
                // Validar formato de código y serial de carnet
                else if (campo.id === 'codigo-carnet' || campo.id === 'serial-carnet') {
                    if (valor !== '') {
                        // Validar que solo contenga números y letras
                        const codigoRegex = /^[A-Za-z0-9]+$/;
                        if (!codigoRegex.test(valor)) {
                            esValido = false;
                            mensaje = 'Solo se permiten letras y números';
                        } else if (valor.length < 5) {
                            esValido = false;
                            mensaje = 'Debe tener al menos 5 caracteres';
                        }
                    } else {
                        // Si el campo está vacío, verificar si es obligatorio
                        const carnetAfiliado = document.getElementById('carnet-patria');
                        if (carnetAfiliado && carnetAfiliado.value !== '0' && carnetAfiliado.value !== '') {
                            esValido = false;
                            mensaje = 'Este campo es obligatorio';
                        }
                    }
                }
                // Validar email
                if (campo.type === 'email' || campo.getAttribute('data-type') === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(valor)) {
                        esValido = false;
                        mensaje = 'Ingrese un correo electrónico válido';
                    }
                }
                // Validar números
                else if (campo.type === 'number' || campo.getAttribute('data-type') === 'number') {
                    if (isNaN(valor)) {
                        esValido = false;
                        mensaje = 'Ingrese un número válido';
                    } else {
                        const min = campo.getAttribute('min');
                        const max = campo.getAttribute('max');
                        const numVal = parseFloat(valor);

                        if (min !== null && numVal < parseFloat(min)) {
                            esValido = false;
                            mensaje = `El valor mínimo permitido es ${min}`;
                        } else if (max !== null && numVal > parseFloat(max)) {
                            esValido = false;
                            mensaje = `El valor máximo permitido es ${max}`;
                        }
                    }
                }
                // Validar campos select (sexo, tipo documento, bancos, etc.)
                else if (campo.tagName === 'SELECT' && (campo.id.includes('sexo') || 
                    campo.id.includes('tipo-ci') || campo.id.includes('banco') || 
                    campo.id.includes('ocupacion') || campo.id.includes('parentesco') ||
                    campo.id.includes('convive') || campo.id.includes('tipo-cuenta'))) {
                    if (valor === '' || valor === null || valor === 'Seleccione') {
                        esValido = false;
                        mensaje = 'Debe seleccionar una opción';
                    }
                }
                // Validar teléfono
                else if (campo.id.includes('telefono') || campo.getAttribute('data-type') === 'phone') {
                    const phoneRegex = /^[0-9\s\-\(\)]+$/;
                    if (!phoneRegex.test(valor)) {
                        esValido = false;
                        mensaje = 'Ingrese un número de teléfono válido';
                    }
                }
                // Validación de cédula/RIF eliminada para permitir cualquier valor
                // Se mantiene el comentario para referencia
            }

            if (!esValido) {
                mostrarError(campo, mensaje || mensajeError);
                return false;
            }

            limpiarError(campo);
            return true;
        }

        // Función para validar un grupo de radio buttons o checkboxes
        function validarRadioGroup(nombreGrupo, mensajeError) {
            const inputs = document.querySelectorAll(`input[name="${nombreGrupo}"]`);
            if (inputs.length === 0) return true;

            const esRadio = inputs[0].type === 'radio';
            const seleccionado = Array.from(inputs).some(input => input.checked);
            const esRequerido = inputs[0].required || inputs[0].getAttribute('required') !== null;

            // Solo validar si es requerido
            if (esRequerido && !seleccionado) {
                const primerInput = inputs[0];
                const errorElement = primerInput.closest('.form-group') || primerInput.closest('.form-check');
                mostrarError(errorElement, mensajeError ||
                    `Debe seleccionar ${esRadio ? 'una opción' : 'al menos una opción'}`);
                return false;
            }

            // Limpiar errores si es válido
            if (seleccionado) {
                inputs.forEach(input => {
                    const errorElement = input.closest('.form-group') || input.closest('.form-check');
                    limpiarError(errorElement);
                });
            }

            return true;
        }

        // Función para validar la sección de la madre
        function validarSeccionMadre() {
            let valido = true;

            // Validar estado de la madre
            if (!validarRadioGroup('estado_madre', 'Debe seleccionar el estado de la madre')) {
                valido = false;
            }

            // Si la madre está presente, validar sus datos
            if (document.querySelector('input[name="estado_madre"]:checked')?.value === 'Presente') {
                const camposMadre = [{
                        id: 'tipo-ci',
                        mensaje: 'Seleccione el tipo de documento'
                    },
                    {
                        id: 'numero_documento',
                        mensaje: 'Ingrese el número de cédula'
                    },
                    {
                        id: 'primer-nombre',
                        mensaje: 'Ingrese el primer nombre'
                    },
                    {
                        id: 'primer-apellido',
                        mensaje: 'Ingrese el primer apellido'
                    },
                    {
                        id: 'sexo',
                        mensaje: 'Seleccione el género'
                    },
                    {
                        id: 'lugar-nacimiento',
                        mensaje: 'Ingrese el lugar de nacimiento'
                    },
                    {
                        id: 'idEstado',
                        mensaje: 'Seleccione el estado'
                    },
                    {
                        id: 'idMunicipio',
                        mensaje: 'Seleccione el municipio'
                    },
                    {
                        id: 'idparroquia',
                        mensaje: 'Seleccione la parroquia'
                    },
                    {
                        id: 'prefijo',
                        mensaje: 'Seleccione el prefijo telefónico'
                    },
                    {
                        id: 'telefono',
                        mensaje: 'Ingrese el número de teléfono'
                    },
                    {
                        id: 'ocupacion-madre',
                        mensaje: 'Seleccione una ocupación'
                    }
                ];

                // Asegurarse de que los campos de ubicación sean requeridos
                const ubicaciones = ['lugar-nacimiento', 'idEstado', 'idMunicipio', 'idparroquia'];
                ubicaciones.forEach(id => {
                    const elemento = document.getElementById(id);
                    if (elemento) elemento.required = true;
                });

                camposMadre.forEach(campo => {
                    const elemento = document.getElementById(campo.id);
                    if (elemento && !validarCampo(elemento, campo.mensaje)) {
                        valido = false;
                    }
                });
            }

            return valido;
        }

        // Función para validar la sección del padre
        function validarSeccionPadre() {
            let valido = true;

            // Validar estado del padre
            if (!validarRadioGroup('estado_padre', 'Debe seleccionar el estado del padre')) {
                valido = false;
            }

            // Si el padre está presente, validar sus datos
            if (document.querySelector('input[name="estado_padre"]:checked')?.value === 'Presente') {
                const camposPadre = [{
                        id: 'tipo-ci-padre',
                        mensaje: 'Seleccione el tipo de documento'
                    },
                    {
                        id: 'numero_documento-padre',
                        mensaje: 'Ingrese el número de cédula'
                    },
                    {
                        id: 'primer-nombre-padre',
                        mensaje: 'Ingrese el primer nombre'
                    },
                    {
                        id: 'primer-apellido-padre',
                        mensaje: 'Ingrese el primer apellido'
                    },
                    {
                        id: 'sexo-padre',
                        mensaje: 'Seleccione el género'
                    },
                    {
                        id: 'lugar-nacimiento-padre',
                        mensaje: 'Ingrese el lugar de nacimiento'
                    },
                    {
                        id: 'idEstado-padre',
                        mensaje: 'Seleccione el estado'
                    },
                    {
                        id: 'idMunicipio-padre',
                        mensaje: 'Seleccione el municipio'
                    },
                    {
                        id: 'idparroquia-padre',
                        mensaje: 'Seleccione la parroquia'
                    },
                    {
                        id: 'prefijo-padre',
                        mensaje: 'Seleccione el prefijo telefónico'
                    },
                    {
                        id: 'telefono-padre',
                        mensaje: 'Ingrese el número de teléfono'
                    },
                    {
                        id: 'ocupacion-padre',
                        mensaje: 'Seleccione una ocupación'
                    }
                ];

                // Asegurarse de que los campos de ubicación sean requeridos
                const ubicaciones = ['lugar-nacimiento-padre', 'idEstado-padre', 'idMunicipio-padre', 'idparroquia-padre'];
                ubicaciones.forEach(id => {
                    const elemento = document.getElementById(id);
                    if (elemento) elemento.required = true;
                });

                camposPadre.forEach(campo => {
                    const elemento = document.getElementById(campo.id);
                    if (elemento && !validarCampo(elemento, campo.mensaje)) {
                        valido = false;
                    }
                });
            }

            return valido;
        }

        // Función para validar la sección del representante legal
        function validarSeccionRepresentante() {
            let valido = true;

            // Validar tipo de representante
            if (!validarRadioGroup('tipo_representante', 'Seleccione el tipo de representante')) {
                valido = false;
            }

            const camposRepresentante = [{
                    id: 'tipo-ci-representante',
                    mensaje: 'Seleccione el tipo de documento'
                },
                {
                    id: 'numero_documento-representante',
                    mensaje: 'Ingrese el número de cédula'
                },
                {
                    id: 'primer-nombre-representante',
                    mensaje: 'Ingrese el primer nombre'
                },
                {
                    id: 'primer-apellido-representante',
                    mensaje: 'Ingrese el primer apellido'
                },
                {
                    id: 'sexo-representante',
                    mensaje: 'Seleccione el género'
                },
                {
                    id: 'lugar-nacimiento-representante',
                    mensaje: 'Ingrese el lugar de nacimiento'
                },
                {
                    id: 'idEstado-representante',
                    mensaje: 'Seleccione el estado'
                },
                {
                    id: 'idMunicipio-representante',
                    mensaje: 'Seleccione el municipio'
                },
                {
                    id: 'idparroquia-representante',
                    mensaje: 'Seleccione la parroquia'
                },
                {
                    id: 'prefijo-representante',
                    mensaje: 'Seleccione el prefijo telefónico'
                },
                {
                    id: 'telefono-representante',
                    mensaje: 'Ingrese el número de teléfono'
                },
                {
                    id: 'parentesco',
                    mensaje: 'Seleccione el parentesco'
                },
                {
                    id: 'banco-representante',
                    mensaje: 'Seleccione el banco'
                },
                {
                    id: 'tipo-cuenta-representante',
                    mensaje: 'Seleccione el tipo de cuenta'
                },
                {
                    id: 'numero-cuenta-representante',
                    mensaje: 'Ingrese el número de cuenta'
                },
                {
                    id: 'carnet-patria',
                    mensaje: 'Seleccione la opción de Carnet de la Patria'
                },
                {
                    id: 'codigo-carnet',
                    mensaje: 'Ingrese el código del carnet'
                },
                {
                    id: 'serial-carnet',
                    mensaje: 'Ingrese el serial del carnet'
                }
            ];

            // Asegurarse de que los campos de ubicación sean requeridos
            const ubicaciones = [
                'lugar-nacimiento-representante',
                'idEstado-representante',
                'idMunicipio-representante',
                'idparroquia-representante'
            ];

            ubicaciones.forEach(id => {
                const elemento = document.getElementById(id);
                if (elemento) elemento.required = true;
            });

            camposRepresentante.forEach(campo => {
                const elemento = document.getElementById(campo.id);
                if (elemento && !validarCampo(elemento, campo.mensaje)) {
                    valido = false;
                }
            });

            return valido;
        }

        // Función para validar todo el formulario
        function validarFormularioCompleto() {
            // Limpiar errores previos
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

            // Validar cada sección
            console.log('=== INICIANDO VALIDACIÓN COMPLETA ===');
            const validoMadre = validarSeccionMadre();
            console.log('Validación madre:', validoMadre);
            const validoPadre = validarSeccionPadre();
            console.log('Validación padre:', validoPadre);
            const validoRepresentante = validarSeccionRepresentante();
            console.log('Validación representante:', validoRepresentante);

            // Si hay errores, desplazarse al primero
            if (!validoMadre || !validoPadre || !validoRepresentante) {
                console.log('=== VALIDACIÓN FALLÓ ===');
                console.log('Madre válida:', validoMadre, 'Padre válido:', validoPadre, 'Representante válido:', validoRepresentante);
                const primerError = document.querySelector('.is-invalid');
                if (primerError) {
                    primerError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    primerError.focus();
                }
                return false;
            }

            console.log('=== VALIDACIÓN EXITOSA ===');
            return true;
        }

        // Función para agregar validación en tiempo real
        function agregarValidacionEnTiempoReal() {
            // Validar campos de texto, email, número, teléfono y selects
            document.querySelectorAll(
                    'input[type="text"], input[type="email"], input[type="number"], input[type="tel"], textarea, select')
                .forEach(input => {
                    if (input.type === 'file') return;

                    // Marcar como requerido si es un campo de ubicación o del Carnet de la Patria
                    if (input.id.includes('lugar-nacimiento') ||
                        input.id.includes('idEstado') ||
                        input.id.includes('idMunicipio') ||
                        input.id.includes('idparroquia') ||
                        input.id === 'carnet-patria' ||
                        input.id === 'codigo-carnet' ||
                        input.id === 'serial-carnet') {
                        input.required = true;

                        // Agregar clase de Bootstrap para estilos de campo requerido
                        if (!input.classList.contains('is-required')) {
                            input.classList.add('is-required');
                        }
                    }

                    // Validar campos del Carnet de la Patria cuando cambian
                    if (input.id === 'carnet-patria') {
                        input.addEventListener('change', function() {
                            const codigoCarnet = document.getElementById('codigo-carnet');
                            const serialCarnet = document.getElementById('serial-carnet');

                            // Mostrar/ocultar campos según la selección
                            if (this.value === '0') { // No aplica
                                if (codigoCarnet) codigoCarnet.required = false;
                                if (serialCarnet) serialCarnet.required = false;
                            } else {
                                if (codigoCarnet) codigoCarnet.required = true;
                                if (serialCarnet) serialCarnet.required = true;
                            }

                            validarCampo(this, 'Seleccione una opción');
                        });
                    }

                    // Validar al salir del campo
                    input.addEventListener('blur', function() {
                        // Validar siempre los campos obligatorios, independientemente de si tienen valor o no
                        if (this.required || this.id.includes('lugar-nacimiento') || this.value.trim() !== '') {
                            validarCampo(this, 'Este campo es obligatorio');
                        }
                    });

                    // Validar mientras se escribe (solo si ya tiene un valor)
                    input.addEventListener('input', function() {
                        if (this.value.trim() !== '') {
                            validarCampo(this, 'Este campo es obligatorio');
                        } else {
                            limpiarError(this);
                            this.classList.remove('is-invalid');
                        }
                    });

                    // Validar cambios en selects de ubicación
                    if (input.tagName === 'SELECT' &&
                        (input.id.includes('idEstado') ||
                            input.id.includes('idMunicipio') ||
                            input.id.includes('idparroquia'))) {
                        input.addEventListener('change', function() {
                            validarCampo(this, 'Este campo es obligatorio');
                        });
                    }
                });

            // Validar radio buttons y checkboxes
            document.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(input => {
                input.addEventListener('change', function() {
                    const name = this.name;
                    const formGroup = this.closest('.form-group') || this.closest('.form-check');

                    if (this.type === 'radio') {
                        validarRadioGroup(name);
                    } else if (this.required) {
                        const checked = document.querySelectorAll(`input[name="${name}"]:checked`).length >
                            0;
                        if (!checked) {
                            mostrarError(formGroup, 'Debe seleccionar al menos una opción');
                        } else {
                            limpiarError(formGroup);
                        }
                    }
                });
            });
        }

        // Inicializar validaciones cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar validación en tiempo real
            agregarValidacionEnTiempoReal();
            
            // Inicializar validaciones específicas
            inicializarValidaciones();
        });

        // ================= VALIDACIÓN DE FECHAS DE NACIMIENTO =================
        // Función para validar que la fecha corresponde a una persona mayor de 18 años
        function validarEdad(fechaInput) {
            // Si no hay input o no tiene valor, limpiar estilos y salir
            if (!fechaInput || !fechaInput.value) {
                limpiarEstiloEdad(fechaInput);
                return true;
            }

            // Calcular la edad exacta
            const fechaNacimiento = new Date(fechaInput.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
            const mes = hoy.getMonth() - fechaNacimiento.getMonth();

            // Ajustar la edad si aún no ha cumplido años este año
            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                edad--;
            }

            // Obtener el contenedor del campo y verificar si ya existe una advertencia
            const grupo = fechaInput.closest('.form-group') || fechaInput.closest('.input-group') || fechaInput.closest('[class*="col-"]') || fechaInput.parentElement;
            let advertencia = grupo ? grupo.querySelector('.advertencia-edad') : null;

            // Verificar si es menor de 18 años
            if (edad < 18) {
                // Crear mensaje de advertencia
                const mensaje = 'Advertencia: La persona es menor de 18 años';

                // Crear elemento de advertencia si no existe
                if (!advertencia && grupo) {
                    advertencia = document.createElement('small');
                    advertencia.className = 'form-text text-danger advertencia-edad';
                    grupo.appendChild(advertencia);
                }

                // Actualizar el texto de la advertencia
                if (advertencia) {
                    advertencia.textContent = mensaje;
                }

                // Resaltar el campo en rojo
                fechaInput.classList.add('is-invalid');
                fechaInput.classList.remove('is-valid');
            } else {
                // Si es mayor de edad, limpiar cualquier advertencia previa
                limpiarEstiloEdad(fechaInput);
            }

            return true; // Siempre permitir cualquier fecha
        }

        /**
         * Limpia los estilos y mensajes de advertencia de un campo de fecha
         * @param {HTMLInputElement} input - Elemento input a limpiar
         */
        function limpiarEstiloEdad(input) {
            if (!input) return;

            // Buscar y eliminar el mensaje de advertencia si existe
            const grupo = input.closest('.form-group') || input.closest('.input-group') || input.closest('[class*="col-"]') || input.parentElement;
            if (grupo) {
                const advertencia = grupo.querySelector('.advertencia-edad');
                if (advertencia) {
                    advertencia.remove();
                }
            }
            // Restaurar estilos por defecto
            input.classList.remove('is-invalid', 'is-valid');
        }

        /**
         * Configura los eventos de validación para todos los campos de fecha
         */
        function configurarValidacionFechas() {
            // IDs de todos los campos de fecha que necesitan validación
            const fechas = [
                'fecha-nacimiento-representante', // Fecha de nacimiento del representante
                'fecha-nacimiento-padre', // Fecha de nacimiento del padre
                'fechaNacimiento' // Fecha de nacimiento de la madre
            ];

            // Configurar eventos para cada campo de fecha
            fechas.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    // Validar cuando cambia la fecha
                    input.addEventListener('change', function() {
                        validarEdad(this);
                    });

                    // También validar cuando el campo pierde el foco
                    input.addEventListener('blur', function() {
                        validarEdad(this);
                    });
                }
            });
        }

        // Inicializar la validación cuando el DOM esté completamente cargado
        if (document.readyState === 'loading') {
            // Si el DOM aún no está listo, esperar a que cargue
            document.addEventListener('DOMContentLoaded', configurarValidacionFechas);
        } else {
            // Si el DOM ya está listo, ejecutar directamente
            configurarValidacionFechas();
        }
        // Función para mostrar/ocultar campos según el estado
        function toggleCamposPorEstado(estado, prefijo) {
            const campos = document.querySelectorAll(`[data-depende="${prefijo}"]`);
            const esPresente = estado === 'Presente';

            campos.forEach(campo => {
                campo.disabled = !esPresente;
                if (campo.required) {
                    campo.required = esPresente;
                }

                // Manejar selectpicker si está presente
                if (typeof $ !== 'undefined' && $.fn.selectpicker && $(campo).hasClass('selectpicker')) {
                    $(campo).prop('disabled', !esPresente);
                    $(campo).selectpicker('destroy');
                    $(campo).selectpicker();
                }

                // Manejar select2 si está presente
                if (typeof $.fn.select2 === 'function' && $(campo).hasClass('select2-hidden-accessible')) {
                    $(campo).prop('disabled', !esPresente).trigger('change.select2');
                }
            });
        }

        // Inicializar campos según el estado al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Para la madre
            const estadoMadre = document.querySelector('input[name="estado_madre"]:checked');
            if (estadoMadre) {
                toggleCamposPorEstado(estadoMadre.value, 'madre');
            }

            // Para el padre
            const estadoPadre = document.querySelector('input[name="estado_padre"]:checked');
            if (estadoPadre) {
                toggleCamposPorEstado(estadoPadre.value, 'padre');
            }

            // Event listeners para los radios de estado
            document.querySelectorAll('input[name="estado_madre"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    toggleCamposPorEstado(this.value, 'madre');
                });
            });

            document.querySelectorAll('input[name="estado_padre"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    toggleCamposPorEstado(this.value, 'padre');
                });
            });

            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Resetear bandera global al cargar la página
            window.copiandoDatosProgenitor = false;
            console.log('[INICIO] Bandera copiandoDatosProgenitor reseteada al cargar página');

            // BLOQUEO GLOBAL para selectpickers
            window.reconstruyendoSelectpickers = false;
            window.colaReconstruccionGlobal = [];

            // MÉTODO DE EMERGENCIA: Resetear bandera cada 5 segundos por si queda atascada
            setInterval(() => {
                if (window.copiandoDatosProgenitor) {
                    console.warn('[EMERGENCIA] Detectada bandera atascada, reseteando automáticamente...');
                    window.copiandoDatosProgenitor = false;
                }
                if (window.reconstruyendoSelectpickers) {
                    console.warn('[EMERGENCIA] Resetando bloqueo de selectpickers atascado...');
                    window.reconstruyendoSelectpickers = false;
                }
            }, 5000);

            // MÉTODO DE EMERGENCIA: Agregar función global para forzar reseteo manual
            window.forzarReseteoCopia = function() {
                console.log('[EMERGENCIA MANUAL] Forzando reseteo de bandera copiandoDatosProgenitor');
                window.copiandoDatosProgenitor = false;
            };
        }); // Cierre del DOMContentLoaded para event listeners
    </script>

    @livewireScripts
    @stack('scripts')
@stop

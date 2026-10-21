@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@stop

@section('title', 'Editar representante')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div>
                    <h1 class="title-main">Editar Representante</h1>
                    <p class="title-subtitle">Actualiza la información del representante:
                        {{ $representante->persona->primer_nombre ?? '' }}
                        {{ $representante->persona->primer_apellido ?? '' }}</p>
                </div>
            </div>

            @if (request('from') === 'inscripcion_edit')
                <a href="{{ route('admin.transacciones.inscripcion.edit', request('inscripcion_id'))  }}" class="btn-create"
                    style="background: var(--gray-700);">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Listado</span>
                </a>
            @else
                <a href="{{ route('representante.index') }}" class="btn-create" style="background: var(--gray-700);">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Listado</span>
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alerts-container">
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

        <div class="card-body-modern" style="padding: 2rem;">
            <form id="representante-form" action="{{ route('representante.update', $representante->id) }}"
                method="POST" class="form-modern needs-validation" novalidate>

                    @csrf
                    @method('PUT')

                    @if ($from === 'inscripcion_edit')
                        <input type="hidden" name="from" value="{{ $from }}">
                        <input type="hidden" name="inscripcion_id" value="{{ $inscripcion_id }}">
                    @endif

                    <input type="hidden" name="id" value="{{ $representante->id }}">
                    <input type="hidden" name="es_legal" id="es_legal"
                        value="{{ $representante->status === 1 ? '1' : '0' }}">
                    <input type="hidden" name="persona_id" value="{{ $representante->persona_id }}">
                    <input type="radio" name="tipo_representante" value="progenitor" id="tipo_progenitor"
                        style="display: none;" {{ $representante->status !== 1 ? 'checked' : '' }}>
                    <input type="radio" name="tipo_representante" value="legal" id="tipo_legal" style="display: none;"
                        {{ $representante->status === 1 ? 'checked' : '' }}>

                    <div class="card-modern mb-4">
                        <div class="card-header-modern">
                            <div class="header-left">
                                <div class="header-icon" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h3>Datos Básicos</h3>
                                    <p>Información personal y de contacto del representante</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                        @if (isset($representante))
                            <input type="hidden" name="id" value="{{ $representante->id }}">
                        @endif
                        
                        <div class="card-modern mb-4">
                            <div class="card-header-modern">
                                <div class="header-left">
                                    <div class="header-icon" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div>
                                        <h3>Información Personal</h3>
                                        <p>Datos básicos de identificación</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-id-card me-2"></i> Tipo de Documento <span
                                                class="required-badge">*</span>
                                        </label>
                                        <select class="form-select" id="tipo-ci-representante" name="tipo-ci-representante"
                                            required>
                                            <option value="" disabled
                                                {{ !isset($representante->persona->tipo_documento_id) ? 'selected' : '' }}>
                                                Seleccione</option>
                                            @foreach ($tipoDocumentos as $tipoDoc)
                                                <option value="{{ $tipoDoc->id }}"
                                                    {{ old('tipo_documento_id', $representante->persona->tipo_documento_id ?? '') == $tipoDoc->id ? 'selected' : '' }}>
                                                    {{ $tipoDoc->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione el tipo de documento.
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-id-card me-2"></i> Número de Cédula <span
                                                class="required-badge">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="numero_documento-representante"
                                            name="numero_documento-representante"
                                            value="{{ old('numero_documento-representante', $representante->persona->numero_documento ?? '') }}"
                                            maxlength="9" pattern="\d{6,9}" title="Ingrese solo números (entre 6 y 9 dígitos)"
                                            required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        <div class="invalid-feedback">
                                            Por favor ingrese un número de cédula válido (solo números).
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-calendar-alt me-2"></i> Fecha de Nacimiento <span
                                                class="required-badge">*</span>
                                        </label>
                                        @php
                                            $fechaNac = $representante->persona->fecha_nacimiento ? \Carbon\Carbon::parse($representante->persona->fecha_nacimiento)->format('Y-m-d') : '';
                                            if ($fechaNac === '2000-01-01' && ($esFichaNueva ?? false)) {
                                                $fechaNac = '';
                                            }
                                        @endphp
                                        <input type="date" class="form-control" id="fecha-nacimiento-representante"
                                            name="fecha-nacimiento-representante"
                                            value="{{ old('fecha-nacimiento-representante', $fechaNac) }}"
                                            required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese una fecha de nacimiento válida.
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-user me-2"></i> Primer Nombre <span class="required-badge">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="primer-nombre-representante"
                                            name="primer-nombre-representante"
                                            value="{{ old('primer-nombre-representante', $representante->persona->primer_nombre ?? '') }}"
                                            pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                                            title="Solo se permiten letras y espacios, no se aceptan números" required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese un nombre válido (solo letras y espacios).
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-user me-2"></i> Segundo Nombre
                                        </label>
                                        <input type="text" class="form-control" id="segundo-nombre-representante"
                                            name="segundo-nombre-representante"
                                            value="{{ old('segundo-nombre-representante', $representante->persona->segundo_nombre ?? '') }}"
                                            pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                                            title="Solo se permiten letras y espacios, no se aceptan números">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-user me-2"></i> Tercer Nombre
                                        </label>
                                        <input type="text" class="form-control" id="tercer-nombre-representante"
                                            name="tercer-nombre-representante"
                                            value="{{ old('tercer-nombre-representante', $representante->persona->tercer_nombre ?? '') }}"
                                            pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                                            title="Solo se permiten letras y espacios, no se aceptan números">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-user me-2"></i> Primer Apellido <span class="required-badge">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="primer-apellido-representante"
                                            name="primer-apellido-representante"
                                            value="{{ old('primer-apellido-representante', $representante->persona->primer_apellido ?? '') }}"
                                            pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                                            title="Solo se permiten letras y espacios, no se aceptan números" required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese un apellido válido (solo letras y espacios).
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-user me-2"></i> Segundo Apellido
                                        </label>
                                        <input type="text" class="form-control" id="segundo-apellido-representante"
                                            name="segundo-apellido-representante"
                                            value="{{ old('segundo-apellido-representante', $representante->persona->segundo_apellido ?? '') }}"
                                            pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                                            title="Solo se permiten letras y espacios, no se aceptan números">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-venus-mars me-2"></i> Género <span class="required-badge">*</span>
                                        </label>
                                        <select class="form-select" id="sexo-representante" name="sexo-representante" required>
                                            <option value="" disabled selected>Seleccione</option>
                                            @foreach ($generos as $genero)
                                                <option value="{{ $genero->id }}"
                                                    {{ old('genero_id', $representante->persona->genero_id ?? '') == $genero->id ? 'selected' : '' }}>
                                                    {{ $genero->genero }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione un género.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-modern mb-4">
                            <div class="card-header-modern">
                                <div class="header-left">
                                    <div class="header-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div>
                                        <h3>Teléfonos y Contacto</h3>
                                        <p>Información de contacto telefónico</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="prefijo_telefono" class="form-label">Prefijo Teléfono <span
                                                class="required-badge">*</span></label>
                                        <select class="form-select" id="prefijo_telefono" name="prefijo_telefono" required>
                                            <option value="">Seleccione</option>
                                            @foreach ($prefijos_telefono as $prefijo)
                                                <option value="{{ $prefijo->id }}"
                                                    {{ old('prefijo_telefono', $representante->persona->prefijo_id ?? '') == $prefijo->id ? 'selected' : '' }}>
                                                    {{ $prefijo->prefijo }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione un prefijo telefónico.
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="telefono_movil" class="form-label">Teléfono Móvil <span
                                                class="required-badge">*</span></label>
                                        <input type="text" class="form-control" id="telefono_movil" name="telefono_movil"
                                            value="{{ old('telefono_movil', $representante->legal->telefono ?? ($representante->persona->telefono ?? '')) }}"
                                            pattern="[0-9]+" title="Ingrese solo números"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese un número de teléfono válido (solo números).
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="prefijo_dos" class="form-label">Prefijo Teléfono 2</label>
                                        <select class="form-select" id="prefijo_dos" name="prefijo_dos">
                                            <option value="">Seleccione</option>
                                            @foreach ($prefijos_telefono as $prefijo)
                                                <option value="{{ $prefijo->id }}"
                                                    {{ old('prefijo_dos', $representante->persona->prefijo_dos_id ?? '') == $prefijo->id ? 'selected' : '' }}>
                                                    {{ $prefijo->prefijo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="telefono_dos" class="form-label">Teléfono Móvil 2</label>
                                        <input type="text" class="form-control" id="telefono_dos" name="telefono_dos"
                                            value="{{ old('telefono_dos', $representante->persona->telefono_dos ?? '') }}"
                                            pattern="[0-9]+" title="Ingrese solo números"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        <div class="invalid-feedback">
                                            Por favor ingrese un número de teléfono válido (solo números).
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-modern mb-4">
                            <div class="card-header-modern">
                                <div class="header-left">
                                    <div class="header-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div>
                                        <h3>Dirección y Ubicación</h3>
                                        <p>Información de residencia del representante</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <!-- Fila para País, Estado, Municipio y Parroquia -->
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-globe me-2"></i> País <span
                                                class="required-badge">*</span>
                                        </label>
                                        @php
                                            $valPais = ($esFichaNueva ?? false) ? old('pais_id', '') : old('pais_id', $representante->pais_id ?? '');
                                        @endphp
                                        <select class="form-select" id="pais_id" name="pais_id" required>
                                            <option value="" disabled {{ empty($valPais) ? 'selected' : '' }}>Seleccione</option>
                                            @php
                                                // Obtener países únicos
                                                $paisesUnicos = isset($paises) ? $paises->unique('id')->sortBy('nameES') : collect([]);
                                            @endphp
                                            @foreach ($paisesUnicos as $pais)
                                                <option value="{{ $pais->id }}"
                                                    {{ $valPais == $pais->id ? 'selected' : '' }}>
                                                    {{ $pais->nameES }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione un país.
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-map-marker-alt me-2"></i> Estado <span
                                                class="required-badge">*</span>
                                        </label>
                                        @php
                                            $valEstado = ($esFichaNueva ?? false) ? old('estado_id', '') : old('estado_id', $representante->estado_id ?? '');
                                        @endphp
                                        <select class="form-select" id="estado_id" name="estado_id" required>
                                            <option value="" disabled {{ empty($valEstado) ? 'selected' : '' }}>Seleccione</option>
                                            @foreach ($estados as $estado)
                                                <option value="{{ $estado->id }}"
                                                    data-pais-id="{{ $estado->pais_id }}"
                                                    {{ $valEstado == $estado->id ? 'selected' : '' }}>
                                                    {{ $estado->nombre_estado }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione un estado.
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-map-marker-alt me-2"></i> Municipio <span
                                                class="required-badge">*</span>
                                        </label>
                                        @php
                                            $valMunicipio = ($esFichaNueva ?? false) ? old('municipio_id', '') : old('municipio_id', $representante->municipio_id ?? '');
                                        @endphp
                                        <select class="form-select" id="municipio_id" name="municipio_id" required>
                                            <option value="" disabled {{ empty($valMunicipio) ? 'selected' : '' }}>Seleccione</option>
                                            @foreach ($municipios as $municipio)
                                                <option value="{{ $municipio->id }}"
                                                    data-estado-id="{{ $municipio->estado_id }}"
                                                    {{ $valMunicipio == $municipio->id ? 'selected' : '' }}>
                                                    {{ $municipio->nombre_municipio }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione un municipio.
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label-modern">
                                            <i class="fas fa-map-marker-alt me-2"></i> Parroquia <span
                                                class="required-badge">*</span>
                                        </label>
                                        @php
                                            $valParroquia = ($esFichaNueva ?? false) ? old('parroquia_id', '') : old('parroquia_id', $representante->parroquia_id ?? '');
                                        @endphp
                                        <select class="form-select" id="parroquia_id" name="parroquia_id" required>
                                            <option value="" disabled {{ empty($valParroquia) ? 'selected' : '' }}>Seleccione</option>
                                            @foreach ($parroquias_cargadas as $parroquia)
                                                <option value="{{ $parroquia->id }}"
                                                    data-municipio-id="{{ $parroquia->municipio_id }}"
                                                    {{ $valParroquia == $parroquia->id ? 'selected' : '' }}>
                                                    {{ $parroquia->nombre_localidad }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione una parroquia.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-modern mb-4">
                            <div class="card-header-modern">
                                <div class="header-left">
                                    <div class="header-icon" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <h3>Relación Familiar y Ocupación</h3>
                                        <p>Información sobre la relación con el estudiante y ocupación</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="ocupacion_id" class="form-label">Ocupación</label>
                                        @php
                                            $valOcupacion = ($esFichaNueva ?? false) ? old('ocupacion_id', '') : old('ocupacion_id', $representante->ocupacion_representante ?? '');
                                        @endphp
                                        <select class="form-select" id="ocupacion_id" name="ocupacion_id">
                                            <option value="" {{ empty($valOcupacion) ? 'selected' : '' }}>Seleccione</option>
                                            @foreach ($ocupaciones as $ocupacion)
                                                <option value="{{ $ocupacion->id }}"
                                                    {{ $valOcupacion == $ocupacion->id ? 'selected' : '' }}>
                                                    {{ $ocupacion->nombre_ocupacion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <span><span class="text-danger">(*)</span>Convive con el Estudiante?</span>
                                        <div class="d-flex mt-1">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="radio" id="convive-si-representante"
                                                    name="convive-representante" value="si" required
                                                    {{ old('convive-representante', $representante->convivenciaestudiante_representante ?? 'no') == 'si' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="convive-si-representante">Si</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="convive-no-representante"
                                                    name="convive-representante" value="no" required
                                                    {{ old('convive-representante', $representante->convivenciaestudiante_representante ?? 'no') == 'no' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="convive-no-representante">No</label>
                                            </div>
                                        </div>
                                        <small id="convive-representante-error" class="text-danger"></small>
                                        <div class="invalid-feedback">
                                            Por favor indique si convive con el estudiante.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    @if ($representante->status === 1)
                    <div class="card-modern mb-4">
                        <div class="card-header-modern">
                            <div class="header-left">
                                <div class="header-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h3>Datos del Representante Legal</h3>
                                    <p>Información de contacto y acceso al sistema</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="card-modern mb-3">
                                <div class="card-header-modern">
                                    <div class="header-left">
                                        <div class="header-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                                            <i class="fas fa-wifi"></i>
                                        </div>
                                        <div>
                                            <h3>Conectividad y Participación Ciudadana</h3>
                                            <p>Información de contacto y participación</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="correo-representante" class="form-label">Correo Electrónico</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="correo-representante"
                                                name="correo-representante" maxlength="254"
                                                title="No olvide incluir el símbolo @ en la dirección de correo"
                                                placeholder="ejemplo@correo.com"
                                                value="{{ old('correo-representante', $representante->persona->email ?? '') }}">
                                        </div>
                                        <small id="correo-representante-error" class="text-danger"></small>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label d-block">
                                            <span class="required-badge">(*)</span> ¿Pertenece a alguna Organización
                                            Política o Comunitaria?
                                        </label>
                                        <div class="d-flex mt-1">
                                            <div class="form-check me-3">
                                                @php
                                                    $valorOrganizacion = old('pertenece_organizacion');
                                                    if (is_null($valorOrganizacion) && isset($representante->legal)) {
                                                        $valorOrganizacion = $representante->legal
                                                            ->pertenece_a_organizacion_representante
                                                            ? 1
                                                            : 0;
                                                    }
                                                    $valorOrganizacion = $valorOrganizacion ?? 0;
                                                @endphp
                                                <input class="form-check-input" type="radio"
                                                    id="pertenece_organizacion_si" name="pertenece_organizacion"
                                                    value="1" {{ $valorOrganizacion == 1 ? 'checked' : '' }}>
                                                <label class="form-check-label" for="pertenece_organizacion_si">
                                                    Sí
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    id="pertenece_organizacion_no" name="pertenece_organizacion"
                                                    value="0" {{ $valorOrganizacion == 0 ? 'checked' : '' }}>
                                                <label class="form-check-label" for="pertenece_organizacion_no">
                                                    No
                                                </label>
                                            </div>
                                        </div>
                                        <small id="pertenece-organizacion-error" class="text-danger"></small>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div id="campo_organizacion" class="form-group"
                                        style="display: {{ old('pertenece_organizacion', $representante->legal ? ($representante->legal->pertenece_a_organizacion_representante ? 1 : 0) : 0) == 1 ? 'block' : 'none' }};">
                                        <span class="required-badge">(*)</span> Especifique la organización
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-sitemap"></i></span>
                                            <input type="text" class="form-control"
                                                id="cual_organizacion_representante"
                                                name="cual_organizacion_representante" maxlength="50"
                                                value="{{ old('cual_organizacion_representante', $representante->legal->cual_organizacion_representante ?? '') }}"
                                                title="Ingrese el nombre de la organización (solo letras y espacios)"
                                                pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+">
                                        </div>
                                        <small id="cual_organizacion_representante-error" class="text-danger"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                            </div>
                            <div class="card-modern mb-3">
                                <div class="card-header-modern">
                                    <div class="header-left">
                                        <div class="header-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <div>
                                            <h3>Identificación Familiar y Datos de Cuenta</h3>
                                            <p>Información familiar y de acceso al sistema</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-3">

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="parentesco" class="form-label">Parentesco</label>
                                        <select class="form-select" id="parentesco" name="parentesco" required>
                                            <option value="" disabled
                                                {{ !$representante->legal || !$representante->legal->parentesco ? 'selected' : '' }}>
                                                Seleccione</option>
                                            <option value="Padre"
                                                {{ old('parentesco', $representante->legal->parentesco ?? '') == 'Padre' ? 'selected' : '' }}>
                                                Padre</option>
                                            <option value="Madre"
                                                {{ old('parentesco', $representante->legal->parentesco ?? '') == 'Madre' ? 'selected' : '' }}>
                                                Madre</option>
                                            <option value="Hermano"
                                                {{ old('parentesco', $representante->legal->parentesco ?? '') == 'Hermano' ? 'selected' : '' }}>
                                                Hermano</option>
                                            <option value="Hermana"
                                                {{ old('parentesco', $representante->legal->parentesco ?? '') == 'Hermana' ? 'selected' : '' }}>
                                                Hermana</option>
                                            <option value="Abuelo"
                                                {{ old('parentesco', $representante->legal->parentesco ?? '') == 'Abuelo' ? 'selected' : '' }}>
                                                Abuelo</option>
                                            <option value="Abuela"
                                                {{ old('parentesco', $representante->legal->parentesco ?? '') == 'Abuela' ? 'selected' : '' }}>
                                                Abuela</option>
                                            <option value="Tío"
                                                {{ old('parentesco', $representante->legal->parentesco ?? '') == 'Tío' ? 'selected' : '' }}>
                                                Tío</option>
                                            <option value="Tía"
                                                {{ old('parentesco', $representante->legal->parentesco ?? '') == 'Tía' ? 'selected' : '' }}>
                                                Tía</option>
                                            <option value="Otro"
                                                {{ old('parentesco', $representante->legal->parentesco ?? '') == 'Otro' ? 'selected' : '' }}>
                                                Otro</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione un parentesco.
                                        </div>
                                        <small id="parentesco-error" class="text-danger"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="carnet_patria_afiliado" class="form-label">Carnet de la Patria
                                            Afiliado a</label>
                                        @php
                                            $valorCarnet = old('carnet_patria_afiliado');
                                            if (!isset($valorCarnet) && isset($representante->legal)) {
                                                $valorCarnet = $representante->legal->carnet_patria_afiliado;
                                            }
                                            $valorCarnet = $valorCarnet !== null ? (int) $valorCarnet : null;
                                        @endphp
                                        <select class="form-select" id="carnet_patria_afiliado"
                                            name="carnet_patria_afiliado">
                                            <option value=""
                                                {{ $valorCarnet === null || $valorCarnet === '' ? 'selected' : '' }}>
                                                Seleccione</option>
                                            <option value="0" {{ $valorCarnet === 0 ? 'selected' : '' }}>Ninguno
                                            </option>
                                            <option value="1" {{ $valorCarnet === 1 ? 'selected' : '' }}>Padre
                                            </option>
                                            <option value="2" {{ $valorCarnet === 2 ? 'selected' : '' }}>Madre
                                            </option>
                                            <option value="3" {{ $valorCarnet === 3 ? 'selected' : '' }}>Hijo
                                            </option>
                                            <option value="4" {{ $valorCarnet === 4 ? 'selected' : '' }}>Hija
                                            </option>
                                            <option value="5" {{ $valorCarnet === 5 ? 'selected' : '' }}>Abuelo
                                            </option>
                                            <option value="6" {{ $valorCarnet === 6 ? 'selected' : '' }}>Abuela
                                            </option>
                                            <option value="7" {{ $valorCarnet === 7 ? 'selected' : '' }}>Tutor
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione una opción.
                                        </div>
                                        <small id="carnet-patria-error" class="text-danger"></small>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="codigo_carnet" class="form-label">Código</label>
                                        <input type="text" class="form-control" id="codigo_carnet"
                                            name="codigo_carnet"
                                            value="{{ old('codigo_carnet', $representante->legal->codigo_carnet_patria_representante ?? '') }}"
                                            maxlength="10" pattern="[0-9]+"
                                            title="Ingrese solo números (máximo 10 dígitos)">
                                        <div class="invalid-feedback">
                                            Por favor ingrese un código válido (solo números).
                                        </div>
                                        <small id="codigo-error" class="text-danger"></small>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="serial_carnet" class="form-label">Serial</label>
                                        <input type="text" class="form-control" id="serial_carnet"
                                            name="serial_carnet"
                                            value="{{ old('serial_carnet', $representante->legal->serial_carnet_patria_representante ?? '') }}"
                                            maxlength="9" pattern="[0-9]+"
                                            title="Ingrese solo números (máximo 9 dígitos)">
                                        <div class="invalid-feedback">
                                            Por favor ingrese un serial válido (solo números).
                                        </div>
                                        <small id="serial-error" class="text-danger"></small>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="tipo_cuenta" class="form-label">Tipo de Cuenta</label>
                                        <select class="form-select" id="tipo_cuenta" name="tipo_cuenta">
                                            <option value=""
                                                {{ !$representante->legal || !$representante->legal->tipo_cuenta ? 'selected' : '' }}>
                                                Seleccione</option>
                                            <option value="Corriente"
                                                {{ old('tipo_cuenta', $representante->legal->tipo_cuenta ?? '') == 'Corriente' ? 'selected' : '' }}>
                                                Corriente</option>
                                            <option value="Ahorro"
                                                {{ old('tipo_cuenta', $representante->legal->tipo_cuenta ?? '') == 'Ahorro' ? 'selected' : '' }}>
                                                Ahorro</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione un tipo de cuenta.
                                        </div>
                                        <small id="tipo-cuenta-error" class="text-danger"></small>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="banco_id" class="form-label">Banco</label>
                                        <select class="form-select" id="banco_id" name="banco_id">
                                            <option value="">Seleccione</option>
                                            @foreach ($bancos as $banco)
                                                <option value="{{ $banco->id }}"
                                                    {{ old('banco_id', $representante->legal->banco_id ?? '') == $banco->id ? 'selected' : '' }}>
                                                    {{ $banco->nombre_banco }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione un banco.
                                        </div>
                                        <small id="banco-error" class="text-danger"></small>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label for="direccion_habitacion" class="form-label">Dirección de
                                            Habitación</label>
                                        <textarea class="form-control" id="direccion_habitacion" name="direccion_habitacion" rows="3" maxlength="200"
                                            required placeholder="Ej: Barrio Araguaney, Avenida 5 entre calles 8 y 9, Casa #12-34"
                                            title="Ingrese su dirección completa incluyendo puntos de referencia">{{ old('direccion_habitacion', $representante->persona->direccion ?? '') }}</textarea>
                                        <div class="invalid-feedback">
                                            Por favor ingrese una dirección válida.
                                        </div>
                                        <small id="direccion-habitacion-error" class="text-danger"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    @endif
                        <div class="d-flex justify-content-end gap-3 pt-5 pb-4 border-top mt-4">
                            @if (request('from') === 'inscripcion_edit')
                                <a href="{{ route('admin.transacciones.inscripcion.edit', request('inscripcion_id')) }}" class="btn-cancel-modern">
                                    <i class="fas fa-arrow-left me-2"></i> Cancelar y Volver
                                </a>
                            @else
                                <a href="{{ route('representante.index') }}" class="btn-cancel-modern">
                                    <i class="fas fa-arrow-left me-2"></i> Cancelar y Volver
                                </a>
                            @endif

                            <button type="submit" class="btn-primary-modern">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>

                </form>
        </div>
    </div>
@endsection

@section('js')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Función para mostrar errores
        function mostrarError(element, mensaje) {
            limpiarError(element);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-danger small mt-1';
            errorDiv.textContent = mensaje;
            
            const container = element.closest('.form-group') || element.closest('.form-check') || element.parentElement;
            container.appendChild(errorDiv);
            element.classList.add('is-invalid');
        }

        // Función para limpiar errores
        function limpiarError(element) {
            const container = element.closest('.form-group') || element.closest('.form-check') || element.parentElement;
            const errores = container.querySelectorAll('.text-danger');
            errores.forEach(error => error.remove());
            element.classList.remove('is-invalid');
        }

        // Función para validar un grupo de radio buttons
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
                mostrarError(errorElement, mensajeError || 'Debe seleccionar una opción');
                return false;
            }

            // Limpiar errores si es válido
            if (seleccionado) {
                inputs.forEach(input => {
                    limpiarError(input);
                });
            }

            return true;
        }

        // Validar formulario antes de enviar
        function validarFormulario() {
            let valido = true;

            // Validar campo convive-representante
            if (!validarRadioGroup('convive-representante', 'Debe indicar si convive con el estudiante')) {
                valido = false;
            }

            return valido;
        }

        // Función para inicializar los selects dependientes
        function inicializarSelectsDependientes() {
            const $paisSelect = $('#pais_id');
            const $estadoSelect = $('#estado_id');
            const $municipioSelect = $('#municipio_id');
            const $parroquiaSelect = $('#parroquia_id');

            // Obtener los valores actuales
            const paisId = $paisSelect.val();
            const estadoId = $estadoSelect.val();
            const municipioId = $municipioSelect.val();
            const parroquiaId = $parroquiaSelect.val();

            // En formulario de edición, todos los campos deben estar habilitados
            // No deshabilitamos ningún campo inicialmente

            // Función para filtrar opciones
            function filtrarOpciones($select, dataAttr, valorFiltro) {
                $select.find('option').each(function() {
                    const $option = $(this);
                    if ($option.val() === '') {
                        $option.show();
                        return;
                    }

                    const dataValue = $option.data(dataAttr);
                    if (!valorFiltro || dataValue == valorFiltro) {
                        $option.show();
                    } else {
                        $option.hide();
                        if ($option.is(':selected')) {
                            $option.prop('selected', false);
                        }
                    }
                });
            }

            // Manejar cambio de país
            $paisSelect.on('change', function() {
                const paisId = $(this).val();

                // Limpiar estado, municipio y parroquia si cambia el país
                if (paisId !== $paisSelect.data('previous-value')) {
                    $estadoSelect.val('').trigger('change');
                    $municipioSelect.val('').trigger('change');
                    $parroquiaSelect.val('').trigger('change');
                }
                $paisSelect.data('previous-value', paisId);

                // Filtrar estados por país (si los datos tienen país_id)
                filtrarOpciones($estadoSelect, 'pais-id', paisId);
            });

            // Manejar cambio de estado
            $estadoSelect.on('change', function() {
                const estadoId = $(this).val();

                // Limpiar municipio y parroquia si cambia el estado
                if (estadoId !== $estadoSelect.data('previous-value')) {
                    $municipioSelect.val('').trigger('change');
                    $parroquiaSelect.val('').trigger('change');
                }
                $estadoSelect.data('previous-value', estadoId);

                // Filtrar municipios
                filtrarOpciones($municipioSelect, 'estado-id', estadoId);
            });

            // Manejar cambio de municipio
            $municipioSelect.on('change', function() {
                const municipioId = $(this).val();

                // Limpiar parroquias si cambia el municipio
                if (municipioId !== $municipioSelect.data('previous-value')) {
                    $parroquiaSelect.val('').trigger('change');
                }
                $municipioSelect.data('previous-value', municipioId);

                // Filtrar parroquias
                filtrarOpciones($parroquiaSelect, 'municipio-id', municipioId);
            });

            // Inicializar selects al cargar la página
            // Solo si hay valores preseleccionados válidos (no vacíos), aplicar los filtros correspondientes
            @if (!($esFichaNueva ?? false))
            if (paisId) {
                $paisSelect.trigger('change');
                
                setTimeout(() => {
                    // Establecer el estado después de filtrar
                    if (estadoId) {
                        $estadoSelect.val(estadoId);
                        $estadoSelect.trigger('change');
                        
                        setTimeout(() => {
                            // Establecer el municipio después de filtrar
                            if (municipioId) {
                                $municipioSelect.val(municipioId);
                                $municipioSelect.trigger('change');
                                
                                setTimeout(() => {
                                    // Establecer la parroquia después de filtrar
                                    if (parroquiaId) {
                                        $parroquiaSelect.val(parroquiaId);
                                    }
                                }, 100);
                            }
                        }, 100);
                    }
                }, 100);
            }
            @endif
        }

        // Función para mostrar/ocultar campo de organización
        function toggleCampoOrganizacion() {
            const pertenece = $('input[name="pertenece_organizacion"]:checked').val();

            if (pertenece === '1') {
                $('#campo_organizacion').show();
                $('#cual_organizacion_representante').prop('required', true);
            } else {
                $('#campo_organizacion').hide();
                $('#cual_organizacion_representante')
                    .prop('required', false)
                    .val('');
            }
        }


        // Función para mostrar/ocultar campos según el tipo de representante
        function toggleRepresentativeFields() {
            const isLegal = $('input[name="tipo_representante"]:checked').val() === 'legal';

            const legalFields = [
                '#correo-representante',
                '#banco_id',
                '#parentesco'
            ];

            if (isLegal) {
                $('#seccion-conectividad').show();
                $('#seccion-identificacion-familiar').show();

                legalFields.forEach(selector => {
                    $(selector).prop('required', true);
                });

                if ($('input[name="pertenece_organizacion"]:checked').val() === '1') {
                    $('#cual_organizacion_representante').prop('required', true);
                }

            } else {
                $('#seccion-conectividad').hide();
                $('#seccion-identificacion-familiar').hide();

                legalFields.forEach(selector => {
                    $(selector)
                        .prop('required', false)
                        .val('');
                });

                $('#cual_organizacion_representante')
                    .prop('required', false)
                    .val('');

                $('input[name="pertenece_organizacion"]').prop('checked', false);
            }
        }


        $(document).ready(function() {
            console.log('Documento listo - Inicializando campo de organización');

            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('.modal')
            });

            // Asegurar que el formulario se envíe correctamente
            $('#representante-form').on('submit', function(e) {
                e.preventDefault();

                // Validar formulario con JavaScript
                if (!validarFormulario()) {
                    return false;
                }

                const form = this;

                // Validación HTML5
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');

                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstInvalid.focus();
                    }
                    return;
                }

                const submitBtn = $(form).find('button[type="submit"]');
                submitBtn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST', // Laravel usará _method=PUT
                    data: $(form).serialize(),
                    dataType: 'json',

                    success: function(response) {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.href = '{{ route('representante.index') }}';
                        }
                    },

                    error: function(xhr) {
                        submitBtn.prop('disabled', false)
                            .html('<i class="fas fa-save me-1"></i> Guardar Cambios');

                        let msg = 'Error al guardar.';
                        if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }

                        $('#representante-form').prepend(`
                            <div class="alert alert-danger alert-dismissible fade show">
                                ${msg}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);
                    }
                });
            });


            // Inicializar selects dependientes
            inicializarSelectsDependientes();

            // Inicializar el estado del campo de organización
            toggleCampoOrganizacion();

            // Inicializar visibilidad de campos según el tipo de representante
            toggleRepresentativeFields();

            // Manejar cambios en los radio buttons de organización
            $('input[name="pertenece_organizacion"]').on('change', function() {
                toggleCampoOrganizacion();
            });

            // Manejar cambios en el tipo de representante
            $('input[name="tipo_representante"]').on('change', function() {
                toggleRepresentativeFields();
            });

            // Manejar cambios en los radio buttons de convivencia
            $('input[name="convive-representante"]').on('change', function() {
                // Limpiar errores al seleccionar una opción
                const radios = document.querySelectorAll('input[name="convive-representante"]');
                radios.forEach(radio => {
                    limpiarError(radio);
                });
            });
        });
    </script>
@endsection

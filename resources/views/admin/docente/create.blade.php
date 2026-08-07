@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
@stop

@section('title', 'Crear Docente')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h1 class="title-main">Paso 1 de 1. Crear Nuevo Docente</h1>
                    <p class="title-subtitle">Registra la información del docente</p>
                </div>
            </div>
            <a href="{{ route('admin.docente.index') }}" class="btn-create" style="background: var(--gray-500);">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
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
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h3>Formulario de Registro</h3>
                        <p>Complete todos los campos requeridos</p>
                    </div>
                </div>
            </div> 
            <div class="card-body-modern" style="padding: 2rem;">
                <form id="docenteForm" action="{{ route('admin.docente.store') }}" method="POST" class="form-modern">
                    @csrf
                    <div class="section-title-modern">
                        <i class="fas fa-id-badge"></i> Datos de Identificación
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-id-card-alt"></i>
                                Tipo de Documento <span class="required-badge">*</span>
                            </label>
                            <select name="tipo_documento_id" id="tipo_documento_id"
                                class="form-control-modern @error('tipo_documento_id') is-invalid @enderror" required>
                                <option selected disabled>Seleccione</option>
                                @foreach ($tipoDocumentos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('tipo_documento_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_documento_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern">
                                <i class="fas fa-info-circle"></i>
                                Seleccione V, E
                            </small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-id-card"></i>
                                Cédula <span class="required-badge">*</span>
                            </label>
                            <input type="text" name="numero_documento" inputmode="numeric" id="numero_documento"
                                pattern="[0-9]+" maxlength="8" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                required class="form-control-modern @error('numero_documento') is-invalid @enderror"
                                value="{{ old('numero_documento') }}" placeholder="12345678">
                            @error('numero_documento')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern">
                                <i class="fas fa-info-circle"></i>
                                Solo números, máximo 8 dígitos
                            </small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-hashtag"></i>
                                Código
                            </label>
                            <input type="text" name="codigo" inputmode="numeric" pattern="[0-9]+" maxlength="9"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                class="form-control-modern @error('codigo') is-invalid @enderror"
                                value="{{ old('codigo') }}" placeholder="79322403">
                            @error('codigo')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern">
                                <i class="fas fa-info-circle"></i>
                                Código interno opcional
                            </small>
                        </div>
                    </div> 

                    <div class="section-title-modern" style="margin-top: 2rem;">
                        <i class="fas fa-user"></i> Datos Personales
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-user"></i>
                                Primer nombre <span class="required-badge">*</span>
                            </label>
                            <input type="text" name="primer_nombre" id="primer_nombre"
                                class=" text-capitalize form-control-modern @error('primer_nombre') is-invalid @enderror"
                                value="{{ old('primer_nombre') }}" placeholder="Ej: Juan" required>
                            @error('primer_nombre')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-user"></i>
                                Segundo nombre
                            </label>
                            <input type="text" name="segundo_nombre" id="segundo_nombre"
                                class="text-capitalize form-control-modern @error('segundo_nombre') is-invalid @enderror"
                                value="{{ old('segundo_nombre') }}" placeholder="Ej: Carlos">
                            @error('segundo_nombre')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-user"></i>
                                Tercer nombre
                            </label>
                            <input type="text" name="tercer_nombre" id="tercer_nombre"
                                class="text-capitalize form-control-modern @error('tercer_nombre') is-invalid @enderror"
                                value="{{ old('tercer_nombre') }}" placeholder="Opcional">
                            @error('tercer_nombre')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-user-tag"></i>
                                Primer apellido <span class="required-badge">*</span>
                            </label>
                            <input type="text" name="primer_apellido" id="primer_apellido"
                                class="text-capitalize form-control-modern @error('primer_apellido') is-invalid @enderror"
                                value="{{ old('primer_apellido') }}" placeholder="Ej: Pérez" required>
                            @error('primer_apellido')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-user-tag"></i>
                                Segundo apellido
                            </label>
                            <input type="text" name="segundo_apellido" id="segundo_apellido"
                                class="text-capitalize form-control-modern @error('segundo_apellido') is-invalid @enderror"
                                value="{{ old('segundo_apellido') }}" placeholder="Ej: Gómez">
                            @error('segundo_apellido')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-venus-mars"></i>
                                Género
                                <span class="required-badge">*</span>
                            </label>
                            <select name="genero" id="genero"
                                class="form-control-modern @error('genero') is-invalid @enderror" required>
                                <option value="">Seleccione</option>
                                @foreach ($generos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('genero') == $item->id ? 'selected' : '' }}>
                                        {{ $item->genero }}
                                    </option>
                                @endforeach
                            </select>
                            @error('genero')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-calendar"></i>
                                Fecha de nacimiento <span class="required-badge">*</span>
                            </label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                                class="form-control-modern @error('fecha_nacimiento') is-invalid @enderror"
                                value="{{ old('fecha_nacimiento') }}" max="{{ date('Y-m-d') }}" required>
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern">
                                <i class="fas fa-info-circle"></i>
                                La fecha debe corresponder a una persona mayor de 18 años
                            </small>
                        </div>
                    </div> 

                    <div class="section-title-modern" style="margin-top: 2rem;">
                        <i class="fas fa-phone"></i> Información de Contacto
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-id-card-alt"></i>
                                Prefijo
                            </label>
                            <select name="prefijo_id" id="prefijo_id"
                                class="form-control-modern @error('prefijo_id') is-invalid @enderror" required>
                                <option value="" selected disabled>Seleccione</option>
                                @foreach ($prefijos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('prefijo_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->prefijo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prefijo_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-phone"></i>
                                Numero de teléfono
                            </label>
                            <input type="text" name="primer_telefono" id="primer_telefono" inputmode="numeric"
                                pattern="[0-9]*" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                required class="form-control-modern @error('primer_telefono') is-invalid @enderror"
                                value="{{ old('primer_telefono') }}" placeholder="Ej: 12345678">
                            @error('primer_telefono')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-id-card-alt"></i>
                                Prefijo 2
                            </label>
                            <select name="prefijo_dos_id" id="prefijo_dos_id"
                                class="form-control-modern @error('prefijo_dos_id') is-invalid @enderror">
                                <option value="" selected disabled>Seleccione</option>
                                @foreach ($prefijos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('prefijo_dos_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->prefijo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prefijo_dos_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-phone"></i>
                                Segundo teléfono
                            </label>
                            <input type="text" name="telefono_dos" id="telefono_dos" inputmode="numeric"
                                pattern="[0-9]*" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                class="form-control-modern @error('telefono_dos') is-invalid @enderror"
                                value="{{ old('telefono_dos') }}" placeholder="Ej: 12345678">
                            @error('telefono_dos')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-envelope"></i>
                                Correo electrónico
                            </label>
                            <input type="email" name="correo"
                                class="form-control-modern @error('correo') is-invalid @enderror"
                                value="{{ old('correo') }}" placeholder="ejemplo@correo.com">
                            @error('correo')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern">
                                <i class="fas fa-info-circle"></i>
                                Opcional, para notificaciones
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-building"></i>
                                Dependencia
                            </label>
                            <input type="text" name="dependencia" id="dependencia" required
                                class=" text-capitalize form-control-modern @error('dependencia') is-invalid @enderror"
                                value="{{ old('dependencia') }}" placeholder="Ej: Departamento de Matemáticas">
                            @error('dependencia')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern">
                                <i class="fas fa-info-circle"></i>
                                Unidad o área de trabajo
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-map-marker-alt"></i>
                                Dirección
                            </label>
                            <textarea
                                class="text-capitalize form-control-modern @error('direccion') is-invalid @enderror"
                                name="direccion" id="direccion" rows="3" required placeholder="Ingrese la dirección completa">{{ old('direccion') }}</textarea>
                            @error('direccion')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                        <label class="form-label-modern">
                            <i class=""></i>
                            Horas academicas disponibles
                        </label>
                        <input type="number" name="horas_academicas" id="horas_academicas" required
                                class=" text-capitalize form-control-modern"
                                value="{{ old('hora_academica') }}" placeholder="Ej: 5" >
                    </div>
                    </div> 
                    <div class="form-actions-modern">
                        <a href="{{ route('admin.docente.index') }}" class="btn-secondary-modern">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-primary-modern">
                            <i class="fas fa-save"></i> Siguiente
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
    </script>
    <script src="{{ asset('js/validations/docente.js') }}"></script>
@stop

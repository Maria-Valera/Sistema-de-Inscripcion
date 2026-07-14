@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
@stop

@section('title', 'Editar Docente')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div>
                    <h1 class="title-main">Editar Docente</h1>
                    <p class="title-subtitle">Actualiza la información del docente</p>
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
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h3>Formulario de Edición</h3>
                        <p>Modifique los campos necesarios</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern" style="padding: 2rem;">
                <form id="docenteForm" action="{{ route('admin.docente.update', $docente->id) }}" method="POST" class="form-modern">
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
                                <option value="">Seleccione</option>
                                @foreach ($tipoDocumentos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('tipo_documento_id', $docente->persona->tipo_documento_id) == $item->id ? 'selected' : '' }}>
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
                                Seleccione V, E, J o G
                            </small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-id-card"></i>
                                Cédula <span class="required-badge">*</span>
                            </label>
                            <input type="text" name="numero_documento" inputmode="numeric" id="numero_documento"
                                pattern="[0-9]+" maxlength="8" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                class="form-control-modern @error('numero_documento') is-invalid @enderror"
                                value="{{ old('numero_documento', $docente->persona->numero_documento) }}"
                                data-original-value="{{ $docente->persona->numero_documento }}"
                                data-persona-id="{{ $docente->persona->id }}"
                                placeholder="12345678" required>
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
                                value="{{ old('codigo', $docente->codigo) }}" placeholder="79322403">
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
                                class="text-capitalize form-control-modern @error('primer_nombre') is-invalid @enderror"
                                value="{{ old('primer_nombre', $docente->persona->primer_nombre) }}"
                                placeholder="Ej: Juan" required>
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
                            <input type="text" name="segundo_nombre"
                                class="text-capitalize form-control-modern @error('segundo_nombre') is-invalid @enderror"
                                value="{{ old('segundo_nombre', $docente->persona->segundo_nombre) }}"
                                placeholder="Ej: Carlos">
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
                            <input type="text" name="tercer_nombre"
                                class="text-capitalize form-control-modern @error('tercer_nombre') is-invalid @enderror"
                                value="{{ old('tercer_nombre', $docente->persona->tercer_nombre) }}"
                                placeholder="Opcional">
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
                                value="{{ old('primer_apellido', $docente->persona->primer_apellido) }}"
                                placeholder="Ej: Pérez" required>
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
                            <input type="text" name="segundo_apellido"
                                class="text-capitalize form-control-modern @error('segundo_apellido') is-invalid @enderror"
                                value="{{ old('segundo_apellido', $docente->persona->segundo_apellido) }}"
                                placeholder="Ej: Gómez">
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
                                Género <span class="required-badge">*</span>
                            </label>
                            <select name="genero" id="genero"
                                class="form-control-modern @error('genero') is-invalid @enderror" required>
                                <option value="">Seleccione</option>
                                @foreach ($generos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('genero', $docente->persona->genero_id) == $item->id ? 'selected' : '' }}>
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
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                class="form-control-modern @error('fecha_nacimiento') is-invalid @enderror"
                                value="{{ old('fecha_nacimiento', $docente->persona->fecha_nacimiento->format('Y-m-d')) }}"
                                max="{{ date('Y-m-d') }}" required>
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern">
                                <i class="fas fa-info-circle"></i>
                                Debe ser una fecha anterior a hoy
                            </small>
                        </div>
                    </div> 

                    <div class="section-title-modern" style="margin-top: 2rem;">
                        <i class="fas fa-phone"></i> Información de Contacto
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-envelope"></i>
                                Correo electrónico
                            </label>
                            <input type="email" name="correo"
                                class="form-control-modern @error('correo') is-invalid @enderror"
                                value="{{ old('correo', $docente->persona->email) }}" placeholder="ejemplo@correo.com">
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
                                <i class="fas fa-phone"></i>
                                Prefijo Telefono
                            </label>
                            <select name="prefijo_id" id="prefijo_id"
                                class="form-control-modern 
                                @error('prefijo_id') is-invalid @enderror" required>
                                <option value="" disabled selected>Seleccione</option>
                                @foreach ($prefijos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('prefijo_id', $docente->persona->prefijo_id) == $item->id ? 'selected' : '' }}>
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-phone"></i>
                                Primer Telefono
                            </label>
                            <input type="text" name="primer_telefono" id="primer_telefono"
                                class="form-control-modern 
                            @error('primer_telefono') is-invalid @enderror" maxlength="8" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                value="{{ old('primer_telefono', $docente->persona->telefono) }}"
                                placeholder="Ej: 12345678" required>
                            @error('primer_telefono')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-phone"></i>
                                Prefijo Telefono 2
                            </label>
                            <select name="prefijo_dos_id" id="prefijo_dos_id"
                                class="form-control-modern 
                                @error('prefijo_dos_id') is-invalid @enderror">
                                <option value="" disabled selected>Seleccione</option>
                                @foreach ($prefijos as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('prefijo_dos_id', $docente->persona->prefijo_dos_id) == $item->id ? 'selected' : '' }}>
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-phone"></i>
                                Segundo Telefono
                            </label>
                            <input type="text" name="telefono_dos" id="telefono_dos"
                                class="form-control-modern 
                            @error('telefono_dos') is-invalid @enderror" maxlength="8" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                value="{{ old('telefono_dos', $docente->persona->telefono_dos) }}"
                                placeholder="Ej: 12345678">
                            @error('telefono_dos')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-building"></i>
                                Dependencia
                            </label>
                            <input type="text" name="dependencia" id="dependencia" required
                                class=" text-capitalize form-control-modern @error('dependencia') is-invalid @enderror"
                                value="{{ old('dependencia', $docente->dependencia) }}" placeholder="Ej: Departamento de Matemáticas">
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
                            <textarea class="text-capitalize form-control-modern @error('direccion') is-invalid @enderror" name="direccion" id="direccion" rows="3"
                                placeholder="Ingrese la dirección completa" required>{{ old('direccion', $docente->persona->direccion) }}</textarea>
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
                                value="{{ old('hora_academica', $docente->horas_academicas) }}" placeholder="Ej: 5" >
                    </div>
                    </div> 
                    <div class="form-actions-modern">
                        <a href="{{ route('admin.docente.index') }}" class="btn-secondary-modern">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-primary-modern">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // Configurar la URL para verificación de cédula
        window.verificarCedulaUrl = "{{ route('admin.docente.verificar_cedula') }}";
    </script>
    <script src="{{ asset('js/validations/docente.js') }}"></script>
@endsection
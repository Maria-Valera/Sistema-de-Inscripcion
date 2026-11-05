@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
@stop

@section('title', 'Acta de Entrevista')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <h1 class="title-main">Acta de Entrevista</h1>
                    <p class="title-subtitle">Registra los hechos y manifestaciones de la entrevista</p>
                </div>
            </div>
            <a href="{{ route('admin.inasistencia.index') }}" class="btn-create" style="background: var(--gray-500);">
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
                        <h3>Formulario de Acta de Entrevista</h3>
                        <p>Complete todos los campos requeridos</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern" style="padding: 2rem;">
                <form id="actaEntrevistaForm" action="{{ route('admin.acta.store') }}" method="POST" class="form-modern">
                    @csrf

                    <div class="section-title-modern">
                        <i class="fas fa-user-tag"></i> Datos del Entrevistado
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-user-shield"></i>
                                Rol <span class="required-badge">*</span>
                            </label>
                            <select name="rol_id" id="rol_id"
                                class="form-control-modern @error('rol_id') is-invalid @enderror" required>
                                <option value="" selected disabled>Seleccione</option>
                                <option value="1">Administrativo</option>
                                <option value="2">Docente</option>
                                <option value="3">Subdirector(a)</option>
                            </select>
                            @error('rol_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text-modern">
                                <i class="fas fa-info-circle"></i>
                                Seleccione el rol del entrevistado
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-user"></i>
                                Usuario <span class="required-badge">*</span>
                            </label>
                            <input type="text" name="usuario" id="usuario"
                                class="form-control-modern @error('usuario') is-invalid @enderror"
                                value="{{ old('usuario') }}" placeholder="Ej: Juan Pérez" required>
                            @error('usuario')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="section-title-modern" style="margin-top: 2rem;">
                        <i class="fas fa-clipboard-list"></i> Desarrollo de la Entrevista
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-exclamation-triangle"></i>
                                Hechos presuntamente incurridos <span class="required-badge">*</span>
                            </label>
                            <textarea
                                class="form-control-modern @error('hechos_presuntos') is-invalid @enderror"
                                name="hechos_presuntos" id="hechos_presuntos" rows="4" required
                                placeholder="Describa los hechos presuntamente incurridos">{{ old('hechos_presuntos') }}</textarea>
                            @error('hechos_presuntos')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-comment-dots"></i>
                                Ante tal(es) planteamiento(s) el ciudadano manifiesta <span class="required-badge">*</span>
                            </label>
                            <textarea
                                class="form-control-modern @error('manifestacion_ciudadano') is-invalid @enderror"
                                name="manifestacion_ciudadano" id="manifestacion_ciudadano" rows="4" required
                                placeholder="Describa la manifestación del ciudadano">{{ old('manifestacion_ciudadano') }}</textarea>
                            @error('manifestacion_ciudadano')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-handshake"></i>
                                Acuerdos allegados y compromisos incurridos <span class="required-badge">*</span>
                            </label>
                            <textarea
                                class="form-control-modern @error('acuerdos_compromisos') is-invalid @enderror"
                                name="acuerdos_compromisos" id="acuerdos_compromisos" rows="4" required
                                placeholder="Describa los acuerdos allegados y compromisos incurridos">{{ old('acuerdos_compromisos') }}</textarea>
                            @error('acuerdos_compromisos')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions-modern">
                        <a href="{{ route('admin.inasistencia.index') }}" class="btn-secondary-modern">
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
@stop

@section('js')
    <script src="{{ asset('js/validations/acta_entrevista.js') }}"></script>
@stop

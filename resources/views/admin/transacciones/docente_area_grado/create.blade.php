@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="{{ asset('css/horario-pilot.css') }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-es_ES.min.js"></script>
@stop

@section('title', 'Agregar Asignaciones a Docentes')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h1 class="title-main">Agregar Asignaciones a Docentes</h1>
                    <p class="title-subtitle">Gestión de asignaciones académicas</p>
                </div>
            </div>

            <a href="{{ route('admin.transacciones.docente_area_grado.index') }}" class="btn-create" style="background: var(--gray-700);">
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
    <livewire:admin.transaccion-docente.docente-area-grado />
                <div class="form-actions-modern">
                    <a href="{{ route('admin.transacciones.docente_area_grado.index') }}" type="button" class="btn-cancel-modern">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <a href="{{ route('admin.transacciones.docente_area_grado.index') }}" type="button" class="btn-create">
                        <i class="fas fa-save"></i> Guardar Asignación
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

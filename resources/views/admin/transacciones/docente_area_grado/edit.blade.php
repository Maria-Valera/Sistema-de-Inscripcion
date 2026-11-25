@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="{{ asset('css/horario-pilot.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-es_ES.min.js"></script>
@stop

@section('title', 'Editar Asignaciones a Docentes')

@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Editar Asignaciones a Docentes</h1>
                    <p class="title-subtitle mb-0">Gestión de asignaciones académicas</p>
                </div>
            </div>
            <a href="{{ route('admin.transacciones.docente_area_grado.index') }}" class="btn-hero-white text-decoration-none">
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
    <livewire:admin.transaccion-docente.docente-area-grado :docenteId="$docenteId"/>
</div>

@endsection

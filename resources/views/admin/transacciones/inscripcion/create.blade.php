@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}?v={{ @filemtime(public_path('css/index.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}?v={{ @filemtime(public_path('css/modal-styles.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}?v={{ @filemtime(public_path('css/view-styles.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/materias-styles.css') }}?v={{ @filemtime(public_path('css/materias-styles.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Registrar Nuevo Ingreso')

@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Registrar Nuevo Ingreso</h1>
                    <p class="title-subtitle mb-0">Formulario de inscripción de estudiantes</p>
                </div>
            </div>
            <a href="{{ route('admin.transacciones.inscripcion.index') }}" 
               class="btn-hero-white text-decoration-none" title="Volver al listado">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="main-container pb-4 mb-4">
        @php
            $anoActivo = \App\Support\AnioEscolarSeleccionado::modelo();
        @endphp
        @if (!$anoActivo)
            <div class="alert alert-warning">
                <strong>No hay Calendario Escolar activo.</strong>
                <a href="{{ route('admin.anio_escolar.index') }}">Ir a Calendario Escolar</a>
            </div>
        @endif
        <livewire:admin.transaccion-inscripcion.inscripcion />
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
        });

        setTimeout(() => {
            $('.alert-modern').fadeOut('slow');
        }, 5000);
    });
    
</script>
@stop

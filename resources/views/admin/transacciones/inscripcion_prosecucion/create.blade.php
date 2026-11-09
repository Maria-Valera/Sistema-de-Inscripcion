@extends('adminlte::page')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/materias-styles.css') }}">

@stop
@section('title', 'Registrar Prosecucion')
@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h1 class="title-main">Registrar Prosecucion</h1>
                    <p class="title-subtitle">Formulario de inscripción de estudiantes</p>
                </div>
            </div>
            <a href="{{ route('admin.transacciones.inscripcion_prosecucion.index') }}" 
               class="btn-create" style="background: var(--gray-500);">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="main-container">
        @php
            $anoActivo = \App\Support\AnioEscolarSeleccionado::modelo();
        @endphp
        @if (!$anoActivo)
            <div class="alert alert-warning">
                <strong>No hay Calendario Escolar activo.</strong>
                <a href="{{ route('admin.anio_escolar.index') }}">Ir a Calendario Escolar</a>
            </div>
        @endif
        <livewire:admin.transaccion-inscripcion.inscripcion-prosecucion />
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

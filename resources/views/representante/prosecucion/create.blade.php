@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/materias-styles.css') }}">
    @livewireStyles
@stop

@section('title', 'Nueva Inscripción por Prosecución')

@section('content_header')
    <div class="content-header-modern">
        {{-- Breadcrumbs --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0; font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" style="color: var(--primary); text-decoration: none;"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('portal-representante.index') }}" style="color: var(--primary); text-decoration: none;">Portal del Representante</a></li>
                <li class="breadcrumb-item"><a href="{{ route('portal-representante.prosecucion.index') }}" style="color: var(--primary); text-decoration: none;">Inscripción por Prosecución</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="color: var(--gray-700); font-weight: 700;">Nueva Inscripción</li>
            </ol>
        </nav>

        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div>
                    <h1 class="title-main">Nueva Inscripción por Prosecución</h1>
                    <p class="title-subtitle">Promoción y asignación de materias del representado</p>
                </div>
            </div>
            <a href="{{ route('portal-representante.prosecucion.index') }}" class="btn-create" style="background: var(--gray-500);">
                <i class="fas fa-arrow-left"></i>
                <span>Volver al Listado</span>
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="main-container">
    @php
        $anoActivo = App\Models\AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();
    @endphp
    @if (!$anoActivo)
        <div class="alert alert-warning">
            <strong>No hay Calendario Escolar activo.</strong> El proceso de inscripción está actualmente inhabilitado.
        </div>
    @else
        <livewire:admin.transaccion-inscripcion.inscripcion-prosecucion />
    @endif
</div>
@stop

@section('js')
    @livewireScripts
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

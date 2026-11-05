@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
@stop

@section('title', 'Mis Representados - Inscripción por Prosecución')

@section('content_header')
    <div class="content-header-modern">
        {{-- Breadcrumbs --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0; font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" style="color: var(--primary); text-decoration: none;"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('portal-representante.index') }}" style="color: var(--primary); text-decoration: none;">Portal del Representante</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="color: var(--gray-700); font-weight: 700;">Inscripción por Prosecución</li>
            </ol>
        </nav>

        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h1 class="title-main">Inscripción por Prosecución</h1>
                    <p class="title-subtitle">Historial de inscripciones de representados y nuevo registro</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal-representante.index') }}" class="btn-create" style="background: var(--gray-500);">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Menú</span>
                </a>
                <a href="{{ route('portal-representante.prosecucion.create') }}" class="btn-create {{ !$anioEscolarActivo ? 'disabled' : '' }}" 
                   @if(!$anioEscolarActivo) style="pointer-events: none; opacity: 0.6;" @endif>
                    <i class="fas fa-plus"></i>
                    <span>Nueva Inscripción</span>
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="main-container">
    {{-- Alerta si NO hay año escolar activo --}}
    @if (!$anioEscolarActivo)
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Atención: No hay Calendario Escolar activo</h5>
                    <p class="mb-0">
                        El proceso de inscripción se encuentra cerrado actualmente. No es posible crear nuevos registros de prosecución.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Tabla de Representados Inscritos --}}
    <div class="card-modern">
        <div class="card-header-modern d-flex align-items-center justify-content-between">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div>
                    <h3 class="mb-0">Inscripciones Realizadas</h3>
                    <p class="mb-0 text-muted">{{ count($inscripciones) }} alumnos registrados</p>
                </div>
            </div>
        </div>

        <div class="card-body-modern">
            <div class="table-wrapper">
                <table class="table-modern">
                    <thead>
                        <tr style="text-align: center">
                            <th style="text-align: center">Estudiante / Cédula</th>
                            <th style="text-align: center">Nivel Promovido</th>
                            <th style="text-align: center">Sección</th>
                            <th style="text-align: center">Fecha Registro</th>
                            <th style="text-align: center">Estado</th>
                            <th style="text-align: center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center">
                        @if (empty($inscripciones))
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-folder-open"></i>
                                        </div>
                                        <h4>No hay inscripciones registradas</h4>
                                        <p>Inicie una nueva inscripción por prosecución con el botón superior</p>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach ($inscripciones as $ins)
                                <tr style="text-align: center">
                                    <td class="tittle-main" style="font-weight: 700; text-align: left; padding-left: 2rem;">
                                        {{ $ins['estudiante'] }}
                                        <div style="font-size: 0.75rem; color: var(--gray-500); font-weight: normal;">V-{{ $ins['cedula'] }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border" style="font-size: 0.8rem; font-weight: 600;">{{ $ins['grado'] }}</span>
                                    </td>
                                    <td>
                                        Sección {{ $ins['seccion'] }}
                                    </td>
                                    <td>
                                        {{ $ins['fecha'] }}
                                    </td>
                                    <td>
                                        <span class="status-badge status-active">
                                            <span class="status-dot"></span>
                                            {{ $ins['estado'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons d-flex justify-content-center">
                                            <a href="{{ route('portal-representante.carnet.index') }}" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" title="Ver carnet estudiantil">
                                                <i class="fas fa-id-card text-success"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

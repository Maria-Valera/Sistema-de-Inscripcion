@extends('adminlte::page')

@section('title', 'Calendarios académicos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1 class="title-main">Calendarios académicos</h1>
                    <p class="title-subtitle">Gestión de calendarios académicos</p>
                </div>
            </div>

            @can('calendario_academico.gestionar')
                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                    <button class="btn-create" onclick="window.location='{{ route('admin.calendario_academico.create_manual') }}'">
                        <i class="fas fa-calendar-plus"></i> <span>Crear nuevo calendario</span>
                    </button>
                @endif
            @endcan

        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('partials.alerta-modo-consulta')

        {{-- Alertas modernas --}}
        @if (session('exito'))
            <div class="alerts-container">
                <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('exito') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

                @if (session('error'))
            <div class="alerts-container">
                <div class="alert-modern alert-danger alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="alert-content">
                        <h4>Error</h4>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alerts-container">
                <div class="alert-modern alert-danger alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="alert-content">
                        <h4>Revisa los datos</h4>
                        <p>{{ $errors->first() }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        <div class="card-modern">
            {{-- Cabecera de la tarjeta --}}
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Listado de Calendarios Académicos</h3>
                        <p class="mb-0 text-muted">{{ $calendarios->total() }} registros encontrados</p>
                    </div>
                </div>

            </div>

            {{-- Tabla moderna --}}
            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th style="width: 60px">#</th>
                                <th style="text-align: center">Año escolar</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center">Días registrados</th>
                                <th style="text-align: center; width: 120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($calendarios as $index => $calendario)
                                <tr>
                                    <td style="text-align: center">{{ $calendarios->firstItem() + $index }}</td>
                                    <td style="text-align: center">
                                        <strong>
                                            {{ \Carbon\Carbon::parse($calendario->anioEscolar->inicio_anio_escolar)->format('Y') }} -
                                            {{ \Carbon\Carbon::parse($calendario->anioEscolar->cierre_anio_escolar)->format('Y') }}
                                        </strong>
                                    </td>
                                    <td style="text-align: center">
                                        @if ($calendario->estaConfirmado())
                                            <span class="status-badge status-active">
                                                <span class="status-dot"></span> Confirmado
                                            </span>
                                        @else
                                            <span class="status-badge status-inactive">
                                                <span class="status-dot"></span> Pendiente de revisión
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: center">{{ $calendario->dias_count }}</td>
                                    <td style="text-align: center">
                                        <div class="action-buttons">
                                            <div class="dropdown dropstart text-center">
                                                <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <a href="{{ route('admin.calendario_academico.show', $calendario) }}"
                                                            class="dropdown-item d-flex align-items-center text-success">
                                                            <i class="fas fa-calendar-check me-2"></i> Revisar eventos
                                                        </a>
                                                    </li>

                                                                                                                                                            @can('calendario_academico.gestionar')
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <button type="button" class="dropdown-item d-flex align-items-center text-warning"
                                                                    data-bs-toggle="modal" data-bs-target="#editarCalendario{{ $calendario->id }}">
                                                                    <i class="fas fa-pen me-2"></i> Editar estado                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item d-flex align-items-center text-danger"
                                                                    data-bs-toggle="modal" data-bs-target="#eliminarCalendario{{ $calendario->id }}">
                                                                    <i class="fas fa-trash-alt me-2"></i> Eliminar
                                                                </button>
                                                            </li>
                                                        @endif
                                                    @endcan

                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-calendar-plus"></i>
                                            </div>
                                            <h4>No hay calendarios cargados</h4>
                                            <p>Crea un nuevo calendario académico de forma manual.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


                        {{-- Modales de editar y eliminar (fuera de la tabla, uno por calendario) --}}
            @can('calendario_academico.gestionar')
                @foreach ($calendarios as $calendario)
                    @include('calendario_academico.modales.editModal')
                    @include('calendario_academico.modales.deleteModal')
                @endforeach
            @endcan

            {{-- Pie de tarjeta --}}
            <div class="card-footer-modern d-flex flex-wrap align-items-center justify-content-between">

                <div>
                    {{ $calendarios->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

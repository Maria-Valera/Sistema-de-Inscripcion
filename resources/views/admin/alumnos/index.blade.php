@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop
@section('title', 'Gestión de alumnos')
@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de alumnos</h1>
                    <p class="title-subtitle">Administración de los alumnos</p>
                </div>
            </div>
        </div>
    </div>
@stop
@section('content')
    <div class="main-container">
        @if (!$anioEscolarActivo)
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Atención: No hay Calendario Escolar activo</h5>
                        <p class="mb-0">
                            Puedes ver los registros, pero <strong>no podrás crear, editar o eliminar</strong> alumnos hasta
                            que se registre un Calendario Escolar activo.
                            <a href="{{ route('admin.anio_escolar.index') }}" class="alert-link">Ir a Calendario Escolar</a>
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if (session('success') || session('error'))
            <div class="alerts-container">
                @if (session('success'))
                    <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Éxito</h4>
                            <p>{{ session('success') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Error</h4>
                            <p>{{ session('error') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            </div>
        @endif
        <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Listado de alumnos</h3>
                        <p class="mb-0 text-muted">{{ $alumnos->total() }} registros encontrados</p>
                    </div>
                </div>
                <div class="header-right d-flex align-items-center gap-2 flex-wrap">
                    <form action="{{ route('admin.alumnos.index') }}" class="mb-0 search-sm">
                        <div class="search-modern">
                            <i class="fas fa-search"></i>
                            <input type="text" title="Buscar por cédula, nombre, apellido" name="buscar" id="buscar"
                                class="form-control-modern" placeholder="Buscar por cédula, nombre, apellido"
                                value="{{ request('buscar') }}">
                        </div>
                    </form>
                    <button class="btn-filtro"  data-bs-toggle="modal"
                        data-bs-target="#modalFiltros">
                        <i class="fas fa-filter"></i>
                    </button>

                    <a href="{{ route('admin.alumnos.reporteGeneralPDF', [
                        'genero' => request('genero'),
                        'tipo_documento' => request('tipo_documento'),
                        'estatus' => request('estatus', 'Activo'),
                    ]) }}"
                        target="_blank" class="btn-pdf">
                        <i class="fas fa-file-pdf"></i> PDF General
                    </a>
                    @php
                        $anioActivo = \App\Models\AnioEscolar::activos()->first();
                        $anioExtendido = \App\Models\AnioEscolar::where('status', 'Extendido')->first();
                        $mostrarAnio = $anioActivo ?? $anioExtendido;
                    @endphp
                    @if ($mostrarAnio)
                        <div class="d-flex align-items-center bg-light rounded px-2 py-1 border">
                            <span class="badge bg-primary me-2" style="font-size: 0.7rem;">
                                <i class="fas fa-calendar-check me-1"></i> Calendario Escolar
                            </span>
                            <span class="text-muted me-2" style="font-size: 0.8rem;">
                                <i class="fas fa-play-circle text-primary me-1"></i>
                                {{ \Carbon\Carbon::parse($mostrarAnio->inicio_anio_escolar)->format('d/m/Y') }}
                            </span>
                            <span class="text-muted" style="font-size: 0.8rem;">
                                <i class="fas fa-flag-checkered text-danger me-1"></i>
                                {{ \Carbon\Carbon::parse($mostrarAnio->cierre_anio_escolar)->format('d/m/Y') }}
                            </span>
                        </div>
                    @else
                        <div
                            class="d-flex align-items-center bg-warning bg-opacity-10 rounded px-2 py-1 border border-warning">
                            <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                            <span class="fw-semibold" style="font-size: 0.8rem;">Sin año activo</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern overflow-hidden ">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center">Cédula</th>
                                <th style="text-align: center">Nombres</th>
                                <th style="text-align: center">Edad</th>
                                <th style="text-align: center">Genero</th>
                                <th style="text-align: center">Lateralidad</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @if ($alumnos->isEmpty())
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h4>No hay alumnos registrados</h4>
                                            <p>Agrega un nuevo alumno con el botón superior</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($alumnos as $index => $datos)
                                    <tr class="  row-12" style="text-align: center">
                                        <td class="tittle-main" style="font-weight: 700">
                                            {{ $datos->persona->tipoDocumento->nombre }}-{{ $datos->persona->numero_documento }}
                                        </td>
                                        <td style="text-align: center">
                                            {{ $datos->persona->primer_nombre }} {{ $datos->persona->primer_apellido }}
                                        </td>
                                        <td style="text-align: center">
                                            {{ $datos->persona->fecha_nacimiento->age }}
                                        </td>
                                        <td>
                                            {{ $datos->persona->genero->genero }}
                                        </td>
                                        <td style="text-align: center">
                                            {{ $datos->lateralidad->lateralidad }}
                                        </td>
                                        <td>
                                            @if ($datos->status)
                                                <span class="status-badge status-active">
                                                    <span class="status-dot"></span>
                                                    Activo
                                                </span>
                                            @else
                                                <span class="status-badge status-inactive">
                                                    <span class="status-dot"></span>
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <div class="dropdown dropstart text-center">
                                                    <button
                                                        class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <button
                                                                class="dropdown-item d-flex align-items-center text-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModal{{ $datos->id }}"
                                                                title="Ver detalles">
                                                                <i class="fas fa-eye me-2"></i>
                                                                Ver más
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.alumnos.edit', $datos->id) }}"
                                                                class="dropdown-item d-flex align-items-center text-warning"
                                                                title="Editar">
                                                                <i class="fas fa-edit me-2"></i>
                                                                Editar
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button
                                                                class="dropdown-item d-flex align-items-center text-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                                @disabled(!$anioEscolarActivo) title="Inactivar">
                                                                <i class="fas fa-ban me-2"></i>
                                                                Inactivar
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.alumnos.reporte.individual', ['id' => $datos->id]) }}"
                                                                class="dropdown-item d-flex align-items-center text-danger"
                                                                title="Generar reporte PDF" target="_blank">
                                                                <i class="fas fa-file-pdf"></i>
                                                                PDF
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @include('admin.alumnos.modales.showModal')
                                    <div class="modal fade" id="confirmarEliminar{{ $datos->id }}" tabindex="-1"
                                        aria-labelledby="modalLabel{{ $datos->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content modal-modern">
                                                <div class="modal-header-delete">
                                                    <div class="modal-icon-delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </div>
                                                    <h5 class="modal-title-delete">Confirmar Eliminación</h5>
                                                    <button type="button" class="btn-close-modal"
                                                        data-bs-dismiss="modal" aria-label="Cerrar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="modal-body-delete">
                                                    <p>¿Deseas eliminar a este alumno?</p>
                                                    <p class="delete-warning">
                                                        Esta acción no se puede deshacer.
                                                    </p>
                                                </div>
                                                <div class="modal-footer-delete">
                                                    <form action="{{ route('admin.alumnos.destroy', $datos->id) }}"
                                                        method="POST" class="w-100">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="footer-buttons">
                                                            <button type="button" class="btn-modal-cancel"
                                                                data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit"
                                                                class="btn-modal-delete">Eliminar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">
                <x-pagination :paginator="$alumnos" />
            </div>
        </div>
    </div>
@endsection
@include('admin.alumnos.modales.filtroModal')

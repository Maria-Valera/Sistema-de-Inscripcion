@extends('adminlte::page')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop
@section('title', 'Gestión de Asignación de Niveles Academicos a Áreas de Formación')
@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <h1 class="title-main">Asignación de Niveles Academicos a Áreas de Formación</h1>
                    <p class="title-subtitle">Gestión de vínculos entre niveles academicos y áreas formativas</p>
                </div>
            </div>
            <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearAsignacion"
                @if (!$anioEscolarActivo) disabled @endif
                title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Nueva Asignación' }}">
                <i class="fas fa-plus"></i>
                <span>Nueva Asignación</span>
            </button>
        </div>
    </div>
@stop
@section('content')
    <div class="main-container">
        @include('admin.transacciones.grado_area_formacion.modales.createModal')
        @if (!$anioEscolarActivo)
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Atención: No hay Calendario Escolar activo</h5>
                        <p class="mb-0">
                            Puedes ver los registros, pero <strong>no podrás crear, editar o eliminar</strong> bancos hasta
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
                            <h4>¡Éxito!</h4>
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
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3>Listado de Asignaciones</h3>
                        <p>{{ $gradoAreaFormacion->count() }} registros encontrados</p>
                    </div>
                </div>
                <div class="header-right">
                    @php
                        $anioActivo = \App\Models\AnioEscolar::activos()->first();
                        $anioExtendido = \App\Models\AnioEscolar::where('status', 'Extendido')->first();
                        $mostrarAnio = $anioActivo ?? $anioExtendido;
                    @endphp
                    @if ($mostrarAnio)
                        <div
                            class="d-flex align-items-center justify-content-between bg-light rounded px-2 py-1 mb-2 border">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary rounded me-2 py-1 px-2" style="font-size: 0.7rem;">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    Calendario Escolar
                                </span>
                                <div class="d-flex align-items-center" style="font-size: 0.8rem;">
                                    <span class="text-muted me-2">
                                        <i class="fas fa-play-circle text-primary me-1"></i>
                                        {{ \Carbon\Carbon::parse($mostrarAnio->inicio_anio_escolar)->format('d/m/Y') }}
                                    </span>
                                    <span class="text-muted me-2">
                                        <i class="fas fa-flag-checkered text-danger me-1"></i>
                                        {{ \Carbon\Carbon::parse($mostrarAnio->cierre_anio_escolar)->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div
                            class="d-flex align-items-center justify-content-between bg-warning bg-opacity-10 rounded px-2 py-1 mb-2 border border-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle text-warning me-1" style="font-size: 0.8rem;"></i>
                                <span class="fw-semibold" style="font-size: 0.8rem;">Sin año activo</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern overflow-hidden  " style="text-align: center">
                        <thead>
                            <tr>
                                <th style="text-align: center">Código</th>
                                <th>Niveles academicos</th>
                                <th>Área de Formación</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @forelse ($gradoAreaFormacion as $index => $datos)
                                <tr class="  ">
                                    <td style="text-align: center">
                                        <div class="number-badge" style="padding: 0.5rem 1rem; min-width: 200px;">
                                            {{ $datos->codigo }}

                                        </div>
                                    </td>
                                    <td style="text-align: center">
                                        <div class="cell-content" style="text-align: center">
                                            <i class="fas fa-graduation-cap text-primary me-2"></i>
                                            <span class="fw-semibold">{{ $datos->grado->numero_grado ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cell-content">
                                            <i class="fas fa-bookmark text-secondary me-2"></i>
                                            <span>{{ $datos->area_formacion->nombre_area_formacion ?? '—' }}</span>
                                        </div>
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
                                                <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <button
                                                            class="dropdown-item d-flex align-items-center text-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewModalEditar{{ $datos->id }}"
                                                            title="Editar"
                                                            @if (!$anioEscolarActivo) disabled @endif
                                                            title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Editar' }}">
                                                            <i class="fas fa-pen me-2"></i>
                                                            Editar
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item d-flex align-items-center text-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                            @disabled(!$anioEscolarActivo) title="Inactivar Calendario Escolar">
                                                            <i class="fas fa-ban me-2"></i>
                                                            Inactivar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @include('admin.transacciones.grado_area_formacion.modales.editModal')
                                <div class="modal fade" id="confirmarEliminar{{ $datos->id }}" tabindex="-1"
                                    aria-labelledby="confirmarEliminarLabel{{ $datos->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-modern">
                                            <div class="modal-header-delete">
                                                <div class="modal-icon-delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </div>
                                                <h5 class="modal-title-delete"
                                                    id="confirmarEliminarLabel{{ $datos->id }}">Confirmar Inactivación
                                                </h5>
                                                <button type="button" class="btn-close-modal" data-bs-dismiss="modal"
                                                    aria-label="Close">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body-delete">
                                                <p class="delete-message">¿Estás seguro de que deseas inactivar esta
                                                    asignación?</p>
                                                <p class="delete-warning">
                                                </p>
                                            </div>
                                            <div class="modal-footer-delete">
                                                <form
                                                    action="{{ url('admin/transacciones/grado_area_formacion/' . $datos->id) }}"
                                                    method="POST" class="w-100">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="footer-buttons">
                                                        <button type="button" class="btn-modal-cancel"
                                                            data-bs-dismiss="modal">
                                                            Cancelar
                                                        </button>
                                                        <button type="submit" class="btn-modal-delete">
                                                            <i class="fas fa-trash me-1"></i>
                                                            Inactivar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h4>No hay asignaciones registradas</h4>
                                            <p>Comienza creando una nueva asignación usando el botón superior</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <x-pagination :paginator="$gradoAreaFormacion" />
@endsection

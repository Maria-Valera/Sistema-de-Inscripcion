@extends('adminlte::page')

@section('title', 'Gestión de Áreas de Formación')

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
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <h1 class="title-main">Áreas de Formación</h1>
                    <p class="title-subtitle">Gestión de asignaciones y títulos universitarios</p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearAsignacion"
                    title="Nueva Asignación">
                    <i class="fas fa-plus"></i>
                    <span>Nueva Asignación</span>
                </button>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">

        {{-- Incluir modal de creación --}}
        @include('admin.transacciones.area_estudio_realizado.modales.createModal')
        @include('partials.alerta-modo-consulta')

        {{-- Alertas --}}
        @if (session('success') || session('error'))
            <div class="alerts-container">
                @if (session('success'))
                    <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="alert-content">
                            <h4>¡Éxito!</h4>
                            <p>{{ session('success') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar"><i
                                class="fas fa-times"></i></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div class="alert-content">
                            <h4>Error</h4>
                            <p>{{ session('error') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar"><i
                                class="fas fa-times"></i></button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Contenedor de la tabla --}}
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon"><i class="fas fa-list-ul"></i></div>
                    <div>
                        <h3>Listado de Asignaciones</h3>
                        <p>{{ $areaEstudioRealizado->total() }} registros encontrados</p>
                    </div>
                </div>
                {{-- Buscador --}}
                <form action="{{ route('admin.transacciones.area_estudio_realizado.index') }}">
                    <div class="form-group-modern mb-2">
                        <div class="search-modern">
                            <i class="fas fa-search"></i>
                            <input type="text" name="buscar" id="buscar" class="form-control-modern"
                                placeholder="Buscar..." value="{{ request('buscar') }}">
                        </div>
                        <small class="form-text-modern" style="margin-top: 0.5rem; color: var(--gray-500);  ">
                            <i class="fas fa-info-circle"></i>
                            Buscar por nombre de área de formación
                        </small>
                    </div>
                </form>

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
                    <!-- --------------------------- -->

                </div>

            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern overflow-hidden" style="text-align: center">
                        <thead>
                            <tr>
                                <th style="text-align: center; font-weight: bold">Área de Formación</th>
                                <th style="text-align: center; font-weight: bold">Título Universitario</th>
                                <th style="text-align: center; ">Estado</th>
                                <th style="text-align: center; ">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($areaEstudioRealizado as $index => $datos)
                                <tr class=" ">
                                    <td style="font-weight: bold">
                                        {{ $datos->areaFormacion->nombre_area_formacion ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $datos->estudiosRealizado->estudios ?? '—' }}
                                    </td>
                                    <td>
                                        @if ($datos->status)
                                            <span class="status-badge status-active">Activo</span>
                                        @else
                                            <span class="status-badge status-inactive">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- BOTONES DE ACCIONES --}}
                                        <div class="action-buttons">
                                            <div class="dropdown dropstart text-center">
                                                <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    {{-- Editar --}}
                                                    @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                        <li>
                                                            <button
                                                                class="dropdown-item d-flex align-items-center text-warning"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModalEditar{{ $datos->id }}"
                                                                title="Editar">
                                                                <i class="fas fa-pen me-2"></i>
                                                                Editar
                                                            </button>
                                                        </li>

                                                        {{-- Inactivar --}}
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                                title="Inactivar">
                                                                <i class="fas fa-ban me-2"></i>
                                                                Inactivar
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                @include('admin.transacciones.area_estudio_realizado.modales.editModal', [
                                    'datos' => $datos,
                                    'area_formacion' => $area_formacion,
                                    'estudios' => $estudios,
                                ])

                                {{-- Modal Eliminar --}}
                                <div class="modal fade" id="confirmarEliminar{{ $datos->id }}" tabindex="-1"
                                    aria-labelledby="confirmarEliminarLabel{{ $datos->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-modern">
                                            <div class="modal-header-delete">
                                                <div class="modal-icon-delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </div>
                                                <h5 class="modal-title-delete"
                                                    id="confirmarEliminarLabel{{ $datos->id }}">Confirmar Inactivacion
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
                                                    action="{{ url('admin/transacciones/area_estudio_realizado/' . $datos->id) }}"
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
                                    <td colspan="5" class="text-center">No hay asignaciones registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}

            </div>
        </div>


    </div>
    <x-pagination :paginator="$areaEstudioRealizado" />
@endsection

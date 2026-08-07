@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('title', 'Gestión de Etnias Indígenas')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Etnias Indígenas</h1>
                    <p class="title-subtitle">Administración de las etnias indígenas</p>
                </div>
            </div>
            <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrear"
                @if (!$anioEscolarActivo) disabled @endif
                title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Nueva Etnia Indígena' }}">
                <i class="fas fa-plus"></i>
                <span>Nueva Etnia Indígena</span>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('admin.etnia_indigena.modales.createModal')
        @if (!$anioEscolarActivo)
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Atención: No hay Calendario Escolar activo</h5>
                        <p class="mb-0">
                            Puedes ver los registros, pero <strong>no podrás crear, editar o eliminar</strong> hasta que se
                            registre un Calendario Escolar activo.
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
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3>Listado de Etnias Indígenas</h3>
                        <p>{{ $etniaIndigena->total() }} registros encontrados</p>
                    </div>
                </div>
                <form action="{{ route('admin.etnia_indigena.index') }}">
                    <div class="form-group-modern mb-2">
                        <div class="search-modern">
                            <i class="fas fa-search"></i>
                            <input type="text" name="buscar" id="buscar" class="form-control-modern"
                                placeholder="Buscar..." value="{{ request('buscar') }}">
                        </div>
                        <small class="form-text-modern" style="margin-top: 0.5rem; color: var(--gray-500);">
                            <i class="fas fa-info-circle"></i>
                            Buscar por nombre de etnia
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
                        <div class="d-flex align-items-center justify-content-between bg-light rounded px-2 py-1  border">
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
                            class="d-flex align-items-center justify-content-between bg-warning bg-opacity-10 rounded px-2 py-1  border border-warning">
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
                    <table class="table-modern overflow-hidden ">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center">#</th>
                                <th style="text-align: center">Etnia</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @if ($etniaIndigena->isEmpty())
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h4>No hay Etnias Indigenas registradas</h4>
                                            <p>Agrega una nueva Etnia Indigena con el botón superior</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($etniaIndigena as $index => $datos)
                                    <tr class="  row-12" style="text-align: center">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="title-main">{{ $datos->nombre }}</td>
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
                                                <button class="action-btn btn-edit" data-bs-toggle="modal"
                                                    data-bs-target="#viewModalEditar{{ $datos->id }}" title="Editar"
                                                    @if (!$anioEscolarActivo) disabled @endif
                                                    title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Editar' }}">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button class="action-btn btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                    title="Eliminar" @if (!$anioEscolarActivo) disabled @endif
                                                    title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Eliminar' }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                        </td>
                                    </tr>
                                    @include('admin.etnia_indigena.modales.editModal')
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
                                                    <p>¿Deseas eliminar esta Etnia Indigena?</p>
                                                    <p class="delete-warning">
                                                        Esta acción no se puede deshacer.
                                                    </p>
                                                </div>
                                                <div class="modal-footer-delete">
                                                    <form action="{{ url('admin/etnia_indigena/' . $datos->id) }}"
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
        </div>
    </div>
    <x-pagination :paginator="$etniaIndigena" />
@endsection

@push('js')
    <script src="{{ asset('js/validations/etnia_indigena.js') }}"></script>
@endpush

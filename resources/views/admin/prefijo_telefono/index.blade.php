@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('title', 'Gestión de Prefijos de Telefono')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Prefijos de Telefono</h1>
                    <p class="title-subtitle">Administración de los prefijos</p>
                </div>
            </div>
            <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrear"
                @if (!$anioEscolarActivo) disabled @endif
                title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Nuevo Prefijo' }}">
                <i class="fas fa-plus"></i>
                <span>Nuevo Prefijo</span>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('admin.prefijo_telefono.modales.createModal')

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
                        <h3>Listado de Prefijos</h3>
                        <p>{{ $prefijos->total() }} registros encontrados</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern overflow-hidden ">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center">Prefijo</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @if ($prefijos->isEmpty())
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h4>No hay prefijos registrados</h4>
                                            <p>Agrega un nuevo prefijos con el botón superior</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($prefijos as $index => $datos)
                                    <tr class="  row-12" style="text-align: center">
                                        <td class="title-main">{{ $datos->prefijo }}</td>
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
                                                    title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Editar Prefijo' }}">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button class="action-btn btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                    title="Eliminar" @if (!$anioEscolarActivo) disabled @endif
                                                    title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Eliminar Prefijo' }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                        </td>
                                    </tr>
                                    @include('admin.prefijo_telefono.modales.editModal')

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
                                                    <p>¿Deseas eliminar este prefijo?</p>
                                                    <p class="delete-warning">
                                                        Esta acción no se puede deshacer.
                                                    </p>
                                                </div>
                                                <div class="modal-footer-delete">
                                                    <form action="{{ url('admin/prefijo_telefono/' . $datos->id) }}"
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
    <x-pagination :paginator="$prefijos" />
@endsection

@section('js')
    <script src="{{ asset('js/validations/prefijoTelefono.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection

@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">

@stop

@section('title', 'Gestión de Niveles Academicos')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Niveles Academicos</h1>
                    <p class="title-subtitle">Administración de los Niveles académicos</p>
                </div>
            </div>
            <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearGrado"
                @if (!$anioEscolarActivo) disabled @endif
                title="{{ !$anioEscolarActivo ? 'Debe registrar un año escolar activo' : 'Nuevo Nivel' }}">
                <i class="fas fa-plus"></i>
                <span>Nuevo Nivel</span>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('admin.grado.modales.createModal')
        @if (!$anioEscolarActivo)
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Atención: No hay año escolar activo</h5>
                        <p class="mb-0">
                            Puedes ver los registros, pero <strong>no podrás crear, editar o eliminar</strong> hasta que se
                            registre un año escolar activo.
                            <a href="{{ route('admin.anio_escolar.index') }}" class="alert-link">Ir a Año Escolar</a>
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
                        <h3>Listado de Niveles</h3>
                        <p>{{ $grados->total() }} registros encontrados</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern overflow-hidden ">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center">Nivel Academico</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @if ($grados->isEmpty())
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h4>No hay años registrados</h4>
                                            <p>Agrega un nuevo año con el botón superior</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($grados as $index => $datos)
                                    <tr class="  row-12" style="text-align: center">
                                        <td class="title-main">{{ $datos->numero_grado }}</td>
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
                                                                class="dropdown-item d-flex align-items-center text-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModal{{ $datos->id }}"
                                                                title="Ver mas"
                                                                @if (!$anioEscolarActivo) disabled @endif
                                                                title="{{ !$anioEscolarActivo ? 'Debe registrar un año escolar activo' : 'Ver mas' }}">
                                                                <i class="fas fa-eye me-2"></i>
                                                                Ver mas
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button
                                                                class="dropdown-item d-flex align-items-center text-warning"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModalEditar{{ $datos->id }}"
                                                                title="Editar"
                                                                @if (!$anioEscolarActivo) disabled @endif
                                                                title="{{ !$anioEscolarActivo ? 'Debe registrar un año escolar activo' : 'Editar' }}">
                                                                <i class="fas fa-pen me-2"></i>
                                                                Editar
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button"
                                                                class="dropdown-item d-flex align-items-center text-danger btn-inactivar-grado"
                                                                data-tiene-estudiantes="{{ $datos->inscripciones_count > 0 ? '1' : '0' }}"
                                                                data-modal-id="confirmarEliminar{{ $datos->id }}"
                                                                @disabled(!$anioEscolarActivo)>
                                                                <i class="fas fa-ban me-2"></i>
                                                                Inactivar
                                                            </button>

                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @include('admin.grado.modales.showModal')
                                    @include('admin.grado.modales.editModal')
                                    <div class="modal fade" id="confirmarEliminar{{ $datos->id }}" tabindex="-1"
                                        aria-labelledby="modalLabel{{ $datos->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content modal-modern">
                                                <div class="modal-header-delete">
                                                    <div class="modal-icon-delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </div>
                                                    <h5 class="modal-title-delete">Confirmar Inactivacion</h5>
                                                    <button type="button" class="btn-close-modal"
                                                        data-bs-dismiss="modal" aria-label="Cerrar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="modal-body-delete">
                                                    <p>¿Deseas inactivar este nivel academico?</p>
                                                    <p class="delete-warning">
                                                    </p>
                                                </div>
                                                <div class="modal-footer-delete">
                                                    <form action="{{ url('admin/grado/' . $datos->id) }}" method="POST"
                                                        class="w-100">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="footer-buttons">
                                                            <button type="button" class="btn-modal-cancel"
                                                                data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit"
                                                                class="btn-modal-delete">Inactivar</button>
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

    <x-pagination :paginator="$grados" />

    @push('js')
        <script src="{{ asset('js/validations/grado.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                document.querySelectorAll('.btn-inactivar-grado').forEach(btn => {

                    btn.addEventListener('click', function() {

                        const tieneEstudiantes = this.dataset.tieneEstudiantes === '1';
                        const modalId = this.dataset.modalId;

                        if (tieneEstudiantes) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'No permitido',
                                html: 'Este <b>nivel académico</b> tiene estudiantes inscritos.<br><br>' +
                                    'No puede ser inactivado mientras tenga estudiantes inscritos.',
                                confirmButtonText: 'Entendido'
                            });
                            return;
                        }

                        const modal = new bootstrap.Modal(
                            document.getElementById(modalId)
                        );
                        modal.show();
                    });

                });

            });
        </script>
    @endpush

@endsection

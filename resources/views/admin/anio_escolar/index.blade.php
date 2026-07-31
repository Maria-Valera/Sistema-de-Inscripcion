@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('js/anio-escolar/anio-escolar-create.js') }}"></script>
    <script src="{{ asset('js/anio-escolar/anio-escolar-extender.js') }}"></script>
@stop


@section('title', 'Gestión de Calendarios Escolares')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Calendarios Escolares</h1>
                    <p class="title-subtitle">Administración de periodos académicos</p>
                </div>
            </div>
            <button type="button" id="btnNuevoAnioEscolar" class="btn-create">
                <i class="fas fa-plus"></i>
                <span>Nuevo Calendario Escolar</span>
            </button>
        </div>
    </div>
@stop

@section('content')
    @include('admin.anio_escolar.modales.createModal')

    @php
        $anioEscolarActivo = \App\Models\AnioEscolar::activos()->exists();
    @endphp
    @if (!$anioEscolarActivo)
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">No hay Calendario Escolar activo</h5>
                    <p class="mb-0">Debe registrar un Calendario Escolar activo para poder utilizar los demás módulos del
                        sistema.</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('success') || session('error') || session('warning'))
        <div class="alerts-container mb-3">
            @if (session('success'))
                <div class="alert-modern alert-success alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-modern alert-error alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Error</h4>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert-modern alert-warning alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Advertencia</h4>
                        <p>{{ session('warning') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
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
                    <h3>Listado de Calendarios Escolares</h3>
                    <p>{{ $escolar->total() }} registros encontrados</p>
                </div>
            </div>
        </div>

        <div class="card-body-modern">
            <div class="table-wrapper">
                <table class="table-modern overflow-hidden ">
                    <thead>
                        <tr style="text-align: center">
                            <th width="60">#</th>
                            <th style="text-align: center">Inicio</th>
                            <th style="text-align: center">Cierre</th>
                            <th style="text-align: center">Duración</th>
                            <th style="text-align: center">Estado</th>
                            <th style="text-align: center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody style="text-align: center">
                        @forelse ($escolar as $index => $datos)
                            <tr class=" row-12" style="text-align: center">
                                <td>{{ $escolar->firstItem() + $index }}</td>
                                <td>
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                    <span
                                        class="fw-semibold">{{ \Carbon\Carbon::parse($datos->inicio_anio_escolar)->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                    <span
                                        class="fw-semibold">{{ \Carbon\Carbon::parse($datos->cierre_anio_escolar)->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    <span class="badge badgebg-info text-dark">
                                        {{ $datos->duracion_meses }} meses
                                    </span>
                                </td>
                                <td>
                                    @if ($datos->status == 'Activo')
                                        <span class="status-badge status-active">
                                            <span class="status-dot"></span>
                                            Activo
                                        </span>
                                    @elseif ($datos->status == 'Extendido')
                                        <span class="status-badge status-extended">
                                            <span class="status-dot"></span>
                                            Extendido
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
                                                    <button class="dropdown-item d-flex align-items-center text-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewModal{{ $datos->id }}"
                                                        title="Ver detalles del Calendario Escolar">
                                                        <i class="fas fa-eye me-2"></i>
                                                        Ver más
                                                    </button>
                                                </li>

                                                <li>
                                                    @if (in_array($datos->status, ['Activo', 'Extendido']))
                                                        <button class="dropdown-item d-flex align-items-center text-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewModalExtender{{ $datos->id }}"
                                                            title="Extender Calendario Escolar">
                                                            <i class="fas fa-calendar-plus me-2"></i>
                                                            Extender
                                                        </button>
                                                    @endif
                                                </li>

                                                <li>
                                                    @if (in_array($datos->status, ['Activo', 'Extendido']))
                                                        <button class="dropdown-item d-flex align-items-center text-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal{{ $datos->id }}"
                                                            @disabled(!$anioEscolarActivo) title="Inactivar Calendario Escolar">
                                                            <i class="fas fa-ban me-2"></i>
                                                            Inactivar
                                                        </button>
                                                    @endif
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            @include('admin.anio_escolar.modales.extenderModal')
                            @include('admin.anio_escolar.modales.showModal')

                            <div class="modal fade" id="deleteModal{{ $datos->id }}" tabindex="-1"
                                aria-labelledby="deleteModalLabel{{ $datos->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content modal-modern">
                                        <div class="modal-header-delete">
                                            <div class="modal-icon-delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </div>
                                            <h5 class="modal-title-delete" id="deleteModalLabel{{ $datos->id }}">
                                                Confirmar Inactivación</h5>
                                            <button type="button" class="btn-close-modal" data-bs-dismiss="modal"
                                                aria-label="Close">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="modal-body-delete">
                                            <p class="delete-message">¿Estás seguro de que deseas inactivar este año
                                                escolar?</p>
                                            <p class="delete-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Esta acción marcará el año como inactivo
                                            </p>
                                        </div>
                                        <div class="modal-footer-delete">
                                            <form action="{{ route('admin.anio_escolar.destroy', $datos->id) }}"
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
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <h4>No hay Calendarios Escolares registrados</h4>
                                        <p>Comienza creando un nuevo Calendario Escolar usando el botón superior</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <x-pagination :paginator="$escolar" />
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const anioEscolarActivo = @json($anioEscolarActivo);
        const btn = document.getElementById('btnNuevoAnioEscolar');

        if (!btn) return;

        btn.addEventListener('click', function() {

            if (anioEscolarActivo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Calendario Escolar activo',
                    html: 'Ya existe un <b>Calendario Escolar activo o extendido</b>.<br><br>' +
                        'Debe cerrar o finalizar el año actual antes de crear uno nuevo.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            const modal = new bootstrap.Modal(
                document.getElementById('modalCrearAnioEscolar')
            );
            modal.show();
        });
    });
</script>

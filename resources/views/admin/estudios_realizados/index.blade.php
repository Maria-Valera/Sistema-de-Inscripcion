@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Gestión de Estudios Realizados')

@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Gestión de Estudios Realizados</h1>
                    <p class="title-subtitle mb-0">Administración de los estudios y títulos académicos</p>
                </div>
            </div>

            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button" class="btn-hero-white w-100 w-md-auto justify-content-center mt-3 mt-md-0" data-bs-toggle="modal" data-bs-target="#modalCrearEstudio"
                    title="Nuevo Estudio Realizado">
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Estudio Realizado</span>
                </button>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">

        @include('admin.estudios_realizados.modales.createModal')
        @include('partials.alerta-modo-consulta')

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

        <div class="card-modern shadow-sm">
            <div class="card-header-modern d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de Estudios Realizados</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $estudiosRealizados->total() }} registros encontrados</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                    <form action="{{ route('admin.estudios_realizados.index') }}" method="GET" class="m-0 w-100 w-md-auto">
                        <div class="search-wrapper position-relative w-100 w-md-auto" style="min-width: 260px;">
                            <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                            <input type="text" name="buscar" id="buscar" class="form-control search-input-modern pl-5 w-100 w-md-auto"
                                placeholder="Buscar estudio..." value="{{ request('buscar') }}"
                                style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body-modern p-0">
                <div class="table-responsive">
                    <table class="table-modern table-striped text-nowrap" id="tablaEstudiosRealizados">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 80px; text-align: center;">#</th>
                                <th style="text-align: left;">Estudio Realizado</th>
                                <th style="width: 140px; text-align: center;">Estado</th>
                                <th style="width: 100px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @if ($estudiosRealizados->isEmpty())
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state text-center py-5">
                                            <div class="empty-icon text-muted mb-2 opacity-50">
                                                <i class="fas fa-user-graduate fa-3x"></i>
                                            </div>
                                            <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay estudios realizados registrados</h4>
                                            <p class="text-muted" style="font-size: 0.88rem;">Agrega uno nuevo con el botón superior.</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($estudiosRealizados as $index => $datos)
                                    <tr>
                                        <td class="text-muted" style="font-size: 0.85rem; text-align: center;">
                                            {{ $estudiosRealizados->firstItem() + $index }}
                                        </td>
                                        <td class="text-start fw-semibold text-dark">
                                            {{ $datos->estudios }}
                                        </td>
                                        <td class="text-nowrap text-center">
                                            @if ($datos->status)
                                                <span class="badge badge-success px-3 py-1">
                                                    <i class="fas fa-check-circle me-1" style="font-size: 0.75rem;"></i> Activo
                                                </span>
                                            @else
                                                <span class="badge badge-danger px-3 py-1">
                                                    <i class="fas fa-times-circle me-1" style="font-size: 0.75rem;"></i> Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap text-center">
                                            <div class="action-buttons justify-content-center">
                                                <div class="dropdown dropstart text-center">
                                                    <button class="btn btn-sm action-btn rounded-circle"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end shadow-sm">
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <button class="dropdown-item dropdown-item-modern text-warning d-flex align-items-center"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#viewModalEditar{{ $datos->id }}"
                                                                    title="Editar">
                                                                    <i class="fas fa-pen me-2 text-warning"></i>
                                                                    <span class="text-warning fw-medium">Editar</span>
                                                                </button>
                                                            </li>

                                                            <li>
                                                                <button class="dropdown-item dropdown-item-modern text-danger d-flex align-items-center"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                                    title="Inactivar Estudio">
                                                                    <i class="fas fa-ban me-2 text-danger"></i>
                                                                    <span class="text-danger fw-medium">Inactivar</span>
                                                                </button>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    @include('admin.estudios_realizados.modales.editModal')

                                    {{-- Modal Confirmación Inactivar --}}
                                    <div class="modal fade modal-danger-delete" id="confirmarEliminar{{ $datos->id }}" tabindex="-1"
                                        aria-labelledby="modalLabel{{ $datos->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                            <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
                                                <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                             style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                            <i class="fas fa-ban" style="font-size: 1.15rem;"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="modal-title m-0 fw-bold" id="modalLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                                                Confirmar Inactivación
                                                            </h5>
                                                            <p class="text-muted m-0" style="font-size: 0.82rem;">Acción sobre estudio realizado</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                                    <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                        ¿Deseas inactivar este estudio realizado?
                                                    </p>
                                                    <div class="p-3 d-flex align-items-center gap-3"
                                                         style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                             style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-weight: 700;">
                                                            <i class="fas fa-graduation-cap"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $datos->estudios }}</div>
                                                            <div class="text-muted" style="font-size: 0.8rem;">Registro #{{ $index + 1 }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                                                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                                        <i class="fas fa-times me-1"></i> Cancelar
                                                    </button>
                                                    <form action="{{ url('admin/estudios_realizados/' . $datos->id) }}"
                                                        method="POST" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn" style="background-color: #EF4444; color: #FFF; border-radius: 8px; font-weight: 600; padding: 9px 18px; border: none;">
                                                            <i class="fas fa-ban me-1"></i> Inactivar
                                                        </button>
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

        <x-pagination :paginator="$estudiosRealizados" />
    </div>

    @push('js')
        <script src="{{ asset('js/validations/estudios_realizados.js') }}"></script>
    @endpush

@endsection

@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Gestión de Docentes')

@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Gestión de Docentes</h1>
                    <p class="title-subtitle mb-0">Administración del personal docente y carga horaria</p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <a class="btn-hero-white w-100 w-md-auto justify-content-center mt-3 mt-md-0" href="{{ route('admin.docente.create') }}"
                    title="Registrar nuevo docente">
                    <i class="fas fa-plus"></i>
                    <span>Registrar</span>
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
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
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de Docentes</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $docentes->total() }} registros encontrados</p>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row align-items-center gap-2 w-100 w-md-auto">
                    <form action="{{ route('admin.docente.index') }}" method="GET" class="m-0 w-100 w-md-auto">
                        <div class="search-wrapper position-relative w-100 w-md-auto">
                            <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                            <input type="text" name="buscar" id="buscar" class="form-control search-input-modern pl-5 w-100 w-md-auto"
                                placeholder="Buscar docente..." value="{{ request('buscar') }}"
                                style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                        </div>
                    </form>

                    <a href="{{ route('admin.docente.reporteGeneralPDF') }}" target="_blank"
                       class="btn btn-outline-danger btn-pdf-outline d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto text-nowrap"
                       title="Exportar reporte general en PDF">
                        <i class="fas fa-file-pdf"></i>
                        <span>PDF General</span>
                    </a>
                </div>
            </div>

            <div class="card-body-modern p-0">
                <div class="table-responsive">
                    <table class="table-modern table-striped text-nowrap" id="tablaDocentes">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center; width: 130px;" class="text-nowrap">Cédula</th>
                                <th style="text-align: left;" class="text-nowrap">Nombre</th>
                                <th style="text-align: left;" class="text-nowrap">Apellido</th>
                                <th style="text-align: center; width: 110px;" class="d-none d-md-table-cell text-nowrap">Código</th>
                                <th style="text-align: left;" class="d-none d-md-table-cell text-nowrap">Correo</th>
                                <th style="text-align: center; width: 120px;" class="text-nowrap">Estado</th>
                                <th style="text-align: center; width: 100px;" class="text-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @if ($docentes->isEmpty())
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state text-center py-5">
                                            <div class="empty-icon text-muted mb-2 opacity-50">
                                                <i class="fas fa-chalkboard-teacher fa-3x"></i>
                                            </div>
                                            <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay docentes registrados</h4>
                                            <p class="text-muted" style="font-size: 0.88rem;">Agrega un nuevo docente con el botón superior.</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($docentes as $datos)
                                    <tr style="text-align: center">
                                        <td class="fw-bold text-dark text-nowrap" style="font-size: 0.92rem;">
                                            {{ $datos->persona->tipoDocumento->nombre ?? 'V' }}-{{ $datos->persona->numero_documento }}
                                        </td>
                                        <td class="text-start fw-semibold text-dark text-nowrap">{{ $datos->persona->primer_nombre }}</td>
                                        <td class="text-start fw-semibold text-dark text-nowrap">{{ $datos->persona->primer_apellido }}</td>
                                        <td class="d-none d-md-table-cell text-nowrap">
                                            <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.82rem; border: 1px solid #DBEAFE;">
                                                {{ $datos->codigo }}
                                            </span>
                                        </td>
                                        <td class="text-start text-muted d-none d-md-table-cell text-nowrap" style="font-size: 0.88rem;">
                                            <span class="d-inline-block text-truncate" style="max-width: 150px; vertical-align: middle;" title="{{ $datos->persona->email ?? 'Sin registrar' }}">
                                                {{ $datos->persona->email ?? 'Sin registrar' }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap">
                                            @if ($datos->status)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle me-1"></i> Activo
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times-circle me-1"></i> Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="action-buttons">
                                                <div class="dropdown dropstart text-center">
                                                    <button class="btn btn-sm action-btn rounded-circle"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                        title="Acciones">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <button class="dropdown-item dropdown-item-modern text-primary d-flex align-items-center"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModal{{ $datos->id }}"
                                                                title="Ver detalles">
                                                                <i class="fas fa-eye me-2 text-primary"></i>
                                                                <span class="text-primary fw-medium">Ver más</span>
                                                            </button>
                                                        </li>
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <a class="dropdown-item dropdown-item-modern text-warning d-flex align-items-center"
                                                                    href="{{ route('admin.docente.edit', $datos->id) }}"
                                                                    title="Editar">
                                                                    <i class="fas fa-pen me-2 text-warning"></i>
                                                                    <span class="text-warning fw-medium">Editar</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item dropdown-item-modern text-navy d-flex align-items-center"
                                                                    href="{{ route('admin.docente.disponibilidad', $datos->id) }}"
                                                                    title="Disponibilidad">
                                                                    <i class="fas fa-calendar-times me-2" style="color: #1E3A8A;"></i>
                                                                    <span class="fw-medium" style="color: #1E3A8A;">Disponibilidad</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item dropdown-item-modern item-delete text-danger d-flex align-items-center"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                                    title="Inactivar Docente">
                                                                    <i class="fas fa-ban me-2 text-danger"></i>
                                                                    <span class="text-danger fw-medium">Inactivar</span>
                                                                </button>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a class="dropdown-item dropdown-item-modern text-secondary item-pdf d-flex align-items-center"
                                                                href="{{ route('admin.docente.reportePDF', $datos->id) }}"
                                                                target="_blank" title="Generar reporte PDF">
                                                                <i class="fas fa-file-pdf me-2" style="color: #475569;"></i>
                                                                <span class="fw-medium" style="color: #475569;">PDF individual</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    @include('admin.docente.modales.showModal')

                                    {{-- Modal Confirmar Inactivación (Design System) --}}
                                    <div class="modal fade modal-danger-delete" id="confirmarEliminar{{ $datos->id }}" tabindex="-1"
                                        aria-labelledby="modalLabel{{ $datos->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                            <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: visible; background: #FFFFFF;">
                                                <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                             style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                            <i class="fas fa-ban" style="font-size: 1.15rem;"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="modal-title m-0 fw-bold" id="modalLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                                                Confirmar Inactivación
                                                            </h5>
                                                            <p class="text-muted m-0" style="font-size: 0.82rem;">Acción sobre personal docente</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>

                                                <form action="{{ url('admin/docente/' . $datos->id) }}" method="POST" class="w-100 m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                                        <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                            ¿Deseas inactivar a este docente?
                                                        </p>
                                                        <div class="p-3 d-flex align-items-center gap-3"
                                                             style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                                 style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-weight: 700;">
                                                                <i class="fas fa-user-tie"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                                                    {{ $datos->persona->primer_nombre }} {{ $datos->persona->primer_apellido }}
                                                                </div>
                                                                <div class="text-muted" style="font-size: 0.8rem;">
                                                                    C.I: {{ $datos->persona->tipoDocumento->nombre ?? 'V' }}-{{ $datos->persona->numero_documento }} | Código: {{ $datos->codigo }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                                                        <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-1"></i> Cancelar
                                                        </button>
                                                        <button type="submit" class="btn" style="background-color: #EF4444; color: #FFF; border-radius: 8px; font-weight: 600; padding: 9px 18px; border: none;">
                                                            <i class="fas fa-ban me-1"></i> Inactivar
                                                        </button>
                                                    </div>
                                                </form>
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

    <x-pagination :paginator="$docentes" />

@endsection

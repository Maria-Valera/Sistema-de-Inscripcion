@extends('adminlte::page')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
@stop
@section('title', 'Gestión de inscripciones')
@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de inscripciones nuevo ingreso</h1>
                    <p class="title-subtitle">
                        Administración de las inscripciones de nuevo ingreso
                    </p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <a href="{{ route('admin.transacciones.inscripcion.create') }}" class="btn-create"
                            title="Crear nueva inscripción">
                            <i class="fas fa-plus"></i>
                            <span>Registrar</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
@section('content')
    <div class="main-container">
        @include('partials.alerta-modo-consulta')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'No se puede ejecutar el percentil',
                    html: `{!! nl2br(e(session('error'))) !!}`,
                    showCancelButton: true,
                    confirmButtonText: 'Ir a configuración',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('admin.grado.index') }}";
                    }
                });
            </script>
        @endif
        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Proceso completado',
                    html: `{!! nl2br(e(session('success'))) !!}`,
                    confirmButtonText: 'Aceptar'
                });
            </script>
        @endif
        @if (request('grado_id') || request('seccion_id') || request('tipo_inscripcion'))
            <div class="card-modern filtros-simple mb-3">
                <div class="filtros-simple-content">
                    <div class="filtros-text">
                        <i class="fas fa-filter"></i>
                        <span>Filtros activos:</span>
                        @if (request('tipo_inscripcion'))
                            <span class="badge-filtros-small">
                                {{ request('tipo_inscripcion') == 'nuevo_ingreso' ? 'Nuevo Ingreso' : 'Prosecución' }}
                            </span>
                        @endif
                        @if (request('grado_id'))
                            <span class="badge-filtros-small">
                                Nivel Academico {{ $grados->find(request('grado_id'))->numero_grado ?? 'N/A' }}
                            </span>
                        @endif
                        @if (request('seccion_id'))
                            <span class="badge-filtros-small">
                                Sección {{ $secciones->find(request('seccion_id'))->nombre ?? 'N/A' }}
                            </span>
                        @endif
                        @if (request('status'))
                            <span class="badge-filtros-small">
                                {{ request('status') }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('admin.transacciones.inscripcion.index') }}" class="btn-clear-simple">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        @endif
        <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Listado de inscripciones</h3>
                        <p>{{ $inscripciones->total() }} registros encontrados</p>

                    </div>
                </div>

                <div class="header-right d-flex align-items-center gap-2 flex-wrap">
                    <form action="{{ route('admin.transacciones.inscripcion.index') }}" class="mb-0 search-sm">
                        <input type="hidden" name="grado_id" value="{{ request('grado_id') }}">
                        <input type="hidden" name="seccion_id" value="{{ request('seccion_id') }}">
                        <input type="hidden" name="tipo_inscripcion" value="{{ request('tipo_inscripcion') }}">

                        <div class="search-modern">
                            <i class="fas fa-search"></i>
                            <input type="text" name="buscar" title="Buscar por nombre, apellido o cedula" id="buscar"
                                class="form-control-modern" placeholder="Buscar..." value="{{ request('buscar') }}">
                        </div>
                    </form>
                    <div>
                        @foreach ($grados as $grado)
                            @if ($grado->id === 1)
                                @include('admin.transacciones.percentil.boton-percentil', [
                                    'anioEscolarActivo' => $anioEscolarActivo,
                                    'gradoId' => $grado->id,
                                ])
                            @endif
                        @endforeach
                    </div>
                    <button class="btn-filtro" data-bs-toggle="modal" data-bs-target="#modalFiltros">
                        <i class="fas fa-filter"></i>
                        @if (request('grado_id') || request('seccion_id') || request('tipo_inscripcion') || request('status'))
                            <span class="badge-sm bg-danger">
                                {{ collect([request('grado_id'), request('seccion_id'), request('tipo_inscripcion'), request('status')])->filter()->count() }}
                            </span>
                        @endif
                    </button>
                    <a href="{{ route('admin.transacciones.inscripcion.reporteGeneralNuevoIngresoPDF') }}" target="_blank"
                        class="btn-pdf" id="generarPdfBtn">
                        <i class="fas fa-file-pdf"></i> PDF General
                    </a>
                    @php
                        $mostrarAnio = \App\Support\AnioEscolarSeleccionado::modelo();
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
                    <table class="table-modern ">
                        <thead>
                            <tr class="text-center">
                                <th style="font-weight: bold">Cedula</th>
                                <th class="text-center">Estudiante</th>
                                <th class="text-center">Representante Legal</th>
                                <th class="text-center">Nivel Académico</th>
                                <th class="text-center">Sección</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @if ($inscripciones->isEmpty())
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                                            <h4>No hay inscripciones registradas</h4>
                                            <p>
                                                @if (request('grado_id') || request('seccion_id') || request('tipo_inscripcion') || request('buscar'))
                                                    No se encontraron resultados con los filtros aplicados
                                                @else
                                                    Agrega una nueva inscripción con el botón superior
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($inscripciones as $datos)
                                    <tr class="row-12">
                                        <td style="font-weight: bold">
                                            {{ $datos->alumno->persona->tipoDocumento->nombre }}-{{ $datos->alumno->persona->numero_documento }}
                                        </td>
                                        <td class="tittle-main fw-bold">
                                            {{ $datos->alumno->persona->primer_nombre }}
                                            {{ $datos->alumno->persona->primer_apellido }}
                                            <br>
                                            <small>
                                                Edad: {{ $datos->alumno->persona->fecha_nacimiento->age }} |
                                                Peso: {{ $datos->alumno->peso }} |
                                                Estatura: {{ $datos->alumno->estatura }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @if ($datos->representanteLegal && $datos->representanteLegal->representante)
                                                {{ $datos->representanteLegal->representante->persona->primer_nombre }}
                                                {{ $datos->representanteLegal->representante->persona->primer_apellido }}
                                            @else
                                                No especificado
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $datos->grado_actual?->numero_grado ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            @if ($datos->seccion)
                                                {{ $datos->seccion->nombre }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($datos->status === 'Activo')
                                                <span class="status-badge status-active">
                                                    <span class="status-dot"></span> Activo
                                                </span>
                                            @elseif ($datos->status === 'Pendiente')
                                                <span class="status-badge status-pending">
                                                    <span class="status-dot"></span> Pendiente
                                                </span>
                                            @else
                                                <span class="status-badge status-inactive">
                                                    <span class="status-dot"></span> Inactivo
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
                                                                data-bs-target="#viewModal{{ $datos->id }}">
                                                                <i class="fas fa-eye me-2"></i>
                                                                Ver mas
                                                            </button>
                                                        </li>
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            @if ($datos->nuevoIngreso)
                                                                <li>
                                                                    <a href="{{ route('admin.transacciones.inscripcion.edit', $datos->id) }}"
                                                                        class="dropdown-item d-flex align-items-center text-warning"
                                                                        title="Editar">
                                                                        <i class="fas fa-edit me-2"></i>
                                                                        Editar
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li>
                                                                @if ($datos->status === 'Activo' || $datos->status === 'Pendiente')
                                                                    <button
                                                                        class="dropdown-item d-flex align-items-center text-danger"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarEliminar{{ $datos->id }}">
                                                                        <i class="fas fa-ban me-2"></i>
                                                                        Inactivar
                                                                    </button>
                                                                @else
                                                                    <button
                                                                        class="dropdown-item d-flex align-items-center text-success"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarRestaurar{{ $datos->id }}">
                                                                        <i class="fas fa-undo me-2"></i>
                                                                        Restaurar
                                                                    </button>
                                                                @endif
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a href="{{ route('admin.transacciones.inscripcion.reporte', $datos->id) }}"
                                                                class="dropdown-item d-flex align-items-center text-danger"
                                                                title="Generar Reporte PDF" target="_blank">
                                                                <i class="fas fa-file-pdf me-2"></i>
                                                                PDF
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @include('admin.transacciones.inscripcion.modales.showModal')
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @include('admin.transacciones.inscripcion.modales.filtroModal')
            </div>
            <div class="mt-3" style="display:flex; align-items:center; position:relative;">
                <div style="margin: 0 auto;">
                    <x-pagination :paginator="$inscripciones" />
                </div>
            </div>
        </div>
    </div>
    @foreach ($inscripciones as $datos)
        @include('admin.transacciones.inscripcion.modales.inactivarModal', [
            'datos' => $datos,
        ])
    @endforeach
    @foreach ($inscripciones as $datos)
        @include('admin.transacciones.inscripcion.modales.restaurarModal', [
            'datos' => $datos,
        ])
    @endforeach

    <script>
        function actualizarEnlacePDF() {
            const generarPdfBtn = document.getElementById('generarPdfBtn');
            if (generarPdfBtn) {
                const urlParams = new URLSearchParams(window.location.search);
                let reportUrl = generarPdfBtn.getAttribute('href').split('?')[0];
                const params = new URLSearchParams();
                for (const [key, value] of urlParams.entries()) {
                    if (value) {
                        params.append(key, value);
                    }
                }
                if (params.toString()) {
                    reportUrl += '?' + params.toString();
                }
                generarPdfBtn.setAttribute('href', reportUrl);
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            actualizarEnlacePDF();
            window.addEventListener('popstate', actualizarEnlacePDF);
            const filtroModal = document.getElementById('modalFiltros');
            if (filtroModal) {
                filtroModal.addEventListener('shown.bs.modal', actualizarEnlacePDF);
            }
        });
    </script>
@endsection

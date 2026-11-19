@extends('adminlte::page')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}?v={{ @filemtime(public_path('css/index.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}?v={{ @filemtime(public_path('css/modal-styles.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}?v={{ @filemtime(public_path('css/pagination.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}?v={{ @filemtime(public_path('css/view-styles.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop
@section('title', 'Gestión de inscripciones')
@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Gestión de inscripciones nuevo ingreso</h1>
                    <p class="title-subtitle mb-0">
                        Administración de las inscripciones de nuevo ingreso
                    </p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <a href="{{ route('admin.transacciones.inscripcion.create') }}" class="btn-hero-white text-decoration-none"
                    title="Crear nueva inscripción">
                    <i class="fas fa-plus"></i>
                    <span>+ Registrar</span>
                </a>
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
        @if (request('grado_id') || request('seccion_id') || request('tipo_inscripcion') || request('status'))
            <div class="card-modern filtros-simple mb-3">
                <div class="filtros-simple-content">
                    <div class="filtros-text">
                        <i class="fas fa-sliders-h"></i>
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
        @php
            $activeFiltersCount = collect(['grado_id', 'seccion_id', 'tipo_inscripcion', 'status'])
                ->filter(fn ($key) => request()->filled($key))
                ->count();
        @endphp
        <div class="card-modern shadow-sm">
            <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3 w-100 bg-white border-bottom px-4 py-3" style="background: #FFFFFF !important; border-bottom: 1px solid #E2E8F0 !important;">
                <!-- IZQUIERDA: Título y Subtítulo -->
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de inscripciones</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $inscripciones->total() }} registros encontrados</p>
                    </div>
                </div>

                <!-- DERECHA: Controles (Buscador separado con gap-3 de los botones agrupados) -->
                <div class="header-right d-flex align-items-center gap-3 flex-wrap">
                    <!-- 1. El Buscador -->
                    <form action="{{ route('admin.transacciones.inscripcion.index') }}" method="GET" class="m-0">
                        <input type="hidden" name="grado_id" value="{{ request('grado_id') }}">
                        <input type="hidden" name="seccion_id" value="{{ request('seccion_id') }}">
                        <input type="hidden" name="tipo_inscripcion" value="{{ request('tipo_inscripcion') }}">

                        <div class="search-wrapper" style="position: relative; border: 1px solid #E2E8F0; border-radius: 8px; height: 38px; background: #FFFFFF; width: 250px; box-sizing: border-box;">
                            <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748B; font-size: 0.85rem; pointer-events: none; z-index: 2;"></i>
                            <input type="text" name="buscar" id="buscar"
                                placeholder="Buscar estudiante o cédula..." value="{{ request('buscar') }}"
                                style="border: none !important; outline: none !important; background: transparent !important; padding-left: 40px !important; padding-right: 12px !important; width: 250px !important; height: 100% !important; font-size: 0.85rem !important; color: #1E293B !important; box-sizing: border-box !important;">
                        </div>
                    </form>

                    <!-- Botones de Acción Agrupados con d-flex gap-2 -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <!-- 3. Botón "Filtrar" -->
                        <button type="button" class="btn btn-filter-global d-inline-flex align-items-center justify-content-center gap-2 text-nowrap"
                            data-bs-toggle="offcanvas" data-bs-target="#panelFiltros" aria-controls="panelFiltros"
                            title="Filtrar inscripciones" style="height: 38px; border-radius: 8px; border: 1px solid #E2E8F0; background-color: #FFFFFF; color: #1E293B; padding: 0 14px;">
                            <i class="fas fa-sliders-h" style="color: #64748B;"></i>
                            <span>Filtrar</span>
                            @if ($activeFiltersCount > 0)
                                <span class="badge text-white ms-1" style="background-color: #4A6FC1; font-size: 0.72rem; border-radius: 9999px; padding: 2px 6px;">
                                    {{ $activeFiltersCount }}
                                </span>
                            @endif
                        </button>

                        <!-- 4. Botón "PDF General" -->
                        <a href="{{ route('admin.transacciones.inscripcion.reporteGeneralNuevoIngresoPDF') }}" target="_blank"
                            class="btn-pdf-global d-inline-flex align-items-center justify-content-center gap-2 text-nowrap" id="generarPdfBtn" style="height: 38px; border-radius: 8px;">
                            <i class="fas fa-file-pdf"></i>
                            <span>PDF General</span>
                        </a>

                        <!-- 5. Botón "Ejecutar Percentil" (Primario) -->
                        @foreach ($grados as $grado)
                            @if ($grado->id === 1)
                                @include('admin.transacciones.percentil.boton-percentil', [
                                    'anioEscolarActivo' => $anioEscolarActivo,
                                    'gradoId' => $grado->id,
                                ])
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="card-body-modern p-0">
                <div class="table-responsive">
                    <table class="table-modern text-nowrap">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 130px; text-align: center;">Cédula</th>
                                <th style="text-align: left;">Estudiante</th>
                                <th style="text-align: center;">Representante Legal</th>
                                <th style="text-align: center;">Nivel Académico</th>
                                <th style="text-align: center;">Sección</th>
                                <th style="width: 120px; text-align: center;">Estado</th>
                                <th style="width: 100px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @if ($inscripciones->isEmpty())
                                <tr>
                                    <td colspan="7" class="py-5">
                                        <div class="empty-state-saas text-center">
                                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                                 style="width: 72px; height: 72px; background-color: #F8FAFC; border: 1px solid #E2E8F0; color: #94A3B8;">
                                                <i class="fas fa-inbox fa-2x"></i>
                                            </div>
                                            <h4 class="fw-bold mb-1" style="color: #1E293B; font-size: 1.15rem;">No hay inscripciones registradas</h4>
                                            <p class="text-muted mx-auto mb-3" style="max-width: 420px; font-size: 0.88rem;">
                                                @if (request('grado_id') || request('seccion_id') || request('tipo_inscripcion') || request('buscar'))
                                                    No se encontraron resultados con los filtros aplicados.
                                                @else
                                                    Comienza registrando la primera inscripción de nuevo ingreso para este año escolar.
                                                @endif
                                            </p>
                                            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                <div class="d-flex justify-content-center">
                                                    <a href="{{ route('admin.transacciones.inscripcion.create') }}" class="btn-primary-modern text-decoration-none">
                                                        <i class="fas fa-plus me-1"></i> + Crear primera inscripción
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($inscripciones as $datos)
                                    <tr class="row-12">
                                        <td class="fw-bold text-dark text-center" style="font-size: 0.92rem;">
                                            {{ $datos->alumno->persona->tipoDocumento->nombre }}-{{ $datos->alumno->persona->numero_documento }}
                                        </td>
                                        <td class="text-start">
                                            <div class="fw-bold" style="color: #1E293B;">
                                                {{ $datos->alumno->persona->primer_nombre }}
                                                {{ $datos->alumno->persona->primer_apellido }}
                                            </div>
                                            <small class="text-muted" style="font-size: 0.8rem;">
                                                Edad: {{ $datos->alumno->persona->fecha_nacimiento ? $datos->alumno->persona->fecha_nacimiento->age : 'N/A' }} |
                                                Peso: {{ $datos->alumno->peso ?? 'N/A' }} |
                                                Estatura: {{ $datos->alumno->estatura ?? 'N/A' }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @if ($datos->representanteLegal && $datos->representanteLegal->representante)
                                                {{ $datos->representanteLegal->representante->persona->primer_nombre }}
                                                {{ $datos->representanteLegal->representante->persona->primer_apellido }}
                                            @else
                                                <span class="text-muted fst-italic">No especificado</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-semibold">
                                            {{ $datos->grado_actual?->numero_grado ? $datos->grado_actual->numero_grado . 'º' : 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            @if ($datos->seccion)
                                                <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE; font-size: 0.8rem; font-weight: 500;">
                                                    Sección {{ $datos->seccion->nombre }}
                                                </span>
                                            @else
                                                <span class="text-muted fst-italic">N/A</span>
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
                                                <div class="dropdown text-center">
                                                    <button class="btn btn-light btn-sm rounded-circle action-btn shadow-none"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px; padding: 0;">
                                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModal{{ $datos->id }}">
                                                                <i class="fas fa-eye me-2 text-primary"></i>
                                                                Ver más
                                                            </button>
                                                        </li>
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            @if ($datos->nuevoIngreso)
                                                                <li>
                                                                    <a href="{{ route('admin.transacciones.inscripcion.edit', $datos->id) }}"
                                                                        class="dropdown-item d-flex align-items-center"
                                                                        title="Editar">
                                                                        <i class="fas fa-edit me-2 text-warning"></i>
                                                                        Editar
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li>
                                                                @if ($datos->status === 'Activo' || $datos->status === 'Pendiente')
                                                                    <button class="dropdown-item d-flex align-items-center text-danger"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarEliminar{{ $datos->id }}">
                                                                        <i class="fas fa-ban me-2 text-danger"></i>
                                                                        Inactivar
                                                                    </button>
                                                                @else
                                                                    <button class="dropdown-item d-flex align-items-center text-success"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarRestaurar{{ $datos->id }}">
                                                                        <i class="fas fa-undo me-2 text-success"></i>
                                                                        Restaurar
                                                                    </button>
                                                                @endif
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a href="{{ route('admin.transacciones.inscripcion.reporte', $datos->id) }}"
                                                                class="dropdown-item d-flex align-items-center text-danger"
                                                                title="Generar Reporte PDF" target="_blank">
                                                                <i class="fas fa-file-pdf me-2 text-danger"></i>
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
            </div>
            <div class="mt-3" style="display:flex; align-items:center; position:relative;">
                <div style="margin: 0 auto;">
                    <x-pagination :paginator="$inscripciones" />
                </div>
            </div>
        </div>
    </div>
    @include('admin.transacciones.inscripcion.modales.filtroModal')
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
            const filtroPanel = document.getElementById('panelFiltros');
            if (filtroPanel) {
                filtroPanel.addEventListener('shown.bs.offcanvas', actualizarEnlacePDF);
            }
        });

        window.addEventListener('ui:open-filter-panel', function(e) {
            const targetId = e.detail?.target;
            if (!targetId) return;
            const el = document.getElementById(targetId);
            if (el && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
                bootstrap.Offcanvas.getOrCreateInstance(el).show();
            }
        });
    </script>
@endsection

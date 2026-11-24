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
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Gestión de inscripciones de prosecución</h1>
                    <p class="title-subtitle mb-0">
                        Administración de las inscripciones de prosecución
                    </p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button" class="btn-hero-white border-0" data-bs-toggle="modal" data-bs-target="#modalProsecucion"
                    title="Registrar Prosecución">
                    <i class="fas fa-plus"></i>
                    <span>+ Registrar</span>
                </button>
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
                    <div class="alert-modern alert-success alert alert-dismissible fade show">
                        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
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
                        <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div class="alert-content">
                            <h4>Error</h4>
                            <p>{{ session('error') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        @if (request('grado_id') ||
                request('seccion_id') ||
                request('tipo_inscripcion') ||
                request('materias_pendientes') ||
                request('status'))
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
                        @if ($materiasPendientes)
                            @if (request('materias_pendientes'))
                                <span class="badge-filtros-small">
                                    @if (request('materias_pendientes') === 'con_pendientes')
                                        Con materias pendientes
                                    @elseif (request('materias_pendientes') === 'sin_pendientes')
                                        Sin materias pendientes
                                    @endif
                                </span>
                            @endif
                        @endif

                        @if (request('status'))
                            <span class="badge-filtros-small">
                                {{ request('status') }}
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('admin.transacciones.inscripcion_prosecucion.index') }}" class="btn-clear-simple">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        @endif

        @php
            $activeFiltersCount = collect(['grado_id', 'seccion_id', 'tipo_inscripcion', 'status', 'materias_pendientes'])
                ->filter(fn ($key) => request()->filled($key))
                ->count();
        @endphp

        <div class="card-modern shadow-sm">
            <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3 w-100 bg-white border-bottom px-4 py-3" style="background: #FFFFFF !important; border-bottom: 1px solid #E2E8F0 !important;">
                <!-- IZQUIERDA: Ícono, Título y Contador -->
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de inscripciones</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $prosecuciones->total() }} registros encontrados</p>
                    </div>
                </div>

                <!-- DERECHA: Buscador + Filtros + PDF General agrupados -->
                <div class="header-right d-flex align-items-center gap-2 flex-wrap">
                    <!-- 1. El Buscador -->
                    <form action="{{ route('admin.transacciones.inscripcion_prosecucion.index') }}" method="GET" class="m-0">
                        <input type="hidden" name="grado_id" value="{{ request('grado_id') }}">
                        <input type="hidden" name="seccion_id" value="{{ request('seccion_id') }}">
                        <input type="hidden" name="tipo_inscripcion" value="{{ request('tipo_inscripcion') }}">
                        <input type="hidden" name="materias_pendientes" value="{{ request('materias_pendientes') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">

                        <div class="search-wrapper" style="position: relative; border: 1px solid #E2E8F0; border-radius: 8px; height: 38px; background: #FFFFFF; width: 250px; box-sizing: border-box;">
                            <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748B; font-size: 0.85rem; pointer-events: none; z-index: 2;"></i>
                            <input type="text" name="buscar" id="buscar"
                                placeholder="Buscar estudiante o cédula..." value="{{ request('buscar') }}"
                                style="border: none !important; outline: none !important; background: transparent !important; padding-left: 40px !important; padding-right: 12px !important; width: 250px !important; height: 100% !important; font-size: 0.85rem !important; color: #1E293B !important; box-sizing: border-box !important;">
                        </div>
                    </form>

                    <!-- 2. Botón de Filtros -->
                    <button type="button" class="btn btn-filter-global d-inline-flex align-items-center justify-content-center gap-2 text-nowrap"
                        data-bs-toggle="offcanvas" data-bs-target="#panelFiltros" aria-controls="panelFiltros"
                        title="Filtrar inscripciones" style="height: 38px; border-radius: 8px;">
                        <i class="fas fa-sliders-h"></i>
                        <span>Filtros</span>
                        @if ($activeFiltersCount > 0)
                            <span class="badge text-white ms-1" style="background-color: #4A6FC1; font-size: 0.72rem; border-radius: 9999px; padding: 2px 6px;">
                                {{ $activeFiltersCount }}
                            </span>
                        @endif
                    </button>

                    <!-- 3. Botón PDF General -->
                    <a href="{{ route('admin.transacciones.inscripcion_prosecucion.reporteGeneralProsecucionPDF') }}"
                        target="_blank" class="btn-pdf-global d-inline-flex align-items-center justify-content-center gap-2 text-nowrap" id="generarPdfBtn" style="height: 38px; border-radius: 8px;">
                        <i class="fas fa-file-pdf"></i>
                        <span>PDF General</span>
                    </a>
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
                            @if ($prosecuciones->isEmpty())
                                <tr>
                                    <td colspan="7" class="py-5">
                                        <div class="empty-state-saas text-center">
                                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                                 style="width: 72px; height: 72px; background-color: #F8FAFC; border: 1px solid #E2E8F0; color: #94A3B8;">
                                                <i class="fas fa-inbox fa-2x"></i>
                                            </div>
                                            <h4 class="fw-bold mb-1" style="color: #1E293B; font-size: 1.15rem; font-weight: 600;">No hay inscripciones registradas</h4>
                                            <p class="text-muted mx-auto mb-3" style="max-width: 420px; font-size: 0.88rem; color: #64748B;">
                                                @if (request('grado_id') || request('seccion_id') || request('tipo_inscripcion') || request('buscar') || request('materias_pendientes') || request('status'))
                                                    No se encontraron resultados con los filtros aplicados.
                                                @else
                                                    Agrega una nueva inscripción con el botón superior.
                                                @endif
                                            </p>
                                            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                <div class="d-flex justify-content-center">
                                                    <button type="button" class="btn-primary-modern border-0" data-bs-toggle="modal" data-bs-target="#modalProsecucion">
                                                        <i class="fas fa-plus me-1"></i> + Crear primera inscripción
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($prosecuciones as $datos)
                                    <tr class="row-12">
                                        <td class="fw-bold text-dark text-center" style="font-size: 0.92rem;">
                                            {{ $datos->inscripcion->alumno->persona->tipoDocumento->nombre }}-{{ $datos->inscripcion->alumno->persona->numero_documento }}
                                        </td>
                                        <td class="text-start">
                                            <div class="fw-bold" style="color: #1E293B;">
                                                {{ $datos->inscripcion->alumno->persona->primer_nombre }}
                                                {{ $datos->inscripcion->alumno->persona->primer_apellido }}
                                            </div>
                                            <small class="text-muted" style="font-size: 0.8rem;">
                                                Edad: {{ $datos->inscripcion->alumno->persona->fecha_nacimiento ? $datos->inscripcion->alumno->persona->fecha_nacimiento->age : 'N/A' }} |
                                                Peso: {{ $datos->inscripcion->alumno->peso ?? 'N/A' }} |
                                                Estatura: {{ $datos->inscripcion->alumno->estatura ?? 'N/A' }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @if ($datos->inscripcion->representanteLegal && $datos->inscripcion->representanteLegal->representante)
                                                {{ $datos->inscripcion->representanteLegal->representante->persona->primer_nombre }}
                                                {{ $datos->inscripcion->representanteLegal->representante->persona->primer_apellido }}
                                            @else
                                                <span class="text-muted fst-italic">No especificado</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-semibold">
                                            {{ $datos->grado?->numero_grado ? $datos->grado->numero_grado . 'º' : 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $datos->seccion?->nombre ?? 'N/A' }}
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
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-strategy="fixed"
                                                        data-bs-boundary="viewport"
                                                        aria-expanded="false"
                                                        style="width: 32px; height: 32px; padding: 0;">
                                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <button
                                                                class="dropdown-item dropdown-item-modern d-flex align-items-center text-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModal{{ $datos->id }}">
                                                                <i class="fas fa-eye me-2"></i>
                                                                Ver más
                                                            </button>
                                                        </li>
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                @if ($datos->status === 'Activo')
                                                                    <button
                                                                        class="dropdown-item dropdown-item-modern d-flex align-items-center text-danger"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarEliminar{{ $datos->id }}">
                                                                        <i class="fas fa-ban me-2"></i>
                                                                        Inactivar
                                                                    </button>
                                                                @else
                                                                    <button
                                                                        class="dropdown-item dropdown-item-modern d-flex align-items-center text-success"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarRestaurar{{ $datos->id }}">
                                                                        <i class="fas fa-undo me-2"></i>
                                                                        Restaurar
                                                                    </button>
                                                                @endif
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a href="{{ route('admin.transacciones.inscripcion_prosecucion.reporte', $datos->id) }}"
                                                                class="dropdown-item dropdown-item-modern d-flex align-items-center text-danger"
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
                                    @include('admin.transacciones.inscripcion_prosecucion.modales.showModal')
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3" style="display:flex; align-items:center; position:relative;">
                <div style="margin: 0 auto;">
                    <x-pagination :paginator="$prosecuciones" />
                </div>
            </div>
        </div>
    </div>
    @include('admin.transacciones.inscripcion_prosecucion.modales.filtroModal')
    @foreach ($prosecuciones as $datos)
        @include('admin.transacciones.inscripcion_prosecucion.modales.inactivarModal', [
            'datos' => $datos,
        ])
    @endforeach
    @foreach ($prosecuciones as $datos)
        @include('admin.transacciones.inscripcion_prosecucion.modales.restaurarModal', [
            'datos' => $datos,
        ])
    @endforeach

    <!-- Modal Seleccionar Estudiante para Prosecución (Diseño SaaS) -->
    <div class="modal fade" id="modalProsecucion" tabindex="-1" aria-labelledby="modalProsecucionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-modern shadow border-0" style="border-radius: 14px; overflow: hidden;">
                <form action="{{ route('admin.transacciones.inscripcion_prosecucion.create') }}" method="GET" id="formModalProsecucion">
                    <!-- Header: Fondo blanco, ícono de birrete azul, título oscuro y botón de cerrar -->
                    <div class="modal-header bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #E2E8F0 !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                 style="width: 42px; height: 42px; background-color: #EFF6FF; color: #4A6FC1; border-radius: 10px;">
                                <i class="fas fa-graduation-cap fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="modalProsecucionLabel" style="color: #1E293B; font-size: 1.05rem;">
                                    Registrar Prosecución
                                </h5>
                                <p class="text-muted mb-0" style="font-size: 0.82rem;">
                                    Selección de estudiante a inscribir
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4 bg-white">
                        <p class="text-muted mb-3" style="font-size: 0.88rem; color: #64748B;">
                            Seleccione el estudiante que desea inscribir por prosecución
                        </p>

                        <div class="mb-2">
                            <label for="modal_alumno_id" class="form-label-modern fw-semibold mb-2" style="color: #1E293B; font-size: 0.88rem;">
                                <i class="fas fa-user-graduate me-1" style="color: #4A6FC1;"></i> Estudiante <span class="text-danger">*</span>
                            </label>
                            <select name="alumno_id" id="modal_alumno_id" class="form-select form-control-modern select-modern w-100" required>
                                <option value="">Seleccione un estudiante...</option>
                                @foreach ($alumnosDisponibles ?? [] as $alumno)
                                    @php
                                        $insAnt = $anioEscolar ? $alumno->ultimaInscripcionAntesDe($anioEscolar->id) : null;
                                        $gradoAnt = $insAnt?->grado?->numero_grado;
                                        $cedula = ($alumno->persona->tipoDocumento->nombre ?? '') . '-' . $alumno->persona->numero_documento;
                                    @endphp
                                    <option value="{{ $alumno->id }}">
                                        {{ $alumno->persona->primer_nombre }} {{ $alumno->persona->primer_apellido }} ({{ $cedula }}){{ $gradoAnt ? ' - ' . $gradoAnt . '° Nivel Académico' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="errorModalAlumno" class="text-danger mt-2 d-none" style="font-size: 0.82rem;">
                                <i class="fas fa-exclamation-circle me-1"></i> Por favor, seleccione un estudiante antes de continuar.
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Cancelar y Continuar -->
                    <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-end gap-2" style="background-color: #F8FAFC !important; border-top: 1px solid #E2E8F0 !important;">
                        <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary-modern" id="btnContinuarModalProsecucion" disabled>
                            <span>Continuar</span>
                            <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Actualizar el enlace de generación de PDF con los filtros actuales
        function actualizarEnlacePDF() {
            const generarPdfBtn = document.getElementById('generarPdfBtn');
            if (generarPdfBtn) {
                // Obtener todos los parámetros de la URL actual
                const urlParams = new URLSearchParams(window.location.search);

                // Construir la URL base del reporte
                let reportUrl = generarPdfBtn.getAttribute('href').split('?')[0];
                const params = new URLSearchParams();

                // Agregar todos los parámetros de filtro actuales
                for (const [key, value] of urlParams.entries()) {
                    if (value) {
                        params.append(key, value);
                    }
                }

                // Construir URL final
                if (params.toString()) {
                    reportUrl += '?' + params.toString();
                }

                // Actualizar el atributo href del botón
                generarPdfBtn.setAttribute('href', reportUrl);
            }
        }

        // Inicializar interactividad cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Actualizar el enlace de PDF al cargar la página
            actualizarEnlacePDF();

            // Actualizar el enlace cuando cambie la URL (navegación con filtros)
            window.addEventListener('popstate', actualizarEnlacePDF);

            // Actualizar el enlace cuando se muestre el panel de filtros
            const filtroPanel = document.getElementById('panelFiltros');
            if (filtroPanel) {
                filtroPanel.addEventListener('shown.bs.offcanvas', actualizarEnlacePDF);
            }

            // Habilitar / deshabilitar botón Continuar de la modal de prosecución
            const selectAlumnoModal = document.getElementById('modal_alumno_id');
            const btnContinuarModal = document.getElementById('btnContinuarModalProsecucion');
            const errorModalAlumno = document.getElementById('errorModalAlumno');
            const formModalPros = document.getElementById('formModalProsecucion');

            if (selectAlumnoModal && btnContinuarModal) {
                selectAlumnoModal.addEventListener('change', function() {
                    if (this.value) {
                        btnContinuarModal.removeAttribute('disabled');
                        if (errorModalAlumno) errorModalAlumno.classList.add('d-none');
                    } else {
                        btnContinuarModal.setAttribute('disabled', 'disabled');
                    }
                });

                if (formModalPros) {
                    formModalPros.addEventListener('submit', function(e) {
                        if (!selectAlumnoModal.value) {
                            e.preventDefault();
                            if (errorModalAlumno) errorModalAlumno.classList.remove('d-none');
                            selectAlumnoModal.focus();
                        }
                    });
                }
            }

            // Resetear selector y botón al cerrar la modal
            const modalProsecucionEl = document.getElementById('modalProsecucion');
            if (modalProsecucionEl) {
                modalProsecucionEl.addEventListener('hidden.bs.modal', function() {
                    if (selectAlumnoModal) {
                        selectAlumnoModal.value = '';
                    }
                    if (btnContinuarModal) {
                        btnContinuarModal.setAttribute('disabled', 'disabled');
                    }
                    if (errorModalAlumno) {
                        errorModalAlumno.classList.add('d-none');
                    }
                });
            }
        });
    </script>
@endsection

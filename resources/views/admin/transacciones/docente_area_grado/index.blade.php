@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Gestión de Asignaciones')

@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Asignación de Docentes</h1>
                    <p class="title-subtitle mb-0">Gestión de materias, niveles académicos y secciones</p>
                </div>
            </div>

            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <a class="btn-hero-white w-100 w-md-auto justify-content-center mt-3 mt-md-0" href="{{ route('admin.transacciones.docente_area_grado.create') }}"
                    title="Nueva Asignación">
                    <i class="fas fa-plus"></i>
                    <span>Nueva Asignación</span>
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('partials.alerta-modo-consulta')

        {{-- Sección de alertas --}}
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

        {{-- Tabla de docentes --}}
        <div class="card-modern shadow-sm">
            <div class="card-header-modern d-flex justify-content-between align-items-center flex-column flex-md-row gap-3 w-100">
                <!-- IZQUIERDA: Título y Subtítulo -->
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de Docentes</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $docentes->total() }} docentes registrados</p>
                    </div>
                </div>

                <!-- DERECHA: Buscador y PDF -->
                <div class="d-flex align-items-center gap-3 flex-column flex-sm-row w-100 w-md-auto">
                    <!-- BUSCADOR -->
                    <form action="{{ route('admin.transacciones.docente_area_grado.index') }}" method="GET" class="m-0 w-100 w-md-auto">
                        <div class="search-wrapper position-relative" style="min-width: 250px;">
                            <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                            <input type="text" name="buscar" id="buscar" class="form-control search-input-modern pl-5 w-100"
                                placeholder="Buscar docente..." value="{{ request('buscar') }}"
                                style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                        </div>
                    </form>

                    <!-- PDF GENERAL -->
                    <a href="{{ route('admin.transacciones.docente_area_grado.reportePDFGeneral', request()->query()) }}"
                        class="btn-pdf-global d-inline-flex align-items-center justify-content-center gap-2 text-nowrap"
                        target="_blank" title="Exportar reporte general en PDF">
                        <i class="fas fa-file-pdf"></i>
                        <span>PDF General</span>
                    </a>
                </div>
            </div>

            <div class="card-body-modern p-0">
                <div class="table-responsive">
                    <table class="table-modern table-striped text-nowrap">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 130px; text-align: center;" class="text-nowrap">Cédula</th>
                                <th style="text-align: left;" class="text-nowrap">Docente</th>
                                <th style="text-align: center;">Áreas, Nivel Académico y Secciones</th>
                                <th style="text-align: center;">Grupo Estable</th>
                                <th style="width: 120px; text-align: center;" class="text-nowrap">Estado</th>
                                <th style="width: 100px; text-align: center;" class="text-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($docentes as $datos)
                                <tr>
                                    {{-- Cédula --}}
                                    <td class="fw-bold text-dark text-nowrap" style="font-size: 0.92rem; text-align: center;">
                                        {{ $datos->persona->tipoDocumento->nombre ?? 'V' }}-{{ $datos->persona->numero_documento }}
                                    </td>

                                    {{-- Docente --}}
                                    <td class="text-start fw-semibold text-dark text-nowrap">
                                        {{ $datos->persona->primer_nombre }} {{ $datos->persona->primer_apellido }}
                                    </td>

                                    {{-- Áreas, Grados y Secciones --}}
                                    <td class="text-center" style="white-space: normal; min-width: 280px;">
                                        @php
                                            // Filtrar solo asignaciones de tipo 'area' activas
                                            $asigs = $datos->asignacionesAreas
                                                ->where('status', true)
                                                ->where('tipo_asignacion', 'area')
                                                ->filter(
                                                    fn($a) => $a->areaEstudios !== null ||
                                                        $a->grado !== null ||
                                                        $a->seccion !== null,
                                                );
                                        @endphp

                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            @forelse($asigs as $asign)
                                                @php
                                                    $areaFormacion = optional($asign->areaEstudios)->areaFormacion;
                                                    $grado = optional($asign->grado);
                                                    $seccion = optional($asign->seccion); 
                                                @endphp
                                                <span class="badge d-inline-flex align-items-center gap-1"
                                                      style="background-color: #F1F5F9; color: #334155; border: 1px solid #E2E8F0; font-weight: 500; font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 6px;"
                                                      title="{{ $areaFormacion->nombre_area_formacion ?? 'N/A' }} - Nivel {{ $grado->numero_grado ?? 'N/A' }}° - Sec. {{ $seccion->nombre ?? 'N/A' }}">
                                                    <i class="fas fa-book-open text-primary" style="font-size: 0.72rem;"></i>
                                                    <span>{{ Str::limit($areaFormacion->nombre_area_formacion ?? 'N/A', 18) }}</span>
                                                    <span class="text-muted fw-bold">·</span>
                                                    <span class="fw-bold" style="color: #4A6FC1;">{{ $grado->numero_grado ? $grado->numero_grado . '°' : 'N/A' }}</span>
                                                    <span class="badge bg-white text-dark border px-1" style="font-size: 0.72rem;">{{ $seccion->nombre ?? 'N/A' }}</span>
                                                </span>
                                            @empty
                                                <span class="text-muted" style="font-size: 0.82rem;">
                                                    <i class="fas fa-minus-circle me-1"></i> Sin áreas asignadas
                                                </span>
                                            @endforelse
                                        </div>
                                    </td>

                                    {{-- Grupos Estables --}}
                                    <td class="text-center" style="white-space: normal; min-width: 180px;">
                                        @php
                                            // Filtrar solo asignaciones de tipo grupo_estable activas
                                            $grupos = $datos->asignacionesAreas
                                                ->where('status', true)
                                                ->where('tipo_asignacion', 'grupo_estable');
                                        @endphp

                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            @forelse($grupos as $asign)
                                                <span class="badge d-inline-flex align-items-center gap-1"
                                                    style="background-color: #F1F5F9; color: #334155; border: 1px solid #E2E8F0; font-weight: 500; font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 6px;"
                                                    title="{{ optional($asign->grupoEstable)->nombre_grupo_estable ?? 'N/A' }} - Nivel: {{ optional($asign->gradoGrupoEstable)->numero_grado ?? 'N/A' }}">
                                                    <i class="fas fa-users text-info" style="font-size: 0.72rem;"></i>
                                                    <span>{{ optional($asign->grupoEstable)->nombre_grupo_estable ?? 'N/A' }}</span>
                                                    @if(optional($asign->gradoGrupoEstable)->numero_grado)
                                                        <span class="text-muted fw-bold">·</span>
                                                        <span class="fw-bold" style="color: #4A6FC1;">{{ optional($asign->gradoGrupoEstable)->numero_grado }}°</span>
                                                    @endif
                                                </span>
                                            @empty
                                                <span class="text-muted" style="font-size: 0.82rem;">
                                                    <i class="fas fa-minus-circle me-1"></i> Sin grupos
                                                </span>
                                            @endforelse
                                        </div>
                                    </td>

                                    {{-- Estado --}}
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

                                    {{-- Acciones --}}
                                    <td class="text-nowrap text-center">
                                        <div class="action-buttons justify-content-center">
                                            <div class="dropdown dropstart text-center">
                                                <button class="btn btn-sm action-btn rounded-circle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end shadow-sm">
                                                    {{-- Ver más --}}
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
                                                        {{-- Editar --}}
                                                        <li>
                                                            <a class="dropdown-item dropdown-item-modern text-warning d-flex align-items-center"
                                                                href="{{ route('admin.transacciones.docente_area_grado.edit', $datos->id) }}"
                                                                title="Editar">
                                                                <i class="fas fa-pen me-2 text-warning"></i>
                                                                <span class="text-warning fw-medium">Editar</span>
                                                            </a>
                                                        </li>

                                                        {{-- Inactivar --}}
                                                        <li>
                                                            <button class="dropdown-item dropdown-item-modern text-danger d-flex align-items-center"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                                title="Inactivar">
                                                                <i class="fas fa-ban me-2 text-danger"></i>
                                                                <span class="text-danger fw-medium">Inactivar</span>
                                                            </button>
                                                        </li>
                                                    @endif

                                                    {{-- PDF Materias --}}
                                                    <li>
                                                        <a href="{{ route('admin.docente.DocenteMateriaIndividual', ['id' => $datos->id] + request()->query()) }}"
                                                            class="dropdown-item dropdown-item-modern text-danger d-flex align-items-center"
                                                            target="_blank"
                                                            title="Generar PDF de materias">
                                                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                                                            <span class="text-danger fw-medium">Área de formación PDF</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Ruta de ver detalle --}}
                                @include('admin.transacciones.docente_area_grado.modales.showModal')

                                {{-- Modal de confirmación para eliminar --}}
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
                                                        <p class="text-muted m-0" style="font-size: 0.82rem;">Acción sobre asignación de docente</p>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                                <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                    ¿Deseas inactivar esta asignación?
                                                </p>
                                                <div class="p-3 d-flex align-items-center gap-3"
                                                     style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                    <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                         style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-weight: 700;">
                                                        <i class="fas fa-chalkboard-teacher"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                                            {{ $datos->persona->primer_nombre }} {{ $datos->persona->primer_apellido }}
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.8rem;">
                                                            C.I: {{ $datos->persona->tipoDocumento->nombre ?? 'V' }}-{{ $datos->persona->numero_documento }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                                                <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                                    <i class="fas fa-times me-1"></i> Cancelar
                                                </button>
                                                <form action="{{ route('admin.transacciones.docente_area_grado.destroyAsignacion', $datos->id) }}"
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
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state text-center py-5">
                                            <div class="empty-icon text-muted mb-2 opacity-50">
                                                <i class="fas fa-user-slash fa-3x"></i>
                                            </div>
                                            <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay docentes con materias asignadas</h4>
                                            <p class="text-muted" style="font-size: 0.88rem;">Debe registrar docentes primero y asignarles materias y secciones.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @include('admin.transacciones.docente_area_grado.modales.filtroModal')

        {{-- Paginación --}}
        <x-pagination :paginator="$docentes" />
    </div>
@endsection

@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('title', 'Gestión de Asignaciones')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h1 class="title-main">Asignación de Docentes</h1>
                    <p class="title-subtitle">Gestión de materias, niveles academicos y secciones</p>
                </div>
            </div>

            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <a type="button" class="btn-create" href="{{ route('admin.transacciones.docente_area_grado.create') }}"
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
        <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">

                <!-- IZQUIERDA -->
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Listado de Docentes</h3>
                        <p class="mb-0">{{ $docentes->total() }} docentes registrados</p>
                    </div>
                </div>

                <!-- DERECHA -->
                <div class="header-right d-flex align-items-center gap-2 flex-wrap">

                    <!-- BUSCADOR -->
                    <form action="{{ route('admin.transacciones.docente_area_grado.index') }}" class="mb-0 search-sm">
                        <div class="search-modern">
                            <i class="fas fa-search"></i>
                            <input type="text" name="buscar" id="buscar" class="form-control-modern"
                                placeholder="Buscar..." value="{{ request('buscar') }}">
                        </div>
                    </form>

                    <!-- PDF -->
                    <a href="{{ route('admin.transacciones.docente_area_grado.reportePDFGeneral', request()->query()) }}"
                        class="btn-pdf" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF General
                    </a>

                    <!-- FILTRO -->
                    <button class="btn-modal-create" data-bs-toggle="modal" data-bs-target="#modalFiltros">
                        <i class="fas fa-filter"></i>
                    </button>

                    <!-- Calendario Escolar -->
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

        </div>

        <div class="card-body-modern">
            <div class="table-wrapper">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Docente</th>
                            <th style="text-align: center;">Áreas, nivel academico y secciones</th>
                            <th style="text-align: center;">Grupo Estable</th>
                            <th style="text-align: center; width: 120px;">Estado</th>
                            <th style="text-align: center; width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($docentes as $datos)
                            <tr>
                                {{-- Datos del docente --}}
                                <td
                                    style="padding-left: 1rem; font-weight: 600; color: var(--gray-900); font-size: 0.95rem; text-align: center;">
                                    <span>
                                        {{ $datos->persona->tipoDocumento->nombre ?? ' ' }}-{{ $datos->persona->numero_documento }}
                                    </span>
                                </td>

                                <td>
                                    <div class="cell-content" style="gap: 0.75rem;">
                                        <div>
                                            {{ $datos->persona->primer_nombre }}
                                            {{ $datos->persona->primer_apellido }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Áreas, Grados y Secciones en un solo TD --}}
                                <td style="text-align: center;">
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

                                    @forelse($asigs as $asign)
                                        @php
                                            $areaFormacion = optional($asign->areaEstudios)->areaFormacion;
                                            $grado = optional($asign->grado);
                                            $seccion = optional($asign->seccion); 
                                        @endphp

                                        <div style="margin-bottom: 0.5rem;">
                                            {{-- Área de formación --}}
                                            <span title="{{ $areaFormacion->nombre_area_formacion ?? 'N/A' }}">
                                                <i class="fas fa-graduation-cap"></i>
                                                {{ Str::limit($areaFormacion->nombre_area_formacion ?? 'N/A', 20) }}
                                            </span>

                                            {{-- Grado --}}
                                            <span title="Grado: {{ $grado->numero_grado ?? 'N/A' }}">
                                            - {{ $grado->numero_grado .'° ' ?? 'N/A' }}
                                            </span>

                                            {{-- Sección --}}
                                            <span title="Sección: {{ $seccion->nombre ?? 'N/A' }}">
                                                - {{ $seccion->nombre ?? 'N/A' }}
                                            </span>
                                        </div>
                                    @empty
                                        <span class="text-muted" style="font-size: 0.85rem;">
                                            <i class="fas fa-minus-circle"></i> Sin areas asignadas
                                        </span>
                                    @endforelse
                                </td>

                                {{-- Grupos Estables --}}
                                <td style="text-align: center;">
                                    @php
                                        // Filtrar solo asignaciones de tipo grupo_estable activas
                                        $grupos = $datos->asignacionesAreas
                                            ->where('status', true)
                                            ->where('tipo_asignacion', 'grupo_estable');
                                    @endphp

                                    @forelse($grupos as $asign)
                                        <span style="font-weight: bold; margin-bottom: 2rem;"
                                            title="{{ optional($asign->grupoEstable)->nombre_grupo_estable ?? 'N/A' }} - Nivel: {{ optional($asign->gradoGrupoEstable)->numero_grado ?? 'N/A' }}">
                                            <i class="fas fa-users"></i>
                                            {{ optional($asign->grupoEstable)->nombre_grupo_estable ?? 'N/A' }}
                                            -
                                            {{ optional($asign->gradoGrupoEstable)->numero_grado ?? 'N/A' }}
                                        </span>
                                        @if (!$loop->last)
                                            <br>
                                        @endif
                                    @empty
                                        <span class="text-muted" style="font-size: 0.85rem;">
                                            <i class="fas fa-minus-circle"></i> Sin grupos asignados
                                        </span>
                                    @endforelse
                                </td>


                                {{-- Status --}}
                                <td style="text-align: center;">
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
                                                {{-- Ver mas --}}
                                                <li>
                                                    <button class="dropdown-item d-flex align-items-center text-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewModal{{ $datos->id }}"
                                                        title="Ver detalles">
                                                        <i class="fas fa-eye me-2"></i>
                                                        Ver más
                                                    </button>
                                                </li>

                                                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                    {{-- Editar --}}
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center text-warning"
                                                            href="{{ route('admin.transacciones.docente_area_grado.edit', $datos->id) }}"
                                                            title="Editar">
                                                            <i class="fas fa-pen me-2"></i>
                                                            Editar
                                                        </a>
                                                    </li>

                                                    {{-- Inactivar --}}
                                                    <li>
                                                        <button class="dropdown-item d-flex align-items-center text-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                            title="Inactivar">
                                                            <i class="fas fa-ban me-2"></i>
                                                            Inactivar
                                                        </button>
                                                    </li>
                                                @endif
                                                                                                    {{-- PDF Materias --}}
                                                    <li>
                                                        <a href="{{ route('admin.docente.DocenteMateriaIndividual', ['id' => $datos->id] + request()->query()) }}"
                                                            class="dropdown-item d-flex align-items-center text-danger"
                                                            target="_blank"
                                                            title="Generar PDF de materias">
                                                            <i class="fas fa-file-pdf me-2"></i>
                                                            Area de formacion PDF
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
                            <div class="modal fade" id="confirmarEliminar{{ $datos->id }}" tabindex="-1"
                                aria-labelledby="modalLabel{{ $datos->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content modal-modern">
                                        <div class="modal-header-delete">
                                            <div class="modal-icon-delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </div>
                                            <h5 class="modal-title-delete">Confirmar Inactivación</h5>
                                            <button type="button" class="btn-close-modal" data-bs-dismiss="modal"
                                                aria-label="Cerrar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="modal-body-delete">
                                            <p>¿Deseas inactivar esta asignación?</p>
                                            <p class="delete-warning">
                                                {{-- La asignación se marcará como inactiva pero los datos permanecerán en el
                                                    sistema. --}}
                                            </p>
                                        </div>
                                        <div class="modal-footer-delete">
                                            <form
                                                action="{{ route('admin.transacciones.docente_area_grado.destroyAsignacion', $datos->id) }}"
                                                method="POST" class="w-100">
                                                @csrf
                                                @method('DELETE')
                                                <div class="footer-buttons">
                                                    <button type="button" class="btn-modal-cancel"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn-modal-delete">Inactivar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-user-slash"></i>
                                        </div>
                                        <h4>No hay docentes con materias y años asignados</h4>
                                        <p>Debe registrar docentes primero en el módulo de Docentes y asignarles
                                            materias y años</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    @include('admin.transacciones.docente_area_grado.modales.filtroModal')

    {{-- Paginación --}}
    <x-pagination :paginator="$docentes" />



@endsection

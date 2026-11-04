@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">

@stop

@section('title', 'Histórico de Inscripciones Estudiantil')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h1 class="title-main">Histórico de Inscripciones Estudiantil</h1>
                    <p class="title-subtitle">Consulta de registros por Calendario Escolar</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="header-left">
                        <div class="header-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div>
                            <h3>Registros académicos</h3>
                            <p>
                                {{ $tipo === 'docentes' ? $docentes->total() : $inscripciones->total() }}
                                registros encontrados
                            </p>
                        </div>
                    </div>
                    <div class="header-right">
                        @php
                            $anioActivo = \App\Models\AnioEscolar::activos()->first();
                            $anioExtendido = \App\Models\AnioEscolar::where('status', 'Extendido')->first();
                            $mostrarAnio = $anioActivo ?? $anioExtendido;
                        @endphp
                        @if ($mostrarAnio)
                            <div
                                class="d-flex align-items-center justify-content-between bg-light rounded px-2 py-1  border">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary rounded me-2 py-1 px-2" style="font-size: 0.7rem;">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        Calendario Escolar
                                    </span>
                                    <div class="d-flex align-items-center" style="font-size: 0.8rem;">
                                        <span class="text-muted me-2">
                                            <i class="fas fa-play-circle text-primary me-1"></i>
                                            {{ \Carbon\Carbon::parse($mostrarAnio->inicio_anio_escolar)->format('d/m/Y') }}
                                        </span>
                                        <span class="text-muted me-2">
                                            <i class="fas fa-flag-checkered text-danger me-1"></i>
                                            {{ \Carbon\Carbon::parse($mostrarAnio->cierre_anio_escolar)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div
                                class="d-flex align-items-center justify-content-between bg-warning bg-opacity-10 rounded px-2 py-1  border border-warning">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle text-warning me-1" style="font-size: 0.8rem;"></i>
                                    <span class="fw-semibold" style="font-size: 0.8rem;">Sin año activo</span>
                                </div>

                            </div>
                        @endif
                    </div>
                    <div>
                        <button class="btn-modal-create" data-bs-toggle="modal" data-bs-target="#modalFiltros">
                            <i class="fas fa-filter"></i>
                            Filtros
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            @if ($tipo === 'inscripciones')
                                <th>Calendario Escolar</th>
                                <th class="text-center">Alumno</th>
                                <th class="text-center">Nivel Academico</th>
                                <th class="text-center">Sección</th>
                                <th class="text-center">Tipo de Inscripción</th>
                                <th class="text-center">Acciones</th>
                            @elseif($tipo === 'docentes')
                                <th class="text-center">Calendario Escolar</th>
                                <th class="text-center">Docente</th>
                                <th class="text-center">Areas de Formacion</th>
                                <th class="text-center">Grupo Estable</th>
                                <th class="text-center">Acciones</th>
                            @endif
                        </thead>
                        <tbody class="text-center">
                            @if ($tipo === 'inscripciones')
                                @if ($modalidad === 'prosecucion')
                                    @forelse ($inscripciones as $prosecucion)
                                        <tr>
                                            <td>
                                                {{ optional($prosecucion->anioEscolar)->inicio_anio_escolar
                                                    ? \Carbon\Carbon::parse($prosecucion->anioEscolar->inicio_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                                -
                                                {{ optional($prosecucion->anioEscolar)->cierre_anio_escolar
                                                    ? \Carbon\Carbon::parse($prosecucion->anioEscolar->cierre_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                            </td>
                                            <td class="text-center">
                                                {{ $prosecucion->inscripcion->alumno->persona->primer_apellido }}
                                                {{ $prosecucion->inscripcion->alumno->persona->primer_nombre }}
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="badge bg-info mb-1">
                                                        {{ $prosecucion->grado->numero_grado }}°
                                                    </span>
                                                    @if ($prosecucion->repite_grado)
                                                        <small class="text-warning">
                                                            <i class="fas fa-redo"></i> Repite
                                                        </small>
                                                    @else
                                                        <small class="text-success">
                                                            <i class="fas fa-arrow-up"></i> Promovido
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {{ $prosecucion->seccion->nombre ?? '—' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    <i class="fas fa-arrows-alt-v"></i> Prosecución
                                                </span>
                                            </td>
                                            <td class="d-flex justify-content-center">
                                                <button class="action-btn btn-view" data-bs-toggle="modal"
                                                    data-bs-target="#showModalProsecucion-{{ $prosecucion->id }}"
                                                    title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @include('admin.historico.modales.showModalProsecucion', [
                                            'datos' => $prosecucion,
                                        ])
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <i class="fas fa-inbox fa-2x"></i>
                                                    <h4>No hay inscripciones de prosecución</h4>
                                                    <p>No se encontraron registros para el Calendario Escolar seleccionado</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                @elseif($modalidad === 'nuevo_ingreso')
                                    @forelse ($inscripciones as $inscripcion)
                                        <tr>
                                            <td>
                                                {{ optional($inscripcion->anioEscolar)->inicio_anio_escolar
                                                    ? \Carbon\Carbon::parse($inscripcion->anioEscolar->inicio_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                                -
                                                {{ optional($inscripcion->anioEscolar)->cierre_anio_escolar
                                                    ? \Carbon\Carbon::parse($inscripcion->anioEscolar->cierre_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                            </td>
                                            <td>
                                                {{ $inscripcion->alumno->persona->primer_apellido }}
                                                {{ $inscripcion->alumno->persona->primer_nombre }}
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    {{ $inscripcion->grado->numero_grado }}°
                                                </span>
                                            </td>
                                            <td>{{ $inscripcion->seccionAsignada->nombre ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-star"></i> Nuevo Ingreso
                                                </span>
                                            </td>
                                            <td class="d-flex justify-content-center">
                                                <button class="action-btn btn-view" data-bs-toggle="modal"
                                                    data-bs-target="#showModalNuevoIngreso{{ $inscripcion->id }}"
                                                    title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @include('admin.historico.modales.showModalNuevoIngreso', [
                                            'datos' => $inscripcion,
                                        ])
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <i class="fas fa-inbox fa-2x"></i>
                                                    <h4>No hay inscripciones de nuevo ingreso</h4>
                                                    <p>No se encontraron registros para el Calendario Escolar seleccionado</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                    @forelse ($inscripciones as $inscripcion)
                                        <tr>
                                            <td>
                                                {{ optional($inscripcion->anioEscolar)->inicio_anio_escolar
                                                    ? \Carbon\Carbon::parse($inscripcion->anioEscolar->inicio_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                                -
                                                {{ optional($inscripcion->anioEscolar)->cierre_anio_escolar
                                                    ? \Carbon\Carbon::parse($inscripcion->anioEscolar->cierre_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                            </td>
                                            <td>
                                                {{ $inscripcion->alumno->persona->primer_apellido }}
                                                {{ $inscripcion->alumno->persona->primer_nombre }}
                                            </td>
                                            <td>
                                                @if ($inscripcion->prosecucion)
                                                    <span class="badge bg-info">
                                                        {{ $inscripcion->prosecucion->grado->numero_grado }}°
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        {{ $inscripcion->grado->numero_grado }}°
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inscripcion->prosecucion)
                                                    {{ $inscripcion->prosecucion->seccion->nombre ?? '—' }}
                                                @else
                                                    {{ $inscripcion->seccionAsignada->nombre ?? '—' }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inscripcion->prosecucion)
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-arrows-alt-v"></i> Prosecución
                                                    </span>
                                                @elseif ($inscripcion->nuevoIngreso)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-star"></i> Nuevo Ingreso
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">—</span>
                                                @endif
                                            </td>
                                            <td class="d-flex justify-content-center">
                                                @if ($inscripcion->prosecucion)
                                                    <button class="action-btn btn-view" data-bs-toggle="modal"
                                                        data-bs-target="#showModalProsecucion-{{ $inscripcion->prosecucion->id }}"
                                                        title="Ver historial de prosecución">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                @elseif($inscripcion->nuevoIngreso)
                                                    <button class="action-btn btn-view" title="Ver detalles"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#showModalNuevoIngreso{{ $inscripcion->id }}">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                @else
                                                @endif

                                            </td>
                                        </tr>
                                        @if ($inscripcion->prosecucion)
                                            @include('admin.historico.modales.showModalProsecucion', [
                                                'datos' => $inscripcion->prosecucion,
                                            ])
                                        @endif

                                        @if ($inscripcion->nuevoIngreso)
                                            @include('admin.historico.modales.showModalNuevoIngreso', [
                                                'datos' => $inscripcion,
                                            ])
                                        @endif

                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <i class="fas fa-inbox fa-2x"></i>
                                                    <h4>No hay inscripciones</h4>
                                                    <p>No se encontraron registros para el Calendario Escolar seleccionado</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                @endif
                            @endif
                            {{-- DOCENTES --}}
                            @if ($tipo === 'docentes')
                                @forelse ($docentes as $docente)
                                    @php
                                        $asignaciones = $docente->asignacionesAreas->where('status', true);
                                    @endphp

                                    <tr>
                                        <td>
                                            {{ optional($docente->anioEscolar)->inicio_anio_escolar
                                                ? \Carbon\Carbon::parse($docente->anioEscolar->inicio_anio_escolar)->format('d/m/Y')
                                                : '—' }}
                                            -
                                            {{ optional($docente->anioEscolar)->cierre_anio_escolar
                                                ? \Carbon\Carbon::parse($docente->anioEscolar->cierre_anio_escolar)->format('d/m/Y')
                                                : '—' }}
                                        </td>
                                        <td>
                                            {{ $docente->persona->primer_apellido }}
                                            {{ $docente->persona->primer_nombre }}
                                        </td>
                                        <td style="text-align: center;">
                                            @php
                                                $asigs = $asignaciones
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
                                                    <span title="{{ $areaFormacion->nombre_area_formacion ?? 'N/A' }}">
                                                        <i class="fas fa-graduation-cap"></i>
                                                        {{ Str::limit($areaFormacion->nombre_area_formacion ?? 'N/A', 30) }}
                                                    </span>
                                                    <span title="Grado: {{ $grado->numero_grado ?? 'N/A' }}">
                                                        - {{ $grado->numero_grado . '° ' ?? 'N/A' }}
                                                    </span>
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
                                        <td style="text-align: center;">
                                            @php
                                                $grupos = $asignaciones
                                                    ->where('status', true)
                                                    ->where('tipo_asignacion', 'grupo_estable');
                                            @endphp
                                            @forelse($grupos as $asign)
                                                <span style="font-weight: bold; margin-bottom: 2rem;"
                                                    title="{{ optional($asign->grupoEstable)->nombre_grupo_estable ?? 'N/A' }} - Grado: {{ optional($asign->gradoGrupoEstable)->numero_grado ?? 'N/A' }}">
                                                    <i class="fas fa-users"></i>
                                                    {{ Str::limit($asign->grupoEstable->nombre_grupo_estable ?? 'N/A', 20) }}
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
                                        <td class="d-flex justify-content-center">
                                            <button class="action-btn btn-view" title="Ver detalles"
                                                data-bs-toggle="modal"
                                                data-bs-target="#showModalDocente{{ $docente->id }}">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @include('admin.historico.modales.showModalDocente', [
                                        'datos' => $docente,
                                    ])
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox fa-2x"></i>
                                                <h4>No hay docentes</h4>
                                                <p>Seleccione otro Calendario Escolar</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @include('admin.historico.modales.filtroModal')
        <div class="mt-4">
            <x-pagination :paginator="$tipo === 'docentes' ? $docentes : $inscripciones" />
        </div>

    </div>
@endsection

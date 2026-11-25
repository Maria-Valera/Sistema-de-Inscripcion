@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Histórico de Inscripciones Estudiantil')

@section('content_header')
    <div class="content-header-modern hero-header-horario hero-header-modern" style="background: linear-gradient(135deg, #4A6FC1 0%, #3B5BB3 100%) !important; color: #FFFFFF !important;">
        <div class="header-content d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper" style="background: rgba(255, 255, 255, 0.2) !important; color: #FFFFFF !important; width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fas fa-history text-white"></i>
                </div>
                <div>
                    <h1 class="title-main text-white m-0" style="color: #FFFFFF !important; font-size: 1.45rem; font-weight: 700;">Histórico de Inscripciones Estudiantil</h1>
                    <p class="title-subtitle text-white m-0" style="color: rgba(255, 255, 255, 0.9) !important; font-size: 0.88rem; margin-top: 0.25rem !important;">Consulta de registros por Calendario Escolar</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center" style="padding: 1.25rem 1.5rem !important;">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon header-icon-pastel-blue" style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background-color: #EFF6FF !important; color: #4A6FC1 !important; display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; box-shadow: none !important; margin: 0 !important;">
                        <i class="fas fa-list" style="color: #4A6FC1 !important;"></i>
                    </div>
                    <div>
                        <h3 class="m-0" style="font-size: 1.15rem; font-weight: 700; color: #1E293B;">Registros académicos</h3>
                        <p class="mb-0 text-muted" style="font-size: 0.85rem;">
                            {{ $tipo === 'docentes' ? $docentes->total() : $inscripciones->total() }}
                            registros encontrados
                        </p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-filter-global" type="button" data-bs-toggle="offcanvas" data-bs-target="#historicoFiltros" aria-controls="historicoFiltros">
                        <i class="fas fa-sliders-h me-1"></i> Filtrar
                    </button>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table table-modern table-hover align-middle w-100">
                        <thead>
                            <tr>
                                @if ($tipo === 'inscripciones')
                                    <th>Calendario Escolar</th>
                                    <th class="text-center">Alumno</th>
                                    <th class="text-center">Nivel Académico</th>
                                    <th class="text-center">Sección</th>
                                    <th class="text-center">Tipo de Inscripción</th>
                                    <th class="text-center">Acciones</th>
                                @elseif($tipo === 'docentes')
                                    <th class="text-center">Calendario Escolar</th>
                                    <th class="text-center">Docente</th>
                                    <th class="text-center">Áreas de Formación</th>
                                    <th class="text-center">Grupo Estable</th>
                                    <th class="text-center">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @if ($tipo === 'inscripciones')
                                @if ($modalidad === 'prosecucion')
                                    @forelse ($inscripciones as $prosecucion)
                                        <tr>
                                            <td class="align-middle">
                                                {{ optional($prosecucion->anioEscolar)->inicio_anio_escolar
                                                    ? \Carbon\Carbon::parse($prosecucion->anioEscolar->inicio_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                                -
                                                {{ optional($prosecucion->anioEscolar)->cierre_anio_escolar
                                                    ? \Carbon\Carbon::parse($prosecucion->anioEscolar->cierre_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                            </td>
                                            <td class="text-center align-middle">
                                                {{ $prosecucion->inscripcion->alumno->persona->primer_apellido }}
                                                {{ $prosecucion->inscripcion->alumno->persona->primer_nombre }}
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <span class="badge-pastel-blue mb-1">
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
                                            <td class="text-center align-middle">
                                                {{ $prosecucion->seccion->nombre ?? '—' }}
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge-pastel-purple">
                                                    <i class="fas fa-arrows-alt-v"></i> Prosecución
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#showModalProsecucion-{{ $prosecucion->id }}"
                                                    class="text-secondary hover-text-primary"
                                                    style="background: transparent; border: none; box-shadow: none; text-decoration: none; padding: 0;"
                                                    title="Ver Detalles">
                                                    <i class="fas fa-eye" style="font-size: 1.25rem;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @include('admin.historico.modales.showModalProsecucion', [
                                            'datos' => $prosecucion,
                                        ])
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state text-center py-5">
                                                    <div class="empty-icon mb-3" style="width: 72px; height: 72px; border-radius: 50%; background: #F1F5F9; color: #94A3B8; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto;">
                                                        <i class="fas fa-inbox"></i>
                                                    </div>
                                                    <h4 style="font-size: 1.15rem; font-weight: 600; color: #1E293B; margin-bottom: 0.5rem;">
                                                        No hay inscripciones de prosecución
                                                    </h4>
                                                    <p style="font-size: 0.9rem; color: #64748B; margin-bottom: 0;">
                                                        No se encontraron registros para el Calendario Escolar seleccionado.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                @elseif($modalidad === 'nuevo_ingreso')
                                    @forelse ($inscripciones as $inscripcion)
                                        <tr>
                                            <td class="align-middle">
                                                {{ optional($inscripcion->anioEscolar)->inicio_anio_escolar
                                                    ? \Carbon\Carbon::parse($inscripcion->anioEscolar->inicio_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                                -
                                                {{ optional($inscripcion->anioEscolar)->cierre_anio_escolar
                                                    ? \Carbon\Carbon::parse($inscripcion->anioEscolar->cierre_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                            </td>
                                            <td class="text-center align-middle">
                                                {{ $inscripcion->alumno->persona->primer_apellido }}
                                                {{ $inscripcion->alumno->persona->primer_nombre }}
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge-pastel-blue">
                                                    {{ $inscripcion->grado->numero_grado }}°
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">{{ $inscripcion->seccionAsignada->nombre ?? '—' }}</td>
                                            <td class="text-center align-middle">
                                                <span class="badge-pastel-green">
                                                    <i class="fas fa-star"></i> Nuevo Ingreso
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#showModalNuevoIngreso{{ $inscripcion->id }}"
                                                    class="text-secondary hover-text-primary"
                                                    style="background: transparent; border: none; box-shadow: none; text-decoration: none; padding: 0;"
                                                    title="Ver Detalles">
                                                    <i class="fas fa-eye" style="font-size: 1.25rem;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @include('admin.historico.modales.showModalNuevoIngreso', [
                                            'datos' => $inscripcion,
                                        ])
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state text-center py-5">
                                                    <div class="empty-icon mb-3" style="width: 72px; height: 72px; border-radius: 50%; background: #F1F5F9; color: #94A3B8; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto;">
                                                        <i class="fas fa-inbox"></i>
                                                    </div>
                                                    <h4 style="font-size: 1.15rem; font-weight: 600; color: #1E293B; margin-bottom: 0.5rem;">
                                                        No hay inscripciones de nuevo ingreso
                                                    </h4>
                                                    <p style="font-size: 0.9rem; color: #64748B; margin-bottom: 0;">
                                                        No se encontraron registros para el Calendario Escolar seleccionado.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                    @forelse ($inscripciones as $inscripcion)
                                        <tr>
                                            <td class="align-middle">
                                                {{ optional($inscripcion->anioEscolar)->inicio_anio_escolar
                                                    ? \Carbon\Carbon::parse($inscripcion->anioEscolar->inicio_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                                -
                                                {{ optional($inscripcion->anioEscolar)->cierre_anio_escolar
                                                    ? \Carbon\Carbon::parse($inscripcion->anioEscolar->cierre_anio_escolar)->format('d/m/Y')
                                                    : '—' }}
                                            </td>
                                            <td class="text-center align-middle">
                                                {{ $inscripcion->alumno->persona->primer_apellido }}
                                                {{ $inscripcion->alumno->persona->primer_nombre }}
                                            </td>
                                            <td class="text-center align-middle">
                                                @if ($inscripcion->prosecucion)
                                                    <span class="badge-pastel-blue">
                                                        {{ $inscripcion->prosecucion->grado->numero_grado }}°
                                                    </span>
                                                @else
                                                    <span class="badge-pastel-blue">
                                                        {{ $inscripcion->grado->numero_grado }}°
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                @if ($inscripcion->prosecucion)
                                                    {{ $inscripcion->prosecucion->seccion->nombre ?? '—' }}
                                                @else
                                                    {{ $inscripcion->seccionAsignada->nombre ?? '—' }}
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                @if ($inscripcion->prosecucion)
                                                    <span class="badge-pastel-purple">
                                                        <i class="fas fa-arrows-alt-v"></i> Prosecución
                                                    </span>
                                                @elseif ($inscripcion->nuevoIngreso)
                                                    <span class="badge-pastel-green">
                                                        <i class="fas fa-star"></i> Nuevo Ingreso
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                @if ($inscripcion->prosecucion)
                                                    <a href="javascript:void(0)" data-bs-toggle="modal"
                                                        data-bs-target="#showModalProsecucion-{{ $inscripcion->prosecucion->id }}"
                                                        class="text-secondary hover-text-primary"
                                                        style="background: transparent; border: none; box-shadow: none; text-decoration: none; padding: 0;"
                                                        title="Ver detalles">
                                                        <i class="fas fa-eye" style="font-size: 1.25rem;"></i>
                                                    </a>
                                                @elseif($inscripcion->nuevoIngreso)
                                                    <a href="javascript:void(0)" data-bs-toggle="modal"
                                                        data-bs-target="#showModalNuevoIngreso{{ $inscripcion->id }}"
                                                        class="text-secondary hover-text-primary"
                                                        style="background: transparent; border: none; box-shadow: none; text-decoration: none; padding: 0;"
                                                        title="Ver detalles">
                                                        <i class="fas fa-eye" style="font-size: 1.25rem;"></i>
                                                    </a>
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
                                                <div class="empty-state text-center py-5">
                                                    <div class="empty-icon mb-3" style="width: 72px; height: 72px; border-radius: 50%; background: #F1F5F9; color: #94A3B8; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto;">
                                                        <i class="fas fa-inbox"></i>
                                                    </div>
                                                    <h4 style="font-size: 1.15rem; font-weight: 600; color: #1E293B; margin-bottom: 0.5rem;">
                                                        No hay inscripciones registradas
                                                    </h4>
                                                    <p style="font-size: 0.9rem; color: #64748B; margin-bottom: 0;">
                                                        No se encontraron registros para el Calendario Escolar seleccionado.
                                                    </p>
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
                                        <td class="align-middle">
                                            {{ optional($docente->anioEscolar)->inicio_anio_escolar
                                                ? \Carbon\Carbon::parse($docente->anioEscolar->inicio_anio_escolar)->format('d/m/Y')
                                                : '—' }}
                                            -
                                            {{ optional($docente->anioEscolar)->cierre_anio_escolar
                                                ? \Carbon\Carbon::parse($docente->anioEscolar->cierre_anio_escolar)->format('d/m/Y')
                                                : '—' }}
                                        </td>
                                        <td class="text-center align-middle">
                                            {{ $docente->persona->primer_apellido }}
                                            {{ $docente->persona->primer_nombre }}
                                        </td>
                                        <td style="text-align: center;" class="align-middle">
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
                                                    <i class="fas fa-minus-circle"></i> Sin áreas asignadas
                                                </span>
                                            @endforelse
                                        </td>
                                        <td style="text-align: center;" class="align-middle">
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
                                        <td class="text-center align-middle">
                                            <a href="javascript:void(0)" data-bs-toggle="modal"
                                                data-bs-target="#showModalDocente{{ $docente->id }}"
                                                class="text-secondary hover-text-primary"
                                                style="background: transparent; border: none; box-shadow: none; text-decoration: none; padding: 0;"
                                                title="Ver Detalles">
                                                <i class="fas fa-eye" style="font-size: 1.25rem;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @include('admin.historico.modales.showModalDocente', [
                                        'datos' => $docente,
                                    ])
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state text-center py-5">
                                                <div class="empty-icon mb-3" style="width: 72px; height: 72px; border-radius: 50%; background: #F1F5F9; color: #94A3B8; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto;">
                                                    <i class="fas fa-inbox"></i>
                                                </div>
                                                <h4 style="font-size: 1.15rem; font-weight: 600; color: #1E293B; margin-bottom: 0.5rem;">
                                                    No hay docentes registrados
                                                </h4>
                                                <p style="font-size: 0.9rem; color: #64748B; margin-bottom: 0;">
                                                    Seleccione otro Calendario Escolar para visualizar los registros.
                                                </p>
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
        <x-ui.filter-panel target="historicoFiltros" formId="historico-filtros-form" title="Filtrar">
            <form id="historico-filtros-form" method="GET" action="{{ route('admin.historico.index') }}">
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.88rem;">
                        <i class="fas fa-calendar-alt text-primary me-1"></i> Calendario Escolar
                    </label>
                    <select name="anio_escolar_id" class="form-select form-control-modern">
                        <option value="">Todos los años</option>
                        @foreach ($anios as $anio)
                            <option value="{{ $anio->id }}"
                                {{ $anio->id == request('anio_escolar_id') ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($anio->inicio_anio_escolar)->format('Y') }}
                                -
                                {{ \Carbon\Carbon::parse($anio->cierre_anio_escolar)->format('Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.88rem;">
                        <i class="fas fa-layer-group text-primary me-1"></i> Módulos
                    </label>
                    <select name="tipo" id="tipoSelectHistorico" class="form-select form-control-modern">
                        <option value="inscripciones"
                            {{ request('tipo', 'inscripciones') == 'inscripciones' ? 'selected' : '' }}>
                            Inscripciones
                        </option>
                        <option value="docentes" {{ request('tipo') == 'docentes' ? 'selected' : '' }}>
                            Docentes
                        </option>
                    </select>
                </div>

                <div class="mb-3" id="filtroModalidad"
                    style="{{ request('tipo', 'inscripciones') === 'inscripciones' ? '' : 'display:none;' }}">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.88rem;">
                        <i class="fas fa-user-graduate text-primary me-1"></i> Modalidad de Inscripción
                    </label>
                    <select name="modalidad" class="form-select form-control-modern">
                        <option value="">Todas</option>
                        <option value="nuevo_ingreso"
                            {{ request('modalidad') === 'nuevo_ingreso' ? 'selected' : '' }}>
                            Nuevo Ingreso
                        </option>
                        <option value="prosecucion" {{ request('modalidad') === 'prosecucion' ? 'selected' : '' }}>
                            Prosecución
                        </option>
                    </select>
                </div>

                <div class="mb-3" id="filtroGradoSeccion">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.88rem;">
                        <i class="fas fa-graduation-cap text-primary me-1"></i> Nivel Académico
                    </label>
                    <select name="grado_id" id="gradoSelect" class="form-select form-control-modern">
                        <option value="">Todos los niveles académicos</option>
                        @foreach ($grados as $grado)
                            <option value="{{ $grado->id }}"
                                {{ request('grado_id') == $grado->id ? 'selected' : '' }}>
                                {{ $grado->numero_grado }}°
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.88rem;">
                        <i class="fas fa-chalkboard text-primary me-1"></i> Sección
                    </label>
                    <select name="seccion_id" id="seccionSelect" class="form-select form-control-modern"
                        @disabled(!request('grado_id'))>
                        <option value="">
                            {{ request('grado_id') ? 'Todas las secciones' : 'Primero seleccione un nivel académico' }}
                        </option>
                        @foreach ($secciones as $seccion)
                            <option value="{{ $seccion->id }}"
                                {{ request('seccion_id') == $seccion->id ? 'selected' : '' }}>
                                {{ $seccion->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </x-ui.filter-panel>

        <div class="mt-4">
            <x-pagination :paginator="$tipo === 'docentes' ? $docentes : $inscripciones" />
        </div>

    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tipoSelect = document.getElementById('tipoSelectHistorico');
            const modalidadDiv = document.getElementById('filtroModalidad');
            if (tipoSelect && modalidadDiv) {
                tipoSelect.addEventListener('change', function() {
                    if (this.value === 'inscripciones') {
                        modalidadDiv.style.display = 'block';
                    } else {
                        modalidadDiv.style.display = 'none';
                    }
                });
            }

            const gradoSelect = document.getElementById('gradoSelect');
            const seccionSelect = document.getElementById('seccionSelect');
            const baseUrl = "{{ url('admin/historico/secciones-por-grado') }}";

            if (gradoSelect && seccionSelect) {
                gradoSelect.addEventListener('change', async function() {
                    const gradoId = this.value;
                    seccionSelect.innerHTML = '';
                    seccionSelect.disabled = true;

                    if (!gradoId) {
                        seccionSelect.innerHTML = '<option value="">Primero seleccione un nivel académico</option>';
                        return;
                    }

                    seccionSelect.innerHTML = '<option value="">Cargando secciones...</option>';

                    try {
                        const response = await fetch(`${baseUrl}/${gradoId}`);
                        if (!response.ok) throw new Error('HTTP ' + response.status);
                        const secciones = await response.json();
                        seccionSelect.innerHTML = '<option value="">Todas las secciones</option>';
                        if (!secciones.length) {
                            seccionSelect.innerHTML = '<option value="">No hay secciones para este nivel académico</option>';
                            return;
                        }
                        secciones.forEach(seccion => {
                            const option = document.createElement('option');
                            option.value = seccion.id;
                            option.textContent = seccion.nombre;
                            seccionSelect.appendChild(option);
                        });
                        seccionSelect.disabled = false;
                    } catch (error) {
                        console.error(error);
                        seccionSelect.innerHTML = '<option value="">Error al cargar secciones</option>';
                    }
                });
            }
        });
    </script>
@stop

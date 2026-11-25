@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/horario-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/horario-pilot.css') }}?v={{ @filemtime(public_path('css/horario-pilot.css')) }}">
@stop

@section('title', 'Gestión de Horarios')

@section('content_header')
    @php
        $activeFiltersCount = collect(['grado_id', 'seccion_id', 'area_formacion_id'])
            ->filter(fn ($key) => request()->filled($key))
            ->count();
    @endphp
    <div class="content-header-modern hero-header-horario">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Horarios</h1>
                    <p class="title-subtitle">Administración de horarios académicos</p>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                    @if ($hayErroresCriticos ?? false)
                        <div class="d-inline-flex flex-column align-items-end text-end" style="max-width: 270px;">
                            <div class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No puedes crear un horario porque hay errores en la carga académica. Corrige los errores en el panel inferior para habilitar este botón.">
                                <button type="button" class="btn d-inline-flex align-items-center gap-2 px-3 py-2 text-white shadow-none" disabled style="border-radius: 10px; font-weight: 600; cursor: not-allowed; background-color: rgba(255, 255, 255, 0.22); border: 1px solid rgba(255, 255, 255, 0.35); opacity: 0.9; pointer-events: none;">
                                    <i class="fas fa-lock text-warning"></i>
                                    <span>Nuevo Horario</span>
                                </button>
                            </div>
                            <span class="fw-semibold mt-1 d-inline-flex align-items-center gap-1" style="font-size: 0.78rem; line-height: 1.2; color: #FEF3C7;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>No puedes crear un horario porque hay errores en la carga académica.</span>
                            </span>
                            <span style="font-size: 0.72rem; line-height: 1.2; color: rgba(255, 255, 255, 0.8);">
                                Corrige los errores en el panel inferior para habilitar este botón.
                            </span>
                        </div>
                    @else
                        <button type="button" class="btn-hero-white" data-bs-toggle="modal" data-bs-target="#modalNuevoHorario">
                            <i class="fas fa-plus"></i>
                            <span>Nuevo Horario</span>
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('partials.alerta-modo-consulta')
        @php
            $erroresMostrar = session('errores_validacion') ?? ($erroresCriticos ?? []);

            $erroresAgrupados = [];
            foreach ($erroresMostrar as $err) {
                $grupoKey = $err['grupo'] ?? null;
                if (!$grupoKey) {
                    if (preg_match('/secci[oó]n\s+([A-Za-z0-9]+)\s*\(([^)]+)\)/ui', $err['mensaje'] ?? '', $m)) {
                        $grupoKey = "{$m[2]}, Sección {$m[1]}";
                    } elseif (isset($err['codigo']) && str_starts_with($err['codigo'], 'docente_')) {
                        $grupoKey = 'Docentes y Disponibilidad';
                    } elseif (isset($err['codigo']) && str_starts_with($err['codigo'], 'demanda_aula_')) {
                        $grupoKey = 'Aulas Especializadas';
                    } else {
                        $grupoKey = 'Configuración General';
                    }
                }
                $erroresAgrupados[$grupoKey][] = $err;
            }
            $totalGrupos = count($erroresAgrupados);
        @endphp

        @if (session('success') || session('error') || session('warning') || (($hayErroresCriticos ?? false) && !empty($erroresMostrar)))
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

                {{-- Acordeón Agrupado de Errores por Sección --}}
                @if (session('warning') || (($hayErroresCriticos ?? false) && !empty($erroresMostrar)))
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 1.25rem; border-radius: 8px;">
                        <div class="d-flex align-items-start gap-3">
                            <div style="color: #d97706; font-size: 1.5rem; margin-top: -2px;" class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2 pb-2 border-bottom border-warning">
                                    <div>
                                        <h5 class="fw-bold mb-0" style="color: #92400e;">
                                            <i class="fas fa-{{ !empty($erroresAgrupados) ? 'lock' : 'exclamation-circle' }} me-1"></i>
                                            {{ !empty($erroresAgrupados) ? 'Creación de Horarios Bloqueada' : 'Atención' }}
                                        </h5>
                                        <small style="color: #78350f;">
                                            {{ session('warning') ?? 'Se detectaron ' . count($erroresMostrar) . ' materias sin docente asignado en ' . $totalGrupos . ' sección(es).' }}
                                        </small>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.transacciones.docente_area_grado.index') }}" class="btn btn-sm btn-outline-warning text-dark fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3 py-1" style="background: #fef3c7; border-color: #d97706; border-radius: 6px;">
                                            <i class="fas fa-user-edit"></i>
                                            <span>Ir a Carga Académica</span>
                                        </a>
                                    </div>
                                </div>

                                @if (!empty($erroresAgrupados))
                                    <div class="mt-2">
                                        <div class="accordion accordion-seccion-error" id="accordionErrores">
                                            @php $grupoIdx = 0; @endphp
                                            @foreach ($erroresAgrupados as $nombreGrupo => $listaErrores)
                                                @php $grupoIdx++; @endphp
                                                @if ($grupoIdx === 6)
                                                    <div class="collapse" id="collapseMasSecciones">
                                                @endif

                                                <div class="accordion-item shadow-2xs">
                                                    <h2 class="accordion-header" id="heading_{{ $grupoIdx }}">
                                                        <button class="accordion-button collapsed py-2 px-3"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#collapse_{{ $grupoIdx }}"
                                                                aria-expanded="false"
                                                                aria-controls="collapse_{{ $grupoIdx }}">
                                                            <div class="d-flex align-items-center justify-content-between w-100 me-2">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <i class="fas fa-layer-group text-warning"></i>
                                                                    <span class="text-dark">{{ $nombreGrupo }}</span>
                                                                </div>
                                                                <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.78rem;">
                                                                    {{ count($listaErrores) }} {{ count($listaErrores) === 1 ? 'materia pendiente' : 'materias pendientes' }}
                                                                </span>
                                                            </div>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse_{{ $grupoIdx }}"
                                                         class="accordion-collapse collapse"
                                                         aria-labelledby="heading_{{ $grupoIdx }}">
                                                        <div class="accordion-body p-2 bg-light">
                                                            <div class="list-group list-group-flush rounded border">
                                                                @foreach ($listaErrores as $errItem)
                                                                    <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3 bg-white materia-error-item">
                                                                        <div class="d-flex align-items-start gap-2">
                                                                            <i class="fas fa-exclamation-circle text-danger mt-1"></i>
                                                                            <div>
                                                                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                                                                                    {{ $errItem['materia'] ?? $errItem['mensaje'] }}
                                                                                </div>
                                                                                <div class="text-muted" style="font-size: 0.78rem;">
                                                                                    {{ $errItem['detalle'] ?? 'Requiere asignación de docente activo.' }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <a href="{{ route('admin.transacciones.docente_area_grado.index') }}"
                                                                           class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 ms-3 px-2 py-1 flex-shrink-0"
                                                                           style="font-size: 0.76rem; border-radius: 4px;">
                                                                            <i class="fas fa-user-plus"></i>
                                                                            <span>Asignar</span>
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if ($loop->last && $grupoIdx >= 6)
                                                    </div>{{-- Fin collapseMasSecciones --}}
                                                @endif
                                            @endforeach
                                        </div>

                                        @if ($totalGrupos > 5)
                                            <div class="text-center mt-3 pt-2">
                                                <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 fw-semibold px-3 py-1 shadow-sm"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapseMasSecciones"
                                                        aria-expanded="false"
                                                        aria-controls="collapseMasSecciones"
                                                        id="btnToggleMasSecciones">
                                                    <i class="fas fa-chevron-down" id="iconoMasSecciones"></i>
                                                    <span id="textoMasSecciones">Ver todas las secciones con errores ({{ $totalGrupos - 5 }} más)</span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
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

        @php
            $horarioFilterFields = [
                [
                    'name' => 'grado_id',
                    'label' => 'Año / Grado',
                    'icon' => 'fas fa-graduation-cap',
                    'placeholder' => 'Todos los años',
                    'value' => request('grado_id'),
                    'options' => $grados->map(fn ($g) => [
                        'value' => (string) $g->id,
                        'label' => $g->numero_grado . '° Año',
                    ])->values()->all(),
                ],
                [
                    'name' => 'seccion_id',
                    'label' => 'Sección',
                    'icon' => 'fas fa-users',
                    'placeholder' => 'Todas las secciones',
                    'value' => request('seccion_id'),
                    'options' => $secciones->map(fn ($s) => [
                        'value' => (string) $s->id,
                        'label' => 'Sección ' . $s->nombre,
                    ])->values()->all(),
                ],
                [
                    'name' => 'area_formacion_id',
                    'label' => 'Área de Formación',
                    'icon' => 'fas fa-book',
                    'placeholder' => 'Todas las áreas',
                    'value' => request('area_formacion_id'),
                    'options' => $areasFormacion->map(fn ($a) => [
                        'value' => (string) $a->id,
                        'label' => $a->nombre_area_formacion,
                    ])->values()->all(),
                ],
            ];

            $activeChips = [];
            if (request()->filled('buscar')) {
                $activeChips[] = ['name' => 'buscar', 'label' => 'Búsqueda: ' . request('buscar')];
            }
            if (request()->filled('grado_id')) {
                $gradoSel = $grados->firstWhere('id', request('grado_id'));
                if ($gradoSel) {
                    $activeChips[] = ['name' => 'grado_id', 'label' => 'Año: ' . $gradoSel->numero_grado . '° Año'];
                }
            }
            if (request()->filled('seccion_id')) {
                $seccionSel = $secciones->firstWhere('id', request('seccion_id'));
                if ($seccionSel) {
                    $activeChips[] = ['name' => 'seccion_id', 'label' => 'Sección: ' . $seccionSel->nombre];
                }
            }
            if (request()->filled('area_formacion_id')) {
                $areaSel = $areasFormacion->firstWhere('id', request('area_formacion_id'));
                if ($areaSel) {
                    $activeChips[] = ['name' => 'area_formacion_id', 'label' => 'Área: ' . $areaSel->nombre_area_formacion];
                }
            }
        @endphp

        <x-ui.filter-panel
            id="horarioFilterPanel"
            title="Filtros de Horarios"
            :action="route('admin.horario.index')"
            external-search-selector="#horarioQuickSearch"
            :filters="$horarioFilterFields"
            results-target="#horariosResultadosWrapper"
        />

        <div data-filter-sync-id="horariosActiveChips">
            @if (!empty($activeChips))
                <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                    <span class="text-muted fw-semibold" style="font-size: 0.82rem;">Filtros aplicados:</span>
                    @foreach ($activeChips as $chip)
                        <span class="filter-chip-saas">
                            <span>{{ $chip['label'] }}</span>
                            <button type="button"
                                class="filter-chip-remove"
                                data-panel-id="horarioFilterPanel"
                                data-quick-remove-filter="{{ $chip['name'] }}"
                                aria-label="Quitar {{ $chip['label'] }}">
                                &times;
                            </button>
                        </span>
                    @endforeach
                    <button type="button"
                        class="btn-clear-chips ms-1"
                        data-panel-id="horarioFilterPanel"
                        data-quick-remove-filter="__all__">
                        Limpiar todo
                    </button>
                </div>
            @endif
        </div>

        <!-- Listado de Horarios Generados -->
        <x-ui.data-table
            title="Listado de Horarios"
            :subtitle="$horariosPaginados->total() . ' registros encontrados'"
            sync-count-id="horariosTotalCount"
            icon="fas fa-calendar-alt"
            search-id="horarioQuickSearch"
            search-name="buscar"
            :search-value="request('buscar', '')"
            search-placeholder="Buscar año, sección o área..."
            results-wrapper-id="horariosResultadosWrapper"
        >
            <x-slot:headerActions>
                <x-ui.filter-bar target="horarioFilterPanel" label="Filtrar" :active-count="$activeFiltersCount" />
            </x-slot:headerActions>

            <x-slot:thead>
                <tr class="text-center">
                    <th class="text-center" style="width: 64px;">#</th>
                    <th class="text-start">Año / Grado</th>
                    <th class="text-center">Sección</th>
                    <th class="text-start">Áreas de Formación</th>
                    <th class="text-center">Total Clases</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center" style="width: 96px;">Acciones</th>
                </tr>
            </x-slot:thead>

            @if($horariosPaginados->isEmpty())
                <tr class="row-empty">
                    <td colspan="7">
                        <div class="empty-state-saas">
                            <svg width="92" height="92" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="14" y="22" width="60" height="54" rx="12" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="2.2"/>
                                <path d="M14 36C14 29.3726 19.3726 24 26 24H62C68.6274 24 74 29.3726 74 36V40H14V36Z" fill="#EFF6FF"/>
                                <line x1="14" y1="40" x2="74" y2="40" stroke="#CBD5E1" stroke-width="2"/>
                                <line x1="30" y1="16" x2="30" y2="27" stroke="#94A3B8" stroke-width="2.5" stroke-linecap="round"/>
                                <line x1="58" y1="16" x2="58" y2="27" stroke="#94A3B8" stroke-width="2.5" stroke-linecap="round"/>
                                <rect x="24" y="48" width="12" height="8" rx="2.5" fill="#E2E8F0"/>
                                <rect x="40" y="48" width="12" height="8" rx="2.5" fill="#DBEAFE"/>
                                <rect x="24" y="60" width="12" height="8" rx="2.5" fill="#E2E8F0"/>
                                <rect x="40" y="60" width="12" height="8" rx="2.5" fill="#E2E8F0"/>
                                <circle cx="70" cy="68" r="16" fill="#EFF6FF" stroke="#4A6FC1" stroke-width="2.2"/>
                                <line x1="70" y1="61" x2="70" y2="75" stroke="#4A6FC1" stroke-width="2.5" stroke-linecap="round"/>
                                <line x1="63" y1="68" x2="77" y2="68" stroke="#4A6FC1" stroke-width="2.5" stroke-linecap="round"/>
                            </svg>
                            <h4 class="empty-state-saas-title">Aún no hay horarios para este año</h4>
                            <p class="empty-state-saas-text">
                                Organiza los bloques semanales de cada sección manualmente o mediante el generador automático.
                            </p>
                            @if (\App\Support\AnioEscolarSeleccionado::esActual() && !($hayErroresCriticos ?? false))
                                <button type="button" class="btn-saas-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoHorario">
                                    <i class="fas fa-plus"></i>
                                    <span>Crear mi primer horario</span>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @else
                @foreach($horariosPaginados as $index => $horario)
                    <tr data-horario-row
                        data-grado-id="{{ $horario['grado_id'] }}"
                        data-seccion-id="{{ $horario['seccion_id'] }}"
                        data-grado="{{ $horario['grado'] }}"
                        data-seccion="{{ $horario['seccion'] }}">
                        <td class="text-center text-muted fw-semibold">
                            {{ ($horariosPaginados->currentPage() - 1) * $horariosPaginados->perPage() + $index + 1 }}
                        </td>
                        <td class="text-start fw-bold text-dark">
                            {{ $horario['grado'] }}° Año
                        </td>
                        <td class="text-center">
                            <span class="pill-badge pill-solid-primary">
                                Sección {{ $horario['seccion'] }}
                            </span>
                        </td>
                        <td class="text-start text-muted text-truncate" style="max-width: 340px;" title="{{ $horario['areas'] }}">
                            {{ $horario['areas'] }}
                        </td>
                        <td class="text-center">
                            <span class="metric-inline-saas">
                                <i class="fas fa-layer-group text-muted" style="font-size: 0.8rem;"></i>
                                <span>{{ $horario['total_asignaciones'] }} {{ $horario['total_asignaciones'] === 1 ? 'bloque' : 'bloques' }}</span>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="pill-badge pill-success">
                                <span class="pill-dot"></span>
                                Activo
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown d-inline-block">
                                <button class="action-btn-saas"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    data-bs-boundary="viewport"
                                    aria-expanded="false"
                                    aria-label="Acciones">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-saas">
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewModalHorario"
                                            onclick="cargarHorarioEnModal({{ $horario['grado_id'] }}, {{ $horario['seccion_id'] }}, '{{ $horario['grado'] }}', '{{ $horario['seccion'] }}')">
                                            <i class="fas fa-eye me-2" style="color: #4A6FC1;"></i>
                                            Ver Horario
                                        </button>
                                    </li>
                                    @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                        <li>
                                            <a href="{{ route('admin.horario.edit', ['grado_id' => $horario['grado_id'], 'seccion_id' => $horario['seccion_id']]) }}"
                                                class="dropdown-item d-flex align-items-center">
                                                <i class="fas fa-pen me-2 text-warning"></i>
                                                Editar
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center"
                                            onclick="generarPDF({{ $horario['grado_id'] }}, {{ $horario['seccion_id'] }})">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                                            PDF Horario
                                        </button>
                                    </li>
                                    @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                        <li>
                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmarEliminar{{ $horario['id'] }}">
                                                <i class="fas fa-trash me-2"></i>
                                                Eliminar
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @endif
        </x-ui.data-table>

        <div data-filter-sync-id="horariosPaginationModals">
            <x-pagination :paginator="$horariosPaginados" />

            @foreach($horariosPaginados as $horario)
                @include('admin.horario.modales.inactivarModal', ['horario' => $horario])
            @endforeach
        </div>

        <!-- Incluir modal de visualización -->
        @include('admin.horario.modales.showModal')

        <!-- Modal Generar Nuevo Horario -->
        <div class="modal fade" id="modalNuevoHorario" tabindex="-1" aria-labelledby="modalNuevoHorarioLabel" aria-hidden="true" data-bs-backdrop="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.horario.create') }}" method="GET" id="formNuevoHorario">
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 38px; height: 38px; background-color: #EFF6FF; color: #4A6FC1;">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold mb-0" id="modalNuevoHorarioLabel" style="color: #0F172A; font-size: 1.05rem;">
                                        Generar Nuevo Horario
                                    </h5>
                                    <p class="text-muted mb-0" style="font-size: 0.82rem;">
                                        Selecciona el nivel y área para abrir la cuadrícula
                                    </p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body p-3">
                            <div class="row">
                                <!-- Columna Izquierda -->
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="nivel_id" class="form-label fw-bold text-dark mb-2" style="font-size: 0.88rem;">
                                        <i class="fas fa-layer-group me-2" style="color: #4A6FC1;"></i>Nivel Académico
                                    </label>
                                    <select name="nivel_id" id="nivel_id" class="form-select w-100" required>
                                        <option value="">Elija nivel...</option>
                                        @foreach($grados as $grado)
                                            <option value="{{ $grado->id }}">{{ $grado->numero_grado }}° Grado</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Columna Derecha -->
                                <div class="col-md-6">
                                    <label for="area_id" class="form-label fw-bold text-dark mb-2" style="font-size: 0.88rem;">
                                        <i class="fas fa-book me-2" style="color: #4A6FC1;"></i>Área de Formación
                                    </label>
                                    <select name="area_id" id="area_id" class="form-select w-100" disabled>
                                        <option value="">Todas las áreas (Horario completo)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-modal-continue" id="btnContinuarModal">
                                <span>Continuar</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function generarPDF(gradoId, seccionId) {
            try {
                const response = await fetch(`/admin/horario/${gradoId}/${seccionId}/show-api`);
                if (!response.ok) {
                    throw new Error('Error al obtener los datos del horario');
                }
                const data = await response.json();

                const dias = data.dias || [];
                const bloques = data.bloques || [];
                const asignaciones = data.asignaciones || [];
                const reservados = data.reservados || [];
                const gradoNombre = data.grado ? `${data.grado.numero_grado}° Grado` : 'Grado N/A';
                const seccionNombre = data.seccion ? `Sección "${data.seccion.nombre}"` : 'Sección N/A';
                const anioEscolar = data.anio_escolar || 'Año Escolar Activo';

                // Construir filas de la tabla
                let filasHtml = '';
                bloques.forEach(bloque => {
                    const numBloque = bloque.bloque || bloque.id;
                    const horas = (bloque.hora_inicio && bloque.hora_fin) ? `${bloque.hora_inicio} - ${bloque.hora_fin}` : `Bloque ${numBloque}`;

                    let celdasDias = '';
                    dias.forEach(dia => {
                        const numDia = dia.dia || dia.id;
                        const esReservado = reservados.some(r => Number(r.dia) === Number(numDia) && Number(r.bloque) === Number(numBloque));

                        if (esReservado) {
                            celdasDias += `<td style="background-color: #f1f5f9; color: #64748b; font-size: 11px; text-align: center; vertical-align: middle; font-weight: 600;">RESERVADO</td>`;
                        } else {
                            const asig = asignaciones.find(a => Number(a.dia) === Number(numDia) && Number(a.bloque) === Number(numBloque));
                            if (asig) {
                                const lock = asig.fijada ? ' 🔒' : '';
                                celdasDias += `
                                    <td style="padding: 6px; vertical-align: top; font-size: 11px; background-color: #f8fafc;">
                                        <div style="font-weight: bold; color: #1e293b; margin-bottom: 2px;">${asig.materia || 'Materia'}${lock}</div>
                                        <div style="color: #475569; font-size: 10px;">${asig.docente || 'Docente N/A'}</div>
                                        <div style="color: #64748b; font-size: 9px; margin-top: 2px;">📍 ${asig.aula || 'Aula regular'}</div>
                                    </td>
                                `;
                            } else {
                                celdasDias += `<td style="text-align: center; color: #cbd5e1; font-size: 12px; vertical-align: middle;">-</td>`;
                            }
                        }
                    });

                    filasHtml += `
                        <tr>
                            <td style="font-weight: 600; text-align: center; vertical-align: middle; background-color: #f8fafc; font-size: 11px; white-space: nowrap; padding: 6px 10px;">
                                Bloque ${numBloque}<br><span style="font-size: 9px; color: #64748b; font-weight: normal;">${horas}</span>
                            </td>
                            ${celdasDias}
                        </tr>
                    `;
                });

                let cabecerasDias = dias.map(d => `<th style="padding: 8px; text-align: center; background-color: #0f172a; color: #ffffff; font-size: 12px; font-weight: 600;">${d.nombre_dia || d.nombre}</th>`).join('');

                const printWindow = window.open('', '_blank', 'width=950,height=650');
                if (!printWindow) {
                    alert('Por favor, permita las ventanas emergentes en su navegador para imprimir el horario.');
                    return;
                }

                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="es">
                    <head>
                        <meta charset="UTF-8">
                        <title>Horario Escolar - ${gradoNombre} ${seccionNombre}</title>
                        <style>
                            @page { size: landscape; margin: 12mm; }
                            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; color: #1e293b; }
                            .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
                            .header h1 { margin: 0 0 6px 0; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; }
                            .header p { margin: 2px 0; font-size: 13px; color: #475569; }
                            table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
                            th, td { border: 1px solid #cbd5e1; }
                            .footer { margin-top: 20px; font-size: 10px; color: #94a3b8; text-align: right; }
                            @media print {
                                body { padding: 0; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>Horario Escolar Oficial</h1>
                            <p><strong>${gradoNombre} - ${seccionNombre}</strong> &nbsp;|&nbsp; <strong>Año Escolar:</strong> ${anioEscolar}</p>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th style="padding: 8px; width: 110px; text-align: center; background-color: #0f172a; color: #ffffff; font-size: 12px;">Hora / Bloque</th>
                                    ${cabecerasDias}
                                </tr>
                            </thead>
                            <tbody>
                                ${filasHtml}
                            </tbody>
                        </table>
                        <div class="footer">
                            Generado el ${new Date().toLocaleDateString('es-VE', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                        </div>
                        <script>
                            window.onload = function() {
                                window.print();
                            };
                        <\/script>
                    </body>
                    </html>
                `);
                printWindow.document.close();
            } catch (err) {
                console.error('Error al generar PDF:', err);
                alert('No se pudo generar el reporte del horario.');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar Tooltips de Bootstrap
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Toggle para ver más secciones con errores
            const collapseMas = document.getElementById('collapseMasSecciones');
            const textoMas = document.getElementById('textoMasSecciones');
            const iconoMas = document.getElementById('iconoMasSecciones');

            if (collapseMas && textoMas && iconoMas) {
                const textoOriginal = textoMas.textContent;
                collapseMas.addEventListener('show.bs.collapse', function () {
                    textoMas.textContent = 'Mostrar solo las primeras 5 secciones';
                    iconoMas.className = 'fas fa-chevron-up';
                });
                collapseMas.addEventListener('hide.bs.collapse', function () {
                    textoMas.textContent = textoOriginal;
                    iconoMas.className = 'fas fa-chevron-down';
                });
            }

            // Modal Generar Nuevo Horario: selects dependientes
            const modalNuevoHorarioEl = document.getElementById('modalNuevoHorario');
            const modalNivelSelect = document.getElementById('nivel_id');
            const modalAreaSelect = document.getElementById('area_id');

            if (modalNivelSelect && modalAreaSelect) {
                modalNivelSelect.addEventListener('change', async function () {
                    const nivelId = this.value;
                    if (nivelId) {
                        modalAreaSelect.disabled = false;
                        modalAreaSelect.innerHTML = '<option value="">Cargando áreas...</option>';
                        try {
                            const response = await fetch(`/api/areas-formacion/grado?grado_id=${nivelId}`);
                            const areas = await response.json();
                            modalAreaSelect.innerHTML = '<option value="">Todas las áreas (Horario completo)</option>';
                            areas.forEach(area => {
                                const option = document.createElement('option');
                                option.value = area.id;
                                option.textContent = area.nombre;
                                modalAreaSelect.appendChild(option);
                            });
                        } catch (error) {
                            console.error('Error al cargar áreas de formación:', error);
                            modalAreaSelect.innerHTML = '<option value="">Error al cargar áreas</option>';
                        }
                    } else {
                        modalAreaSelect.disabled = true;
                        modalAreaSelect.innerHTML = '<option value="">Todas las áreas (Horario completo)</option>';
                    }
                });
            }

            if (modalNuevoHorarioEl) {
                modalNuevoHorarioEl.addEventListener('hidden.bs.modal', function () {
                    if (modalNivelSelect) modalNivelSelect.value = '';
                    if (modalAreaSelect) {
                        modalAreaSelect.disabled = true;
                        modalAreaSelect.innerHTML = '<option value="">Todas las áreas (Horario completo)</option>';
                    }
                });

                // Cerrar al hacer clic fuera del cuadro del modal
                modalNuevoHorarioEl.addEventListener('click', function (e) {
                    if (e.target === modalNuevoHorarioEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modalNuevoHorarioEl).hide();
                    }
                });
            }

            // Click en fila de horario para abrir vista previa (ignorando botones/enlaces/dropdown)
            document.addEventListener('click', function (e) {
                const row = e.target.closest('tr[data-horario-row]');
                if (!row) return;
                if (e.target.closest('a, button, .dropdown, .dropdown-menu, input, select')) return;

                const modalEl = document.getElementById('viewModalHorario');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
                if (typeof cargarHorarioEnModal === 'function') {
                    cargarHorarioEnModal(
                        row.dataset.gradoId,
                        row.dataset.seccionId,
                        row.dataset.grado,
                        row.dataset.seccion
                    );
                }
            });

            // Feedback visual en el botón al confirmar eliminación de horario
            document.addEventListener('submit', function (e) {
                const form = e.target.closest('.form-eliminar-horario');
                if (!form) return;
                const btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i><span>Eliminando...</span>';
                }
            });
        });
    </script>
@endsection


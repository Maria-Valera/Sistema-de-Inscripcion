@extends('adminlte::page')

{{ Breadcrumbs::render('admin.cargas-academicas.index') }}

@section('title', 'Cargas Académicas')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h1 class="title-main">Cargas Académicas</h1>
                    <p class="title-subtitle">
                        Año Escolar: <strong>{{ $anioModelo ? \Carbon\Carbon::parse($anioModelo->inicio_anio_escolar)->format('Y') . '-' . \Carbon\Carbon::parse($anioModelo->cierre_anio_escolar)->format('Y') : 'No seleccionado' }}</strong>
                        @if ($anioModelo)
                            <span class="badge {{ $esActual ? 'badge-success' : 'badge-secondary' }} ml-2" style="font-size: 0.75rem;">
                                {{ $anioModelo->status }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>

            @can('carga_academica.gestionar')
                @if ($esActual)
                    <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalConfirmarGenerar">
                        <i class="fas fa-sync-alt"></i>
                        <span>Generar Cargas del Año</span>
                    </button>
                @endif
            @endcan
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <style>
        .badge-tipo-area {
            background-color: #EFF6FF;
            color: #1E40AF;
            border: 1px solid #BFDBFE;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .badge-tipo-grupo {
            background-color: #FAF5FF;
            color: #6B21A8;
            border: 1px solid #E9D5FF;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .search-bar-container {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            background: #fff;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
    </style>
@stop

@section('content')
    <div class="main-container">

        @if (!$esActual)
            <div class="alert alert-info alert-dismissible fade show" role="alert" style="border-left: 4px solid #17a2b8;">
                <i class="fas fa-info-circle mr-2"></i>
                Estás visualizando un año escolar en modo <strong>Solo Consulta</strong>. La generación y modificación de cargas académicas están bloqueadas.
            </div>
        @endif

        @if (!$calendario)
            <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-left: 4px solid #ffc107;">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                El año escolar actual no posee un <strong>Calendario Académico</strong> registrado. Para sincronizar o generar cargas académicas, primero debes crear el calendario del año.
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert" style="border-radius: 8px;">
                <i class="fas fa-check-circle fa-lg"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert" style="border-radius: 8px;">
                <i class="fas fa-exclamation-triangle fa-lg"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Resumen de generación si viene en sesión --}}
        @if (session('resumen_generacion'))
            @php $res = session('resumen_generacion'); @endphp
            @if (!empty($res['omisiones']) || !empty($res['duplicados_fusionados']))
                <div class="card card-outline card-warning mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-warning fw-bold"><i class="fas fa-info-circle mr-1"></i> Detalles de la Sincronización</h3>
                    </div>
                    <div class="card-body p-3">
                        @if (!empty($res['duplicados_fusionados']))
                            <p class="mb-1 text-muted small"><strong>Duplicados fusionados:</strong> {{ count($res['duplicados_fusionados']) }} registros detectados con la misma combinación académica.</p>
                        @endif
                        @if (!empty($res['omisiones']))
                            <p class="mb-1 text-danger small"><strong>Filas omitidas por inconsistencias en la plantilla base:</strong></p>
                            <ul class="small mb-0 text-danger ps-3">
                                @foreach (array_slice($res['omisiones'], 0, 5) as $om)
                                    <li>DocenteAreaGrado #{{ $om['docente_area_grado_id'] }}: {{ $om['motivo'] }}</li>
                                @endforeach
                                @if (count($res['omisiones']) > 5)
                                    <li><em>... y {{ count($res['omisiones']) - 5 }} omisiones adicionales.</em></li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- Barra de Búsqueda y Filtros en Vivo --}}
        <form method="GET" action="{{ route('admin.cargas-academicas.index') }}" class="search-bar-container">
            <div class="flex-grow-1" style="min-width: 240px;">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0" placeholder="Buscar por docente, cédula, área o grado/sección...">
                </div>
            </div>

            <div style="min-width: 160px;">
                <select name="tipo" class="form-select form-control" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    <option value="area" {{ request('tipo') === 'area' ? 'selected' : '' }}>Área de Formación</option>
                    <option value="grupo_estable" {{ request('tipo') === 'grupo_estable' ? 'selected' : '' }}>Grupo Estable</option>
                </select>
            </div>

            <div style="min-width: 140px;">
                <select name="activa" class="form-select form-control" onchange="this.form.submit()">
                    <option value="">Estado</option>
                    <option value="1" {{ request('activa') === '1' ? 'selected' : '' }}>Activas</option>
                    <option value="0" {{ request('activa') === '0' ? 'selected' : '' }}>Inactivas</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="background-color: #4A6FC1; border-color: #4A6FC1;">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>

            @if (request()->hasAny(['buscar', 'tipo', 'activa']))
                <a href="{{ route('admin.cargas-academicas.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> Limpiar
                </a>
            @endif
        </form>

        {{-- Tabla de Cargas Académicas --}}
        <div class="card shadow-sm" style="border-radius: 10px; border: 1px solid #E5E7EB; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #F8FAFC; color: #475569; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Tipo</th>
                            <th>Docente</th>
                            <th>Área / Grupo Estable</th>
                            <th>Grado y Sección</th>
                            <th>Estado</th>
                            <th>Sincronizada</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.9rem;">
                        @forelse ($cargas as $carga)
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#{{ $carga->id }}</td>
                                <td>
                                    @if ($carga->tipo === \App\Enums\TipoAsignacionNota::Area)
                                        <span class="badge-tipo-area">
                                            <i class="fas fa-book-open mr-1"></i> Área
                                        </span>
                                    @else
                                        <span class="badge-tipo-grupo">
                                            <i class="fas fa-users mr-1"></i> Grupo Estable
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        {{ $carga->docente?->persona ? $carga->docente->persona->primer_nombre . ' ' . $carga->docente->persona->primer_apellido : 'Docente N/A' }}
                                    </div>
                                    <small class="text-muted">C.I: {{ $carga->docente?->persona?->numero_documento ?? 'S/N' }}</small>
                                </td>
                                <td>
                                    @if ($carga->tipo === \App\Enums\TipoAsignacionNota::Area)
                                        <span class="fw-semibold text-dark">{{ $carga->areaFormacion?->nombre_area_formacion ?? 'N/A' }}</span>
                                        @if ($carga->areaFormacion?->siglas)
                                            <small class="text-muted d-block">({{ $carga->areaFormacion->siglas }})</small>
                                        @endif
                                    @else
                                        <span class="fw-semibold text-purple" style="color: #6B21A8;">{{ $carga->grupoEstable?->nombre_grupo_estable ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($carga->gradoSeccion)
                                        <span class="badge bg-light text-dark border">
                                            Año: <strong>{{ $carga->gradoSeccion->grado?->numero_grado ?? '-' }}°</strong> — Sección: <strong>{{ $carga->gradoSeccion->seccion?->nombre ?? '-' }}</strong>
                                        </span>
                                    @else
                                        <span class="text-muted small">No aplica (Grupo Estable)</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($carga->activa)
                                        <span class="badge bg-success" style="font-size: 0.75rem;">Activa</span>
                                    @else
                                        <span class="badge bg-danger" style="font-size: 0.75rem;">Inactiva</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $carga->sincronizada_at ? $carga->sincronizada_at->format('d/m/Y H:i') : ($carga->created_at ? $carga->created_at->format('d/m/Y H:i') : '-') }}
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                                        <p class="mb-1 fw-bold">No se encontraron cargas académicas para este año escolar.</p>
                                        @if ($esActual && $calendario)
                                            <p class="small">Haz clic en <strong>"Generar Cargas del Año"</strong> para importar la planificación académica base.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($cargas->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $cargas->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de Confirmación para Generar Cargas --}}
    @can('carga_academica.gestionar')
        @if ($esActual)
            <div class="modal fade" id="modalConfirmarGenerar" tabindex="-1" aria-labelledby="modalConfirmarGenerarLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                        <form action="{{ route('admin.cargas-academicas.generar') }}" method="POST">
                            @csrf
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold" id="modalConfirmarGenerarLabel">
                                    <i class="fas fa-sync-alt text-primary mr-2" style="color: #4A6FC1 !important;"></i> Generar Cargas Académicas
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-4">
                                <p class="text-muted mb-3">
                                    Esta acción sincronizará la asignación docente base (<code>docente_area_grados</code>) hacia las cargas académicas del año activo.
                                </p>
                                <div class="alert alert-light border p-3 small text-muted mb-0">
                                    <i class="fas fa-check-circle text-success mr-1"></i> El proceso es <strong>idempotente</strong>: actualizará las cargas existentes sin duplicar asignaciones y creará los pares necesarios en <code>grado_seccion</code>.
                                </div>

                                @if (session('error'))
                                    <div class="alert alert-danger mt-3 mb-0 small">
                                        <strong>Error reportado por el servidor:</strong> {{ session('error') }}
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary px-4" style="background-color: #4A6FC1; border-color: #4A6FC1;">
                                    <i class="fas fa-check mr-1"></i> Confirmar y Generar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('generar_error_modal'))
                var modalEl = document.getElementById('modalConfirmarGenerar');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            @endif
        });
    </script>
@stop

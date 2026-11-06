@extends('adminlte::page')

{{ Breadcrumbs::render('admin.malla-curricular.index') }}

@section('title', 'Malla Curricular')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main">Malla Curricular</h1>
                    <p class="title-subtitle">Configuración oficial de áreas de formación por grado y pensum (Circular 01 MPPE)</p>
                </div>
            </div>

            @can('malla_curricular.gestionar')
                <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearMalla">
                    <i class="fas fa-plus"></i>
                    <span>Nueva Asignatura</span>
                </button>
            @endcan
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <style>
        .badge-orden {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
        }
        .badge-literal {
            background-color: #fdf2f8;
            color: #be185d;
            border: 1px solid #fbcfe8;
            font-weight: 600;
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-numerica {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-weight: 600;
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .filter-panel {
            background: #ffffff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .badge-grado {
            background-color: #f1f5f9;
            color: #0f172a !important;
            border: 1px solid #cbd5e1;
            font-weight: 700;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
            font-size: 0.85rem;
            display: inline-block;
        }
        .table-modern td {
            color: #1e293b !important;
        }
        .modal-body-create,
        .modal-body-edit,
        .modal-body-view {
            max-height: calc(88vh - 140px) !important;
            overflow-y: auto !important;
        }
        .modal-body-create::-webkit-scrollbar,
        .modal-body-edit::-webkit-scrollbar,
        .modal-body-view::-webkit-scrollbar {
            width: 8px;
        }
        .modal-body-create::-webkit-scrollbar-thumb,
        .modal-body-edit::-webkit-scrollbar-thumb,
        .modal-body-view::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
        }
    </style>
@stop

@section('content')
    <div class="main-container">

        {{-- Alertas --}}
        @if (session('success') || session('error') || session('warning'))
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
                @if (session('warning'))
                    <div class="alert-modern alert alert-dismissible fade show" role="alert" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; color: #92400e;">
                        <div class="alert-icon" style="color: #f59e0b;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Advertencia</h4>
                            <p>{{ session('warning') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-times-circle"></i>
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

        {{-- Panel de Selección de Pensum y Grado --}}
        <div class="filter-panel">
            <form action="{{ route('admin.malla-curricular.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label font-weight-bold text-muted small mb-1">
                        <i class="fas fa-graduation-cap text-primary mr-1"></i> Plan de Estudio (Pensum)
                    </label>
                    <select name="pensum_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($pensums as $p)
                            <option value="{{ $p->id }}" {{ $pensumId == $p->id ? 'selected' : '' }}>
                                {{ $p->codigo }} - {{ $p->nombre }} {{ $p->es_vigente ? '★ (Vigente)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label font-weight-bold text-muted small mb-1">
                        <i class="fas fa-user-graduate text-primary mr-1"></i> Grado / Año Escolar
                    </label>
                    <select name="grado_id" class="form-select form-select-sm" onchange="this.form.submit()" style="color: #0f172a !important; background-color: #ffffff !important;">
                        <option value="">Todos los grados</option>
                        @foreach($grados as $g)
                            <option value="{{ $g->id }}" {{ $gradoId == $g->id ? 'selected' : '' }} style="color: #0f172a !important;">
                                {{ $g->nombre_grado ?? ($g->numero_grado ? $g->numero_grado . '° Año' : 'Grado ' . $g->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label font-weight-bold text-muted small mb-1">
                        <i class="fas fa-search text-primary mr-1"></i> Buscar Asignatura
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text"
                               name="buscar"
                               class="form-control"
                               placeholder="Nombre o siglas..."
                               value="{{ $busqueda }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-1 text-end">
                    @if(!empty($gradoId) || !empty($busqueda))
                        <a href="{{ route('admin.malla-curricular.index', ['pensum_id' => $pensumId]) }}" class="btn btn-outline-secondary btn-sm w-100" title="Restablecer">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tarjeta Principal con Listado --}}
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h3>Malla Curricular: {{ $pensumSeleccionado?->codigo ?? '31059' }} - {{ $pensumSeleccionado?->nombre ?? '' }}</h3>
                        <p>{{ $mallas->total() }} asignaturas registradas</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr style="text-align: center">
                                <th>Orden</th>
                                <th>Código</th>
                                <th>Grado</th>
                                <th style="text-align: left;">Área de Formación</th>
                                <th>Horas</th>
                                <th>Escala</th>
                                <th>Mínima</th>
                                <th>Componente</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @forelse ($mallas as $malla)
                                <tr>
                                    <td>
                                        <span class="badge-orden">{{ $malla->orden }}</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace font-weight-bold text-dark">{{ $malla->codigo }}</span>
                                    </td>
                                    <td>
                                        <span class="badge-grado">
                                            {{ $malla->grado ? ($malla->grado->nombre_grado ?? ($malla->grado->numero_grado . '° Año')) : 'N/A' }}
                                        </span>
                                    </td>
                                    <td style="text-align: left;">
                                        <div class="font-weight-bold text-dark">{{ $malla->area_formacion->nombre_area_formacion ?? 'N/A' }}</div>
                                        <small class="text-muted">Siglas: {{ $malla->area_formacion->siglas ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($malla->horas)
                                            <span class="font-weight-bold">{{ $malla->horas }} h</span>
                                        @else
                                            <span class="text-muted font-italic">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($malla->escala === 'literal')
                                            <span class="badge-literal">
                                                <i class="fas fa-font mr-1"></i> Literal (A-E)
                                            </span>
                                        @else
                                            <span class="badge-numerica">
                                                <i class="fas fa-hashtag mr-1"></i> Numérica (1-20)
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-success">{{ number_format($malla->nota_aprobatoria, 0) }} pts</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $malla->componente ?: 'Formación General' }}</small>
                                    </td>
                                    <td>
                                        @if($malla->status)
                                            <span class="status-badge status-active">
                                                <span class="status-dot"></span>
                                                Activa
                                            </span>
                                        @else
                                            <span class="status-badge status-inactive">
                                                <span class="status-dot"></span>
                                                Inactiva
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons justify-content-center">
                                            <div class="dropdown dropstart text-center">
                                                <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <button class="dropdown-item d-flex align-items-center text-primary"
                                                                type="button"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalVerMalla{{ $malla->id }}"
                                                                title="Ver detalles">
                                                            <i class="fas fa-eye me-2 mr-2"></i>
                                                            Ver detalles
                                                        </button>
                                                    </li>
                                                    @can('malla_curricular.gestionar')
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-warning"
                                                                    type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditarMalla{{ $malla->id }}"
                                                                    title="Editar">
                                                                <i class="fas fa-edit me-2 mr-2"></i>
                                                                Editar
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                                    type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#confirmarEliminarMalla{{ $malla->id }}"
                                                                    title="Remover de la malla">
                                                                <i class="fas fa-trash-alt me-2 mr-2"></i>
                                                                Remover
                                                            </button>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modales de Fila --}}
                                @include('admin.maestros.jose.malla_curricular.modales.showModal', ['malla' => $malla])
                                @include('admin.maestros.jose.malla_curricular.modales.editModal', ['malla' => $malla])

                                {{-- Modal Confirmar Eliminación --}}
                                <div class="modal fade" id="confirmarEliminarMalla{{ $malla->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                            <div class="modal-header-delete py-3 px-4 d-flex align-items-center">
                                                <div class="modal-icon-delete" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0;">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </div>
                                                <div class="ml-3 me-auto">
                                                    <h5 class="modal-title-delete mb-0" style="font-size: 1.15rem; font-weight: 700;">Remover de la Malla</h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;">{{ $malla->area_formacion->nombre_area_formacion ?? '' }}</small>
                                                </div>
                                                <button type="button" class="btn-close-modal ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body-delete p-4">
                                                <p>¿Deseas remover el área <strong>{{ $malla->area_formacion->nombre_area_formacion ?? '' }}</strong> de <strong>{{ $malla->grado->nombre_grado ?? '' }}</strong> en el plan <strong>{{ $malla->pensum->codigo ?? '' }}</strong>?</p>
                                                <p class="delete-warning mb-0">Esta acción desvinculará la asignatura del plan de estudio seleccionado.</p>
                                            </div>
                                            <div class="modal-footer-delete py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                                                <form action="{{ route('admin.malla-curricular.destroy', $malla) }}" method="POST" class="w-100 d-flex justify-content-end gap-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal" style="border-radius: 8px;">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit" class="btn btn-danger px-3 shadow-sm" style="border-radius: 8px;">
                                                        <i class="fas fa-trash-alt mr-1"></i> Remover
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fas fa-layer-group fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                                        <p class="mb-0">No hay asignaturas configuradas para el pensum y filtros seleccionados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="card-footer-modern">
                    <div class="pagination-info">
                        Mostrando {{ $mallas->firstItem() ?? 0 }} a {{ $mallas->lastItem() ?? 0 }} de {{ $mallas->total() }} asignaturas
                    </div>
                    <div>
                        {{ $mallas->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal de creación --}}
    @include('admin.maestros.jose.malla_curricular.modales.createModal')
@stop


@extends('adminlte::page')

{{ Breadcrumbs::render('admin.conversion-reglas.index') }}

@section('title', 'Reglas de Conversión entre Pensums')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <h1 class="title-main">Reglas de Conversión entre Pensums</h1>
                    <p class="title-subtitle">Equivalencias, homologaciones y exoneraciones oficiales del MPPE (Circular 01 de enero 2024)</p>
                </div>
            </div>

            @can('conversion_reglas.gestionar')
                <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearRegla">
                    <i class="fas fa-plus"></i>
                    <span>Nueva Regla</span>
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
        .filter-panel {
            background: #ffffff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .badge-regla-directa {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-regla-promedio {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-regla-exonerada {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-regla-no-cuenta {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .origen-pill {
            display: inline-block;
            background: #f8fafc;
            color: #0f172a !important;
            border: 1px solid #cbd5e1;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            margin: 2px;
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

        {{-- Panel de Filtros por Planes --}}
        <div class="filter-panel">
            <form action="{{ route('admin.conversion-reglas.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label font-weight-bold text-muted small mb-1">
                        <i class="fas fa-sign-out-alt text-primary mr-1"></i> Plan de Origen
                    </label>
                    <select name="pensum_origen_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($pensums as $p)
                            <option value="{{ $p->id }}" {{ $origenId == $p->id ? 'selected' : '' }}>
                                {{ $p->codigo }} - {{ $p->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label font-weight-bold text-muted small mb-1">
                        <i class="fas fa-sign-in-alt text-primary mr-1"></i> Plan de Destino
                    </label>
                    <select name="pensum_destino_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($pensums as $p)
                            <option value="{{ $p->id }}" {{ $destinoId == $p->id ? 'selected' : '' }}>
                                {{ $p->codigo }} - {{ $p->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label font-weight-bold text-muted small mb-1">
                        <i class="fas fa-user-graduate text-primary mr-1"></i> Grado Destino
                    </label>
                    <select name="grado_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todos los grados</option>
                        @foreach($grados as $g)
                            <option value="{{ $g->id }}" {{ $gradoId == $g->id ? 'selected' : '' }}>
                                {{ $g->nombre_grado }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label font-weight-bold text-muted small mb-1">
                        <i class="fas fa-filter text-primary mr-1"></i> Tipo de Regla
                    </label>
                    <select name="tipo_regla" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <option value="directa" {{ $tipo === 'directa' ? 'selected' : '' }}>Homologación Directa</option>
                        <option value="promedio" {{ $tipo === 'promedio' ? 'selected' : '' }}>Promedio de Materias</option>
                        <option value="exonerada" {{ $tipo === 'exonerada' ? 'selected' : '' }}>Exonerada (EX)</option>
                        <option value="no_cuenta" {{ $tipo === 'no_cuenta' ? 'selected' : '' }}>No se toma en cuenta</option>
                    </select>
                </div>

                <div class="col-md-1 text-end">
                    @if(!empty($gradoId) || !empty($tipo))
                        <a href="{{ route('admin.conversion-reglas.index', ['pensum_origen_id' => $origenId, 'pensum_destino_id' => $destinoId]) }}" class="btn btn-outline-secondary btn-sm w-100" title="Restablecer">
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
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div>
                        <h3>Matriz de Conversión: {{ $pensumOrigen?->codigo ?? '31059' }} &rarr; {{ $pensumDestino?->codigo ?? '31060' }}</h3>
                        <p>{{ $reglas->total() }} reglas de conversión registradas</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr style="text-align: center">
                                <th>#</th>
                                <th>Grado Destino</th>
                                <th style="text-align: left;">Materia Resultante (Destino)</th>
                                <th>Tipo de Regla</th>
                                <th style="text-align: left;">Materias de Origen (Aportan)</th>
                                <th>Base Legal / Cita</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @forelse ($reglas as $index => $regla)
                                <tr>
                                    <td>{{ $reglas->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge-grado">
                                            {{ $regla->gradoDestino ? ($regla->gradoDestino->nombre_grado ?? ($regla->gradoDestino->numero_grado . '° Año')) : 'N/A' }}
                                        </span>
                                    </td>
                                    <td style="text-align: left;">
                                        <div class="font-weight-bold text-dark">{{ $regla->areaDestino->nombre_area_formacion ?? '' }}</div>
                                        <small class="text-muted">Siglas: {{ $regla->areaDestino->siglas ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($regla->tipo_regla === 'directa')
                                            <span class="badge-regla-directa">
                                                <i class="fas fa-check-circle mr-1"></i> Directa
                                            </span>
                                        @elseif($regla->tipo_regla === 'promedio')
                                            <span class="badge-regla-promedio">
                                                <i class="fas fa-calculator mr-1"></i> Promedio
                                            </span>
                                        @elseif($regla->tipo_regla === 'exonerada')
                                            <span class="badge-regla-exonerada">
                                                <i class="fas fa-ban mr-1"></i> Exonerada (EX)
                                            </span>
                                        @else
                                            <span class="badge-regla-no-cuenta">
                                                <i class="fas fa-minus-circle mr-1"></i> No cuenta
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: left;">
                                        @forelse($regla->origenes as $orig)
                                            <span class="origen-pill">
                                                <i class="fas fa-caret-right text-primary mr-1"></i>
                                                {{ $orig->areaOrigen->nombre_area_formacion ?? 'N/A' }}
                                                <small class="text-muted">({{ $orig->gradoOrigen->nombre_grado ?? '' }})</small>
                                            </span>
                                        @empty
                                            <span class="text-muted font-italic small">
                                                @if($regla->tipo_regla === 'exonerada')
                                                    Aprobación directa por ley (EX)
                                                @else
                                                    Sin materias requeridas
                                                @endif
                                            </span>
                                        @endforelse
                                    </td>
                                    <td>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 180px;" title="{{ $regla->observacion }}">
                                            {{ $regla->observacion ?: 'Circular N° 01' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($regla->status)
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
                                                                data-bs-target="#modalVerRegla{{ $regla->id }}"
                                                                title="Ver detalles">
                                                            <i class="fas fa-eye me-2 mr-2"></i>
                                                            Ver detalles
                                                        </button>
                                                    </li>
                                                    @can('conversion_reglas.gestionar')
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-warning"
                                                                    type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditarRegla{{ $regla->id }}"
                                                                    title="Editar">
                                                                <i class="fas fa-edit me-2 mr-2"></i>
                                                                Editar
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                                    type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#confirmarEliminarRegla{{ $regla->id }}"
                                                                    title="Eliminar regla">
                                                                <i class="fas fa-trash-alt me-2 mr-2"></i>
                                                                Eliminar
                                                            </button>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modales de fila --}}
                                @include('admin.maestros.jose.conversion_reglas.modales.showModal', ['regla' => $regla])
                                @include('admin.maestros.jose.conversion_reglas.modales.editModal', ['regla' => $regla])

                                {{-- Modal Confirmar Eliminación --}}
                                <div class="modal fade" id="confirmarEliminarRegla{{ $regla->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                            <div class="modal-header-delete py-3 px-4 d-flex align-items-center">
                                                <div class="modal-icon-delete" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0;">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </div>
                                                <div class="ml-3 me-auto">
                                                    <h5 class="modal-title-delete mb-0" style="font-size: 1.15rem; font-weight: 700;">Eliminar Regla</h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;">Destino: {{ $regla->areaDestino->nombre_area_formacion ?? '' }}</small>
                                                </div>
                                                <button type="button" class="btn-close-modal ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body-delete p-4">
                                                <p>¿Deseas eliminar la regla de conversión para <strong>{{ $regla->areaDestino->nombre_area_formacion ?? '' }}</strong> de {{ $regla->gradoDestino->nombre_grado ?? '' }}?</p>
                                                <p class="delete-warning mb-0">Esta equivalencia dejará de estar disponible al transferir expedientes entre pensums.</p>
                                            </div>
                                            <div class="modal-footer-delete py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                                                <form action="{{ route('admin.conversion-reglas.destroy', $regla) }}" method="POST" class="w-100 d-flex justify-content-end gap-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal" style="border-radius: 8px;">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit" class="btn btn-danger px-3 shadow-sm" style="border-radius: 8px;">
                                                        <i class="fas fa-trash-alt mr-1"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-exchange-alt fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                                        <p class="mb-0">No hay reglas de conversión registradas para la transición de pensums seleccionada.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="card-footer-modern">
                    <div class="pagination-info">
                        Mostrando {{ $reglas->firstItem() ?? 0 }} a {{ $reglas->lastItem() ?? 0 }} de {{ $reglas->total() }} reglas
                    </div>
                    <div>
                        {{ $reglas->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal de creación --}}
    @include('admin.maestros.jose.conversion_reglas.modales.createModal')
@stop


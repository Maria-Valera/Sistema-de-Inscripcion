@extends('adminlte::page')

{{ Breadcrumbs::render('admin.pensums.index') }}

@section('title', 'Planes de Estudio (Pensums)')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="title-main">Planes de Estudio (Pensums)</h1>
                    <p class="title-subtitle">Gestión de planes curriculares oficiales del MPPE (Circular N° 01 y Circular N° 02)</p>
                </div>
            </div>

            @can('pensums.gestionar')
                <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearPensum">
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Plan</span>
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
        .badge-codigo-plan {
            background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4));
            color: #ffffff;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.95rem;
            letter-spacing: 1.5px;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(91, 110, 225, 0.25);
        }
        .badge-vigente {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .badge-historico {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .badge-count {
            background-color: #f8fafc !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1 !important;
            font-weight: 700;
            padding: 0.35rem 0.7rem;
            border-radius: 8px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }
        .badge-count span,
        .badge-count strong {
            color: #0f172a !important;
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

        {{-- Alertas de éxito, advertencia o error --}}
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

        {{-- Tarjeta Principal con Filtros y Tabla --}}
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3>Planes Curriculares</h3>
                        <p>{{ $pensums->total() }} planes registrados</p>
                    </div>
                </div>

                {{-- Buscador y Filtros --}}
                <div class="header-right">
                    <form action="{{ route('admin.pensums.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        <div class="input-group" style="max-width: 260px;">
                            <input type="text"
                                   name="buscar"
                                   class="form-control form-control-sm"
                                   placeholder="Buscar por código o nombre..."
                                   value="{{ $busqueda }}">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>

                        <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width: 150px;">
                            <option value="">Todos los estados</option>
                            <option value="vigentes" {{ $estado === 'vigentes' ? 'selected' : '' }}>Solo Vigentes</option>
                            <option value="caducados" {{ $estado === 'caducados' ? 'selected' : '' }}>Históricos</option>
                            <option value="inactivos" {{ $estado === 'inactivos' ? 'selected' : '' }}>Inactivos</option>
                        </select>

                        @if(!empty($busqueda) || !empty($estado))
                            <a href="{{ route('admin.pensums.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr style="text-align: center">
                                <th>#</th>
                                <th>Código MPPE</th>
                                <th style="text-align: left;">Nombre del Plan</th>
                                <th>Vigencia</th>
                                <th>Malla</th>
                                <th>Secciones</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @forelse ($pensums as $index => $pensum)
                                <tr>
                                    <td>{{ $pensums->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge-codigo-plan">{{ $pensum->codigo }}</span>
                                    </td>
                                    <td style="text-align: left;">
                                        <div class="font-weight-bold text-dark">{{ $pensum->nombre }}</div>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 320px;">
                                            {{ $pensum->descripcion ?: 'Sin descripción adicional' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($pensum->es_vigente)
                                            <span class="badge-vigente">
                                                <i class="fas fa-check-circle text-success"></i> Vigente (Desde {{ $pensum->anio_inicio }})
                                            </span>
                                        @elseif(!empty($pensum->anio_fin))
                                            <span class="badge-historico">
                                                <i class="fas fa-history"></i> {{ $pensum->anio_inicio }} - {{ $pensum->anio_fin }}
                                            </span>
                                        @else
                                            <span class="badge-historico text-muted">
                                                Inactivo (Desde {{ $pensum->anio_inicio }})
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-count" style="color: #0f172a !important;">
                                            <i class="fas fa-book-open text-primary mr-1"></i> <strong style="color: #0f172a !important;">{{ $pensum->mallas_count }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-count" style="color: #0f172a !important;">
                                            <i class="fas fa-chalkboard text-info mr-1"></i> <strong style="color: #0f172a !important;">{{ $pensum->secciones_count }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        @if($pensum->status)
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
                                                                data-bs-target="#modalVerPensum{{ $pensum->id }}"
                                                                title="Ver detalles">
                                                            <i class="fas fa-eye me-2 mr-2"></i>
                                                            Ver detalles
                                                        </button>
                                                    </li>
                                                    @can('pensums.gestionar')
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-warning"
                                                                    type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditarPensum{{ $pensum->id }}"
                                                                    title="Editar">
                                                                <i class="fas fa-edit me-2 mr-2"></i>
                                                                Editar
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                                    type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#confirmarEliminarPensum{{ $pensum->id }}"
                                                                    title="Desactivar / Eliminar">
                                                                <i class="fas fa-power-off me-2 mr-2"></i>
                                                                Desactivar / Borrar
                                                            </button>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modales de fila --}}
                                @include('admin.maestros.jose.pensums.modales.showModal', ['pensum' => $pensum])
                                @include('admin.maestros.jose.pensums.modales.editModal', ['pensum' => $pensum])

                                {{-- Modal Confirmar Eliminación / Desactivación --}}
                                <div class="modal fade" id="confirmarEliminarPensum{{ $pensum->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                            <div class="modal-header-delete py-3 px-4 d-flex align-items-center">
                                                <div class="modal-icon-delete" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0;">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </div>
                                                <div class="ml-3 me-auto">
                                                    <h5 class="modal-title-delete mb-0" style="font-size: 1.15rem; font-weight: 700;">Confirmar Acción</h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;">Plan {{ $pensum->codigo }} - {{ $pensum->nombre }}</small>
                                                </div>
                                                <button type="button" class="btn-close-modal ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body-delete p-4">
                                                <p>¿Estás seguro de desactivar o eliminar el plan <strong>{{ $pensum->nombre }}</strong> (Código {{ $pensum->codigo }})?</p>
                                                <p class="delete-warning mb-0">
                                                    <i class="fas fa-shield-alt mr-1"></i> Si el plan ya tiene secciones o asignaturas en la malla curricular, el sistema lo <strong>desactivará</strong> protegiendo los datos históricos.
                                                </p>
                                            </div>
                                            <div class="modal-footer-delete py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                                                <form action="{{ route('admin.pensums.destroy', $pensum) }}" method="POST" class="w-100 d-flex justify-content-end gap-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal" style="border-radius: 8px;">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit" class="btn btn-danger px-3 shadow-sm" style="border-radius: 8px;">
                                                        <i class="fas fa-power-off mr-1"></i> Proceder
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-graduation-cap fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                                        <p class="mb-0">No se encontraron planes de estudio registrados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="card-footer-modern">
                    <div class="pagination-info">
                        Mostrando {{ $pensums->firstItem() ?? 0 }} a {{ $pensums->lastItem() ?? 0 }} de {{ $pensums->total() }} planes
                    </div>
                    <div>
                        {{ $pensums->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal de creación --}}
    @include('admin.maestros.jose.pensums.modales.createModal')
@stop


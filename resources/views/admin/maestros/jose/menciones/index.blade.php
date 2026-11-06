@extends('adminlte::page')

{{ Breadcrumbs::render('admin.menciones.index') }}

@section('title', 'Menciones y Ciclos')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-certificate"></i>
                </div>
                <div>
                    <h1 class="title-main">Menciones y Ciclos</h1>
                    <p class="title-subtitle">Catálogo de menciones curriculares y ciclos oficiales del MPPE (Circular N° 02)</p>
                </div>
            </div>

            @can('menciones.gestionar')
                <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearMencion">
                    <i class="fas fa-plus"></i>
                    <span>Nueva Mención</span>
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
        .badge-codigo-mencion {
            background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4));
            color: #ffffff;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.95rem;
            letter-spacing: 1.5px;
            display: inline-flex;
            align-items: center;
        }
        .badge-ciclo {
            background-color: #f1f5f9;
            color: #0f172a !important;
            border: 1px solid #cbd5e1;
            font-weight: 700;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
            font-size: 0.85rem;
            display: inline-block;
        }
        .badge-secciones {
            background-color: #f8fafc;
            color: #0f172a !important;
            border: 1px solid #cbd5e1;
            font-weight: 700;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
            font-size: 0.85rem;
            display: inline-block;
        }
        .badge-secciones strong,
        .badge-secciones span {
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

        {{-- Aviso Informativo MPPE --}}
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-3" style="border-radius: 12px; background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6 !important;">
            <i class="fas fa-info-circle fa-2x mr-3 me-3 text-primary"></i>
            <div>
                <strong>Regla de Negocio del MPPE:</strong> Toda sección debe estar ligada a un plan de estudio (Pensum). En las certificaciones y hojas de registro de título, las secciones que <em>no tengan mención asignada</em> imprimirán automáticamente asteriscos: <code>*****</code>.
            </div>
        </div>

        {{-- Tarjeta Principal --}}
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3>Listado de Menciones</h3>
                        <p>{{ $menciones->total() }} menciones registradas</p>
                    </div>
                </div>

                {{-- Buscador --}}
                <div class="header-right">
                    <form action="{{ route('admin.menciones.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        <div class="input-group" style="max-width: 280px;">
                            <input type="text"
                                   name="buscar"
                                   class="form-control form-control-sm"
                                   placeholder="Buscar por código, nombre o ciclo..."
                                   value="{{ $busqueda }}">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        @if(!empty($busqueda))
                            <a href="{{ route('admin.menciones.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar">
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
                                <th>Código</th>
                                <th style="text-align: left;">Nombre de la Mención</th>
                                <th>Ciclo MPPE</th>
                                <th>Secciones</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @forelse ($menciones as $index => $mencion)
                                <tr>
                                    <td>{{ $menciones->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge-codigo-mencion">{{ $mencion->codigo }}</span>
                                    </td>
                                    <td style="text-align: left;">
                                        <div class="font-weight-bold text-dark">{{ $mencion->nombre }}</div>
                                    </td>
                                    <td>
                                        <span class="badge-ciclo">
                                            Ciclo {{ $mencion->ciclo }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-secciones">
                                            <i class="fas fa-chalkboard text-info mr-1"></i> <strong style="color: #0f172a !important;">{{ $mencion->secciones_count }}</strong> secciones
                                        </span>
                                    </td>
                                    <td>
                                        @if($mencion->status)
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
                                                                data-bs-target="#modalVerMencion{{ $mencion->id }}"
                                                                title="Ver detalles">
                                                            <i class="fas fa-eye me-2 mr-2"></i>
                                                            Ver detalles
                                                        </button>
                                                    </li>
                                                    @can('menciones.gestionar')
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-warning"
                                                                    type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditarMencion{{ $mencion->id }}"
                                                                    title="Editar">
                                                                <i class="fas fa-edit me-2 mr-2"></i>
                                                                Editar
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                                    type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#confirmarEliminarMencion{{ $mencion->id }}"
                                                                    title="Desactivar / Borrar">
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
                                @include('admin.maestros.jose.menciones.modales.showModal', ['mencion' => $mencion])
                                @include('admin.maestros.jose.menciones.modales.editModal', ['mencion' => $mencion])

                                {{-- Modal Confirmar Eliminación / Desactivación --}}
                                <div class="modal fade" id="confirmarEliminarMencion{{ $mencion->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                            <div class="modal-header-delete py-3 px-4 d-flex align-items-center">
                                                <div class="modal-icon-delete" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0;">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </div>
                                                <div class="ml-3 me-auto">
                                                    <h5 class="modal-title-delete mb-0" style="font-size: 1.15rem; font-weight: 700;">Confirmar Acción</h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;">Mención {{ $mencion->codigo }} - {{ $mencion->nombre }}</small>
                                                </div>
                                                <button type="button" class="btn-close-modal ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body-delete p-4">
                                                <p>¿Estás seguro de desactivar o eliminar la mención <strong>{{ $mencion->nombre }}</strong>?</p>
                                                <p class="delete-warning mb-0">Si la mención ya tiene secciones asignadas, el sistema la <strong>desactivará</strong> para proteger la emisión de títulos históricos.</p>
                                            </div>
                                            <div class="modal-footer-delete py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                                                <form action="{{ route('admin.menciones.destroy', $mencion) }}" method="POST" class="w-100 d-flex justify-content-end gap-2">
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
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-certificate fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                                        <p class="mb-0">No se encontraron menciones registradas.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="card-footer-modern">
                    <div class="pagination-info">
                        Mostrando {{ $menciones->firstItem() ?? 0 }} a {{ $menciones->lastItem() ?? 0 }} de {{ $menciones->total() }} menciones
                    </div>
                    <div>
                        {{ $menciones->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal de creación --}}
    @include('admin.maestros.jose.menciones.modales.createModal')
@stop


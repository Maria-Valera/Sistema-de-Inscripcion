@extends('adminlte::page')

{{ Breadcrumbs::render('admin.escala-literal.index') }}

@section('title', 'Escala Literal')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-sort-alpha-down"></i>
                </div>
                <div>
                    <h1 class="title-main">Escala Literal</h1>
                    <p class="title-subtitle">Equivalencia de calificaciones numéricas (1 a 20) a literales para el año escolar</p>
                </div>
            </div>

            @can('escala_literal.gestionar')
                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                    <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearEscala">
                        <i class="fas fa-plus"></i>
                        <span>Nueva Escala</span>
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
        .badge-letra {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4));
            color: #ffffff;
            font-weight: 700;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(91, 110, 225, 0.25);
        }
        .badge-rango {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid #e2e8f0;
        }
    </style>
@stop

@section('content')
    <div class="main-container">

        @if (!\App\Support\AnioEscolarSeleccionado::esActual())
            <div class="alert alert-info alert-dismissible fade show" role="alert" style="border-left: 4px solid #17a2b8;">
                <i class="fas fa-info-circle mr-2 me-2"></i>
                Estás viendo un año escolar anterior en modo <strong>Solo Consulta</strong>. No puedes crear, editar ni eliminar registros.
            </div>
        @endif

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

        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <div>
                        <h3>Escalas Configuradas</h3>
                        <p>
                            {{ $escalas->total() }} escalas para el periodo 
                            <strong class="text-primary">{{ $anioModelo?->nombre_completo ?? 'Activo' }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr style="text-align: center">
                                <th style="width: 60px;">#</th>
                                <th style="width: 100px;">Letra</th>
                                <th style="text-align: left;">Descripción Cualitativa</th>
                                <th>Rango Numérico (Puntos)</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @forelse ($escalas as $index => $esc)
                                <tr>
                                    <td>{{ $escalas->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge-letra">{{ $esc->letra }}</span>
                                    </td>
                                    <td style="text-align: left;">
                                        <div class="font-weight-bold fw-bold text-dark" style="font-size: 0.95rem;">
                                            {{ $esc->descripcion ?: 'Sin descripción' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-rango">
                                            <i class="fas fa-arrows-alt-h text-primary"></i>
                                            <strong>{{ $esc->nota_desde }}</strong> a <strong>{{ $esc->nota_hasta }}</strong> pts
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons justify-content-center">
                                            <div class="dropdown dropstart text-center">
                                                <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    @can('escala_literal.gestionar')
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <button class="dropdown-item d-flex align-items-center text-warning"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditarEscala{{ $esc->id }}"
                                                                        title="Editar">
                                                                    <i class="fas fa-edit me-2 mr-2"></i>
                                                                    Editar
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item d-flex align-items-center text-danger"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarEliminar{{ $esc->id }}"
                                                                        title="Eliminar">
                                                                    <i class="fas fa-trash me-2 mr-2"></i>
                                                                    Eliminar
                                                                </button>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <span class="dropdown-item text-muted disabled">Solo consulta</span>
                                                            </li>
                                                        @endif
                                                    @else
                                                        <li>
                                                            <span class="dropdown-item text-muted disabled">Sin permisos</span>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modal Editar para cada fila --}}
                                @include('admin.maestros.stephania.escala_literal.modales.editModal', ['escala' => $esc])

                                {{-- Modal Confirmar Eliminación --}}
                                <div class="modal fade" id="confirmarEliminar{{ $esc->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                            <div class="modal-header-delete py-3 px-4 d-flex align-items-center">
                                                <div class="modal-icon-delete" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </div>
                                                <div class="ml-3 me-auto">
                                                    <h5 class="modal-title-delete mb-0" style="font-size: 1.15rem; font-weight: 700;">Confirmar Eliminación</h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;">Esta acción no se puede deshacer</small>
                                                </div>
                                                <button type="button" class="btn-close-modal ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body-delete p-4">
                                                <p>¿Deseas eliminar la escala literal <strong>[{{ $esc->letra }}]</strong> ({{ $esc->nota_desde }} - {{ $esc->nota_hasta }} pts)?</p>
                                                <p class="delete-warning mb-0">La escala será removida de la configuración del año escolar actual.</p>
                                            </div>
                                            <div class="modal-footer-delete py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                                                <form action="{{ route('admin.escala-literal.destroy', $esc) }}" method="POST" class="w-100 d-flex justify-content-end gap-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger btn-modal-delete px-4 py-2" style="border-radius: 8px; font-weight: 600;">Eliminar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-sort-alpha-down"></i>
                                            </div>
                                            <h4>No hay escalas literales configuradas</h4>
                                            <p>Agrega los rangos alfabéticos (ej. A, B, C, D) para el año escolar actual.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-pagination :paginator="$escalas" />

        {{-- Modal Crear --}}
        @include('admin.maestros.stephania.escala_literal.modales.createModal')

    </div>
@stop


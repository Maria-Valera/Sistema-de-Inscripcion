@extends('adminlte::page')

{{ Breadcrumbs::render('admin.institucion.index') }}

@section('title', 'Datos del Plantel')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-school"></i>
                </div>
                <div>
                    <h1 class="title-main">Datos del Plantel</h1>
                    <p class="title-subtitle">Información institucional y configuración de la sede educativa</p>
                </div>
            </div>

            @can('institucion.gestionar')
                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                    <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrear">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Plantel</span>
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
@stop

@section('content')
    <div class="main-container">

        @if (!\App\Support\AnioEscolarSeleccionado::esActual())
            <div class="alert alert-info alert-dismissible fade show" role="alert" style="border-left: 4px solid #17a2b8;">
                <i class="fas fa-info-circle mr-2"></i>
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
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3>Listado de Planteles</h3>
                        <p>{{ $instituciones->total() }} registros encontrados</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr style="text-align: center">
                                <th>#</th>
                                <th>Código</th>
                                <th style="text-align: left;">Nombre de la Institución</th>
                                <th style="text-align: left;">Director(a)</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @forelse ($instituciones as $index => $inst)
                                @php
                                    $tieneLogo = !empty($inst->logo_ruta) && file_exists(public_path($inst->logo_ruta));
                                @endphp
                                <tr>
                                    <td>{{ $instituciones->firstItem() + $index }}</td>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $inst->codigo_plantel }}</span>
                                    </td>
                                    <td style="text-align: left;">
                                        <div class="d-flex align-items-center">
                                            @if($tieneLogo)
                                                <img src="{{ asset($inst->logo_ruta) }}" alt="Logo" class="rounded mr-2 shadow-sm" style="width: 36px; height: 36px; object-fit: contain; background: #fff; padding: 2px; border: 1px solid #e5e7eb; flex-shrink: 0;">
                                            @else
                                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-2 shadow-sm text-primary" style="width: 36px; height: 36px; background: rgba(91, 110, 225, 0.12); flex-shrink: 0; border: 1px solid rgba(91, 110, 225, 0.2);">
                                                    <i class="fas fa-school" style="font-size: 0.95rem;"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-weight-bold text-primary">{{ $inst->nombre }}</div>
                                                <small class="text-muted">{{ $inst->dependencia ?: 'Dependencia no especificada' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: left;">
                                        @if($inst->director)
                                            <div>{{ $inst->director->nombre_completo }}</div>
                                            <small class="text-muted">
                                                CI: {{ $inst->director->numero_documento }}
                                                @if($inst->director_encargado)
                                                    <span class="badge badge-warning text-dark ml-1">Encargado(a)</span>
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted font-italic">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inst->localidad)
                                            <div>{{ $inst->localidad->nombre_localidad }}</div>
                                            <small class="text-muted">{{ $inst->localidad->municipio->nombre_municipio ?? '' }}</small>
                                        @else
                                            <span class="text-muted font-italic">No asignada</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inst->activo)
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
                                                                data-bs-target="#modalVer{{ $inst->id }}"
                                                                title="Ver detalles">
                                                            <i class="fas fa-eye me-2 mr-2"></i>
                                                            Ver detalles
                                                        </button>
                                                    </li>
                                                    @can('institucion.gestionar')
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <button class="dropdown-item d-flex align-items-center text-warning"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditar{{ $inst->id }}"
                                                                        title="Editar">
                                                                    <i class="fas fa-edit me-2 mr-2"></i>
                                                                    Editar
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item d-flex align-items-center text-danger"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarEliminar{{ $inst->id }}"
                                                                        title="Desactivar">
                                                                    <i class="fas fa-power-off me-2 mr-2"></i>
                                                                    Desactivar
                                                                </button>
                                                            </li>
                                                        @endif
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modales por cada fila --}}
                                @include('admin.maestros.stephania.institucion.modales.showModal', ['institucion' => $inst])
                                @include('admin.maestros.stephania.institucion.modales.editModal', ['institucion' => $inst])

                                {{-- Modal Confirmar Desactivación --}}
                                <div class="modal fade" id="confirmarEliminar{{ $inst->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                            <div class="modal-header-delete py-3 px-4 d-flex align-items-center">
                                                <div class="modal-icon-delete" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0;">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </div>
                                                <div class="ml-3 me-auto">
                                                    <h5 class="modal-title-delete mb-0" style="font-size: 1.15rem; font-weight: 700;">Confirmar Desactivación</h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;">El registro no se eliminará físicamente</small>
                                                </div>
                                                <button type="button" class="btn-close-modal ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body-delete p-4">
                                                <p>¿Deseas desactivar la institución <strong>{{ $inst->nombre }}</strong>?</p>
                                                <p class="delete-warning mb-0">El plantel quedará en estado inactivo para preservar la integridad de los datos históricos.</p>
                                            </div>
                                            <div class="modal-footer-delete py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                                                <form action="{{ route('admin.institucion.destroy', $inst) }}" method="POST" class="w-100 d-flex justify-content-end gap-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger btn-modal-delete px-4 py-2" style="border-radius: 8px; font-weight: 600;">Desactivar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-school"></i>
                                            </div>
                                            <h4>No hay planteles registrados</h4>
                                            <p>Comienza registrando la sede de tu institución educativa.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-pagination :paginator="$instituciones" />

        {{-- Modal Crear --}}
        @include('admin.maestros.stephania.institucion.modales.createModal')

    </div>
@stop

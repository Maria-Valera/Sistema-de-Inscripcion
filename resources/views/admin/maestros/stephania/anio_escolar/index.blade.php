@extends('adminlte::page')

{{ Breadcrumbs::render('admin.anio-escolar.index') }}

@section('title', 'Años Escolares')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1 class="title-main">Años Escolares</h1>
                    <p class="title-subtitle">Registro, activación y gestión de los períodos lectivos de la institución</p>
                </div>
            </div>

            @can('anio_escolar.gestionar')
                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                    <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearAnioEscolar">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Año Escolar</span>
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
        .badge-status-activo {
            background-color: #dcfce7;
            color: #15803d;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid #bbf7d0;
        }
        .badge-status-inactivo {
            background-color: #f1f5f9;
            color: #64748b;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid #e2e8f0;
        }
        .badge-status-extendido {
            background-color: #fef9c3;
            color: #a16207;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid #fef08a;
        }
        .status-dot-sm {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
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
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div>
                        <h3>Períodos Registrados</h3>
                        <p>{{ $anios->total() }} años escolares registrados en el sistema</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr style="text-align: center">
                                <th style="width: 50px;">#</th>
                                <th style="text-align: left;">Período Lectivo</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Cierre</th>
                                <th>Prórroga / Extensión</th>
                                <th>Estado</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @forelse ($anios as $index => $anio)
                                <tr>
                                    <td>{{ $anios->firstItem() + $index }}</td>
                                    <td style="text-align: left;">
                                        <div class="font-weight-bold fw-bold text-dark" style="font-size: 1rem;">
                                            <i class="fas fa-calendar mr-1 me-1 text-primary"></i>
                                            {{ $anio->periodo ?? ($anio->inicio_anio_escolar?->format('Y') . ' - ' . $anio->cierre_anio_escolar?->format('Y')) }}
                                        </div>
                                    </td>
                                    <td>{{ $anio->inicio_anio_escolar ? \Carbon\Carbon::parse($anio->inicio_anio_escolar)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $anio->cierre_anio_escolar ? \Carbon\Carbon::parse($anio->cierre_anio_escolar)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @if ($anio->extencion_anio_escolar)
                                            <span class="text-warning font-weight-bold fw-bold">
                                                <i class="fas fa-clock mr-1 me-1"></i>
                                                {{ \Carbon\Carbon::parse($anio->extencion_anio_escolar)->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted" style="font-style: italic;">Sin extensión</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($anio->status === 'Activo')
                                            <span class="badge-status-activo">
                                                <span class="status-dot-sm bg-success"></span>
                                                Activo
                                            </span>
                                        @elseif ($anio->status === 'Extendido')
                                            <span class="badge-status-extendido">
                                                <span class="status-dot-sm bg-warning"></span>
                                                Extendido
                                            </span>
                                        @else
                                            <span class="badge-status-inactivo">
                                                <span class="status-dot-sm bg-secondary"></span>
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
                                                    @can('anio_escolar.gestionar')
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <button class="dropdown-item d-flex align-items-center text-warning"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditarAnioEscolar{{ $anio->id }}"
                                                                        title="Editar">
                                                                    <i class="fas fa-edit me-2 mr-2"></i>
                                                                    Editar
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item d-flex align-items-center text-danger"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarEliminar{{ $anio->id }}"
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
                                @include('admin.maestros.stephania.anio_escolar.modales.editModal', ['anio' => $anio])

                                {{-- Modal Confirmar Eliminación --}}
                                <div class="modal fade" id="confirmarEliminar{{ $anio->id }}" tabindex="-1" aria-hidden="true">
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
                                                <p>¿Deseas eliminar el año escolar <strong>{{ $anio->periodo ?? ($anio->inicio_anio_escolar?->format('Y') . ' - ' . $anio->cierre_anio_escolar?->format('Y')) }}</strong>?</p>
                                                <p class="delete-warning mb-0">Se eliminará el registro de la configuración institucional.</p>
                                            </div>
                                            <div class="modal-footer-delete py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                                                <form action="{{ route('admin.anio-escolar.destroy', $anio) }}" method="POST" class="w-100 d-flex justify-content-end gap-2">
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
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-calendar-times"></i>
                                            </div>
                                            <h4>No hay años escolares registrados</h4>
                                            <p>Crea el primer año escolar utilizando el botón superior.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-pagination :paginator="$anios" />

        {{-- Modal Crear --}}
        @include('admin.maestros.stephania.anio_escolar.modales.createModal')

    </div>
@stop


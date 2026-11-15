@extends('adminlte::page')

@section('title', 'Asignación de Áreas de Formación a Títulos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('content_header')
    <div class="content-header-modern hero-header-horario">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <h1 class="title-main">Áreas de Formación a Títulos</h1>
                    <p class="title-subtitle">Gestión de asignaciones entre materias y títulos universitarios de docentes</p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button" class="btn-hero-white" data-bs-toggle="modal" data-bs-target="#modalCrearAsignacion"
                    title="Nueva Asignación">
                    <i class="fas fa-plus"></i>
                    <span>Nueva Asignación</span>
                </button>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">

        {{-- Incluir modal de creación --}}
        @include('admin.transacciones.area_estudio_realizado.modales.createModal')
        @include('partials.alerta-modo-consulta')

        {{-- Alertas --}}
        @if (session('success') || session('error'))
            <div class="alerts-container">
                @if (session('success'))
                    <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="alert-content">
                            <h4>¡Éxito!</h4>
                            <p>{{ session('success') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar"><i
                                class="fas fa-times"></i></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div class="alert-content">
                            <h4>Error</h4>
                            <p>{{ session('error') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar"><i
                                class="fas fa-times"></i></button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Contenedor de la tabla --}}
        <div class="card-modern shadow-sm">
            <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de Asignaciones</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $areaEstudioRealizado->total() }} registros encontrados</p>
                    </div>
                </div>

                {{-- Buscador Redondeado (Design System) --}}
                <div class="header-right">
                    <form action="{{ route('admin.transacciones.area_estudio_realizado.index') }}" method="GET" class="m-0">
                        <div class="search-wrapper position-relative" style="width: 260px;">
                            <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                            <input type="text"
                                   name="buscar"
                                   id="buscarAreaEstudio"
                                   class="form-control search-input-modern pl-5"
                                   placeholder="Buscar materia o título..."
                                   value="{{ request('buscar') }}"
                                   style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body-modern p-0">
                <div class="table-wrapper">
                    <table class="table-modern" id="tablaAreaEstudio">
                        <thead>
                            <tr class="text-center">
                                <th class="text-start">Área de Formación</th>
                                <th class="text-start">Título Universitario</th>
                                <th style="width: 140px;">Estado</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($areaEstudioRealizado as $index => $datos)
                                <tr>
                                    <td class="text-start">
                                        <span class="fw-bold text-dark" style="font-size: 0.93rem;">
                                            <i class="fas fa-bookmark text-primary me-2"></i>
                                            {{ $datos->areaFormacion->nombre_area_formacion ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="text-start">
                                        <span class="text-muted" style="font-size: 0.9rem;">
                                            <i class="fas fa-graduation-cap me-2 text-secondary"></i>
                                            {{ $datos->estudiosRealizado->estudios ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($datos->status)
                                            <span class="badge badge-success px-3 py-1">
                                                <i class="fas fa-check-circle me-1" style="font-size: 0.75rem;"></i> Activo
                                            </span>
                                        @else
                                            <span class="badge badge-danger px-3 py-1">
                                                <i class="fas fa-times-circle me-1" style="font-size: 0.75rem;"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- BOTONES DE ACCIONES --}}
                                        <div class="action-buttons justify-content-center">
                                            <div class="dropdown text-center">
                                                <button class="action-btn"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-modern">
                                                    @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                        <li>
                                                            <button
                                                                class="dropdown-item dropdown-item-modern item-edit"
                                                                type="button"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModalEditar{{ $datos->id }}"
                                                                title="Editar">
                                                                <i class="fas fa-pen me-2"></i>
                                                                <span>Editar</span>
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item dropdown-item-modern item-delete"
                                                                type="button"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                                title="Inactivar">
                                                                <i class="fas fa-ban me-2"></i>
                                                                <span>Inactivar</span>
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                @include('admin.transacciones.area_estudio_realizado.modales.editModal', [
                                    'datos' => $datos,
                                    'area_formacion' => $area_formacion,
                                    'estudios' => $estudios,
                                ])

                                {{-- Modal Confirmar Inactivación (Design System) --}}
                                <div class="modal fade modal-danger-delete" id="confirmarEliminar{{ $datos->id }}" tabindex="-1"
                                    aria-labelledby="confirmarEliminarLabel{{ $datos->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
                                            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                         style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                        <i class="fas fa-ban" style="font-size: 1.15rem;"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="modal-title m-0 fw-bold" id="confirmarEliminarLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                                            Confirmar Inactivación
                                                        </h5>
                                                        <p class="text-muted m-0" style="font-size: 0.82rem;">Acción sobre asignación de materia a título</p>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>

                                            <form action="{{ url('admin/transacciones/area_estudio_realizado/' . $datos->id) }}" method="POST" class="w-100 m-0">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                                    <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                        ¿Deseas inactivar esta asignación?
                                                    </p>
                                                    <div class="p-3 d-flex align-items-center gap-3"
                                                         style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                             style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-size: 1.1rem;">
                                                            <i class="fas fa-university"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                                                {{ $datos->areaFormacion->nombre_area_formacion ?? '—' }}
                                                            </div>
                                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                                {{ $datos->estudiosRealizado->estudios ?? '—' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                                                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                                        <i class="fas fa-times me-1"></i> Cancelar
                                                    </button>
                                                    <button type="submit" class="btn" style="background-color: #EF4444; color: #FFF; border-radius: 8px; font-weight: 600; padding: 9px 18px; border: none;">
                                                        <i class="fas fa-ban me-1"></i> Inactivar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state text-center py-5">
                                            <div class="empty-icon text-muted mb-2 opacity-50">
                                                <i class="fas fa-university fa-3x"></i>
                                            </div>
                                            <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay asignaciones registradas</h4>
                                            <p class="text-muted" style="font-size: 0.88rem;">Comienza creando una nueva asignación usando el botón superior.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <x-pagination :paginator="$areaEstudioRealizado" />
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Buscador en tiempo real local sobre las filas cargadas
            const inputBuscar = document.getElementById('buscarAreaEstudio');
            if (inputBuscar) {
                inputBuscar.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    document.querySelectorAll('#tablaAreaEstudio tbody tr').forEach(row => {
                        if (row.cells.length === 1) return;
                        const text = row.innerText.toLowerCase();
                        row.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }
        });
    </script>
@endpush

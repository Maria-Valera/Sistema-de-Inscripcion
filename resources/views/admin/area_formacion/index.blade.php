@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Gestión de Áreas de Formación')

@section('content_header')
    <div class="content-header-modern hero-header-horario">
        <div class="header-content d-flex justify-content-between align-items-center">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Áreas de Formación</h1>
                    <p class="title-subtitle">Administración de materias y grupos estables</p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button"
                        class="btn-hero-white"
                        data-bs-toggle="modal"
                        data-bs-target="#modalCrearAreaFormacion"
                        title="Nueva Área">
                    <i class="fas fa-plus"></i>
                    <span>Nueva Área</span>
                </button>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="main-container">

    @include('admin.area_formacion.modales.createModal')

    @include('partials.alerta-modo-consulta')

    @if (session('success') || session('error'))
        <div class="alerts-container">
            @if (session('success'))
                <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="alert-content">
                        <h4>Error</h4>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- Tarjeta 1: Materias Activas --}}
    <div class="card-modern shadow-sm">
        <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon d-flex align-items-center justify-content-center"
                     style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Materias activas</h3>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $areaFormacion->total() }} registros encontrados</p>
                </div>
            </div>

            {{-- Buscador Redondeado (Design System) --}}
            <div class="header-right">
                <div class="search-wrapper position-relative" style="width: 260px;">
                    <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                    <input type="text"
                           id="buscarAreaFormacion"
                           class="form-control search-input-modern pl-5"
                           placeholder="Buscar materia..."
                           style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                </div>
            </div>
        </div>

        <div class="card-body-modern p-0">
            <div class="table-wrapper">
                <table class="table-modern" id="tablaAreaFormacion">
                    <thead>
                        <tr style="text-align: center">
                            <th style="text-align: center; width: 60px;">#</th>
                            <th style="text-align: left;">Área de formación</th>
                            <th style="text-align: center; width: 100px;">Código</th>
                            <th style="text-align: center; width: 90px;">Siglas</th>
                            <th style="text-align: center; width: 150px;">Bloques Semanales</th>
                            <th style="text-align: center; width: 140px;">Bloques / Día</th>
                            <th style="text-align: center; width: 140px;">Aula</th>
                            <th style="text-align: center; width: 110px;">Estado</th>
                            <th style="text-align: center; width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center">
                        @if($areaFormacion->isEmpty())
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state text-center py-5">
                                        <div class="empty-icon text-muted mb-2 opacity-50"><i class="fas fa-book fa-3x"></i></div>
                                        <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay áreas de formación registradas</h4>
                                        <p class="text-muted" style="font-size: 0.88rem;">Agrega una nueva área de formación con el botón superior.</p>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach($areaFormacion as $index => $datos)
                                @if($datos->status)
                                    <tr>
                                        <td class="text-muted" style="font-size: 0.85rem;">{{ $areaFormacion->firstItem() ? ($areaFormacion->firstItem() + $index) : ($index + 1) }}</td>
                                        <td class="text-start">
                                            <span class="fw-bold text-dark" style="font-size: 0.93rem;">
                                                <i class="fas fa-bookmark text-primary me-2"></i>
                                                {{ $datos->nombre_area_formacion }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: #F1F5F9; color: #475569; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.82rem;">
                                                {{ $datos->codigo_area }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: #F8FAFC; color: #334155; border: 1px solid #E2E8F0; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.82rem;">
                                                {{ $datos->siglas }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <i class="fas fa-clock text-primary me-1"></i> {{ $datos->bloques_semanales }} bloques
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <i class="fas fa-calendar-day text-primary me-1"></i> {{ $datos->bloques_maximos_por_dia ?? 2 }} / día
                                            </span>
                                        </td>
                                        <td>
                                            @if($datos->aula)
                                                <span class="fw-semibold text-dark" style="font-size: 0.88rem;">
                                                    <i class="fas fa-door-open text-muted me-1"></i>
                                                    {{ $datos->aula->nombre_aula }}
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size: 0.85rem;">Sin asignar</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge status-active">
                                                <span class="status-dot"></span>
                                                Activo
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <div class="dropdown dropstart text-center">
                                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                        style="width: 32px; height: 32px; border: 1px solid #E2E8F0; background: #FFFFFF; color: #64748B;">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end shadow-sm">
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <button
                                                                    class="dropdown-item dropdown-item-modern d-flex align-items-center text-warning"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#viewModalEditar{{ $datos->id }}"
                                                                    title="Editar">
                                                                    <i class="fas fa-pen me-2"></i>
                                                                    <span>Editar</span>
                                                                </button>
                                                            </li>

                                                            <li>
                                                                <button
                                                                    class="dropdown-item dropdown-item-modern item-delete d-flex align-items-center text-danger"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                                    title="Inactivar Área de Formación">
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

                                    @include('admin.area_formacion.modales.editModal')

                                    {{-- Modal Confirmar Inactivación (Design System) --}}
                                    <div class="modal fade modal-danger-delete" id="confirmarEliminar{{ $datos->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $datos->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                            <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: visible; background: #FFFFFF;">
                                                <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                             style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                            <i class="fas fa-ban" style="font-size: 1.15rem;"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="modal-title m-0 fw-bold" id="modalLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                                                Confirmar Inactivación
                                                            </h5>
                                                            <p class="text-muted m-0" style="font-size: 0.82rem;">Acción sobre área de formación</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                <form action="{{ route('admin.area_formacion.destroy', $datos->id) }}" method="POST" class="w-100 m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                                        <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                            ¿Deseas inactivar esta área de formación?
                                                        </p>
                                                        <div class="p-3 d-flex align-items-center gap-3"
                                                             style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                                 style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-weight: 700;">
                                                                <i class="fas fa-book"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $datos->nombre_area_formacion }}</div>
                                                                <div class="text-muted" style="font-size: 0.8rem;">Código: {{ $datos->codigo_area }} | Siglas: {{ $datos->siglas }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
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
                                @endif
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$areaFormacion" />

    @include('admin.area_formacion.modalesGrupoEstable.createModalGrupoEstable')

    {{-- Tarjeta 2: Grupos Estables Activos --}}
    <div class="card-modern shadow-sm mt-4">
        <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon d-flex align-items-center justify-content-center"
                     style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Grupos Estables activos</h3>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $grupoEstable->total() }} registros encontrados</p>
                </div>
            </div>
            <div class="header-right d-flex align-items-center gap-2">
                <div class="search-wrapper position-relative" style="width: 240px;">
                    <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                    <input type="text"
                           id="buscarGrupoEstable"
                           class="form-control search-input-modern pl-5"
                           placeholder="Buscar grupo..."
                           style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                </div>
                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                    <button type="button"
                     class="btn-primary-modern"
                     data-bs-toggle="modal"
                     data-bs-target="#modalCrearGrupoEstable"
                     title="Crear nuevo grupo estable">
                        <i class="fas fa-plus me-1"></i>
                        <span>Nuevo Grupo</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body-modern p-0">
            <div class="table-wrapper">
                <table class="table-modern" id="tablaGrupoEstable">
                    <thead>
                        <tr style="text-align: center">
                            <th style="text-align: center; width: 60px;">#</th>
                            <th style="text-align: center; width: 120px;">Código</th>
                            <th style="text-align: left;">Grupo Estable</th>
                            <th style="text-align: center; width: 120px;">Estado</th>
                            <th style="text-align: center; width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center">
                        @if($grupoEstable->isEmpty())
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state text-center py-5">
                                        <div class="empty-icon text-muted mb-2 opacity-50"><i class="fas fa-inbox fa-3x"></i></div>
                                        <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay grupos estables registrados</h4>
                                        <p class="text-muted" style="font-size: 0.88rem;">Agrega un nuevo grupo estable con el botón superior.</p>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach($grupoEstable as $index => $grupo)
                                @if($grupo->status)
                                    <tr>
                                        <td class="text-muted" style="font-size: 0.85rem;">{{ $grupoEstable->firstItem() ? ($grupoEstable->firstItem() + $index) : ($index + 1) }}</td>
                                        <td>
                                            @if($grupo->codigo)
                                                <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.82rem; border: 1px solid #DBEAFE;">{{ $grupo->codigo }}</span>
                                            @else
                                                <span class="text-muted fst-italic">-</span>
                                            @endif
                                        </td>
                                        <td class="text-start">
                                            <span class="fw-bold text-dark" style="font-size: 0.93rem;">
                                                <i class="fas fa-users text-primary me-2"></i>
                                                {{ $grupo->nombre_grupo_estable }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($grupo->status)
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
                                            <div class="action-buttons">
                                                <div class="dropdown dropstart text-center">
                                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                        style="width: 32px; height: 32px; border: 1px solid #E2E8F0; background: #FFFFFF; color: #64748B;">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end shadow-sm">
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <button
                                                                    class="dropdown-item dropdown-item-modern d-flex align-items-center text-warning"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#viewModalEditarGrupoEstable{{ $grupo->id }}"
                                                                    title="Editar">
                                                                    <i class="fas fa-pen me-2"></i>
                                                                    <span>Editar</span>
                                                                </button>
                                                            </li>

                                                            <li>
                                                                <button
                                                                    class="dropdown-item dropdown-item-modern item-delete d-flex align-items-center text-danger"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#confirmarEliminarGrupo{{ $grupo->id }}"
                                                                    title="Inactivar Grupo Estable">
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
                                    @include('admin.area_formacion.modalesGrupoEstable.editModalGrupoEstable')

                                    <div class="modal fade modal-danger-delete" id="confirmarEliminarGrupo{{ $grupo->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $grupo->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                            <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: visible; background: #FFFFFF;">
                                                <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                             style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                            <i class="fas fa-ban" style="font-size: 1.15rem;"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="modal-title m-0 fw-bold" id="modalLabel{{ $grupo->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                                                Confirmar Inactivación
                                                            </h5>
                                                            <p class="text-muted m-0" style="font-size: 0.82rem;">Acción sobre grupo estable</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                <form action="{{ route('admin.area_formacion.modalesGrupoEstable.destroyGrupoEstable', $grupo->id) }}" method="POST" class="w-100 m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                                        <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                            ¿Deseas inactivar este grupo estable?
                                                        </p>
                                                        <div class="p-3 d-flex align-items-center gap-3"
                                                             style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                                 style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-weight: 700;">
                                                                <i class="fas fa-users"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $grupo->nombre_grupo_estable }}</div>
                                                                <div class="text-muted" style="font-size: 0.8rem;">Código: {{ $grupo->codigo ?? '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
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
                                @endif
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$grupoEstable" />

</div>

@push('js')
    <script src="{{ asset('js/validations/area_formacion.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Buscador en tiempo real para Materias Activas
            const inputBuscarArea = document.getElementById('buscarAreaFormacion');
            if (inputBuscarArea) {
                inputBuscarArea.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    document.querySelectorAll('#tablaAreaFormacion tbody tr').forEach(row => {
                        if (row.cells.length === 1) return;
                        const text = row.innerText.toLowerCase();
                        row.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }

            // Buscador en tiempo real para Grupos Estables
            const inputBuscarGrupo = document.getElementById('buscarGrupoEstable');
            if (inputBuscarGrupo) {
                inputBuscarGrupo.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    document.querySelectorAll('#tablaGrupoEstable tbody tr').forEach(row => {
                        if (row.cells.length === 1) return;
                        const text = row.innerText.toLowerCase();
                        row.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }
        });
    </script>
@endpush

@endsection

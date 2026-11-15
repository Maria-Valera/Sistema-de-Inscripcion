@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Asignación de Aulas a Secciones')

@section('content_header')
    <div class="content-header-modern hero-header-horario">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-door-open"></i>
                </div>
                <div>
                    <h1 class="title-main">Asignación de Aulas a Secciones</h1>
                    <p class="title-subtitle">Administración de espacios físicos asignados a cada sección académica</p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button" class="btn-hero-white" data-bs-toggle="modal" data-bs-target="#modalCrearSeccionAula"
                    title="Nueva asignación">
                    <i class="fas fa-plus"></i>
                    <span>Nueva Asignación</span>
                </button>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="main-container">
    @include('admin.asignar_seccion_aula.modales.createModal')
    @include('admin.asignar_seccion_aula.modales.editModal')
    @include('partials.alerta-modo-consulta')

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

    <div class="card-modern shadow-sm">
        <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon d-flex align-items-center justify-content-center"
                     style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de Aulas Asignadas</h3>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $seccionAulas->total() }} registros encontrados</p>
                </div>
            </div>

            {{-- Buscador Redondeado (Design System) --}}
            <div class="header-right">
                <div class="search-wrapper position-relative" style="width: 260px;">
                    <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                    <input type="text"
                           id="buscarSeccionAula"
                           class="form-control search-input-modern pl-5"
                           placeholder="Buscar aula o sección..."
                           style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                </div>
            </div>
        </div>

        <div class="card-body-modern p-0">
            <div class="table-wrapper">
                <table class="table-modern" id="tablaSeccionAulas">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 70px;">#</th>
                            <th class="text-start">Aula</th>
                            <th style="width: 180px;">Nivel Académico</th>
                            <th style="width: 150px;">Sección</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @if ($seccionAulas->isEmpty())
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state text-center py-5">
                                        <div class="empty-icon text-muted mb-2 opacity-50">
                                            <i class="fas fa-door-open fa-3x"></i>
                                        </div>
                                        <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay aulas asignadas</h4>
                                        <p class="text-muted" style="font-size: 0.88rem;">Agrega una asignación de aula a una sección con el botón superior.</p>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach ($seccionAulas as $index => $asignacion)
                                <tr>
                                    <td class="text-muted" style="font-size: 0.85rem;">{{ ($seccionAulas->currentPage() - 1) * $seccionAulas->perPage() + $index + 1 }}</td>
                                    <td class="text-start">
                                        <span class="fw-bold text-dark" style="font-size: 0.93rem;">
                                            <i class="fas fa-door-open text-primary me-2"></i>
                                            {{ $asignacion->aula->nombre_aula ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; font-weight: 600; padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.85rem; border: 1px solid #DBEAFE;">
                                            <i class="fas fa-graduation-cap text-primary me-1"></i>
                                            Nivel {{ $asignacion->seccion->grado->numero_grado ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #F8FAFC; color: #334155; font-weight: 600; padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.85rem; border: 1px solid #E2E8F0;">
                                            <i class="fas fa-users text-secondary me-1"></i>
                                            Sección {{ $asignacion->seccion->nombre ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
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
                                                                onclick="editarAsignacion({{ $asignacion->id }}, {{ $asignacion->seccion->grado_id ?? 'null' }}, {{ $asignacion->id_seccion }}, {{ $asignacion->id_aula }})"
                                                                title="Editar asignación">
                                                                <i class="fas fa-pen me-2"></i>
                                                                <span>Editar</span>
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button
                                                                class="dropdown-item dropdown-item-modern item-delete"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmarEliminar{{ $asignacion->id }}"
                                                                title="Eliminar asignación">
                                                                <i class="fas fa-trash-alt me-2"></i>
                                                                <span>Eliminar</span>
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modal Confirmar Eliminación (Design System) --}}
                                <div class="modal fade modal-danger-delete" id="confirmarEliminar{{ $asignacion->id }}" tabindex="-1"
                                    aria-labelledby="modalLabel{{ $asignacion->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
                                            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                         style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                        <i class="fas fa-trash-alt" style="font-size: 1.15rem;"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="modal-title m-0 fw-bold" id="modalLabel{{ $asignacion->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                                            Confirmar Eliminación
                                                        </h5>
                                                        <p class="text-muted m-0" style="font-size: 0.82rem;">Acción destructiva sobre asignación de aula</p>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>

                                            <form action="{{ route('admin.seccion_aula.destroy', $asignacion->id) }}" method="POST" class="w-100 m-0">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                                    <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                        ¿Deseas eliminar esta asignación de aula a sección?
                                                    </p>
                                                    <p class="text-muted mb-3" style="font-size: 0.85rem;">
                                                        Esta acción no se puede deshacer.
                                                    </p>
                                                    <div class="p-3 d-flex align-items-center gap-3"
                                                         style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                             style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-size: 1.1rem;">
                                                            <i class="fas fa-door-open"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                                                {{ $asignacion->aula->nombre_aula ?? 'N/A' }}
                                                            </div>
                                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                                Nivel {{ $asignacion->seccion->grado->numero_grado ?? 'N/A' }} - Sección {{ $asignacion->seccion->nombre ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                                                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                                        <i class="fas fa-times me-1"></i> Cancelar
                                                    </button>
                                                    <button type="submit" class="btn" style="background-color: #EF4444; color: #FFF; border-radius: 8px; font-weight: 600; padding: 9px 18px; border: none;">
                                                        <i class="fas fa-trash-alt me-1"></i> Eliminar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <x-pagination :paginator="$seccionAulas"/>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Cargar secciones cuando se selecciona un grado en modal de creación
    $('#id_grado').on('change', function() {
        var gradoId = $(this).val();
        var seccionSelect = $('#id_seccion');
        
        if (gradoId) {
            $.ajax({
                url: '{{ route("admin.seccion_aula.secciones-por-grado", ":grado") }}'.replace(':grado', gradoId),
                method: 'GET',
                success: function(response) {
                    seccionSelect.empty();
                    seccionSelect.append('<option value="">Seleccione una sección</option>');
                    
                    if (response.success && response.secciones.length > 0) {
                        $.each(response.secciones, function(index, seccion) {
                            seccionSelect.append('<option value="' + seccion.id + '">' + seccion.nombre + '</option>');
                        });
                        seccionSelect.prop('disabled', false);
                    } else {
                        seccionSelect.append('<option value="">No hay secciones disponibles</option>');
                        seccionSelect.prop('disabled', true);
                    }
                },
                error: function() {
                    seccionSelect.empty();
                    seccionSelect.append('<option value="">Error al cargar secciones</option>');
                    seccionSelect.prop('disabled', true);
                }
            });
        } else {
            seccionSelect.empty();
            seccionSelect.append('<option value="">Seleccione primero un nivel académico</option>');
            seccionSelect.prop('disabled', true);
        }
    });

    // Cargar secciones cuando se selecciona un grado en modal de edición
    $('#edit_id_grado').on('change', function() {
        var gradoId = $(this).val();
        var seccionSelect = $('#edit_id_seccion');
        
        if (gradoId) {
            $.ajax({
                url: '{{ route("admin.seccion_aula.secciones-por-grado", ":grado") }}'.replace(':grado', gradoId),
                method: 'GET',
                success: function(response) {
                    seccionSelect.empty();
                    seccionSelect.append('<option value="">Seleccione una sección</option>');
                    
                    if (response.success && response.secciones.length > 0) {
                        $.each(response.secciones, function(index, seccion) {
                            seccionSelect.append('<option value="' + seccion.id + '">' + seccion.nombre + '</option>');
                        });
                        seccionSelect.prop('disabled', false);
                    } else {
                        seccionSelect.append('<option value="">No hay secciones disponibles</option>');
                        seccionSelect.prop('disabled', true);
                    }
                },
                error: function() {
                    seccionSelect.empty();
                    seccionSelect.append('<option value="">Error al cargar secciones</option>');
                    seccionSelect.prop('disabled', true);
                }
            });
        } else {
            seccionSelect.empty();
            seccionSelect.append('<option value="">Seleccione primero un nivel académico</option>');
            seccionSelect.prop('disabled', true);
        }
    });

    // Buscador en tiempo real
    $('#buscarSeccionAula').on('input', function() {
        var query = $(this).val().toLowerCase().trim();
        $('#tablaSeccionAulas tbody tr').each(function() {
            if ($(this).find('td').length === 1) return;
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(query) > -1);
        });
    });

    // Limpiar formulario al cerrar la modal de creación
    $('#modalCrearSeccionAula').on('hidden.bs.modal', function() {
        $('#formSeccionAula')[0].reset();
        $('#id_seccion').empty();
        $('#id_seccion').append('<option value="">Seleccione primero un nivel académico</option>');
        $('#id_seccion').prop('disabled', true);
    });

    // Limpiar formulario al cerrar la modal de edición
    $('#modalEditarSeccionAula').on('hidden.bs.modal', function() {
        $('#formEditarSeccionAula')[0].reset();
        $('#edit_id_seccion').empty();
        $('#edit_id_seccion').append('<option value="">Seleccione primero un nivel académico</option>');
        $('#edit_id_seccion').prop('disabled', true);
    });
});

// Función para editar asignación
function editarAsignacion(id, gradoId, seccionId, aulaId) {
    $('#edit_id').val(id);
    $('#edit_id_grado').val(gradoId);
    $('#edit_id_aula').val(aulaId);
    
    // Cargar secciones del grado seleccionado
    if (gradoId) {
        $.ajax({
            url: '{{ route("admin.seccion_aula.secciones-por-grado", ":grado") }}'.replace(':grado', gradoId),
            method: 'GET',
            success: function(response) {
                var seccionSelect = $('#edit_id_seccion');
                seccionSelect.empty();
                seccionSelect.append('<option value="">Seleccione una sección</option>');
                
                if (response.success && response.secciones.length > 0) {
                    $.each(response.secciones, function(index, seccion) {
                        seccionSelect.append('<option value="' + seccion.id + '">' + seccion.nombre + '</option>');
                    });
                    seccionSelect.prop('disabled', false);
                    // Seleccionar la sección actual
                    seccionSelect.val(seccionId);
                } else {
                    seccionSelect.append('<option value="">No hay secciones disponibles</option>');
                    seccionSelect.prop('disabled', true);
                }
            },
            error: function() {
                var seccionSelect = $('#edit_id_seccion');
                seccionSelect.empty();
                seccionSelect.append('<option value="">Error al cargar secciones</option>');
                seccionSelect.prop('disabled', true);
            }
        });
    }
    
    // Establecer la URL del formulario
    $('#formEditarSeccionAula').attr('action', '/admin/seccion_aula/' + id);
    
    // Abrir la modal
    var modal = new bootstrap.Modal(document.getElementById('modalEditarSeccionAula'));
    modal.show();
}
</script>
@endsection

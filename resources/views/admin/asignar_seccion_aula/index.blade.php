@extends('adminlte::page')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('title', 'Gestión de Asignación de Aulas a Secciones')

@section('content_header')
<div class="content-header-modern">
    <div class="header-content">
        <div class="header-title">
            <div class="icon-wrapper">
                <i class="fas fa-door-open"></i>
            </div>
            <div>
                <h1 class="title-main">Gestión de Asignación de Aulas a Secciones</h1>
                <p class="title-subtitle">Administración de asignaciones de aulas a secciones</p>
            </div>
        </div>
        <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearSeccionAula"
            @if (!$anioEscolarActivo) disabled @endif
            title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Nueva asignación' }}">
            <i class="fas fa-plus"></i>
            <span>Nueva Asignación</span>
        </button>
    </div>
</div>
@stop

@section('content')
<div class="main-container">
    @include('admin.asignar_seccion_aula.modales.createModal')
    @if (!$anioEscolarActivo)
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Atención: No hay Calendario Escolar activo</h5>
                    <p class="mb-0">
                        Puedes ver los registros, pero <strong>no podrás crear, editar o eliminar</strong> asignaciones hasta que se registre un Calendario Escolar activo.
                        <a href="{{ route('admin.anio_escolar.index') }}" class="alert-link">Ir a Calendario Escolar</a>
                    </p>
                </div>
            </div>
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
                    <h3>Asignaciones de Aulas</h3>
                    <p>{{ $seccionAulas->total() }} registros encontrados</p>
                </div>
            </div>
        </div>
        <div class="card-body-modern">
            <div class="table-wrapper">
                <table class="table-modern overflow-hidden">
                    <thead>
                        <tr style="text-align: center">
                            <th style="text-align: center">#</th>
                            <th style="text-align: center">Aula</th>
                            <th style="text-align: center">Nivel academico</th>
                            <th style="text-align: center">Seccion</th>
                            <th style="text-align: center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center">
                        @if ($seccionAulas->isEmpty())
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <h4>No aulas asignadas</h4>
                                        <p>Agrega una asignación de aula a una sección con el botón superior</p>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach ($seccionAulas as $index => $asignacion)
                                <tr class="row-12" style="text-align: center">
                                    <td>{{ ($seccionAulas->currentPage() - 1) * $seccionAulas->perPage() + $index + 1 }}</td>
                                    <td class="title-main">{{ $asignacion->aula->nombre_aula ?? 'N/A' }}</td>
                                    <td>{{ $asignacion->seccion->grado->numero_grado ?? 'N/A' }}</td>
                                    <td>{{ $asignacion->seccion->nombre ?? 'N/A' }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <div class="dropdown dropstart text-center">
                                                <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <button
                                                            class="dropdown-item d-flex align-items-center text-warning"
                                                            onclick="editarAsignacion({{ $asignacion->id }}, {{ $asignacion->seccion->grado_id ?? 'null' }}, {{ $asignacion->id_seccion }}, {{ $asignacion->id_aula }})"
                                                            @if (!$anioEscolarActivo) disabled @endif
                                                            title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Editar asignación' }}">
                                                            <i class="fas fa-pen me-2"></i>
                                                            Editar
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button
                                                            class="dropdown-item d-flex align-items-center text-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#confirmarEliminar{{ $asignacion->id }}"
                                                            @if (!$anioEscolarActivo) disabled @endif
                                                            title="{{ !$anioEscolarActivo ? 'Debe registrar un Calendario Escolar activo' : 'Eliminar asignación' }}">
                                                            <i class="fas fa-trash-alt me-2"></i>
                                                            Eliminar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @include('admin.asignar_seccion_aula.modales.editModal')
                                <div class="modal fade" id="confirmarEliminar{{ $asignacion->id }}" tabindex="-1"
                                    aria-labelledby="modalLabel{{ $asignacion->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-modern">
                                            <div class="modal-header-delete">
                                                <div class="modal-icon-delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </div>
                                                <h5 class="modal-title-delete">Confirmar Eliminación</h5>
                                                <button type="button" class="btn-close-modal"
                                                    data-bs-dismiss="modal" aria-label="Cerrar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body-delete">
                                                <p>¿Deseas eliminar esta asignación de aula a sección?</p>
                                                <p class="delete-warning">
                                                    Esta acción no se puede deshacer.
                                                </p>
                                            </div>
                                            <div class="modal-footer-delete">
                                                <form action="{{ route('admin.seccion_aula.destroy', $asignacion->id) }}"
                                                    method="POST" class="w-100">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="footer-buttons">
                                                        <button type="button" class="btn-modal-cancel"
                                                            data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit"
                                                            class="btn-modal-delete">Eliminar</button>
                                                    </div>
                                                </form>
                                            </div>
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

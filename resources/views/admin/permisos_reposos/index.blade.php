@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('title', 'Gestión de Prefijos de Telefono')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Permisos y reposos</h1>
                </div>
            </div>
            <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="fas fa-plus"></i>
                <span>Nuevo permiso</span>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('admin.permisos_reposos.modales.create')
        @include('admin.permisos_reposos.modales.edit')
        @include('admin.permisos_reposos.modales.delete')



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
                        <h3>Listado de permisos y reposos</h3>
                        
                    </div>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern overflow-hidden">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center">Tipo</th>
                                <th style="text-align: center">Nombre</th>
                                <th style="text-align: center">Días</th>
                                <th style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @forelse($permisosReposos as $permiso)
                                <tr>
                                    <td>
                                        <span class="badge {{ $permiso->tipo == 'Permiso' ? 'badge-primary' : 'badge-warning' }}">
                                            {{ $permiso->tipo }}
                                        </span>
                                    </td>
                                    <td>{{ $permiso->nombre_reposo }}</td>
                                    <td>{{ $permiso->dias_reposo }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            @if($anioEscolarActivo)
                                                <button type="button" class="btn-action btn-edit"
                                                    onclick="abrirModalEditar({{ $permiso->id }}, '{{ $permiso->nombre_reposo }}', '{{ $permiso->tipo }}', {{ $permiso->dias_reposo }})"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditar"
                                                    title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn-action btn-delete"
                                                    onclick="abrirModalEliminar({{ $permiso->id }})"
                                                    data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                                    title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn-action btn-disabled" disabled
                                                    title="No hay año escolar activo">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn-action btn-disabled" disabled
                                                    title="No hay año escolar activo">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem;">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p>No hay permisos/reposos registrados</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($permisosReposos->hasPages())
                        <div class="pagination-wrapper">
                            {{ $permisosReposos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function abrirModalEditar(id, nombre, tipo, dias) {
            const form = document.getElementById('formEditarPermiso');
            form.action = '/admin/permisos_reposos/' + id + '/update';
            document.getElementById('edit_nombre_reposo').value = nombre;
            document.getElementById('edit_tipo').value = tipo;
            document.getElementById('edit_dias_reposo').value = dias;
        }

        function abrirModalEliminar(id) {
            const form = document.getElementById('formEliminarPermiso');
            form.action = '/admin/permisos_reposos/' + id;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Remove hidden class from table after page load
            const table = document.querySelector('.table-modern');
            if (table) {
                table.classList.remove('hidden');
            }
        });
    </script>
@endsection
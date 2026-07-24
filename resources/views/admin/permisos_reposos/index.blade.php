@extends('adminlte::page')

@section('css')
    {{-- Estilos modernos reutilizados del sistema --}}
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
@stop

@section('title', 'Gestión de Permisos y Reposos')

@section('content_header')
    <div class="content-header-modern">
        {{-- Breadcrumbs --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0; font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" style="color: var(--primary); text-decoration: none;"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="color: var(--gray-700); font-weight: 700;">Gestión de Inasistencias</li>
            </ol>
        </nav>

        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Permisos y Reposos</h1>
                    <p class="title-subtitle">Módulo universal para la justificación de inasistencias</p>
                </div>
            </div>
            <a href="{{ route('admin.permisos_reposos.create') }}" class="btn-create">
                <i class="fas fa-plus"></i>
                <span>Registrar Permiso/Reposo</span>
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="main-container">
    {{-- Alertas de éxito y error simuladas/dinámicas --}}
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

    {{-- Listado de Solicitudes Recientes a Pantalla Completa --}}
    <div class="card-modern">
        <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h3 class="mb-0">Historial Reciente</h3>
                    <p class="mb-0 text-muted">Solicitudes registradas en el sistema para estudiantes, administrativos y obreros</p>
                </div>
            </div>
            <div class="header-right d-flex align-items-center gap-2 flex-wrap">
                <div class="search-modern">
                    <i class="fas fa-search"></i>
                    <input type="text" id="buscar_solicitud" class="form-control-modern" placeholder="Buscar por nombre o cédula..." onkeyup="filtrarTabla()">
                </div>
                <select id="filtro_estado" class="form-select" style="border-radius: 8px; border: 1px solid var(--gray-200); padding: 0.4rem 2rem 0.4rem 1rem; font-size: 0.85rem;" onchange="filtrarTabla()">
                    <option value="">Todos los estados</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Aprobado">Aprobado</option>
                    <option value="Rechazado">Rechazado</option>
                </select>
            </div>
        </div>

        <div class="card-body-modern">
            <div class="table-wrapper">
                <table class="table-modern" id="tabla_solicitudes">
                    <thead>
                        <tr style="text-align: center">
                            <th style="text-align: center">Persona / Cédula</th>
                            <th style="text-align: center">Tipo Usuario</th>
                            <th style="text-align: center">Tipo Solicitud</th>
                            <th style="text-align: center">Rango Fechas</th>
                            <th style="text-align: center">Estado</th>
                            <th style="text-align: center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center" id="tabla_solicitudes_body">
                        {{-- Fila 1: Estudiante Aprobado --}}
                        <tr class="row-solicitud" style="text-align: center" data-nombre="Ana Maria Valera" data-cedula="24555888" data-estado="Aprobado" data-tipo-usuario="Estudiante" data-tipo-solicitud="Reposo Médico" data-fechas="01/07/2026 al 05/07/2026" data-motivo="Presentó cuadro de dengue clásico verificado por examen de laboratorio." data-archivo="reposo_dengue.pdf">
                            <td class="tittle-main" style="font-weight: 700; text-align: left; padding-left: 1.5rem;">
                                Ana Maria Valera
                                <div style="font-size: 0.75rem; color: var(--gray-500); font-weight: normal;">V-24.555.888</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border" style="font-size: 0.75rem; font-weight: 600;">Estudiante</span>
                            </td>
                            <td>
                                <i class="fas fa-prescription-bottle-alt text-danger me-1"></i> Reposo Médico
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">01/07/2026</div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">al 05/07/2026</div>
                            </td>
                            <td>
                                <span class="status-badge status-active">
                                    <span class="status-dot" style="background: var(--success);"></span>
                                    Aprobado
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" onclick="verDetalles(this)" title="Ver detalles">
                                        <i class="fas fa-eye text-primary"></i>
                                    </button>
                                    <a href="#" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" onclick="descargarJustificativo(this, event)" title="Descargar justificativo">
                                        <i class="fas fa-download text-success"></i>
                                    </a>
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" onclick="abrirCambiarEstado(this)" title="Cambiar estado">
                                        <i class="fas fa-exchange-alt text-warning"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Fila 2: Administrativo Pendiente --}}
                        <tr class="row-solicitud" style="text-align: center" data-nombre="Carlos Mendoza" data-cedula="18222333" data-estado="Pendiente" data-tipo-usuario="Administrativo" data-tipo-solicitud="Permiso" data-fechas="08/07/2026 al 09/07/2026" data-motivo="Asistencia a cita del seguro social para consulta oftalmológica." data-archivo="cita_ivss.pdf">
                            <td class="tittle-main" style="font-weight: 700; text-align: left; padding-left: 1.5rem;">
                                Carlos Mendoza
                                <div style="font-size: 0.75rem; color: var(--gray-500); font-weight: normal;">V-18.222.333</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-success border" style="font-size: 0.75rem; font-weight: 600;">Administrativo</span>
                            </td>
                            <td>
                                <i class="fas fa-user-clock text-info me-1"></i> Permiso
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">08/07/2026</div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">al 09/07/2026</div>
                            </td>
                            <td>
                                <span class="status-badge status-pending">
                                    <span class="status-dot" style="background: var(--warning);"></span>
                                    Pendiente
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" onclick="verDetalles(this)" title="Ver detalles">
                                        <i class="fas fa-eye text-primary"></i>
                                    </button>
                                    <a href="#" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" onclick="descargarJustificativo(this, event)" title="Descargar justificativo">
                                        <i class="fas fa-download text-success"></i>
                                    </a>
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" onclick="abrirCambiarEstado(this)" title="Cambiar estado">
                                        <i class="fas fa-exchange-alt text-warning"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Fila 3: Obrero Rechazado --}}
                        <tr class="row-solicitud" style="text-align: center" data-nombre="Pedro Perez" data-cedula="12444555" data-estado="Rechazado" data-tipo-usuario="Obrero" data-tipo-solicitud="Permiso" data-fechas="25/06/2026 al 25/06/2026" data-motivo="Asuntos familiares no justificados formalmente." data-archivo="">
                            <td class="tittle-main" style="font-weight: 700; text-align: left; padding-left: 1.5rem;">
                                Pedro Perez
                                <div style="font-size: 0.75rem; color: var(--gray-500); font-weight: normal;">V-12.444.555</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border" style="font-size: 0.75rem; font-weight: 600;">Obrero</span>
                            </td>
                            <td>
                                <i class="fas fa-user-clock text-info me-1"></i> Permiso
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">25/06/2026</div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">al 25/06/2026</div>
                            </td>
                            <td>
                                <span class="status-badge status-inactive">
                                    <span class="status-dot" style="background: var(--danger);"></span>
                                    Rechazado
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" onclick="verDetalles(this)" title="Ver detalles">
                                        <i class="fas fa-eye text-primary"></i>
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" disabled title="Sin justificativo">
                                        <i class="fas fa-download text-muted"></i>
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm action-btn" onclick="abrirCambiarEstado(this)" title="Cambiar estado">
                                        <i class="fas fa-exchange-alt text-warning"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE DETALLES --}}
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-modern">
            <div class="modal-header-modern" style="padding: 1.5rem 2rem; background: linear-gradient(135deg, var(--gray-50), white); border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex align-items-center gap-3">
                    <div class="section-icon-modern" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); width: 40px; height: 40px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title section-title-modern" id="modalDetallesLabel" style="margin: 0; font-weight: 700;">Detalles de la Solicitud</h5>
                        <p class="section-subtitle-modern" style="margin: 0; font-size: 0.8rem; color: var(--gray-500);">Información detallada de la inasistencia</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.25rem; color: var(--gray-500);"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-item" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: 0.75rem 1rem; border-radius: var(--radius); display: flex; flex-direction: column;">
                            <span class="info-label" style="font-size: 0.8rem; font-weight: 600; color: var(--gray-500);">Persona</span>
                            <strong id="det_persona" class="text-dark" style="font-size: 1rem;">Ana Maria Valera</strong>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-item" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: 0.75rem 1rem; border-radius: var(--radius); display: flex; flex-direction: column;">
                            <span class="info-label" style="font-size: 0.8rem; font-weight: 600; color: var(--gray-500);">Cédula de Identidad</span>
                            <strong id="det_cedula" class="text-dark" style="font-size: 1rem;">24555888</strong>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="info-item" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: 0.75rem 1rem; border-radius: var(--radius); display: flex; flex-direction: column;">
                            <span class="info-label" style="font-size: 0.8rem; font-weight: 600; color: var(--gray-500);">Tipo de Usuario</span>
                            <strong id="det_tipo_usuario" class="text-primary" style="font-size: 1rem;">Estudiante</strong>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="info-item" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: 0.75rem 1rem; border-radius: var(--radius); display: flex; flex-direction: column;">
                            <span class="info-label" style="font-size: 0.8rem; font-weight: 600; color: var(--gray-500);">Tipo de Solicitud</span>
                            <strong id="det_tipo_solicitud" class="text-dark" style="font-size: 1rem;">Reposo Médico</strong>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="info-item" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: 0.75rem 1rem; border-radius: var(--radius); display: flex; flex-direction: column;">
                            <span class="info-label" style="font-size: 0.8rem; font-weight: 600; color: var(--gray-500);">Estado</span>
                            <div>
                                <span id="det_estado" class="status-badge status-active">
                                    <span class="status-dot"></span>
                                    Aprobado
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <div class="info-item" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: 0.75rem 1rem; border-radius: var(--radius); display: flex; flex-direction: column;">
                            <span class="info-label" style="font-size: 0.8rem; font-weight: 600; color: var(--gray-500);">Rango de Fechas / Período</span>
                            <strong id="det_fechas" class="text-dark" style="font-size: 1rem;">01/07/2026 al 05/07/2026</strong>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="info-item" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: 0.75rem 1rem; border-radius: var(--radius); display: flex; flex-direction: column;">
                            <span class="info-label" style="font-size: 0.8rem; font-weight: 600; color: var(--gray-500);">Documento Justificativo</span>
                            <div id="det_justificativo_container" class="mt-1">
                                <a href="#" id="det_descargar" class="btn btn-outline-primary btn-sm w-100" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">
                                    <i class="fas fa-download me-1"></i> Descargar
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="info-item" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: 0.75rem 1rem; border-radius: var(--radius); display: flex; flex-direction: column;">
                            <span class="info-label" style="font-size: 0.8rem; font-weight: 600; color: var(--gray-500);">Motivo o Descripción de la Inasistencia</span>
                            <p id="det_motivo" class="text-dark mb-0 mt-1" style="font-size: 0.9rem; line-height: 1.4;">Presentó cuadro de dengue clásico verificado por examen de laboratorio.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1.25rem 2rem; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end;">
                <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal" style="margin: 0;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE CAMBIO DE ESTADO --}}
<div class="modal fade" id="modalEstado" tabindex="-1" aria-labelledby="modalEstadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-modern" style="padding: 1.5rem 2rem; background: linear-gradient(135deg, var(--gray-50), white); border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex align-items-center gap-3">
                    <div class="section-icon-modern" style="background: linear-gradient(135deg, var(--orange), var(--orange-dark)); width: 40px; height: 40px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title section-title-modern" id="modalEstadoLabel" style="margin: 0; font-weight: 700;">Cambiar Estado</h5>
                        <p class="section-subtitle-modern" style="margin: 0; font-size: 0.8rem; color: var(--gray-500);">Modificar estatus de la justificación</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.25rem; color: var(--gray-500);"><i class="fas fa-times"></i></button>
            </div>
            <form action="#" method="POST" id="formCambiarEstado">
                @csrf
                <input type="hidden" name="solicitud_id" id="cambiar_estado_id">
                <div class="modal-body" style="padding: 2rem;">
                    <p class="mb-3">
                        Vas a modificar el estado de la solicitud de inasistencia para <strong id="cambiar_estado_nombre">Ana Maria Valera</strong>.
                    </p>
                    
                    <div class="form-group-modern mb-3">
                        <label for="nuevo_estado" class="form-label-modern">
                            <i class="fas fa-exchange-alt"></i> Seleccione Nuevo Estado <span class="required-badge">*</span>
                        </label>
                        <select name="nuevo_estado" id="nuevo_estado" class="form-control-modern" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Aprobado">Aprobado</option>
                            <option value="Rechazado">Rechazado</option>
                        </select>
                    </div>

                    <div class="form-group-modern">
                        <label for="observaciones_estado" class="form-label-modern">
                            <i class="fas fa-comment-dots"></i> Observaciones adicionales (Opcional)
                        </label>
                        <textarea class="form-control-modern" name="observaciones_estado" id="observaciones_estado" rows="3" placeholder="Indique alguna nota u observación sobre este cambio de estado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1.25rem 2rem; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal" style="margin: 0;">Cancelar</button>
                    <button type="submit" class="btn-primary-modern" style="margin: 0;">Actualizar Estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
    // Filtrar tabla del historial
    function filtrarTabla() {
        const buscador = document.getElementById('buscar_solicitud').value.toLowerCase();
        const filtroEstado = document.getElementById('filtro_estado').value;
        const filas = document.querySelectorAll('#tabla_solicitudes_body .row-solicitud');
        
        filas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre').toLowerCase();
            const cedula = fila.getAttribute('data-cedula').toLowerCase();
            const estado = fila.getAttribute('data-estado');
            
            const coincideBuscador = nombre.includes(buscador) || cedula.includes(buscador);
            const coincideEstado = filtroEstado === "" || estado === filtroEstado;
            
            if (coincideBuscador && coincideEstado) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }

    // Modales y acciones
    function verDetalles(button) {
        const fila = button.closest('.row-solicitud');
        
        const nombre = fila.getAttribute('data-nombre');
        const cedula = fila.getAttribute('data-cedula');
        const tipoUsuario = fila.getAttribute('data-tipo-usuario');
        const tipoSolicitud = fila.getAttribute('data-tipo-solicitud');
        const fechas = fila.getAttribute('data-fechas');
        const estado = fila.getAttribute('data-estado');
        const motivo = fila.getAttribute('data-motivo');
        const archivo = fila.getAttribute('data-archivo');
        
        document.getElementById('det_persona').textContent = nombre;
        document.getElementById('det_cedula').textContent = `V-${cedula}`;
        document.getElementById('det_tipo_usuario').textContent = tipoUsuario;
        document.getElementById('det_tipo_solicitud').textContent = tipoSolicitud;
        document.getElementById('det_fechas').textContent = fechas;
        document.getElementById('det_motivo').textContent = motivo;
        
        const badge = document.getElementById('det_estado');
        badge.innerHTML = `<span class="status-dot"></span> ${estado}`;
        
        // Asignar clase de estado al badge en el modal
        badge.className = 'status-badge';
        if (estado === 'Aprobado') {
            badge.classList.add('status-active');
            badge.querySelector('.status-dot').style.background = 'var(--success)';
        } else if (estado === 'Pendiente') {
            badge.classList.add('status-pending');
            badge.querySelector('.status-dot').style.background = 'var(--warning)';
        } else {
            badge.classList.add('status-inactive');
            badge.querySelector('.status-dot').style.background = 'var(--danger)';
        }
        
        const containerJust = document.getElementById('det_justificativo_container');
        if (archivo) {
            containerJust.innerHTML = `
                <a href="#" class="btn btn-outline-primary btn-sm w-100" onclick="descargarMockJustificativo('${archivo}', event)">
                    <i class="fas fa-download me-1"></i> Descargar (${archivo})
                </a>
            `;
        } else {
            containerJust.innerHTML = `<span class="text-muted" style="font-size: 0.85rem;"><i class="fas fa-times-circle text-danger me-1"></i> Sin justificativo</span>`;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
        modal.show();
    }

    function descargarJustificativo(button, event) {
        event.preventDefault();
        const fila = button.closest('.row-solicitud');
        const archivo = fila.getAttribute('data-archivo');
        if (archivo) {
            descargarMockJustificativo(archivo, event);
        }
    }

    function descargarMockJustificativo(nombreArchivo, event) {
        event.preventDefault();
        alert(`Iniciando descarga simulada del archivo justificativo: ${nombreArchivo}`);
    }

    function abrirCambiarEstado(button) {
        const fila = button.closest('.row-solicitud');
        const nombre = fila.getAttribute('data-nombre');
        const estado = fila.getAttribute('data-estado');
        
        document.getElementById('cambiar_estado_nombre').textContent = nombre;
        document.getElementById('nuevo_estado').value = estado;
        
        // Simular ID de fila en el input hidden
        document.getElementById('cambiar_estado_id').value = Math.floor(Math.random() * 1000);
        
        const modal = new bootstrap.Modal(document.getElementById('modalEstado'));
        modal.show();
    }

    document.getElementById('formCambiarEstado').addEventListener('submit', function(e) {
        e.preventDefault();
        const nuevoEst = document.getElementById('nuevo_estado').value;
        alert(`¡El estado de la solicitud ha sido cambiado a "${nuevoEst}" con éxito! (Simulado)`);
        
        // Cerrar modal
        const modalEl = document.getElementById('modalEstado');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    });

    // Auto-cerrar alertas después de 5 segundos
    setTimeout(function() {
        const alertModern = document.querySelector('.alert-modern');
        if (alertModern) {
            alertModern.style.transition = 'opacity 0.5s ease';
            alertModern.style.opacity = 0;
            setTimeout(() => alertModern.remove(), 500);
        }
    }, 5000);
</script>
@stop

<div class="modal fade" id="viewModalHorario" tabindex="-1" aria-labelledby="viewModalHorarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow-lg">
            <!-- Modal Header Limpio -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-wrapper-modal d-inline-flex align-items-center justify-content-center"
                         style="width: 42px; height: 42px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-calendar-alt" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="viewModalHorarioLabel" style="color: #1E293B; font-size: 1.15rem;">
                            Visualización de Horario
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Consulta detallada de la distribución semanal de clases</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Modal Body con Layout 70% / 30% -->
            <div class="modal-body p-4" style="background-color: #F8FAFC;">
                <div class="modal-horario-container">
                    
                    <!-- Columna Izquierda (Horario) - 70% del Ancho -->
                    <div class="modal-main-content">
                        <!-- Header de contexto con pastillas limpias -->
                        <div class="modal-context-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="context-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span id="modalContextNivel">--</span>
                                </span>
                                <span class="context-item">
                                    <i class="fas fa-users"></i>
                                    <span id="modalContextSeccion">--</span>
                                </span>
                            </div>
                            <span class="badge badge-primary px-3 py-2" style="font-size: 0.78rem;">
                                <i class="fas fa-clock me-1"></i> Turno Completo
                            </span>
                        </div>

                        <!-- Estado: carga / vacío / error -->
                        <div id="modalHorarioEstado" class="d-none"></div>

                        <!-- Matriz de Horarios (Sin Scroll Vertical Interno) -->
                        <div class="horario-matrix-wrapper modal-matrix-wrapper" id="modalHorarioMatrixWrapper">
                            <table class="table horario-matrix" id="modalHorarioTable">
                                <thead>
                                    <tr>
                                        <th class="time-header">Hora / Bloque</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Columna Derecha (Docentes) - 30% del Ancho con scroll interno -->
                    <div class="modal-sidebar-docentes">
                        <div class="modal-sidebar-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <h6 class="m-0 fw-bold">Docentes Asignados</h6>
                            </div>
                            <span class="badge" id="modalDocentesCount"
                                  style="background-color: #EFF6FF; color: #4A6FC1; font-weight: 700; border-radius: 999px; padding: 3px 8px;">0</span>
                        </div>
                        <div class="modal-docentes-list" id="modalDocentesList">
                            <div class="empty-state text-center py-4 text-muted">
                                <i class="fas fa-user-tie fa-2x mb-2 d-block opacity-50"></i>
                                <p class="m-0" style="font-size: 0.85rem;">Cargando docentes...</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Footer con Botón Secundario Moderno -->
            <div class="modal-footer d-flex justify-content-end bg-white border-top px-4 py-3">
                <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span>Cerrar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Overlay de carga para modal -->
<div id="modalOverlayCarga" class="modal-overlay-carga oculto">
    <div class="spinner"></div>
    <p id="modalOverlayCargaMensaje">Cargando...</p>
</div>

<style>
    /* =========================================================
       Estilos de Visualización de Horario (Design System)
       ========================================================= */

    /* Backdrop con desenfoque moderno (estilo iOS / macOS frosted glass) */
    .modal-backdrop {
        background-color: rgba(15, 23, 42, 0.6) !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        opacity: 1 !important;
    }

    .modal-backdrop.fade {
        opacity: 0 !important;
        transition: opacity 0.25s ease-out, backdrop-filter 0.25s ease-out !important;
    }

    .modal-backdrop.show,
    .modal-backdrop.fade.show {
        opacity: 1 !important;
    }

    /* Diálogo Amplio */
    #viewModalHorario .modal-dialog {
        max-width: 95vw !important;
        width: 1400px !important;
        margin: 1.5rem auto !important;
    }

    #viewModalHorario .modal-content {
        border: 1px solid #E2E8F0 !important;
        border-radius: 16px !important;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
        overflow: hidden !important;
    }

    /* Layout en 2 Columnas (70% Horario / 30% Docentes) */
    #viewModalHorario .modal-horario-container {
        display: flex !important;
        gap: 1.5rem !important;
        align-items: flex-start !important;
        height: auto !important;
        min-height: auto !important;
        width: 100% !important;
    }

    #viewModalHorario .modal-main-content {
        flex: 0 0 70% !important;
        max-width: 70% !important;
        width: 70% !important;
        min-width: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 1rem !important;
        overflow: visible !important;
    }

    #viewModalHorario .modal-sidebar-docentes {
        flex: 0 0 calc(30% - 1.5rem) !important;
        max-width: calc(30% - 1.5rem) !important;
        width: calc(30% - 1.5rem) !important;
        min-width: 260px !important;
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 14px !important;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04) !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        position: sticky !important;
        top: 0 !important;
    }

    /* Encabezado de Contexto Limpio */
    #viewModalHorario .modal-context-header {
        background: #FFFFFF !important;
        color: #1E293B !important;
        border: 1px solid #E2E8F0 !important;
        border-left: 4px solid #4A6FC1 !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        margin-bottom: 0 !important;
    }

    #viewModalHorario .modal-context-header .context-item {
        background-color: #EFF6FF !important;
        color: #1E3A8A !important;
        border-radius: 50px !important;
        padding: 0.35rem 0.9rem !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.45rem !important;
        white-space: nowrap !important;
    }

    #viewModalHorario .modal-context-header .context-item i {
        color: #4A6FC1 !important;
        font-size: 13px !important;
    }

    /* Tabla Completa sin Scroll Vertical Interno */
    #viewModalHorario .horario-matrix-wrapper.modal-matrix-wrapper {
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 14px !important;
        box-shadow: 0 4px 16px -4px rgba(15, 23, 42, 0.05) !important;
        max-height: none !important;
        overflow-y: visible !important;
        overflow-x: auto !important;
        width: 100% !important;
    }

    #viewModalHorario .horario-matrix {
        width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        border: none !important;
        margin-bottom: 0 !important;
    }

    #viewModalHorario .horario-matrix thead th {
        background: #F8FAFC !important;
        background-color: #F8FAFC !important;
        color: #475569 !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        text-align: center !important;
        text-transform: capitalize !important;
        padding: 0.85rem 0.5rem !important;
        border: none !important;
        border-bottom: 1px solid #E2E8F0 !important;
    }

    #viewModalHorario .horario-matrix thead th.time-header {
        background: #F8FAFC !important;
        color: #475569 !important;
        border-right: 1px solid #E2E8F0 !important;
        border-bottom: 1px solid #E2E8F0 !important;
        text-align: right !important;
        padding: 0.85rem 0.85rem 0.85rem 0.25rem !important;
        width: 130px !important;
    }

    #viewModalHorario .horario-matrix tbody td.time-cell {
        background: #F8FAFC !important;
        background-color: #F8FAFC !important;
        color: #475569 !important;
        font-size: 0.74rem !important;
        text-align: right !important;
        vertical-align: top !important;
        padding: 0.65rem 0.85rem 0.65rem 0.25rem !important;
        border: none !important;
        border-right: 1px solid #E2E8F0 !important;
        border-bottom: 1px solid #F1F5F9 !important;
        line-height: 1.35 !important;
    }

    #viewModalHorario .horario-matrix tbody td.time-cell strong {
        color: #1E293B !important;
        font-weight: 600 !important;
        display: block !important;
        margin-bottom: 2px !important;
    }

    #viewModalHorario .horario-matrix tbody td.time-cell small {
        color: #64748B !important;
        font-size: 0.72rem !important;
    }

    #viewModalHorario .horario-matrix tbody td.drop-zone {
        background: #FFFFFF !important;
        border: none !important;
        border-bottom: 1px solid #F1F5F9 !important;
        padding: 6px !important;
        min-height: 80px !important;
        height: auto !important;
        vertical-align: top !important;
    }

    #viewModalHorario .horario-matrix tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* Sidebar de Docentes Limpio */
    #viewModalHorario .modal-sidebar-header {
        background: #FFFFFF !important;
        color: #1E293B !important;
        border-bottom: 1px solid #F1F5F9 !important;
        padding: 1rem 1.15rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }

    #viewModalHorario .modal-sidebar-header h6 {
        color: #1E293B !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
    }

    #viewModalHorario .modal-sidebar-header i {
        color: #4A6FC1 !important;
        font-size: 1.1rem !important;
    }

    #viewModalHorario .modal-docentes-list {
        padding: 12px !important;
        max-height: calc(100vh - 360px) !important;
        overflow-y: auto !important;
        scrollbar-width: thin;
        background-color: #FFFFFF !important;
    }

    #viewModalHorario .modal-docentes-list::-webkit-scrollbar {
        width: 5px;
    }

    #viewModalHorario .modal-docentes-list::-webkit-scrollbar-thumb {
        background-color: #CBD5E1;
        border-radius: 999px;
    }

    /* Tarjetas de Docentes en el Sidebar */
    #viewModalHorario .modal-docentes-list .docente-card {
        border-radius: 12px !important;
        padding: 12px 14px !important;
        margin-bottom: 10px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        border: none !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    }

    #viewModalHorario .modal-docentes-list .docente-card:last-child {
        margin-bottom: 0 !important;
    }

    #viewModalHorario .modal-docentes-list .docente-card:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(15, 23, 42, 0.08) !important;
    }

    @media (max-width: 991.98px) {
        #viewModalHorario .modal-horario-container {
            flex-direction: column !important;
        }
        #viewModalHorario .modal-main-content,
        #viewModalHorario .modal-sidebar-docentes {
            flex: 1 1 100% !important;
            max-width: 100% !important;
            width: 100% !important;
        }
    }
</style>

<script>
    // Endpoint basado en HorarioAsignacion
    const HORARIO_SHOW_API_URL = @json(route('admin.horario.show-api', ['grado_id' => '__GRADO__', 'seccion_id' => '__SECCION__']));
    const DIAS_POR_DEFECTO = [
        { id: 1, nombre_dia: 'Lunes' }, { id: 2, nombre_dia: 'Martes' }, { id: 3, nombre_dia: 'Miércoles' },
        { id: 4, nombre_dia: 'Jueves' }, { id: 5, nombre_dia: 'Viernes' }
    ];
    const PALETA_PASTEL_MODAL = [
        'card-pastel-blue',
        'card-pastel-purple',
        'card-pastel-green',
        'card-pastel-orange',
        'card-pastel-pink',
        'card-pastel-teal',
    ];

    let horarioModalRequestId = 0;

    function escHtml(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function horaCorta(hora) {
        return hora ? String(hora).substring(0, 5) : '--:--';
    }

    function obtenerClasePastelModal(docenteId, docenteNombre) {
        const clave = String(docenteId || docenteNombre || '0');
        if (/^\d+$/.test(clave)) {
            return PALETA_PASTEL_MODAL[Number(clave) % PALETA_PASTEL_MODAL.length];
        }
        let hash = 0;
        for (let i = 0; i < clave.length; i++) {
            hash = clave.charCodeAt(i) + ((hash << 5) - hash);
        }
        return PALETA_PASTEL_MODAL[Math.abs(hash) % PALETA_PASTEL_MODAL.length];
    }

    // tipo: 'loading' | 'empty' | 'error' | null
    function setEstadoHorarioModal(tipo, mensaje = '') {
        const estado = document.getElementById('modalHorarioEstado');
        const matriz = document.getElementById('modalHorarioMatrixWrapper');
        if (!tipo) {
            estado.classList.add('d-none');
            estado.innerHTML = '';
            matriz.classList.remove('d-none');
            return;
        }
        const config = {
            loading: { cls: 'text-center text-muted py-5', icon: '<div class="spinner-border text-primary mb-3" role="status"></div>' },
            empty:   { cls: 'alert alert-info text-center my-3', icon: '<i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>' },
            error:   { cls: 'alert alert-danger text-center my-3', icon: '<i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>' },
        }[tipo];
        estado.className = config.cls;
        estado.innerHTML = `${config.icon}<div>${escHtml(mensaje)}</div>`;
        matriz.classList.toggle('d-none', tipo !== 'empty');
    }

    // Cargar horario en la modal
    async function cargarHorarioEnModal(gradoId, seccionId, gradoNombre = null, seccionNombre = null) {
        const requestId = ++horarioModalRequestId;
        const docentesList = document.getElementById('modalDocentesList');
        const countEl = document.getElementById('modalDocentesCount');

        const gradoInicial = gradoNombre ? (String(gradoNombre).includes('Año') || String(gradoNombre).includes('Grado') ? gradoNombre : `${gradoNombre}° Año`) : '--';
        const seccionInicial = seccionNombre ? (String(seccionNombre).includes('Sección') ? seccionNombre : `Sección ${seccionNombre}`) : '--';

        document.getElementById('modalContextNivel').textContent = gradoInicial;
        document.getElementById('modalContextSeccion').textContent = seccionInicial;
        if (countEl) countEl.textContent = '0';

        document.querySelector('#modalHorarioTable thead tr').innerHTML = '<th class="time-header">Hora / Bloque</th>';
        document.querySelector('#modalHorarioTable tbody').innerHTML = '';
        docentesList.innerHTML = '<div class="empty-state text-center py-4 text-muted"><i class="fas fa-user-tie fa-2x mb-2 d-block opacity-50"></i><p class="m-0" style="font-size: 0.85rem;">Cargando docentes...</p></div>';
        setEstadoHorarioModal('loading', 'Cargando horario...');

        try {
            const url = HORARIO_SHOW_API_URL
                .replace('__GRADO__', encodeURIComponent(gradoId))
                .replace('__SECCION__', encodeURIComponent(seccionId));

            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            const data = await response.json().catch(() => ({}));

            if (requestId !== horarioModalRequestId) return;

            if (!response.ok) {
                throw new Error(data.message || `Error ${response.status} al consultar el horario`);
            }

            renderizarHorarioEnModal(data);
        } catch (error) {
            if (requestId !== horarioModalRequestId) return;
            console.error('Error al cargar horario:', error);
            setEstadoHorarioModal('error', error.message || 'Error al cargar el horario');
            docentesList.innerHTML = '<div class="empty-state text-center py-4 text-muted"><i class="fas fa-user-tie fa-2x mb-2 d-block opacity-50"></i><p class="m-0" style="font-size: 0.85rem;">Sin datos</p></div>';
        }
    }

    function calcularRangoHoraBloque(numBloque, config = {}) {
        const horaInicioStr = config?.hora_inicio ? String(config.hora_inicio).substring(0, 5) : '07:00';
        const duracionMin = Number(config?.duracion_bloque_min || 90);
        const entreBloquesMin = Number(config?.minutos_entre_bloques || 0);
        const recesosMap = config?.recesos || {};
        const partes = horaInicioStr.split(':').map(Number);
        const h = isNaN(partes[0]) ? 7 : partes[0];
        const m = isNaN(partes[1]) ? 0 : partes[1];

        let minutosDesdeInicio = (h * 60) + m;
        for (let b = 1; b < numBloque; b++) {
            const recesoMin = Number(recesosMap[b] || 0);
            minutosDesdeInicio += duracionMin + entreBloquesMin + recesoMin;
        }

        const inicioMin = minutosDesdeInicio;
        const finMin = inicioMin + duracionMin;

        const formatoHora = (minTotales) => {
            const horas = Math.floor(minTotales / 60) % 24;
            const minutos = minTotales % 60;
            return `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}`;
        };

        return {
            inicio: formatoHora(inicioMin),
            fin: formatoHora(finMin)
        };
    }

    function renderizarHorarioEnModal(data) {
        const thead = document.querySelector('#modalHorarioTable thead tr');
        const tbody = document.querySelector('#modalHorarioTable tbody');
        const docentesList = document.getElementById('modalDocentesList');
        const countEl = document.getElementById('modalDocentesCount');
        const asignaciones = Array.isArray(data.asignaciones) ? data.asignaciones : [];
        const dias = (Array.isArray(data.dias) && data.dias.length) ? data.dias : DIAS_POR_DEFECTO;

        const gradoTexto = data.grado?.nombre || (data.grado?.numero_grado != null ? `${data.grado.numero_grado}° Año` : '--');
        const seccionTexto = data.seccion?.nombre ? `Sección ${data.seccion.nombre}` : (data.seccion?.letra ? `Sección ${data.seccion.letra}` : '--');

        document.getElementById('modalContextNivel').textContent = gradoTexto;
        document.getElementById('modalContextSeccion').textContent = seccionTexto;

        const bloques = (Array.isArray(data.bloques) ? data.bloques : []).map(b => ({ ...b }));
        const idsBloques = new Set(bloques.map(b => Number(b.bloque || b.id)));
        [...new Set(asignaciones.map(a => Number(a.bloque)))]
            .filter(id => !idsBloques.has(id))
            .sort((a, b) => a - b)
            .forEach(id => {
                const rangoCalc = calcularRangoHoraBloque(id, data.configuracion);
                bloques.push({
                    id,
                    bloque: id,
                    hora_inicio: rangoCalc.inicio,
                    hora_fin: rangoCalc.fin
                });
            });

        // Asegurar orden secuencial de bloques 1, 2, 3...
        bloques.sort((a, b) => Number(a.bloque || a.id) - Number(b.bloque || b.id));

        const reservados = Array.isArray(data.reservados) ? data.reservados : [];

        // Encabezado de Días
        thead.innerHTML = '<th class="time-header">Hora / Bloque</th>' +
            dias.map(d => `<th>${escHtml(d.nombre_dia || d.nombre)}</th>`).join('');

        // Cuerpo: filas completas sin scroll vertical interno
        tbody.innerHTML = bloques.map((bloque) => {
            const numBloque = Number(bloque.bloque || bloque.id);
            let horaInicio = bloque.hora_inicio;
            let horaFin = bloque.hora_fin;

            if (!horaInicio || !horaFin) {
                const rangoCalc = calcularRangoHoraBloque(numBloque, data.configuracion);
                horaInicio = rangoCalc.inicio;
                horaFin = rangoCalc.fin;
            }

            const horario = `<strong>${horaCorta(horaInicio)} - ${horaCorta(horaFin)}</strong>`;

            const celdas = dias.map(dia => {
                const numDia = Number(dia.dia || dia.id);
                const esReservado = reservados.some(r => Number(r.dia) === numDia && Number(r.bloque) === numBloque);

                if (esReservado) {
                    return `<td class="drop-zone celda-reservada text-center align-middle" data-day="${escHtml(numDia)}" data-block="${escHtml(numBloque)}" style="background-color: #F8FAFC; color: #94A3B8; font-weight: 500;">
                        <span class="badge" style="background-color: #E2E8F0; color: #64748B;"><i class="fas fa-ban me-1"></i>Reservado</span>
                    </td>`;
                }

                const enCelda = asignaciones.filter(a =>
                    Number(a.dia) === numDia && Number(a.bloque) === numBloque);

                const contenido = enCelda.map(a => {
                    const materia = a.materia || a.materia_nombre || a.cargaAcademica?.materia?.nombre || 'Sin materia';
                    const docente = a.docente || a.docente_nombre || a.cargaAcademica?.docente?.nombre || 'Sin docente';
                    const aula = a.aula || a.aula_nombre || 'Sin aula';
                    const lockIcon = a.fijada ? '<span class="ms-1" style="color: #D97706;" title="Clase fijada"><i class="fas fa-lock"></i></span>' : '';

                    const docenteIdRef = a.docente_id || a.cargaAcademica?.docente_id || a.cargaAcademica?.docente?.id;
                    const clasePastel = obtenerClasePastelModal(docenteIdRef, docente);

                    return `
                    <div class="assigned-docente event-card ${clasePastel} mb-1${a.conflicto_manual ? ' border-warning' : ''}"
                         style="padding: 7px 9px; border-radius: 8px; border: none; transition: transform 0.15s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        <div class="docente-materia fw-bold" style="font-size: 12.5px; line-height: 1.25; margin-bottom: 2px; color: inherit;">
                            ${escHtml(materia)} ${lockIcon}
                        </div>
                        <div class="docente-nombre" style="font-size: 11.5px; opacity: 0.9; margin-bottom: 2px; display: flex; align-items: center; gap: 4px; color: inherit;">
                            <i class="fas fa-chalkboard-teacher" style="font-size: 10px; opacity: 0.75;"></i>
                            <span>${escHtml(docente)}</span>
                        </div>
                        <div class="aula-texto" style="font-size: 11px; opacity: 0.85; display: flex; align-items: center; gap: 4px; color: inherit;">
                            <i class="fas fa-door-open" style="font-size: 10px; opacity: 0.75;"></i>
                            <span>${escHtml(aula)}</span>
                        </div>
                        ${a.conflicto_manual ? '<small class="text-warning d-block mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>Conflicto</small>' : ''}
                    </div>`;
                }).join('');

                return `<td class="drop-zone" data-day="${escHtml(numDia)}" data-block="${escHtml(numBloque)}">${contenido}</td>`;
            }).join('');

            return `<tr><td class="time-cell">${horario}<small>Bloque ${numBloque}</small></td>${celdas}</tr>`;
        }).join('');

        const idsDias = new Set(dias.map(d => Number(d.dia ?? d.id)));
        const fueraDeGrilla = asignaciones.filter(a => !idsDias.has(Number(a.dia))).length;

        if (asignaciones.length === 0) {
            setEstadoHorarioModal('empty', 'Esta sección no tiene clases asignadas en el año escolar activo.');
        } else if (fueraDeGrilla > 0) {
            setEstadoHorarioModal(null);
            const estado = document.getElementById('modalHorarioEstado');
            estado.className = 'alert alert-warning text-center my-2';
            estado.textContent = `${fueraDeGrilla} asignación(es) tienen un día fuera de Lunes–Viernes y no se muestran en la tabla.`;
        } else {
            setEstadoHorarioModal(null);
        }

        // Renderizado de Docentes en la columna lateral
        let lista = [];
        if (Array.isArray(data.docentes) && data.docentes.length > 0) {
            lista = data.docentes;
        } else {
            const docentesUnicos = {};
            asignaciones.forEach(a => {
                const docId = a.docente_id || a.cargaAcademica?.docente?.id;
                if (!docId) return;
                const d = docentesUnicos[docId] ??= {
                    id: docId,
                    nombre: a.docente_nombre || a.docente || a.cargaAcademica?.docente?.nombre || 'Sin nombre',
                    materias: []
                };
                const mat = a.materia_nombre || a.materia || a.cargaAcademica?.materia?.nombre;
                if (mat && !d.materias.includes(mat)) d.materias.push(mat);
            });
            lista = Object.values(docentesUnicos);
        }

        if (countEl) countEl.textContent = lista.length;

        docentesList.innerHTML = lista.length
            ? lista.map(d => {
                const materiasTxt = Array.isArray(d.materias) ? d.materias.join(', ') : (d.materias || 'Sin materia asignada');
                const clasePastel = obtenerClasePastelModal(d.id || d.docente_id, d.nombre);

                return `
                <div class="docente-card ${clasePastel}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h6 class="docente-name mb-0 fw-bold" style="font-size: 0.9rem; color: inherit;">${escHtml(d.nombre)}</h6>
                        <span class="badge" style="background: rgba(255, 255, 255, 0.65); color: inherit; font-size: 10.5px; padding: 2px 7px; border-radius: 999px;">Docente</span>
                    </div>
                    <div class="d-flex align-items-center gap-1" style="font-size: 0.8rem; color: inherit; opacity: 0.9;">
                        <i class="fas fa-book" style="font-size: 0.75rem; opacity: 0.8;"></i>
                        <span>${escHtml(materiasTxt)}</span>
                    </div>
                </div>`;
            }).join('')
            : '<div class="empty-state text-center py-4 text-muted"><i class="fas fa-user-tie fa-2x mb-2 d-block opacity-50"></i><p class="m-0" style="font-size: 0.85rem;">No hay docentes asignados</p></div>';
    }

    function mostrarPantallaCargaModal(mensaje) {
        document.getElementById('modalOverlayCargaMensaje').textContent = mensaje;
        document.getElementById('modalOverlayCarga').classList.remove('oculto');
    }

    function ocultarPantallaCargaModal() {
        document.getElementById('modalOverlayCarga').classList.add('oculto');
    }
</script>

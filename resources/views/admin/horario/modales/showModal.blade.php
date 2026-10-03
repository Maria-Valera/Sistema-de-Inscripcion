<div class="modal fade" id="viewModalHorario" tabindex="-1" aria-labelledby="viewModalHorarioLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow">
            <div class="modal-header modal-header-view">
                <div class="w-100 text-center">
                    <h5 class="modal-title-view mb-2">Ver Horario</h5>
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>
            <div class="modal-body modal-body-view">
                <div class="modal-horario-container">
                    <div class="modal-main-content">
                        <!-- Header de contexto -->
                        <div class="modal-context-header">
                            <div class="modal-context-info">
                                <span class="modal-context-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span id="modalContextNivel">Grado: --</span>
                                </span>
                                <span class="modal-context-separator">|</span>
                                <span class="modal-context-item">
                                    <i class="fas fa-users"></i>
                                    <span id="modalContextSeccion">Sección: --</span>
                                </span>
                            </div>
                        </div>

                        <!-- Estado: carga / vacío / error -->
                        <div id="modalHorarioEstado" class="d-none"></div>

                        <!-- Matriz de Horarios -->
                        <div class="horario-matrix-wrapper" id="modalHorarioMatrixWrapper">
                            <table class="table table-bordered horario-matrix" id="modalHorarioTable">
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

                    <!-- Sidebar de docentes -->
                    <div class="modal-sidebar-docentes">
                        <div class="modal-sidebar-header">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <h5>Docentes Asignados</h5>
                        </div>
                        <div class="modal-docentes-list" id="modalDocentesList">
                            <div class="empty-state">
                                <i class="fas fa-user-tie"></i>
                                <p>Cargando docentes...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-view">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cerrar
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

<link rel="stylesheet" href="{{ asset('css/horario-styles.css') }}">

<style>
    /* Estilos específicos adicionales para showModal.blade.php */
    /* Los estilos principales están en horario-styles.css */

    .modal-sidebar-docentes {
        width: 320px;
        min-width: 320px;
    }

    .modal-docentes-list {
        padding: 1rem;
    }

    .docente-card {
        padding: 0.875rem;
        margin-bottom: 0.625rem;
        word-wrap: break-word;
        overflow-wrap: break-word;
        min-height: auto;
    }

    .docente-name {
        font-size: 0.875rem;
        line-height: 1.4;
        word-wrap: break-word;
        overflow-wrap: break-word;
        margin-bottom: 0.375rem;
    }

    .docente-area {
        font-size: 0.75rem;
        line-height: 1.4;
        align-items: flex-start;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .docente-area i {
        flex-shrink: 0;
        margin-top: 2px;
    }

    .drop-zone {
        height: 100px;
        min-width: 180px;
    }

    .drop-zone .assigned-docente {
        padding: 1rem;
        padding-right: 1rem;
        font-size: 0.85rem;
        gap: 0.5rem;
        min-height: 80px;
        justify-content: center;
    }

    .drop-zone .assigned-docente .docente-nombre {
        font-size: 0.9rem;
        line-height: 1.3;
        word-wrap: break-word;
        word-break: break-word;
        max-width: 100%;
    }

    .drop-zone .assigned-docente .docente-materia {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
        word-wrap: break-word;
        word-break: break-word;
        max-width: 100%;
    }

    .drop-zone .assigned-docente .aula-texto {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        white-space: nowrap;
    }
</style>

<script>
    // Endpoint basado en HorarioAsignacion (lo mismo que genera el servicio y lista index())
    const HORARIO_SHOW_API_URL = @json(route('admin.horario.show-api', ['grado_id' => '__GRADO__', 'seccion_id' => '__SECCION__']));
    const DIAS_POR_DEFECTO = [
        { id: 1, nombre_dia: 'Lunes' }, { id: 2, nombre_dia: 'Martes' }, { id: 3, nombre_dia: 'Miércoles' },
        { id: 4, nombre_dia: 'Jueves' }, { id: 5, nombre_dia: 'Viernes' }
    ];
    let horarioModalRequestId = 0;

    function escHtml(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function horaCorta(hora) {
        return hora ? String(hora).substring(0, 5) : '--:--';
    }

    // tipo: 'loading' | 'empty' | 'error' | null (oculta el estado y muestra la tabla)
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

    // Función para cargar horario en la modal
    async function cargarHorarioEnModal(gradoId, seccionId) {
        const requestId = ++horarioModalRequestId;
        const docentesList = document.getElementById('modalDocentesList');

        document.getElementById('modalContextNivel').textContent = 'Grado: --';
        document.getElementById('modalContextSeccion').textContent = 'Sección: --';
        document.querySelector('#modalHorarioTable thead tr').innerHTML = '<th class="time-header">Hora / Bloque</th>';
        document.querySelector('#modalHorarioTable tbody').innerHTML = '';
        docentesList.innerHTML = '<div class="empty-state"><i class="fas fa-user-tie"></i><p>Cargando docentes...</p></div>';
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

            // Ignorar respuestas viejas si el usuario abrió otro horario mientras tanto
            if (requestId !== horarioModalRequestId) return;

            if (!response.ok) {
                throw new Error(data.message || `Error ${response.status} al consultar el horario`);
            }

            renderizarHorarioEnModal(data);
        } catch (error) {
            if (requestId !== horarioModalRequestId) return;
            console.error('Error al cargar horario:', error);
            setEstadoHorarioModal('error', error.message || 'Error al cargar el horario');
            docentesList.innerHTML = '<div class="empty-state"><i class="fas fa-user-tie"></i><p>Sin datos</p></div>';
        }
    }

    function renderizarHorarioEnModal(data) {
        const thead = document.querySelector('#modalHorarioTable thead tr');
        const tbody = document.querySelector('#modalHorarioTable tbody');
        const docentesList = document.getElementById('modalDocentesList');
        const asignaciones = Array.isArray(data.asignaciones) ? data.asignaciones : [];
        const dias = (Array.isArray(data.dias) && data.dias.length) ? data.dias : DIAS_POR_DEFECTO;

        document.getElementById('modalContextNivel').textContent =
            `Grado: ${data.grado?.numero_grado != null ? data.grado.numero_grado + '° Año' : '--'}`;
        document.getElementById('modalContextSeccion').textContent = `Sección: ${data.seccion?.nombre ?? '--'}`;

        // Filas: bloques activos + cualquier bloque_id usado en asignaciones que no exista en la tabla
        const bloques = (Array.isArray(data.bloques) ? data.bloques : []).map(b => ({ ...b }));
        const idsBloques = new Set(bloques.map(b => Number(b.id)));
        [...new Set(asignaciones.map(a => Number(a.bloque_id)))]
            .filter(id => !idsBloques.has(id))
            .sort((a, b) => a - b)
            .forEach(id => bloques.push({ id, hora_inicio: null, hora_fin: null, huerfano: true }));

        // Encabezado: días en columnas
        thead.innerHTML = '<th class="time-header">Hora / Bloque</th>' +
            dias.map(d => `<th>${escHtml(d.nombre_dia || d.nombre)}</th>`).join('');

        // Cuerpo: una fila por bloque, todas las asignaciones de cada celda
        tbody.innerHTML = bloques.map((bloque, index) => {
            const horario = bloque.huerfano
                ? '<strong>Bloque sin horario</strong>'
                : `<strong>${horaCorta(bloque.hora_inicio)} - ${horaCorta(bloque.hora_fin)}</strong>`;

            const celdas = dias.map(dia => {
                const enCelda = asignaciones.filter(a =>
                    Number(a.dia_id) === Number(dia.id) && Number(a.bloque_id) === Number(bloque.id));

                const contenido = enCelda.map(a => `
                    <div class="assigned-docente mb-1${a.conflicto_manual ? ' border border-warning' : ''}">
                        <div class="docente-materia">${escHtml(a.materia_nombre || 'Sin materia')}</div>
                        <div class="docente-nombre">${escHtml(a.docente_nombre || 'Sin docente')}</div>
                        <div class="aula-texto">${escHtml(a.aula_nombre || 'Sin aula')}</div>
                        ${a.conflicto_manual ? '<small class="text-warning"><i class="fas fa-exclamation-circle"></i> Conflicto</small>' : ''}
                    </div>`).join('');

                return `<td class="drop-zone" data-day="${escHtml(dia.id)}" data-block="${escHtml(bloque.id)}">${contenido}</td>`;
            }).join('');

            return `<tr><td class="time-cell">${horario}<small>Bloque ${index + 1}</small></td>${celdas}</tr>`;
        }).join('');

        // Asignaciones en días fuera de la grilla (no debería pasar, pero se avisa para no ocultarlas)
        const idsDias = new Set(dias.map(d => Number(d.id)));
        const fueraDeGrilla = asignaciones.filter(a => !idsDias.has(Number(a.dia_id))).length;

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

        // Docentes únicos en el sidebar
        const docentesUnicos = {};
        asignaciones.forEach(a => {
            if (!a.docente_id) return;
            const d = docentesUnicos[a.docente_id] ??= { nombre: a.docente_nombre || 'Sin nombre', materias: [] };
            if (a.materia_nombre && !d.materias.includes(a.materia_nombre)) d.materias.push(a.materia_nombre);
        });

        const lista = Object.values(docentesUnicos);
        docentesList.innerHTML = lista.length
            ? lista.map(d => `
                <div class="docente-card">
                    <div class="docente-name">${escHtml(d.nombre)}</div>
                    <div class="docente-area"><i class="fas fa-book"></i><span>${escHtml(d.materias.join(', '))}</span></div>
                </div>`).join('')
            : '<div class="empty-state"><i class="fas fa-user-tie"></i><p>No hay docentes asignados</p></div>';
    }

    function mostrarPantallaCargaModal(mensaje) {
        document.getElementById('modalOverlayCargaMensaje').textContent = mensaje;
        document.getElementById('modalOverlayCarga').classList.remove('oculto');
    }

    function ocultarPantallaCargaModal() {
        document.getElementById('modalOverlayCarga').classList.add('oculto');
    }
</script>

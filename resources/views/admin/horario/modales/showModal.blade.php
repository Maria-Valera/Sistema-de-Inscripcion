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

                        <!-- Matriz de Horarios -->
                        <div class="horario-matrix-wrapper">
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

<style>
    /* Estilos específicos para la modal de horario */
    .modal-horario-container {
        display: flex;
        gap: 20px;
        height: 100%;
    }

    .modal-main-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .modal-sidebar-docentes {
        width: 280px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .modal-sidebar-header {
        background: var(--primary);
        color: white;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-sidebar-header i {
        font-size: 18px;
    }

    .modal-sidebar-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 14px;
    }

    .modal-docentes-list {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
    }

    .modal-context-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .modal-context-info {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
    }

    .modal-context-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .modal-context-item i {
        font-size: 16px;
        opacity: 0.9;
    }

    .modal-context-separator {
        opacity: 0.5;
        font-size: 0.875rem;
    }

    .modal-overlay-carga {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10001;
        flex-direction: column;
        gap: 20px;
    }

    .modal-overlay-carga.oculto {
        display: none;
    }

    .modal-overlay-carga .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    .modal-overlay-carga p {
        color: white;
        font-size: 16px;
        margin: 0;
        font-weight: 500;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Estilos de la vista de horario (copiados de create.blade.php) */
    .horario-matrix-wrapper {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        overflow-x: auto;
    }

    .horario-matrix {
        width: 100%;
        border-collapse: collapse;
    }

    .horario-matrix thead th {
        background: var(--primary);
        color: white;
        padding: 0.5rem;
        font-weight: 600;
        font-size: 0.75rem;
        text-align: center;
        border: 1px solid var(--primary-dark);
        min-width: 80px;
    }

    .horario-matrix .time-header {
        background: var(--gray-700);
        min-width: 80px;
    }

    .horario-matrix tbody td {
        border: 1px solid var(--gray-200);
        vertical-align: middle;
        padding: 0.25rem;
        height: 60px;
    }

    .time-cell {
        background: var(--gray-50);
        padding: 0.25rem 0.5rem;
        text-align: center;
        font-size: 0.75rem;
        color: var(--gray-700);
    }

    .time-cell strong {
        display: block;
        color: var(--gray-900);
    }

    .time-cell small {
        color: var(--gray-500);
        font-size: 0.75rem;
    }

    .drop-zone {
        height: 100px;
        min-width: 180px;
        background: var(--gray-50);
        border: 2px dashed var(--gray-300);
        padding: 0.75rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .drop-zone .assigned-docente {
        background: #0a1128;
        color: white;
        padding: 1rem;
        padding-right: 1rem;
        border-radius: 16px;
        font-size: 0.85rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.5rem;
        animation: fadeIn 0.3s ease;
        position: relative;
        width: 100%;
        min-height: 80px;
        justify-content: center;
    }

    .drop-zone .assigned-docente .docente-nombre {
        font-weight: 700;
        font-size: 0.9rem;
        line-height: 1.3;
        word-wrap: break-word;
        word-break: break-word;
        max-width: 100%;
    }

    .drop-zone .assigned-docente .docente-materia {
        font-size: 0.8rem;
        opacity: 0.9;
        margin-bottom: 0.25rem;
        word-wrap: break-word;
        word-break: break-word;
        max-width: 100%;
    }

    .drop-zone .assigned-docente .aula-texto {
        background: #f3f4f6;
        color: #374151;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .docente-card {
        background: var(--gray-50);
        border-radius: var(--radius);
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        border: 2px solid transparent;
    }

    .docente-card:hover {
        background: var(--gray-100);
        border-color: var(--primary);
        transform: translateX(3px);
    }

    .docente-name {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
        font-size: 0.8125rem;
    }

    .docente-area {
        font-size: 0.6875rem;
        color: var(--gray-600);
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .docente-area i {
        color: var(--primary);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 16px;
        margin: 0;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<script>
    // Función para cargar horario en la modal
    async function cargarHorarioEnModal(gradoId, seccionId) {
        try {
            mostrarPantallaCargaModal('Cargando horario...');

            const response = await fetch(`/api/horario/asignaciones?grado_id=${gradoId}`);
            const horarioData = await response.json();

            // Filtrar por sección específica
            const horarioFiltrado = horarioData.filter(h => h.seccion_id == seccionId);

            const diasResponse = await fetch('/api/dias-semana');
            const diasSemana = await diasResponse.json();

            const bloquesResponse = await fetch('/api/bloques-horario');
            const bloquesHorario = await bloquesResponse.json();

            renderizarHorarioEnModal(horarioFiltrado, diasSemana, bloquesHorario, gradoId, seccionId);

        } catch (error) {
            console.error('Error al cargar horario:', error);
            alert('Error al cargar el horario');
        } finally {
            ocultarPantallaCargaModal();
        }
    }

    function renderizarHorarioEnModal(horarioData, diasSemana, bloquesHorario, gradoId, seccionId) {
        console.log('Datos recibidos:', { horarioData, diasSemana, bloquesHorario, gradoId, seccionId });

        const thead = document.querySelector('#modalHorarioTable thead tr');
        const tbody = document.querySelector('#modalHorarioTable tbody');
        const docentesList = document.querySelector('#modalDocentesList');

        // Actualizar contexto
        document.getElementById('modalContextNivel').textContent = `Grado: ${gradoId}`;
        document.getElementById('modalContextSeccion').textContent = `Sección: ${seccionId}`;

        // Renderizar días en el header
        thead.innerHTML = '<th class="time-header">Hora / Bloque</th>';
        diasSemana.forEach(dia => {
            const th = document.createElement('th');
            th.textContent = dia.nombre_dia || dia.nombre;
            thead.appendChild(th);
        });

        // Renderizar filas y celdas
        tbody.innerHTML = '';
        bloquesHorario.forEach((bloque, index) => {
            const tr = document.createElement('tr');

            // Celda de tiempo
            const timeCell = document.createElement('td');
            timeCell.className = 'time-cell';
            timeCell.innerHTML = `
                <strong>${bloque.hora_inicio} - ${bloque.hora_fin}</strong>
                <small>Bloque ${index + 1}</small>
            `;
            tr.appendChild(timeCell);

            // Celdas de días
            diasSemana.forEach(dia => {
                const td = document.createElement('td');
                td.className = 'drop-zone';
                td.dataset.day = (dia.nombre_dia || dia.nombre).toLowerCase();
                td.dataset.block = index + 1;

                // Buscar asignación para este día y bloque
                const asignacion = horarioData.find(h => {
                    const diaNombre = (dia.nombre_dia || dia.nombre).toLowerCase();
                    const asignacionDia = (h.dia_nombre || '').toLowerCase();
                    console.log('Comparando:', {
                        diaNombre,
                        bloqueId: bloque.id,
                        asignacionDia: asignacionDia,
                        asignacionBloqueId: h.bloque_id,
                        match: asignacionDia === diaNombre && h.bloque_id === bloque.id
                    });
                    return asignacionDia === diaNombre && h.bloque_id === bloque.id;
                });

                console.log('Asignación encontrada para celda:', {
                    dia: (dia.nombre_dia || dia.nombre).toLowerCase(),
                    bloque: bloque.id,
                    asignacion: asignacion ? 'ENCONTRADA' : 'NO ENCONTRADA'
                });

                if (asignacion) {
                    td.innerHTML = `
                        <div class="assigned-docente">
                            <div class="docente-nombre">${asignacion.docente_nombre || 'Docente'}</div>
                            <div class="docente-materia">${asignacion.materia_nombre || 'Materia'}</div>
                            <div class="aula-texto">${asignacion.aula_nombre || 'Sin aula'}</div>
                        </div>
                    `;
                }

                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });

        // Renderizar docentes únicos en el sidebar
        const docentesUnicos = {};
        horarioData.forEach(asignacion => {
            if (asignacion.docente_id && asignacion.docente_nombre) {
                if (!docentesUnicos[asignacion.docente_id]) {
                    docentesUnicos[asignacion.docente_id] = {
                        id: asignacion.docente_id,
                        nombre: asignacion.docente_nombre,
                        materias: []
                    };
                }
                if (asignacion.materia_nombre && !docentesUnicos[asignacion.docente_id].materias.includes(asignacion.materia_nombre)) {
                    docentesUnicos[asignacion.docente_id].materias.push(asignacion.materia_nombre);
                }
            }
        });

        docentesList.innerHTML = '';
        Object.values(docentesUnicos).forEach(docente => {
            const div = document.createElement('div');
            div.className = 'docente-card';
            div.innerHTML = `
                <div class="docente-name">${docente.nombre}</div>
                <div class="docente-area">
                    <i class="fas fa-book"></i>
                    ${docente.materias.join(', ')}
                </div>
            `;
            docentesList.appendChild(div);
        });

        if (Object.keys(docentesUnicos).length === 0) {
            docentesList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-user-tie"></i>
                    <p>No hay docentes asignados</p>
                </div>
            `;
        }
    }

    function mostrarPantallaCargaModal(mensaje) {
        document.getElementById('modalOverlayCargaMensaje').textContent = mensaje;
        document.getElementById('modalOverlayCarga').classList.remove('oculto');
    }

    function ocultarPantallaCargaModal() {
        document.getElementById('modalOverlayCarga').classList.add('oculto');
    }
</script>

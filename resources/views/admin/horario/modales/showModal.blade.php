git <div class="modal fade" id="viewModalHorario" tabindex="-1" aria-labelledby="viewModalHorarioLabel"
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
                    <span>${docente.materias.join(', ')}</span>
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

@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <style>
        .horario-container {
            display: flex;
            gap: 20px;
            height: auto;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-docentes {
            width: 280px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            background: var(--primary);
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header i {
            font-size: 18px;
        }

        .sidebar-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 14px;
        }

        .docentes-list {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }

        .docente-card {
            background: var(--gray-50);
            border-radius: var(--radius);
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            cursor: grab;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            user-select: none;
        }

        .docente-card:hover {
            background: var(--gray-100);
            border-color: var(--primary);
            transform: translateX(3px);
        }

        .docente-card.dragging {
            opacity: 0.5;
            cursor: grabbing;
        }

        .docente-card.selected {
            border-color: var(--primary);
            background: var(--primary-light);
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

        .docente-horas {
            font-size: 0.625rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
            padding-top: 0.25rem;
            border-top: 1px solid var(--gray-200);
        }

        .docente-horas i {
            color: var(--warning);
            font-size: 0.5625rem;
        }

        .selection-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .selection-row {
            display: flex;
            gap: 20px;
            align-items: flex-end;
        }

        .form-group-modern {
            flex: 1;
        }

       
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
            padding: 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            text-align: center;
            border: 1px solid var(--primary-dark);
        }

        .horario-matrix .time-header {
            background: var(--gray-700);
            min-width: 120px;
        }

        .horario-matrix tbody td {
            border: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        .time-cell {
            background: var(--gray-50);
            padding: 0.75rem 1rem;
            text-align: center;
            font-size: 0.875rem;
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
            height: 80px;
            min-width: 140px;
            background: var(--gray-50);
            border: 2px dashed var(--gray-300);
            padding: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .drop-zone:hover {
            background: var(--gray-100);
            border-color: var(--primary);
        }

        .drop-zone.drag-over {
            background: var(--primary-light);
            border-color: var(--primary);
            border-style: solid;
        }

        .drop-zone .assigned-docente {
            background: var(--primary);
            color: white;
            padding: 0.5rem;
            padding-right: 2rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            animation: fadeIn 0.3s ease;
            position: relative;
        }

        .drop-zone .assigned-docente .docente-nombre {
            font-weight: 600;
        }

        .drop-zone .assigned-docente .docente-area {
            font-size: 0.6875rem;
            opacity: 0.9;
        }

        .drop-zone .assigned-docente .docente-materia {
            font-size: 0.6875rem;
            opacity: 0.9;
            margin-top: 0.125rem;
        }

        .drop-zone .assigned-docente .docente-badges {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            margin-top: 0.25rem;
        }

        .drop-zone .assigned-docente .aula-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #ffc107;
            color: #000;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.625rem;
            font-weight: 600;
        }

        .drop-zone .assigned-docente .aula-badge i {
            font-size: 0.5625rem;
        }

        .drop-zone .assigned-docente .seccion-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #6c757d;
            color: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.625rem;
            font-weight: 600;
        }

        .drop-zone .assigned-docente .seccion-badge i {
            font-size: 0.5625rem;
        }

        .drop-zone.ocupado-por-otro {
            background: #2c3e50;
            border-color: #1a252f;
            cursor: not-allowed;
        }

        .drop-zone.ocupado-por-otro .ocupado-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #95a5a6;
            font-size: 0.75rem;
            gap: 0.25rem;
        }

        .drop-zone.ocupado-por-otro .ocupado-indicator i {
            font-size: 1rem;
        }

        .btn-eliminar-docente {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.2s ease;
        }

        .btn-eliminar-docente:hover {
            background: var(--danger);
            transform: scale(1.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            opacity: 0.9;
        }

        .btn-save {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-save:hover {
            opacity: 0.9;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .notifications-container {
            margin-top: 1.5625rem;
        }

        .notification {
            padding: 0.9375rem;
            border-radius: var(--radius);
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .notification:last-child {
            margin-bottom: 0;
        }

        .notification.error {
            background: var(--danger-light);
            border-color: var(--danger);
        }

        .notification.error i {
            color: var(--danger);
            font-size: 1.25rem;
        }

        .notification.error strong {
            color: var(--danger);
        }

        .notification.success {
            background: var(--success-light);
            border-color: var(--success);
        }

        .notification.success i {
            color: var(--success);
            font-size: 1.25rem;
        }

        .notification.success strong {
            color: var(--success);
        }

        .notification.warning {
            background: var(--warning-light);
            border-color: var(--warning);
        }

        .notification.warning i {
            color: var(--warning);
            font-size: 1.25rem;
        }

        .notification.warning strong {
            color: var(--warning);
        }


    </style>
@stop

@section('title', 'Crear Horario')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div>
                    <h1 class="title-main">Crear Horario</h1>
                    <p class="title-subtitle">Asignación de horarios académicos</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        <div class="header-actions">
            <a href="{{ route('admin.horario.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
            <button class="btn-save" id="btnGuardarHorario">
                <i class="fas fa-save"></i>
                Guardar Horario
            </button>
        </div>

        <!-- Selección de nivel académico -->
        <div class="selection-section" id="seccionNivel">
            <h4 class="mb-3">
                <i class="fas fa-graduation-cap" style="color: var(--primary);"></i>
                Paso 1: Seleccionar Nivel Académico
            </h4>
            <div class="selection-row">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="fas fa-layer-group" style="color: var(--primary);"></i>
                        Nivel Académico
                    </label>
                    <select class="form-select form-control-modern" id="nivelAcademico">
                        <option value="">Seleccione un nivel académico</option>
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id }}">
                                {{ $grado->numero_grado }}° Grado
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="btn-filter" id="btnContinuar">
                    <i class="fas fa-arrow-right"></i>
                    Continuar
                </button>
            </div>
        </div>

        <!-- Vista del calendario (oculta inicialmente) -->
        <div class="horario-container" id="vistaCalendario" style="display: none;">
            <div class="main-content">
                <!-- Selector de área de formación -->
                <div class="selection-section">
                    <div class="selection-row">
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="fas fa-book" style="color: var(--primary);"></i>
                                Área de Formación
                            </label>
                            <select class="form-select form-control-modern" id="areaFormacion">
                                <option value="">Todas las áreas</option>
                                @foreach($areasFormacion as $area)
                                    <option value="{{ $area->id }}">
                                        {{ $area->nombre_area_formacion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="fas fa-users" style="color: var(--primary);"></i>
                                Sección
                            </label>
                            <select class="form-select form-control-modern" id="seccion">
                                <option value="">Todas las secciones</option>
                                @foreach($secciones as $seccion)
                                    <option value="{{ $seccion->id }}">
                                        {{ $seccion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="fas fa-door-open" style="color: var(--primary);"></i>
                                Aula
                            </label>
                            <select class="form-select form-control-modern" id="aula">
                                <option value="">Todas las aulas</option>
                                @foreach($aulas as $aula)
                                    <option value="{{ is_array($aula) ? $aula['id_aula'] : $aula->id_aula }}"
                                            data-seccion="{{ is_array($aula) ? $aula['seccion_id'] : null }}"
                                            data-tipo="{{ is_array($aula) ? $aula['tipo_aula'] : $aula->tipo_aula }}">
                                        {{ is_array($aula) ? $aula['nombre_aula'] : $aula->nombre_aula }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Matriz de Horarios -->
                <div class="horario-matrix-wrapper">
                    <table class="table table-bordered horario-matrix">
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
            <div class="sidebar-docentes">
                <div class="sidebar-header">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h5>Docentes Disponibles</h5>
                </div>
                <div class="docentes-list" id="docentesList">
                    <div class="empty-state">
                        <i class="fas fa-user-tie"></i>
                        <p>Seleccione un nivel académico para ver los docentes disponibles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor de notificaciones -->
        <div class="notifications-container" id="notificationsContainer"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nivelSelect = document.getElementById('nivelAcademico');
            const btnContinuar = document.getElementById('btnContinuar');
            const seccionNivel = document.getElementById('seccionNivel');
            const vistaCalendario = document.getElementById('vistaCalendario');
            const areaFormacion = document.getElementById('areaFormacion');
            const docentesList = document.getElementById('docentesList');

            // Datos de prueba para docentes
            const docentesPorNivel = {
                '1': [
                    { id: 1, nombre: 'María García', area: 'Ciencias' },
                    { id: 2, nombre: 'Juan Pérez', area: 'Humanidades' },
                    { id: 3, nombre: 'Ana López', area: 'Técnica' },
                    { id: 4, nombre: 'Carlos Ruiz', area: 'Artística' },
                ],
                '2': [
                    { id: 5, nombre: 'Laura Martínez', area: 'Ciencias' },
                    { id: 6, nombre: 'Pedro Sánchez', area: 'Humanidades' },
                    { id: 7, nombre: 'Sofía Rodríguez', area: 'Técnica' },
                ],
                '3': [
                    { id: 8, nombre: 'Diego Fernández', area: 'Ciencias' },
                    { id: 9, nombre: 'Elena Gómez', area: 'Humanidades' },
                    { id: 10, nombre: 'Miguel Torres', area: 'Técnica' },
                    { id: 11, nombre: 'Lucía Jiménez', area: 'Artística' },
                ]
            };

            let draggedDocente = null;
            let horarioAsignaciones = {};
            const notificationsContainer = document.getElementById('notificationsContainer');

            // Almacenar información de contexto para las asignaciones
            let asignacionesContexto = {};

            // Variables para almacenar datos de la API
            let diasSemana = [];
            let bloquesHorario = [];
            let horarioData = [];

            // Función para cargar datos de la API
            async function cargarDatosAPI() {
                try {
                    const [diasResponse, bloquesResponse, horarioResponse] = await Promise.all([
                        fetch('/api/dias-semana'),
                        fetch('/api/bloques-horario'),
                        fetch('/api/horario')
                    ]);

                    diasSemana = await diasResponse.json();
                    bloquesHorario = await bloquesResponse.json();
                    horarioData = await horarioResponse.json();

                    console.log('Días de semana:', diasSemana);
                    console.log('Bloques horario:', bloquesHorario);
                    console.log('Horario data:', horarioData);

                    renderizarCalendario();
                } catch (error) {
                    console.error('Error al cargar datos de la API:', error);
                    mostrarNotificacion('Error al cargar datos del horario', 'error');
                }
            }

            // Función para cargar docentes desde la API
            async function cargarDocentesAPI(nivel) {
                try {
                    const areaFormacionId = areaFormacion.value;
                    const seccionId = document.getElementById('seccion').value;

                    const params = new URLSearchParams();
                    if (nivel) params.append('grado_id', nivel);
                    if (areaFormacionId) params.append('area_formacion_id', areaFormacionId);
                    if (seccionId) params.append('seccion_id', seccionId);

                    const url = `/api/docente?${params.toString()}`;
                    const response = await fetch(url);
                    const docentes = await response.json();

                    console.log('Docentes cargados:', docentes);

                    renderizarDocentes(docentes);
                } catch (error) {
                    console.error('Error al cargar docentes:', error);
                    mostrarNotificacion('Error al cargar docentes', 'error');
                }
            }

            // Función para renderizar docentes en el sidebar
            function renderizarDocentes(docentes) {
                if (docentes.length === 0) {
                    docentesList.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-user-tie"></i>
                            <p>No hay docentes disponibles</p>
                        </div>
                    `;
                    return;
                }

                // Obtener el área de formación seleccionada para filtrar la visualización
                const areaFormacionId = document.getElementById('areaFormacion').value;
                const seccionId = document.getElementById('seccion').value;

                docentesList.innerHTML = docentes.map(docente => {
                    const nombre = docente.nombre_completo || 'Sin nombre';
                    const horasAcademicas = docente.horas_academicas || 0;

                    // Filtrar áreas de formación según el filtro seleccionado
                    let areasAMostrar = docente.areas || [];
                    if (areaFormacionId) {
                        // Si hay un filtro de área, mostrar solo esa área si el docente la tiene
                        areasAMostrar = areasAMostrar.filter(area => {
                            // Necesitamos comparar con el nombre del área seleccionado
                            const areaSelect = document.getElementById('areaFormacion');
                            const areaNombre = areaSelect.options[areaSelect.selectedIndex]?.text;
                            return area === areaNombre;
                        });
                    }

                    const areaTexto = areasAMostrar.length > 0 ? areasAMostrar.join(', ') : 'Sin área asignada';

                    // Filtrar secciones según el filtro seleccionado
                    let seccionesAMostrar = docente.secciones || [];
                    if (seccionId) {
                        seccionesAMostrar = seccionesAMostrar.filter(seccion => seccion.id == seccionId);
                    }

                    const seccionTexto = seccionesAMostrar.length > 0 
                        ? seccionesAMostrar.map(s => s.nombre).join(', ') 
                        : (docente.secciones && docente.secciones.length > 0 
                            ? docente.secciones.map(s => s.nombre).join(', ') 
                            : 'Sin sección asignada');

                    return `
                        <div class="docente-card" draggable="true" data-id="${docente.id}" data-nombre="${nombre}" data-area="${areaTexto}" data-horas="${horasAcademicas}">
                            <div class="docente-name">${nombre}</div>
                            <div class="docente-area">
                                <i class="fas fa-book"></i>
                                ${areaTexto}
                            </div>
                            <div class="docente-area">
                                <i class="fas fa-users"></i>
                                ${seccionTexto}
                            </div>
                            <div class="docente-horas">
                                <i class="fas fa-clock"></i>
                                ${horasAcademicas} horas académicas
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // Función para renderizar el calendario dinámicamente
            function renderizarCalendario() {
                const thead = document.querySelector('.horario-matrix thead tr');
                const tbody = document.querySelector('.horario-matrix tbody');

                // Verificar que los elementos existan
                if (!thead || !tbody) {
                    console.error('No se encontraron elementos de la tabla del calendario');
                    return;
                }

                // Obtener filtros seleccionados
                const seccionId = document.getElementById('seccion')?.value || '';
                const areaFormacionId = document.getElementById('areaFormacion')?.value || '';

                console.log('Renderizando calendario con filtros:', { seccionId, areaFormacionId });

                // Filtrar horarioData por sección y área de formación
                let horarioFiltrado = horarioData;

                if (seccionId) {
                    horarioFiltrado = horarioFiltrado.filter(h => {
                        return h.seccion && h.seccion.id == seccionId;
                    });
                }

                if (areaFormacionId) {
                    horarioFiltrado = horarioFiltrado.filter(h => {
                        return h.materia && h.materia.id == areaFormacionId;
                    });
                }

                console.log('Horarios filtrados:', horarioFiltrado.length);

                // Renderizar columnas (días de la semana)
                thead.innerHTML = '<th class="time-header">Hora / Bloque</th>';
                diasSemana.forEach(dia => {
                    const th = document.createElement('th');
                    th.textContent = dia.nombre_dia || dia.nombre;
                    thead.appendChild(th);
                });

                // Renderizar filas (bloques horarios) y celdas
                tbody.innerHTML = '';
                bloquesHorario.forEach((bloque, index) => {
                    const tr = document.createElement('tr');

                    // Celda de tiempo
                    const timeCell = document.createElement('td');
                    timeCell.className = 'time-cell';
                    timeCell.innerHTML = `
                        <strong>Bloque ${index + 1}</strong>
                        <br>
                        <small>${bloque.hora_inicio} - ${bloque.hora_fin}</small>
                    `;
                    tr.appendChild(timeCell);

                    // Celdas de días
                    diasSemana.forEach(dia => {
                        const td = document.createElement('td');
                        td.className = 'drop-zone';
                        const diaNombre = dia.nombre_dia || dia.nombre;
                        td.dataset.day = diaNombre.toLowerCase();
                        td.dataset.block = index + 1;

                        // Buscar si hay horario asignado para este día y bloque (usando datos filtrados)
                        const horarioAsignado = horarioFiltrado.find(h => {
                            const apiDiaNombre = h.dia ? (h.dia.nombre_dia || h.dia.nombre) : '';
                            const apiBloqueId = h.bloque ? h.bloque.id : null;
                            return apiDiaNombre.toLowerCase() === diaNombre.toLowerCase() && apiBloqueId === bloque.id;
                        });

                        // Buscar cualquier horario asignado (sin filtro) para detectar ocupación
                        const horarioCualquiera = horarioData.find(h => {
                            const apiDiaNombre = h.dia ? (h.dia.nombre_dia || h.dia.nombre) : '';
                            const apiBloqueId = h.bloque ? h.bloque.id : null;
                            return apiDiaNombre.toLowerCase() === diaNombre.toLowerCase() && apiBloqueId === bloque.id;
                        });

                        // Verificar si hay asignación local (drag and drop)
                        const key = `${diaNombre.toLowerCase()}-${index + 1}`;
                        const asignacionLocal = horarioAsignaciones[key];

                        // Verificar si la asignación local corresponde al contexto actual
                        let asignacionLocalValida = false;
                        let asignacionLocalDeOtroContexto = false;

                        if (asignacionLocal) {
                            // Convertir a strings para comparación consistente
                            const asignacionAreaStr = String(asignacionLocal.areaFormacionId || '');
                            const filtroAreaStr = String(areaFormacionId || '');
                            const asignacionSeccionStr = String(asignacionLocal.seccionId || '');
                            const filtroSeccionStr = String(seccionId || '');

                            console.log('Comparando asignación local:', {
                                key,
                                asignacionAreaStr,
                                filtroAreaStr,
                                asignacionSeccionStr,
                                filtroSeccionStr
                            });

                            // Lógica de coincidencia estricta:
                            // - Si no hay filtro de área, cualquier área coincide
                            // - Si hay filtro de área, debe coincidir exactamente
                            // - Si no hay filtro de sección, cualquier sección coincide (incluyendo vacío)
                            // - Si hay filtro de sección, debe coincidir exactamente (no se aceptan asignaciones genéricas)
                            const coincideArea = !areaFormacionId || asignacionAreaStr === filtroAreaStr;
                            const coincideSeccion = !seccionId || asignacionSeccionStr === filtroSeccionStr;

                            console.log('Resultado comparación:', {
                                coincideArea,
                                coincideSeccion,
                                razonSeccion: !seccionId ? 'sin filtro' :
                                             asignacionSeccionStr === filtroSeccionStr ? 'coincide exacto' : 'no coincide'
                            });

                            if (coincideArea && coincideSeccion) {
                                // La asignación corresponde al contexto actual
                                asignacionLocalValida = true;
                                console.log('Asignación local válida para:', key);
                            } else {
                                // La asignación es de otro contexto (área o sección diferente)
                                asignacionLocalDeOtroContexto = true;
                                console.log('Asignación local de otro contexto para:', key);
                            }
                        }

                        // Determinar si la celda está ocupada por otra área/sección
                        let esOcupadoPorOtro = false;
                        if ((horarioCualquiera && !horarioAsignado && !asignacionLocalValida) || asignacionLocalDeOtroContexto) {
                            // Hay un horario o asignación local pero no corresponde a los filtros actuales
                            esOcupadoPorOtro = true;
                            console.log('Celda marcada como ocupada por otro:', key);
                        }

                        // Aplicar estilo si está ocupado por otro
                        if (esOcupadoPorOtro) {
                            td.classList.add('ocupado-por-otro');
                        }

                        // Priorizar asignación local sobre datos de API
                        if (asignacionLocalValida) {
                            td.innerHTML = `
                                <div class="assigned-docente">
                                    <span class="docente-nombre">${asignacionLocal.nombre}</span>
                                    <span class="docente-materia">${asignacionLocal.area}</span>
                                    <button class="btn-eliminar-docente" onclick="eliminarAsignacion('${key}', event)">×</button>
                                </div>
                            `;
                        } else if (horarioAsignado) {
                            const docenteNombre = horarioAsignado.docente ? horarioAsignado.docente.nombre : 'N/A';
                            const materiaNombre = horarioAsignado.materia ? horarioAsignado.materia.nombre : 'N/A';
                            const tipoAulaRequerida = horarioAsignado.materia ? horarioAsignado.materia.tipo_aula_requerida : 'normal';
                            const aulaFija = horarioAsignado.aula_fija;
                            const seccionNombre = horarioAsignado.seccion ? horarioAsignado.seccion.nombre : '';

                            let aulaBadge = '';
                            if (tipoAulaRequerida !== 'normal' && aulaFija) {
                                aulaBadge = `<span class="aula-badge"><i class="fas fa-map-marker-alt"></i> ${aulaFija.nombre_aula}</span>`;
                            }

                            let seccionBadge = '';
                            if (seccionNombre) {
                                seccionBadge = `<span class="seccion-badge"><i class="fas fa-users"></i> ${seccionNombre}</span>`;
                            }

                            td.innerHTML = `
                                <div class="assigned-docente">
                                    <span class="docente-nombre">${docenteNombre}</span>
                                    <span class="docente-materia">${materiaNombre}</span>
                                    <div class="docente-badges">
                                        ${seccionBadge}
                                        ${aulaBadge}
                                    </div>
                                </div>
                            `;
                        } else if (esOcupadoPorOtro) {
                            // Mostrar indicador de ocupado por otro
                            td.innerHTML = `
                                <div class="ocupado-indicator">
                                    <i class="fas fa-lock"></i>
                                    <span>Ocupado</span>
                                </div>
                            `;
                        } else {
                            // Celda vacía pero visible para permitir drag and drop
                            td.innerHTML = '';
                        }

                        tr.appendChild(td);
                    });

                    tbody.appendChild(tr);
                });

                console.log('Calendario renderizado con', tbody.children.length, 'filas');

                // Re-inicializar drag and drop después de renderizar
                inicializarDragAndDrop();
            }

            // Función para mostrar notificaciones
            function mostrarNotificacion(mensaje, tipo = 'info') {
                const icon = tipo === 'error' ? 'fa-exclamation-circle' : 
                            tipo === 'success' ? 'fa-check-circle' : 
                            tipo === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
                
                const notification = document.createElement('div');
                notification.className = `notification ${tipo}`;
                notification.innerHTML = `
                    <i class="fas ${icon}"></i>
                    <div>
                        <strong>${mensaje}</strong>
                    </div>
                `;
                
                notificationsContainer.appendChild(notification);
                
                // Auto eliminar después de 5 segundos
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            }

            // Continuar a la vista del calendario
            btnContinuar.addEventListener('click', function() {
                const nivel = nivelSelect.value;
                if (!nivel) {
                    mostrarNotificacion('Por favor, seleccione un nivel académico', 'warning');
                    return;
                }

                // Siempre cargar datos básicos (días y bloques) independientemente del área de formación
                cargarDatosAPI();

                // Validar que se seleccione un área de formación
                setTimeout(() => {
                    const areaFormacion = document.getElementById('areaFormacion').value;
                    if (!areaFormacion) {
                        mostrarNotificacion('Por favor, seleccione un área de formación', 'warning');
                        seccionNivel.style.display = 'none';
                        vistaCalendario.style.display = 'flex';
                        // Enfocar el selector de área de formación
                        document.getElementById('areaFormacion').focus();
                    } else {
                        seccionNivel.style.display = 'none';
                        vistaCalendario.style.display = 'flex';
                        cargarDocentesAPI(nivel);
                        filtrarAulasPorSeccion();
                    }
                }, 100);
            });

            // Cargar docentes según nivel
            function cargarDocentes(nivel) {
                const docentes = docentesPorNivel[nivel] || [];
                
                if (docentes.length === 0) {
                    docentesList.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-user-tie"></i>
                            <p>No hay docentes disponibles para este nivel</p>
                        </div>
                    `;
                    return;
                }

                docentesList.innerHTML = docentes.map(docente => `
                    <div class="docente-card" draggable="true" data-id="${docente.id}" data-nombre="${docente.nombre}" data-area="${docente.area}">
                        <div class="docente-name">${docente.nombre}</div>
                        <div class="docente-area">
                            <i class="fas fa-book"></i>
                            ${docente.area}
                        </div>
                    </div>
                `).join('');
            }

            // Filtrar docentes por área de formación
            areaFormacion.addEventListener('change', function() {
                const nivel = nivelSelect.value;
                cargarDocentesAPI(nivel);
                renderizarCalendario(); // Re-renderizar calendario con filtro de área
            });

            // Filtrar docentes por sección
            const seccionSelect = document.getElementById('seccion');
            seccionSelect.addEventListener('change', function() {
                // Validar que se haya seleccionado un área de formación primero
                const areaFormacionId = areaFormacion.value;
                if (!areaFormacionId) {
                    mostrarNotificacion('Por favor, seleccione un área de formación primero', 'warning');
                    seccionSelect.value = ''; // Resetear selección de sección
                    return;
                }

                const nivel = nivelSelect.value;
                cargarDocentesAPI(nivel);
                filtrarAulasPorSeccion();
                renderizarCalendario(); // Re-renderizar calendario con filtro de sección
            });

            // Función para filtrar aulas por sección
            function filtrarAulasPorSeccion() {
                const seccionId = seccionSelect.value;
                const aulaSelect = document.getElementById('aula');
                const opciones = aulaSelect.querySelectorAll('option');

                opciones.forEach(opcion => {
                    if (opcion.value === '') {
                        // Mantener siempre la opción "Todas las aulas"
                        opcion.style.display = 'block';
                        return;
                    }

                    const tipoAula = opcion.dataset.tipo;
                    const seccionAsignada = opcion.dataset.seccion;

                    if (tipoAula === 'regular') {
                        // Aulas regulares: solo mostrar si está asignada a la sección seleccionada
                        if (seccionId && seccionAsignada === seccionId) {
                            opcion.style.display = 'block';
                        } else {
                            // Si no hay sección seleccionada o no coincide, ocultar aulas regulares
                            opcion.style.display = 'none';
                        }
                    } else {
                        // Aulas no regulares: siempre mostrar
                        opcion.style.display = 'block';
                    }
                });

                // Resetear selección si la opción seleccionada ya no es visible
                if (aulaSelect.value !== '') {
                    const opcionSeleccionada = aulaSelect.options[aulaSelect.selectedIndex];
                    if (opcionSeleccionada.style.display === 'none') {
                        aulaSelect.value = '';
                    }
                }
            }

            // Inicializar Drag and Drop
            function inicializarDragAndDrop() {
                // Eventos para los docentes (draggable)
                document.addEventListener('dragstart', function(e) {
                    if (e.target.classList.contains('docente-card')) {
                        draggedDocente = {
                            id: e.target.dataset.id,
                            nombre: e.target.dataset.nombre,
                            area: e.target.dataset.area
                        };
                        e.target.classList.add('dragging');
                        e.dataTransfer.effectAllowed = 'move';
                    }
                });

                document.addEventListener('dragend', function(e) {
                    if (e.target.classList.contains('docente-card')) {
                        e.target.classList.remove('dragging');
                        draggedDocente = null;
                    }
                });

                // Eventos para las zonas de drop
                document.addEventListener('dragover', function(e) {
                    if (e.target.classList.contains('drop-zone') && !e.target.classList.contains('ocupado-por-otro')) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        e.target.classList.add('drag-over');
                    }
                });

                document.addEventListener('dragleave', function(e) {
                    if (e.target.classList.contains('drop-zone')) {
                        e.target.classList.remove('drag-over');
                    }
                });

                document.addEventListener('drop', function(e) {
                    if (e.target.classList.contains('drop-zone') && draggedDocente && !e.target.classList.contains('ocupado-por-otro')) {
                        e.preventDefault();
                        e.target.classList.remove('drag-over');

                        const day = e.target.dataset.day;
                        const block = e.target.dataset.block;
                        const key = `${day}-${block}`;

                        // Verificar si ya hay un docente asignado
                        if (horarioAsignaciones[key]) {
                            mostrarNotificacion('Esta celda ya tiene un docente asignado. Elimine la asignación actual antes de agregar otra.', 'error');
                            return;
                        }

                        // Obtener el área de formación y sección actuales para guardar contexto
                        const areaFormacionId = document.getElementById('areaFormacion').value;
                        const seccionId = document.getElementById('seccion').value;

                        console.log('Guardando asignación con contexto:', {
                            key,
                            areaFormacionId,
                            seccionId,
                            draggedDocente
                        });

                        // Validar que se haya seleccionado un área de formación
                        if (!areaFormacionId) {
                            mostrarNotificacion('Por favor, seleccione un área de formación antes de asignar', 'warning');
                            return;
                        }

                        // Asignar docente con contexto
                        // Si hay sección seleccionada, guardarla; si no, guardar vacío
                        horarioAsignaciones[key] = {
                            ...draggedDocente,
                            areaFormacionId: String(areaFormacionId),
                            seccionId: seccionId ? String(seccionId) : ''
                        };

                        // Actualizar visualmente con botón de eliminar
                        e.target.innerHTML = `
                            <div class="assigned-docente">
                                <span class="docente-nombre">${draggedDocente.nombre}</span>
                                <span class="docente-materia">${draggedDocente.area}</span>
                                <button class="btn-eliminar-docente" onclick="eliminarAsignacion('${key}', event)">×</button>
                            </div>
                        `;

                        mostrarNotificacion(`Docente ${draggedDocente.nombre} asignado correctamente`, 'success');
                    } else if (e.target.classList.contains('ocupado-por-otro')) {
                        mostrarNotificacion('Esta celda está ocupada por otra área o sección', 'warning');
                    }
                });
            }

            // Eliminar asignación
            window.eliminarAsignacion = function(key, event) {
                if (event) {
                    event.stopPropagation();
                }
                
                delete horarioAsignaciones[key];
                const dropZone = document.querySelector(`.drop-zone[data-day="${key.split('-')[0]}"][data-block="${key.split('-')[1]}"]`);
                if (dropZone) {
                    dropZone.innerHTML = '';
                }
                
                mostrarNotificacion('Asignación eliminada correctamente', 'success');
            };

            // Guardar horario
            document.getElementById('btnGuardarHorario').addEventListener('click', function() {
                const asignaciones = Object.keys(horarioAsignaciones).length;
                if (asignaciones === 0) {
                    mostrarNotificacion('No hay asignaciones en el horario', 'warning');
                    return;
                }

                // Aquí se enviarían los datos al servidor
                console.log('Guardando horario...', horarioAsignaciones);
                mostrarNotificacion(`Horario guardado exitosamente con ${asignaciones} asignaciones`, 'success');
            });
        });
    </script>
@endsection

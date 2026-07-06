@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
@stop

@section('title', 'Gestión de Inasistencias')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Inasistencias</h1>
                    <p class="title-subtitle">Control y seguimiento de inasistencias estudiantiles</p>
                </div>
            </div>
            <div class="quick-actions">
                <button class="quick-action-btn">
                    <a href="{{ route('admin.inasistencia.justificacion') }}">
                        <i class="fas fa-plus-circle"></i>
                        <span>Inasistencias diarias</span>
                    </a>
                </button>
                <button class="quick-action-btn">
                    <a href="{{ route('admin.acta.create') }}">
                        <i class="fas fa-file-alt"></i>
                        <span>Acta de Entrevista</span>
                    </a>
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
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

        <nav>
            <button class="active" onclick="mostrarPestaña('inasistencias')">Inasistencias</button>
            <button onclick="mostrarPestaña('disciplina')">Disciplina</button>
            <button onclick="mostrarPestaña('calendario')">Calendario</button>
        </nav>

        <div id="inasistencias" class="tab-content" style="display:block;">
            <div class="card-modern">
                <div class="card-header-modern">
                    <div class="header-left">
                        <div class="header-icon">
                            <i class="fas fa-list-ul"></i>
                        </div>
                        <div>
                            <h3>Listado de Inasistencias</h3>
                            <p>Gestión de inasistencias docentes</p>
                        </div>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div class="table-wrapper">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Docente</th>
                                    <th>Fecha</th>
                                    <th>Justificado</th>
                                    <th>Observaciones</th>
                                    <th>Incorporación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Docente 1</td>
                                    <td>2025-01-01</td>
                                    <td>Si</td>
                                    <td>Enfermedad</td>
                                    <td>20-10-2026</td>
                                    <td>
                                        <button class="btn btn-primary">Editar</button>
                                        <button class="btn btn-danger">Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="disciplina" class="tab-content" style="display:none;">
            <div class="card-modern">
                <div class="card-header-modern">
                    <div class="header-left">
                        <div class="header-icon">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <div>
                            <h3>Procedimientos Disciplinarios</h3>
                            <p>Workflow digital de gestión de faltas y procesos</p>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="disciplina-nav">
                            <button class="disciplina-nav-btn" onclick="cambiarMesDisciplina(-1)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="mesDisciplinaActual">Junio 2026</span>
                            <button class="disciplina-nav-btn" onclick="cambiarMesDisciplina(1)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div class="table-wrapper">
                        <table class="table-modern">
                            <div class="table-container">
                                <thead>
                                    <tr>
                                        <th>Docente</th>
                                        <th>Faltas Injustificadas</th>
                                        <th>Estado del Proceso</th>
                                        <th>Última Acción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDisciplina">
                                    <!-- Se llena con JavaScript -->
                                </tbody>
                            </div>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para ver detalles del proceso disciplinario -->
        <div class="modal fade" id="modalDetalleProceso" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content modal-modern">
                    <div class="modal-header">
                        <div class="modal-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h5 class="modal-title">Detalle del Proceso Disciplinario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body" id="contenidoModalProceso">
                        <!-- Se llena con JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn-modal-primary" onclick="generarDocumento()">
                            <i class="fas fa-file-pdf"></i> Generar Documento
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="calendario" class="tab-content" style="display:none;">
            <div class="card-modern">
                <div class="card-header-modern">
                    <div class="header-left">
                        <div class="header-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h3>Calendario de Seguimiento Docente</h3>
                            <p>Visualización de fechas de incorporación</p>
                        </div>
                    </div>
                    <form>
                        <div class="form-group-modern mb-2">
                            <div class="search-modern">
                                <i class="fas fa-search"></i>
                                <input type="text" id="buscarDocente" class="form-control-modern"
                                    placeholder="Buscar docente..." onkeyup="filtrarCalendario()">
                            </div>
                            <small class="form-text-modern" style="margin-top: 0.5rem; color: var(--gray-500);">
                                <i class="fas fa-info-circle"></i>
                                Buscar por nombre de docente
                            </small>
                        </div>
                    </form>
                </div>
                <div class="card-body-modern">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <button class="calendar-nav-btn" onclick="cambiarMes(-1)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <h4 id="mesActual">Enero 2026</h4>
                            <button class="calendar-nav-btn" onclick="cambiarMes(1)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="calendar-grid">
                            <div class="calendar-day-header">Dom</div>
                            <div class="calendar-day-header">Lun</div>
                            <div class="calendar-day-header">Mar</div>
                            <div class="calendar-day-header">Mié</div>
                            <div class="calendar-day-header">Jue</div>
                            <div class="calendar-day-header">Vie</div>
                            <div class="calendar-day-header">Sáb</div>
                        </div>
                        <div class="calendar-grid" id="diasCalendario">
                            <!-- Los días se generarán con JavaScript -->
                        </div>
                    </div>
                    <div class="calendar-legend">
                        <h5>Leyenda</h5>
                        <div class="legend-items">
                            <div class="legend-item">
                                <div class="legend-color incorporacion"></div>
                                <span>Incorporación</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color hoy"></div>
                                <span>Hoy</span>
                            </div>
                        </div>
                    </div>
                    <div class="docentes-list" id="listaDocentes">
                        <h5>Docentes con Incorporaciones este Mes</h5>
                        <div id="docentesContainer">
                            <!-- Lista de docentes se generará con JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>






        <!-- Sección de Estadísticas Generales -->
        <div class="stats-section">
            <h3 class="section-title">
                <i class="fas fa-chart-bar"></i>
                Estadísticas Generales
            </h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">0</span>
                        <span class="stat-label">Total Inasistencias</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">0</span>
                        <span class="stat-label">Justificadas</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">0</span>
                        <span class="stat-label">Sin Justificar</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">0</span>
                        <span class="stat-label">En Proceso disiplinario</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    function mostrarPestaña(id) {
        // Ocultar todas las pestañas y remover clase active
        document.querySelectorAll('.tab-content').forEach(function(tab) {
            tab.style.display = 'none';
            tab.classList.remove('active');
        });
        
        // Remover clase active de todos los botones
        document.querySelectorAll('nav button').forEach(function(btn) {
            btn.classList.remove('active');
        });
        
        // Mostrar la pestaña seleccionada y agregar clase active
        const selectedTab = document.getElementById(id);
        selectedTab.style.display = 'block';
        selectedTab.classList.add('active');
        
        // Agregar clase active al botón correspondiente
        event.target.classList.add('active');
        
        // Si es la pestaña calendario, inicializar el calendario
        if (id === 'calendario') {
            inicializarCalendario();
        }
        
        // Si es la pestaña disciplina, cargar la tabla
        if (id === 'disciplina') {
            cargarTablaDisciplina();
        }
    }
    
    // Cargar notificaciones de inasistencias
    document.addEventListener('DOMContentLoaded', function() {
        // Aquí iría la lógica para cargar las notificaciones
        // Por ahora, solo mostramos un mensaje de ejemplo
        const notificationsContainer = document.getElementById('notifications-container');
        if (notificationsContainer) {
            notificationsContainer.innerHTML = '<p style="color: #666; text-align: center;">Cargando notificaciones...</p>';
            
            // Simular carga de datos
            setTimeout(function() {
                notificationsContainer.innerHTML = '<p style="color: #666; text-align: center;">No hay inasistencias registradas para hoy</p>';
            }, 1000);
        }
    });

    // Variables globales del calendario
    let fechaActual = new Date();
    let mesActual = fechaActual.getMonth();
    let anioActual = fechaActual.getFullYear();
    
    // Datos de prueba de docentes con fechas de incorporación
    const docentesData = [
        { id: 1, nombre: 'Carlos Méndez', fechaIncorporacion: '2026-01-15', materia: 'Matemáticas' },
        { id: 2, nombre: 'Ana Rodríguez', fechaIncorporacion: '2026-01-20', materia: 'Lenguaje' },
        { id: 3, nombre: 'Luis García', fechaIncorporacion: '2026-01-25', materia: 'Ciencias' },
        { id: 4, nombre: 'María López', fechaIncorporacion: '2026-02-10', materia: 'Historia' },
        { id: 5, nombre: 'José Martínez', fechaIncorporacion: '2026-02-18', materia: 'Inglés' },
        { id: 6, nombre: 'Pedro Sánchez', fechaIncorporacion: '2026-03-05', materia: 'Geografía' },
        { id: 7, nombre: 'Laura Torres', fechaIncorporacion: '2026-03-22', materia: 'Educación Física' },
        { id: 8, nombre: 'Roberto Díaz', fechaIncorporacion: '2026-04-12', materia: 'Arte' },
        { id: 9, nombre: 'Carmen Vega', fechaIncorporacion: '2026-04-28', materia: 'Música' },
        { id: 10, nombre: 'Miguel Ángel', fechaIncorporacion: '2026-05-08', materia: 'Informática' },
        { id: 11, nombre: 'Elena Castro', fechaIncorporacion: '2026-06-05', materia: 'Biología' },
        { id: 12, nombre: 'Fernando Ruiz', fechaIncorporacion: '2026-06-12', materia: 'Química' },
        { id: 13, nombre: 'Patricia Mora', fechaIncorporacion: '2026-06-18', materia: 'Literatura' },
        { id: 14, nombre: 'Javier Solís', fechaIncorporacion: '2026-06-25', materia: 'Física' },
        { id: 15, nombre: 'Sofía Ramírez', fechaIncorporacion: '2026-06-30', materia: 'Matemáticas' },
        { id: 16, nombre: 'Andrés Molina', fechaIncorporacion: '2026-07-08', materia: 'Historia' },
        { id: 17, nombre: 'Claudia Vega', fechaIncorporacion: '2026-07-15', materia: 'Inglés' },
        { id: 18, nombre: 'Ricardo Flores', fechaIncorporacion: '2026-07-22', materia: 'Geografía' },
        { id: 19, nombre: 'Monica Herrera', fechaIncorporacion: '2026-08-05', materia: 'Arte' },
        { id: 20, nombre: 'Diego Benítez', fechaIncorporacion: '2026-08-18', materia: 'Educación Física' }
    ];

    // Función para inicializar el calendario
    function inicializarCalendario() {
        generarCalendario(mesActual, anioActual);
        actualizarListaDocentes();
    }

    // Función para cambiar de mes
    function cambiarMes(delta) {
        mesActual += delta;
        
        if (mesActual > 11) {
            mesActual = 0;
            anioActual++;
        } else if (mesActual < 0) {
            mesActual = 11;
            anioActual--;
        }
        
        generarCalendario(mesActual, anioActual);
        actualizarListaDocentes();
    }

    // Función para generar el календарь
    function generarCalendario(mes, anio) {
        const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        document.getElementById('mesActual').textContent = `${nombresMeses[mes]} ${anio}`;
        
        const primerDia = new Date(anio, mes, 1).getDay();
        const diasEnMes = new Date(anio, mes + 1, 0).getDate();
        const hoy = new Date();
        
        let calendarioHTML = '';
        
        // Días vacíos antes del primer día del mes
        for (let i = 0; i < primerDia; i++) {
            calendarioHTML += '<div class="calendar-day empty"></div>';
        }
        
        // Días del mes
        for (let dia = 1; dia <= diasEnMes; dia++) {
            const fechaStr = `${anio}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
            const esHoy = hoy.getDate() === dia && hoy.getMonth() === mes && hoy.getFullYear() === anio;
            
            // Buscar docentes con incorporación en este día
            const docentesDelDia = docentesData.filter(doc => doc.fechaIncorporacion === fechaStr);
            
            let clases = 'calendar-day';
            if (esHoy) clases += ' hoy';
            if (docentesDelDia.length > 0) clases += ' incorporacion';
            
            calendarioHTML += `<div class="${clases}" data-fecha="${fechaStr}" onclick="seleccionarDia('${fechaStr}')">
                <span class="day-number">${dia}</span>
                ${docentesDelDia.length > 0 ? `<div class="day-indicator">${docentesDelDia.length}</div>` : ''}
            </div>`;
        }
        
        document.getElementById('diasCalendario').innerHTML = calendarioHTML;
    }

    // Función para seleccionar un día y mostrar los docentes
    function seleccionarDia(fecha) {
        // Remover clase seleccionado de todos los días
        document.querySelectorAll('.calendar-day').forEach(dia => {
            dia.classList.remove('seleccionado');
        });
        
        // Agregar clase seleccionado al día clickeado
        const diaSeleccionado = document.querySelector(`.calendar-day[data-fecha="${fecha}"]`);
        if (diaSeleccionado) {
            diaSeleccionado.classList.add('seleccionado');
        }
        
        // Buscar docentes con incorporación en esa fecha
        const docentesDelDia = docentesData.filter(doc => doc.fechaIncorporacion === fecha);
        
        // Actualizar el título de la lista
        const fechaFormateada = new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const listaDocentes = document.getElementById('listaDocentes');
        listaDocentes.querySelector('h5').innerHTML = `<i class="fas fa-calendar-check"></i> Docentes: ${fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1)}`;
        
        // Mostrar los docentes
        const container = document.getElementById('docentesContainer');
        
        if (docentesDelDia.length === 0) {
            container.innerHTML = '<p class="no-docentes">No hay incorporaciones programadas para esta fecha</p>';
            return;
        }
        
        container.innerHTML = docentesDelDia.map(doc => `
            <div class="docente-item" data-nombre="${doc.nombre.toLowerCase()}">
                <div class="docente-info">
                    <div class="docente-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="docente-details">
                        <h6>${doc.nombre}</h6>
                        <p>${doc.materia}</p>
                    </div>
                </div>
                <div class="docente-fecha">
                    <span class="fecha-badge reincorporacion">
                        <i class="fas fa-check-circle"></i>
                        Reincorporación
                    </span>
                </div>
            </div>
        `).join('');
    }

    // Función para actualizar la lista de docentes del mes actual
    function actualizarListaDocentes() {
        const container = document.getElementById('docentesContainer');
        const mesStr = String(mesActual + 1).padStart(2, '0');
        
        const docentesDelMes = docentesData.filter(doc => {
            const docFecha = new Date(doc.fechaIncorporacion);
            return docFecha.getMonth() === mesActual && docFecha.getFullYear() === anioActual;
        });
        
        if (docentesDelMes.length === 0) {
            container.innerHTML = '<p class="no-docentes">No hay incorporaciones programadas para este mes</p>';
            return;
        }
        
        container.innerHTML = docentesDelMes.map(doc => `
            <div class="docente-item" data-nombre="${doc.nombre.toLowerCase()}">
                <div class="docente-info">
                    <div class="docente-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="docente-details">
                        <h6>${doc.nombre}</h6>
                        <p>${doc.materia}</p>
                    </div>
                </div>
                <div class="docente-fecha">
                    <span class="fecha-badge">
                        <i class="fas fa-calendar-check"></i>
                        ${new Date(doc.fechaIncorporacion).toLocaleDateString('es-ES')}
                    </span>
                </div>
            </div>
        `).join('');
    }

    // Función para filtrar el calendario por nombre de docente
    function filtrarCalendario() {
        const busqueda = document.getElementById('buscarDocente').value.toLowerCase();
        const docenteItems = document.querySelectorAll('.docente-item');
        
        docenteItems.forEach(item => {
            const nombre = item.dataset.nombre;
            if (nombre.includes(busqueda)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
        
        // También filtrar los días del calendario
        const diasCalendario = document.querySelectorAll('.calendar-day.incorporacion');
        diasCalendario.forEach(dia => {
            const fecha = dia.dataset.fecha;
            const docentesDelDia = docentesData.filter(doc => 
                doc.fechaIncorporacion === fecha && doc.nombre.toLowerCase().includes(busqueda)
            );
            
            if (busqueda === '' || docentesDelDia.length > 0) {
                dia.style.opacity = '1';
                dia.style.pointerEvents = 'auto';
            } else {
                dia.style.opacity = '0.3';
                dia.style.pointerEvents = 'none';
            }
        });
    }

    // Variables globales para disciplina
    let mesDisciplinaActual = fechaActual.getMonth();
    let anioDisciplinaActual = fechaActual.getFullYear();

    // Datos de prueba para procedimientos disciplinarios
    const disciplinaData = [
        {
            id: 1,
            nombre: 'Roberto Díaz',
            materia: 'Arte',
            faltasInjustificadas: 4,
            estado: 'riesgo',
            ultimaAccion: '2026-06-20',
            documentos: [
                { tipo: 'Acta de entrevista', fecha: '2026-06-15', estado: 'Emitido' },
                { tipo: 'Notificación de falta', fecha: '2026-06-10', estado: 'Emitido' },
                { tipo: 'Reporte de inasistencia', fecha: '2026-06-05', estado: 'Emitido' }
            ],
            testigos: ['María López - Coordinadora', 'Carlos Méndez - Docente'],
            autoridad: 'Lic. Ana García - Directora'
        },
        {
            id: 2,
            nombre: 'Patricia Mora',
            materia: 'Literatura',
            faltasInjustificadas: 2,
            estado: 'advertencia',
            ultimaAccion: '2026-06-18',
            documentos: [
                { tipo: 'Acta de advertencia', fecha: '2026-06-18', estado: 'Emitido' },
                { tipo: 'Reporte de inasistencia', fecha: '2026-06-12', estado: 'Emitido' }
            ],
            testigos: ['Laura Torres - Coordinadora'],
            autoridad: 'Lic. Ana García - Directora'
        },
        {
            id: 3,
            nombre: 'Javier Solís',
            materia: 'Física',
            faltasInjustificadas: 3,
            estado: 'acta',
            ultimaAccion: '2026-06-22',
            documentos: [
                { tipo: 'Acta de entrevista', fecha: '2026-06-22', estado: 'Emitido' },
                { tipo: 'Notificación de apertura', fecha: '2026-06-20', estado: 'Emitido' },
                { tipo: 'Reporte de inasistencia', fecha: '2026-06-15', estado: 'Emitido' }
            ],
            testigos: ['Pedro Sánchez - Coordinador', 'Elena Castro - Docente'],
            autoridad: 'Lic. Ana García - Directora'
        },
        {
            id: 4,
            nombre: 'Elena Castro',
            materia: 'Biología',
            faltasInjustificadas: 1,
            estado: 'advertencia',
            ultimaAccion: '2026-06-10',
            documentos: [
                { tipo: 'Acta de advertencia', fecha: '2026-06-10', estado: 'Emitido' }
            ],
            testigos: ['Fernando Ruiz - Coordinador'],
            autoridad: 'Lic. Ana García - Directora'
        },
        {
            id: 5,
            nombre: 'Diego Benítez',
            materia: 'Educación Física',
            faltasInjustificadas: 5,
            estado: 'apertura',
            ultimaAccion: '2026-06-25',
            documentos: [
                { tipo: 'Resolución de apertura', fecha: '2026-06-25', estado: 'Emitido' },
                { tipo: 'Acta de entrevista final', fecha: '2026-06-23', estado: 'Emitido' },
                { tipo: 'Notificación de despido', fecha: '2026-06-23', estado: 'Pendiente' },
                { tipo: 'Reporte acumulado', fecha: '2026-06-20', estado: 'Emitido' }
            ],
            testigos: ['Sofía Ramírez - Coordinadora', 'Andrés Molina - Docente', 'Claudia Vega - Docente'],
            autoridad: 'Lic. Ana García - Directora'
        },
        {
            id: 6,
            nombre: 'Fernando Ruiz',
            materia: 'Química',
            faltasInjustificadas: 0,
            estado: 'ninguno',
            ultimaAccion: null,
            documentos: [],
            testigos: [],
            autoridad: null
        }
    ];

    // Función para cambiar de mes en disciplina
    function cambiarMesDisciplina(delta) {
        mesDisciplinaActual += delta;
        
        if (mesDisciplinaActual > 11) {
            mesDisciplinaActual = 0;
            anioDisciplinaActual++;
        } else if (mesDisciplinaActual < 0) {
            mesDisciplinaActual = 11;
            anioDisciplinaActual--;
        }
        
        cargarTablaDisciplina();
    }

    // Función para cargar la tabla de disciplina
    function cargarTablaDisciplina() {
        const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        document.getElementById('mesDisciplinaActual').textContent = `${nombresMeses[mesDisciplinaActual]} ${anioDisciplinaActual}`;
        
        const tbody = document.getElementById('tablaDisciplina');
        
        // Filtrar por mes actual
        let datosFiltrados = disciplinaData.filter(doc => {
            if (!doc.ultimaAccion) return false;
            const fecha = new Date(doc.ultimaAccion);
            return fecha.getMonth() === mesDisciplinaActual && fecha.getFullYear() === anioDisciplinaActual;
        });
        
        if (datosFiltrados.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <h4>No hay registros este mes</h4>
                            <p>No hay procedimientos disciplinarios para ${nombresMeses[mesDisciplinaActual]} de ${anioDisciplinaActual}</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = datosFiltrados.map(doc => {
            const estadoBadge = getEstadoBadge(doc.estado, doc.faltasInjustificadas);
            const ultimaAccion = doc.ultimaAccion ? new Date(doc.ultimaAccion).toLocaleDateString('es-ES') : '-';
            
            return `
                <tr class="${doc.faltasInjustificadas >= 3 ? 'row-riesgo' : ''}">
                    <td>
                        <div class="docente-nombre">
                            <strong>${doc.nombre}</strong>
                            <small>${doc.materia}</small>
                        </div>
                    </td>
                    <td>
                        <span class="faltas-badge ${doc.faltasInjustificadas >= 3 ? 'faltas-alta' : 'faltas-normal'}">
                            ${doc.faltasInjustificadas}
                        </span>
                    </td>
                    <td>${estadoBadge}</td>
                    <td>${ultimaAccion}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn btn-view" onclick="verDetalleProceso(${doc.id})" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Función para obtener el badge de estado
    function getEstadoBadge(estado, faltas) {
        if (faltas >= 3) {
            return `<span class="status-badge status-danger">
                <span class="status-dot"></span>
                En riesgo de despido
            </span>`;
        }
        
        switch(estado) {
            case 'advertencia':
                return `<span class="status-badge status-warning">
                    <span class="status-dot"></span>
                    En advertencia
                </span>`;
            case 'acta':
                return `<span class="status-badge status-info">
                    <span class="status-dot"></span>
                    Acta emitida
                </span>`;
            case 'apertura':
                return `<span class="status-badge status-danger">
                    <span class="status-dot"></span>
                    Apertura de procedimiento
                </span>`;
            default:
                return `<span class="status-badge status-inactive">
                    <span class="status-dot"></span>
                    Sin proceso
                </span>`;
        }
    }

    // Función para filtrar disciplina
    function filtrarDisciplina() {
        cargarTablaDisciplina();
    }

    // Función para ver detalle del proceso
    function verDetalleProceso(id) {
        const docente = disciplinaData.find(d => d.id === id);
        if (!docente) return;
        
        const modalBody = document.getElementById('contenidoModalProceso');
        
        const documentosHTML = docente.documentos.length > 0 
            ? docente.documentos.map(doc => `
                <div class="documento-item">
                    <div class="documento-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="documento-info">
                        <h6>${doc.tipo}</h6>
                        <small>Fecha: ${new Date(doc.fecha).toLocaleDateString('es-ES')}</small>
                    </div>
                    <span class="documento-estado">${doc.estado}</span>
                </div>
            `).join('')
            : '<p class="no-documentos">No hay documentos registrados</p>';
        
        const testigosHTML = docente.testigos.length > 0
            ? docente.testigos.map(t => `<span class="testigo-badge">${t}</span>`).join('')
            : '<span class="no-testigos">No hay testigos registrados</span>';
        
        modalBody.innerHTML = `
            <div class="proceso-header">
                <div class="proceso-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="proceso-info">
                    <h4>${docente.nombre}</h4>
                    <p>${docente.materia}</p>
                </div>
                <div class="proceso-estado">
                    ${getEstadoBadge(docente.estado, docente.faltasInjustificadas)}
                </div>
            </div>
            
            <div class="proceso-stats">
                <div class="stat-item">
                    <span class="stat-label">Faltas Injustificadas</span>
                    <span class="stat-value ${docente.faltasInjustificadas >= 3 ? 'stat-danger' : 'stat-normal'}">${docente.faltasInjustificadas}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Última Acción</span>
                    <span class="stat-value">${docente.ultimaAccion ? new Date(docente.ultimaAccion).toLocaleDateString('es-ES') : '-'}</span>
                </div>
            </div>
            
            <div class="proceso-section">
                <h5><i class="fas fa-file-alt"></i> Documentos Emitidos</h5>
                <div class="documentos-list">
                    ${documentosHTML}
                </div>
            </div>
            
            <div class="proceso-section">
                <h5><i class="fas fa-users"></i> Testigos</h5>
                <div class="testigos-list">
                    ${testigosHTML}
                </div>
            </div>
            
            <div class="proceso-section">
                <h5><i class="fas fa-user-shield"></i> Autoridad a Cargo</h5>
                <p class="autoridad-text">${docente.autoridad || 'No asignada'}</p>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('modalDetalleProceso'));
        modal.show();
    }

    // Función para generar documento
    function generarDocumento() {
        alert('Generando documento PDF del proceso disciplinario...');
    }
</script>

    <style>
        .quick-actions-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
        }

        .section-title i {
            color: var(--primary);
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .quick-action-btn {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .quick-action-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        .quick-action-btn i {
            font-size: 28px;
        }

        .stats-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
        }

        .stat-card .stat-content {
            display: flex;
            flex-direction: column;
        }

        .stat-card .stat-content .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .stat-card .stat-content .stat-label {
            font-size: 13px;
            color: #666;
            margin-top: 3px;
        }

        .quick-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .quick-action-btn {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            flex: 1;
            min-width: 240px;
            max-width: 240px; 
            text-align: center;
        }


        .quick-action-btn span {
            text-align: center;
            line-height: 1.2;
        }

        .quick-action-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .quick-action-btn i {
            font-size: 28px;
        }

        /* Estilos de navegación */
        nav {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 0;
        }

        nav button {
            padding: 12px 24px;
            cursor: pointer;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--gray-600);
            transition: all 0.3s ease;
            position: relative;
            top: 2px;
        }

        nav button:hover {
            color: var(--primary);
            background: var(--gray-50);
        }

        nav button.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            font-weight: 600;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Estilos de tabla vacía */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-500);
        }

        .empty-state .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--gray-300);
        }

        .empty-state h4 {
            font-size: 1.25rem;
            color: var(--gray-700);
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 0.95rem;
            color: var(--gray-500);
        }

    /* Estilos de Procedimiento Disciplinario */
    .disciplina-nav {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .disciplina-nav-btn {
        background: var(--primary);
        color: white;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 0.8rem;
    }

    .disciplina-nav-btn:hover {
        background: var(--primary-dark);
        transform: scale(1.05);
    }

    #mesDisciplinaActual {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--gray-900);
        min-width: 120px;
        text-align: center;
    }

    .docente-nombre {
        display: flex;
        flex-direction: column;
    }

    .docente-nombre strong {
        font-size: 0.9rem;
        color: var(--gray-900);
    }

    .docente-nombre small {
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .faltas-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .faltas-badge.faltas-normal {
        background: var(--success-light);
        color: var(--success);
    }

    .faltas-badge.faltas-alta {
        background: var(--danger-light);
        color: var(--danger);
    }

    .row-riesgo {
        background: rgba(220, 38, 38, 0.05);
    }

    .row-riesgo:hover {
        background: rgba(220, 38, 38, 0.1);
    }

    /* Estilos del Modal de Proceso */
    .modal-header {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        border-bottom: 1px solid var(--gray-200);
    }

    .modal-icon {
        width: 48px;
        height: 48px;
        background: var(--primary);
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .modal-title {
        flex: 1;
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .btn-close {
        background: transparent;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        color: var(--gray-500);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-close:hover {
        background: var(--gray-100);
        color: var(--gray-700);
    }

    .modal-body {
        padding: 20px;
        max-height: 500px;
        overflow-y: auto;
    }

    .proceso-header {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        background: var(--gray-50);
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .proceso-avatar {
        width: 56px;
        height: 56px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .proceso-info {
        flex: 1;
    }

    .proceso-info h4 {
        margin: 0 0 5px 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .proceso-info p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--gray-500);
    }

    .proceso-estado {
        flex-shrink: 0;
    }

    .proceso-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-item {
        background: var(--gray-50);
        padding: 15px;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--gray-500);
        font-weight: 500;
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
    }

    .stat-value.stat-danger {
        color: var(--danger);
    }

    .stat-value.stat-normal {
        color: var(--success);
    }

    .proceso-section {
        margin-bottom: 20px;
    }

    .proceso-section h5 {
        margin: 0 0 15px 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--gray-900);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .proceso-section h5 i {
        color: var(--primary);
    }

    .documentos-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .documento-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--gray-50);
        border-radius: 8px;
        border-left: 3px solid var(--primary);
    }

    .documento-icon {
        width: 36px;
        height: 36px;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .documento-info {
        flex: 1;
    }

    .documento-info h6 {
        margin: 0 0 3px 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .documento-info small {
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .documento-estado {
        padding: 4px 10px;
        background: var(--success-light);
        color: var(--success);
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .no-documentos {
        text-align: center;
        color: var(--gray-500);
        font-size: 0.85rem;
        padding: 20px;
        background: var(--gray-50);
        border-radius: 8px;
    }

    .testigos-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .testigo-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .testigo-badge::before {
        content: '';
        width: 8px;
        height: 8px;
        background: var(--primary);
        border-radius: 50%;
    }

    .no-testigos {
        color: var(--gray-500);
        font-size: 0.85rem;
        font-style: italic;
    }

    .autoridad-text {
        font-size: 0.95rem;
        color: var(--gray-700);
        padding: 12px;
        background: var(--gray-50);
        border-radius: 8px;
        margin: 0;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px;
        border-top: 1px solid var(--gray-200);
    }

    .btn-modal-cancel {
        padding: 10px 20px;
        background: var(--gray-100);
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--gray-700);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-modal-cancel:hover {
        background: var(--gray-200);
    }

    .btn-modal-primary {
        padding: 10px 20px;
        background: var(--primary);
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-modal-primary:hover {
        background: var(--primary-dark);
    }

    /* Estilos del Calendario */
    .calendar-container {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding: 0 5px;
    }

    .calendar-header h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .calendar-nav-btn {
        background: var(--primary);
        color: white;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 0.8rem;
    }

    .calendar-nav-btn:hover {
        background: var(--primary-dark);
        transform: scale(1.05);
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 3px;
        margin-bottom: 8px;
    }

    .calendar-day-header {
        text-align: center;
        font-weight: 600;
        color: var(--gray-600);
        font-size: 0.7rem;
        padding: 4px 2px;
    }

    .calendar-day {
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        position: relative;
        min-height: 22px;
        padding: 2px 4px;
        height: 32px;
    }

    .calendar-day.empty {
        background: transparent;
        border: none;
        cursor: default;
    }

    .calendar-day:hover:not(.empty) {
        background: var(--primary-light);
        transform: scale(1.01);
    }

    .calendar-day.hoy {
        background: var(--info);
        color: white;
        border-color: var(--info);
    }

    .calendar-day.incorporacion {
        background: var(--success);
        color: white;
        border-color: var(--success);
    }

    .calendar-day.incorporacion:hover {
        background: #059669;
    }

    .calendar-day.seleccionado {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.3);
    }

    .day-number {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1;
    }

    .day-indicator {
        position: absolute;
        top: 2px;
        right: 2px;
        background: white;
        color: var(--success);
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        font-weight: 700;
        line-height: 1;
    }

    .calendar-legend {
        background: var(--gray-50);
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 15px;
    }

    .calendar-legend h5 {
        margin: 0 0 8px 0;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .legend-items {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: var(--gray-600);
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }

    .legend-color.incorporacion {
        background: var(--success);
    }

    .legend-color.hoy {
        background: var(--info);
    }

    .docentes-list {
        background: white;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .docentes-list h5 {
        margin: 0 0 12px 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--gray-900);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .docentes-list h5::before {
        content: '';
        width: 3px;
        height: 16px;
        background: var(--primary);
        border-radius: 2px;
    }

    .docente-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: var(--gray-50);
        border-radius: 6px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
        border-left: 3px solid var(--primary);
    }

    .docente-item:hover {
        background: var(--primary-light);
        transform: translateX(3px);
    }

    .docente-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .docente-avatar {
        width: 32px;
        height: 32px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }

    .docente-details h6 {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .docente-details p {
        margin: 2px 0 0 0;
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .docente-fecha {
        flex-shrink: 0;
    }

    .fecha-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--success-light);
        color: var(--success);
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .fecha-badge.reincorporacion {
        background: var(--primary-light);
        color: var(--primary);
    }

    .no-docentes {
        text-align: center;
        color: var(--gray-500);
        font-size: 0.8rem;
        padding: 15px;
        background: var(--gray-50);
        border-radius: 6px;
    }

    @media (max-width: 768px) {
        .calendar-grid {
            gap: 4px;
        }

        .calendar-day {
            font-size: 0.875rem;
        }

        .day-number {
            font-size: 0.875rem;
        }

        .docente-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .docente-fecha {
            width: 100%;
        }

        .fecha-badge {
            justify-content: center;
            width: 100%;
        }

        .table-container {
            /*padding: 50px;*/
        }
    }
    </style>
@endsection

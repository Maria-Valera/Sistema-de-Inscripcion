@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <style>
        .horario-container {
            display: flex;
            gap: 20px;
            height: calc(100vh - 200px);
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
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .docente-card:hover {
            background: #e9ecef;
            border-color: var(--primary);
        }

        .docente-card.selected {
            border-color: var(--primary);
            background: #f0f4ff;
        }

        .docente-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
            font-size: 13px;
        }

        .docente-area {
            font-size: 11px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .docente-area i {
            color: var(--primary);
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

        .calendar-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1;
            overflow: hidden;
        }

        #calendar {
            height: 100%;
        }

        .fc-toolbar {
            margin-bottom: 15px !important;
        }

        .fc-day-header {
            background: var(--primary) !important;
            color: white !important;
            font-weight: 600;
        }

        .fc-time-grid .fc-slats .fc-slot {
            height: 40px !important;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 5px;
            font-size: 12px;
        }

        .btn-back {
            background: var(--primary);
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
            background: #28a745;
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
                    </div>
                </div>

                <!-- Calendario -->
                <div class="calendar-wrapper">
                    <div id="calendar"></div>
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
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js"></script>

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
                primaria: [
                    { id: 1, nombre: 'María García', area: 'Ciencias' },
                    { id: 2, nombre: 'Juan Pérez', area: 'Humanidades' },
                    { id: 3, nombre: 'Ana López', area: 'Técnica' },
                    { id: 4, nombre: 'Carlos Ruiz', area: 'Artística' },
                ],
                secundaria: [
                    { id: 5, nombre: 'Laura Martínez', area: 'Ciencias' },
                    { id: 6, nombre: 'Pedro Sánchez', area: 'Humanidades' },
                    { id: 7, nombre: 'Sofía Rodríguez', area: 'Técnica' },
                ],
                bachillerato: [
                    { id: 8, nombre: 'Diego Fernández', area: 'Ciencias' },
                    { id: 9, nombre: 'Elena Gómez', area: 'Humanidades' },
                    { id: 10, nombre: 'Miguel Torres', area: 'Técnica' },
                    { id: 11, nombre: 'Lucía Jiménez', area: 'Artística' },
                ]
            };

            let selectedDocente = null;
            let calendar = null;

            // Continuar a la vista del calendario
            btnContinuar.addEventListener('click', function() {
                const nivel = nivelSelect.value;
                if (!nivel) {
                    alert('Por favor, seleccione un nivel académico');
                    return;
                }

                seccionNivel.style.display = 'none';
                vistaCalendario.style.display = 'flex';

                cargarDocentes(nivel);
                inicializarCalendario();
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
                    <div class="docente-card" data-id="${docente.id}" data-area="${docente.area}">
                        <div class="docente-name">${docente.nombre}</div>
                        <div class="docente-area">
                            <i class="fas fa-book"></i>
                            ${docente.area}
                        </div>
                    </div>
                `).join('');

                // Agregar evento de selección
                document.querySelectorAll('.docente-card').forEach(card => {
                    card.addEventListener('click', function() {
                        document.querySelectorAll('.docente-card').forEach(c => c.classList.remove('selected'));
                        this.classList.add('selected');
                        selectedDocente = {
                            id: this.dataset.id,
                            nombre: this.querySelector('.docente-name').textContent,
                            area: this.dataset.area
                        };
                    });
                });
            }

            // Filtrar docentes por área
            areaFormacion.addEventListener('change', function() {
                const area = this.value.toLowerCase();
                const cards = document.querySelectorAll('.docente-card');
                
                cards.forEach(card => {
                    const cardArea = card.dataset.area.toLowerCase();
                    if (!area || cardArea === area) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Inicializar FullCalendar
            function inicializarCalendario() {
                const calendarEl = document.getElementById('calendar');
                
                calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'es',
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },
                    defaultView: 'agendaWeek',
                    allDaySlot: false,
                    minTime: '07:00:00',
                    maxTime: '18:00:00',
                    slotDuration: '00:30:00',
                    editable: true,
                    selectable: true,
                    selectHelper: true,
                    events: [],
                    select: function(start, end) {
                        if (!selectedDocente) {
                            alert('Por favor, seleccione un docente primero');
                            calendar.unselect();
                            return;
                        }

                        const titulo = prompt('Asignatura:', selectedDocente.area);
                        if (titulo) {
                            calendar.addEvent({
                                title: titulo + ' - ' + selectedDocente.nombre,
                                start: start,
                                end: end,
                                backgroundColor: '#667eea',
                                borderColor: '#667eea'
                            });
                        }
                        calendar.unselect();
                    },
                    eventClick: function(event) {
                        if (confirm('¿Desea eliminar esta asignación?')) {
                            event.remove();
                        }
                    }
                });

                calendar.render();
            }

            // Guardar horario
            document.getElementById('btnGuardarHorario').addEventListener('click', function() {
                if (!calendar) {
                    alert('Primero debe crear el horario');
                    return;
                }

                const events = calendar.getEvents();
                if (events.length === 0) {
                    alert('No hay asignaciones en el horario');
                    return;
                }

                // Aquí se enviarían los datos al servidor
                console.log('Guardando horario...', events);
                alert('Horario guardado exitosamente');
            });
        });
    </script>
@endsection

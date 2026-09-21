@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('title', 'Gestión de Horarios')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Horarios</h1>
                    <p class="title-subtitle">Administración de horarios académicos</p>
                </div>
            </div>
            <a href="{{ route('admin.horario.create') }}" class="btn-create">
                <i class="fas fa-plus"></i>
                <span>Nuevo Horario</span>
            </a>
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

        <!-- Encabezado de Filtros Inteligentes -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h3>Filtros de Consulta</h3>
                        <p>Selecciona los parámetros para visualizar horarios (opcional)</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern">
                <form id="filtroHorarioForm" action="{{ route('admin.horario.index') }}" method="GET" class="filtro-form">
                    <div class="row g-3 justify-content-center">
                        <div class="col-md-3">
                            <label class="form-label-modern text-center">
                                <i class="fas fa-graduation-cap" style="color: var(--primary);"></i>
                                Año / Grado
                            </label>
                            <select class="form-select text-center" id="grado_id" name="grado_id">
                                <option value="">Todos los años</option>
                                @foreach($grados as $grado)
                                    <option value="{{ $grado->id }}" {{ old('grado_id') == $grado->id ? 'selected' : '' }}>
                                        {{ $grado->numero_grado }}° Año
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label-modern text-center">
                                <i class="fas fa-users" style="color: var(--primary);"></i>
                                Sección
                            </label>
                            <select class="form-select text-center" id="seccion_id" name="seccion_id">
                                <option value="">Todas las secciones</option>
                                @foreach($secciones as $seccion)
                                    <option value="{{ $seccion->id }}" {{ old('seccion_id') == $seccion->id ? 'selected' : '' }}>
                                        Sección {{ $seccion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label-modern text-center">
                                <i class="fas fa-book" style="color: var(--primary);"></i>
                                Área de Formación
                            </label>
                            <select class="form-select text-center" id="area_formacion_id" name="area_formacion_id">
                                <option value="">Todas las áreas</option>
                                @foreach($areasFormacion as $area)
                                    <option value="{{ $area->id }}" {{ old('area_formacion_id') == $area->id ? 'selected' : '' }}>
                                        {{ $area->nombre_area_formacion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 d-flex justify-content-center">
                            <button type="submit" class="btn-generate">
                                <i class="fas fa-search"></i>
                                <span>Filtrar</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado de Horarios Generados -->
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h3>Horarios Disponibles</h3>
                        <p>Gestión de horarios académicos</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="horarios-list" id="horariosList">
                    @if($horariosAsignados->count() > 0)
                        @foreach($horariosAsignados as $horario)
                            <div class="horario-item" data-horario-id="{{ $horario['id'] }}">
                                <div class="horario-item-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="horario-item-content">
                                    <h4>{{ $horario['grado'] }}° Año - Sección {{ $horario['seccion'] }}</h4>
                                    <p>{{ $horario['areas'] }} ({{ $horario['total_asignaciones'] }} asignaciones)</p>
                                </div>
                                <div class="horario-item-actions">
                                    <button class="btn-action btn-view" title="Ver" onclick="verHorario({{ $horario['grado_id'] }}, {{ $horario['seccion_id'] }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.horario.create', ['grado_id' => $horario['grado_id'], 'seccion_id' => $horario['seccion_id']]) }}" class="btn-action btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-action btn-pdf" title="Generar PDF" onclick="generarPDF({{ $horario['grado_id'] }}, {{ $horario['seccion_id'] }})">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    <button class="btn-action btn-delete" title="Eliminar" onclick="eliminarHorario({{ $horario['grado_id'] }}, {{ $horario['seccion_id'] }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>No hay horarios asignados. Genera uno nuevo para comenzar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal de ver horario -->
        <div class="modal-ver-horario-overlay" id="modalVerHorario">
            <div class="modal-ver-horario">
                <div class="modal-ver-horario-header">
                    <h3>Ver Horario</h3>
                    <button class="modal-ver-horario-cerrar" onclick="cerrarModalHorario()">×</button>
                </div>
                <div class="modal-ver-horario-body">
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
            </div>
        </div>

        <!-- Overlay de carga para modal -->
        <div id="modalOverlayCarga" class="modal-overlay-carga oculto">
            <div class="spinner"></div>
            <p id="modalOverlayCargaMensaje">Cargando...</p>
        </div>
    </div>

    <style>
        /* Filtros Inteligentes */
        .filtro-form {
            padding: 10px 0;
        }

        .btn-generate {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 500;
            height: 46px;
        }

        .btn-generate:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        /* Listado de Horarios */
        .horarios-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .horario-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: white;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .horario-item:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateX(4px);
        }

        .horario-item-icon {
            width: 48px;
            height: 48px;
            background: #667eea;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .horario-item-content {
            flex: 1;
        }

        .horario-item-content h4 {
            margin: 0 0 4px 0;
            color: #1f2937;
            font-size: 16px;
            font-weight: 600;
        }

        .horario-item-content p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .horario-item-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .btn-view {
            background: #3b82f6;
            color: white;
        }

        .btn-view:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-edit {
            background: #f59e0b;
            color: white;
        }

        .btn-edit:hover {
            background: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-pdf {
            background: #df0c0c;
            color: white;
        }

        .btn-pdf:hover {
            background: #c51f1f;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        /* Modal de ver horario */
        .modal-ver-horario-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .modal-ver-horario-overlay.visible {
            display: flex;
        }

        .modal-ver-horario {
            background: white;
            border-radius: 12px;
            width: 95%;
            max-width: 1400px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }

        .modal-ver-horario-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 12px 12px 0 0;
        }

        .modal-ver-horario-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-ver-horario-cerrar {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: background 0.2s ease;
        }

        .modal-ver-horario-cerrar:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .modal-ver-horario-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

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

        /* Estilos adicionales para la vista de horario en modal (copiados de create.blade.php) */
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
            height: 80px;
            min-width: 140px;
            background: var(--gray-50);
            border: 2px dashed var(--gray-300);
            padding: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .drop-zone .assigned-docente {
            background: #0a1128;
            color: white;
            padding: 0.85rem;
            padding-right: 2rem;
            border-radius: 16px;
            font-size: 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 0.35rem;
            animation: fadeIn 0.3s ease;
            position: relative;
        }

        .drop-zone .assigned-docente .docente-nombre {
            font-weight: 700;
            font-size: 0.8125rem;
            line-height: 1.2;
        }

        .drop-zone .assigned-docente .docente-materia {
            font-size: 0.7rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }

        .drop-zone .assigned-docente .aula-texto {
            background: #f3f4f6;
            color: #374151;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
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

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script>
        function generarPDF(gradoId, seccionId) {
            // Aquí implementar la generación de PDF
            alert('Función de generación de PDF para Grado ID: ' + gradoId + ', Sección ID: ' + seccionId);
        }

        function eliminarHorario(gradoId, seccionId) {
            if (confirm('¿Estás seguro de que deseas eliminar este horario?')) {
                // Aquí implementar la eliminación del horario
                alert('Función de eliminación para Grado ID: ' + gradoId + ', Sección ID: ' + seccionId);
            }
        }

        function verHorario(gradoId, seccionId) {
            // Abrir modal para ver horario
            const modal = document.getElementById('modalVerHorario');
            modal.classList.add('visible');

            // Cargar datos del horario
            cargarHorarioEnModal(gradoId, seccionId);
        }

        function cerrarModalHorario() {
            const modal = document.getElementById('modalVerHorario');
            modal.classList.remove('visible');
        }

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

@endsection

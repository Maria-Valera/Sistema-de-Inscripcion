@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    
    <style>
        .main-container {
            padding: 1.5rem;
            background: var(--gray-50);
            min-height: calc(100vh - 200px);
        }

        nav button {
            padding: 12px 25px;
            cursor: pointer;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 500;
            color: var(--gray-500);
            transition: all 0.3s ease;
            font-size: 14px;
        }

        nav button:hover {
            background: rgba(0,0,0,0.03);
            color: var(--gray-900);
        }

        nav button.active-tab {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: white;
        }

        .tab-content {
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
            border-top: none;
            background: white;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            margin-bottom: 1.5rem;
        }

        .tab-content h2 {
            margin-top: 0;
            margin-bottom: 1.25rem;
            color: var(--gray-900);
            font-size: 1.25rem;
            font-weight: 600;
        }

        /* ===== ESTILOS DE LA TABLA ===== */
        .table-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            margin-top: 1.25rem;
            box-shadow: var(--shadow-md);
        }

        .toolbar-modern {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
            background: var(--gray-50);
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
        }

        .search-box-modern {
            display: flex;
            align-items: center;
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            padding: 0 1rem;
            flex: 1;
            max-width: 350px;
            transition: all 0.3s ease;
        }

        .search-box-modern:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-box-modern input {
            border: none;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            width: 100%;
            outline: none;
            background: transparent;
        }

        .filter-buttons-modern {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-buttons-modern button {
            padding: 0.5rem 1.25rem;
            border: 2px solid var(--gray-200);
            background: white;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--gray-600);
        }

        .filter-buttons-modern button:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
        }

        .filter-buttons-modern button.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .table-wrapper-modern {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        .table-modern {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .table-modern thead {
            background: var(--primary);
            color: white;
        }

        .table-modern thead th {
            padding: 0.875rem 1.125rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr {
            border-bottom: 1px solid var(--gray-100);
            transition: all 0.2s ease;
        }

        .table-modern tbody tr:hover {
            background: var(--gray-50);
        }

        .table-modern tbody td {
            padding: 0.875rem 1.125rem;
            vertical-align: middle;
        }

        .employee-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .employee-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9375rem;
            color: white;
            flex-shrink: 0;
        }

        .status-badge {
            display: inline-block;
            padding: 0.3125rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.justified {
            background: var(--success-light);
            color: var(--success);
        }

        .status-badge.unjustified {
            background: var(--danger-light);
            color: var(--danger);
        }

        .status-badge.pending {
            background: var(--warning-light);
            color: var(--warning);
        }

        .btn-action {
            padding: 0.375rem 0.875rem;
            border: none;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3125rem;
        }

        .btn-justify {
            background: var(--success);
            color: white;
        }

        .btn-justify:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-unjustify {
            background: var(--danger);
            color: white;
        }

        .btn-unjustify:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .btn-undo {
            background: var(--gray-500);
            color: white;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.9375rem 0.3125rem 0 0.3125rem;
            font-size: 0.8125rem;
            color: var(--gray-500);
            flex-wrap: wrap;
            gap: 0.625rem;
        }

        .legend {
            display: flex;
            gap: 1.125rem;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 4px;
        }

        .legend-dot.justified { background: var(--success); }
        .legend-dot.unjustified { background: var(--danger); }
        .legend-dot.pending { background: var(--warning); }

        /* ===== ESTILOS DEL CALENDARIO ===== */
        .calendar-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5625rem;
            box-shadow: var(--shadow-md);
        }

        .calendar-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5625rem;
        }

        .calendar-search {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            max-width: 500px;
        }

        .calendar-search input {
            flex: 1;
            padding: 0.625rem 0.9375rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 0.875rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .calendar-search input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .calendar-search button {
            padding: 0.625rem 1.25rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .calendar-search button:hover {
            background: var(--primary-dark);
        }

        .calendar-views {
            display: flex;
            gap: 0.3125rem;
        }

        .calendar-views button {
            padding: 0.5rem 1rem;
            border: 2px solid var(--gray-200);
            background: white;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 0.8125rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .calendar-views button.active-view {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .teacher-list {
            margin-top: 1.5625rem;
            background: var(--gray-50);
            border-radius: var(--radius);
            padding: 1.25rem;
        }

        .teacher-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.625rem 0.9375rem;
            background: white;
            border-radius: var(--radius);
            margin-bottom: 0.5rem;
            cursor: pointer;
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }

        .teacher-list-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow);
        }

        .teacher-list-item .teacher-name {
            font-weight: 500;
            color: var(--gray-900);
        }

        .teacher-list-item .teacher-date {
            font-size: 0.8125rem;
            color: var(--gray-500);
        }

        .notifications-card {
            margin-top: 1.5625rem;
            padding: 0.9375rem;
            background: var(--gray-50);
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        /* ===== ESTILOS DEL MODAL DE OBSERVACIÓN ===== */
        .observation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .observation-modal.active {
            display: flex;
        }

        .observation-modal-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-xl);
        }

        .observation-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .observation-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--gray-900);
            font-weight: 600;
        }

        .observation-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-400);
            transition: all 0.3s ease;
        }

        .observation-modal-close:hover {
            color: var(--danger);
        }

        .observation-form-group {
            margin-bottom: 1.5rem;
        }

        .observation-form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .observation-form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
            outline: none;
            transition: all 0.3s ease;
        }

        .observation-form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .observation-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .btn-modal-cancel {
            padding: 0.625rem 1.25rem;
            border: 2px solid var(--gray-200);
            background: white;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--gray-600);
        }

        .btn-modal-cancel:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
        }

        .btn-modal-confirm {
            padding: 0.625rem 1.25rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-modal-confirm:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 768px) {
            .toolbar-modern {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box-modern {
                max-width: 100%;
            }
            .calendar-toolbar {
                flex-direction: column;
            }
            .calendar-search {
                max-width: 100%;
            }
        }
    </style>
@stop

@section('title', 'Gestión de Inasistencias')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h1 class="title-main">Justificación de Inasistencias</h1>
                    <p class="title-subtitle">Gestión diaria de inasistencias docentes</p>
                </div>
            </div>
            <div class="quick-actions">
                <button class="quick-action-btn">
                    <a href="{{ route('admin.inasistencia.index') }}">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver</span>
                    </a>
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <h3>Inasistencias de Hoy</h3>
                        <p>Gestión y justificación de inasistencias diarias</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern">
                <!-- Toolbar -->
                <div class="toolbar-modern">
                    <div class="search-box-modern">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar por nombre..." onkeyup="filtrarTabla()" />
                    </div>
                    <div class="filter-buttons-modern">
                        <button class="active" data-filter="all" onclick="filtrarPorEstado('all', this)">Todos</button>
                        <button data-filter="justified" onclick="filtrarPorEstado('justified', this)">Justificadas</button>
                        <button data-filter="unjustified" onclick="filtrarPorEstado('unjustified', this)">Injustificadas</button>
                        <button data-filter="pending" onclick="filtrarPorEstado('pending', this)">Pendientes</button>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="table-wrapper-modern">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Personal</th>
                                <th>Hora Llegada</th>
                                <th>Hora Falta</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div>Mostrando <span id="visibleCount">0</span> de <span id="totalCount">0</span></div>
                    <div class="legend">
                        <span class="legend-item"><span class="legend-dot justified"></span> Justificada</span>
                        <span class="legend-item"><span class="legend-dot unjustified"></span> Injustificada</span>
                        <span class="legend-item"><span class="legend-dot pending"></span> Pendiente</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="notifications-card" id="notificationsContainer"></div>
    </div>

    <!-- Modal de Observación -->
    <div class="observation-modal" id="observationModal">
        <div class="observation-modal-content">
            <div class="observation-modal-header">
                <h3 id="observationModalTitle">Agregar Observación</h3>
                <button class="observation-modal-close" onclick="cerrarModalObservacion()">&times;</button>
            </div>
            <div class="observation-form-group">
                <label for="observationText">Observación:</label>
                <textarea id="observationText" placeholder="Ingrese la observación para esta inasistencia..."></textarea>
            </div>
            <div class="observation-modal-footer">
                <button class="btn-modal-cancel" onclick="cerrarModalObservacion()">Cancelar</button>
                <button class="btn-modal-confirm" onclick="confirmarCambioEstado()">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script>
        // ============================================
        // DATOS
        // ============================================
        
        // Inasistencias para la tabla
        let inasistencias = [
            { id: 1, firstName: 'María', lastName: 'González', arrival: '08:15', absence: '09:30', status: 'pending', observation: '' },
            { id: 2, firstName: 'Carlos', lastName: 'Pérez', arrival: '08:45', absence: '10:00', status: 'justified', observation: 'Enfermedad' },
            { id: 3, firstName: 'Ana', lastName: 'Martínez', arrival: '09:10', absence: '11:20', status: 'unjustified', observation: 'Sin justificación' },
            { id: 4, firstName: 'Luis', lastName: 'Fernández', arrival: '07:55', absence: '08:30', status: 'pending', observation: '' },
            { id: 5, firstName: 'Laura', lastName: 'Rodríguez', arrival: '08:30', absence: '09:45', status: 'justified', observation: 'Cita médica' }
        ];

        // ============================================
        // VARIABLES GLOBALES
        // ============================================
        let currentFilter = 'all';
        let pendingItemId = null;
        let pendingStatus = null;

        // ============================================
        // FUNCIONES DE LA TABLA
        // ============================================
        
        function renderizarTabla() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = inasistencias.filter(item => {
                const matchFilter = currentFilter === 'all' || item.status === currentFilter;
                const fullName = `${item.firstName} ${item.lastName}`.toLowerCase();
                const matchSearch = fullName.includes(searchTerm);
                return matchFilter && matchSearch;
            });

            document.getElementById('visibleCount').textContent = filtered.length;
            document.getElementById('totalCount').textContent = inasistencias.length;

            const tbody = document.getElementById('tableBody');
            
            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray-400);">No hay registros</td></tr>`;
                return;
            }

            tbody.innerHTML = filtered.map(item => {
                const initials = (item.firstName[0] + item.lastName[0]).toUpperCase();
                const colors = ['#3498db', '#2ecc71', '#e67e22', '#9b59b6', '#1abc9c'];
                const color = colors[(item.id - 1) % colors.length];
                
                const statusLabels = {
                    justified: '<span class="status-badge justified">✓ Justificada</span>',
                    unjustified: '<span class="status-badge unjustified">✗ Injustificada</span>',
                    pending: '<span class="status-badge pending">⏳ Pendiente</span>'
                };

                let actions = '';
                if (item.status === 'pending') {
                    actions = `
                        <button class="btn-action btn-justify" onclick="abrirModalObservacion(${item.id}, 'justified')">✓ Justificar</button>
                        <button class="btn-action btn-unjustify" onclick="abrirModalObservacion(${item.id}, 'unjustified')">✗ Injustificar</button>
                    `;
                } else {
                    actions = `<button class="btn-action btn-undo" onclick="cambiarEstado(${item.id}, 'pending')">↩ Deshacer</button>`;
                }

                return `
                    <tr>
                        <td>
                            <div class="employee-info">
                                <div class="employee-avatar" style="background:${color}">${initials}</div>
                                <div><strong>${item.firstName} ${item.lastName}</strong></div>
                            </div>
                        </td>
                        <td>${item.arrival}</td>
                        <td>${item.absence}</td>
                        <td>${statusLabels[item.status]}</td>
                        <td>${actions}</td>
                    </tr>
                `;
            }).join('');
        }

        function cambiarEstado(id, newStatus) {
            const item = inasistencias.find(i => i.id === id);
            if (item) {
                item.status = newStatus;
                item.observation = '';
                renderizarTabla();
                actualizarNotificaciones();
            }
        }

        function abrirModalObservacion(id, newStatus) {
            pendingItemId = id;
            pendingStatus = newStatus;
            
            const statusText = newStatus === 'justified' ? 'Justificar' : 'Injustificar';
            document.getElementById('observationModalTitle').textContent = `${statusText} Inasistencia`;
            document.getElementById('observationText').value = '';
            document.getElementById('observationModal').classList.add('active');
        }

        function cerrarModalObservacion() {
            document.getElementById('observationModal').classList.remove('active');
            pendingItemId = null;
            pendingStatus = null;
            document.getElementById('observationText').value = '';
        }

        function confirmarCambioEstado() {
            if (pendingItemId && pendingStatus) {
                const item = inasistencias.find(i => i.id === pendingItemId);
                if (item) {
                    item.status = pendingStatus;
                    item.observation = document.getElementById('observationText').value;
                    renderizarTabla();
                    actualizarNotificaciones();
                }
            }
            cerrarModalObservacion();
        }

        function filtrarPorEstado(filter, btn) {
            currentFilter = filter;
            document.querySelectorAll('.filter-buttons-modern button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderizarTabla();
        }

        function filtrarTabla() {
            renderizarTabla();
        }

       

        // ============================================
        // NOTIFICACIONES
        // ============================================
        
        function actualizarNotificaciones() {
            const container = document.getElementById('notificationsContainer');
            const pendientes = inasistencias.filter(i => i.status === 'pending').length;
            
            if (pendientes > 0) {
                container.innerHTML = `
                    <div style="background:var(--warning-light);padding:0.9375rem;border-radius:var(--radius);border-left:4px solid var(--warning);display:flex;align-items:center;gap:0.75rem;">
                        <i class="fas fa-exclamation-triangle" style="color:var(--warning);font-size:1.25rem;"></i>
                        <div>
                            <strong style="color:var(--warning);">${pendientes} inasistencias pendientes</strong> por justificar
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div style="background:var(--success-light);padding:0.9375rem;border-radius:var(--radius);border-left:4px solid var(--success);display:flex;align-items:center;gap:0.75rem;">
                        <i class="fas fa-check-circle" style="color:var(--success);font-size:1.25rem;"></i>
                        <div>
                            <strong style="color:var(--success);">No hay inasistencias pendientes</strong>
                        </div>
                    </div>
                `;
            }
        }


        // ============================================
        // INICIALIZACIÓN
        // ============================================
        
        document.addEventListener('DOMContentLoaded', function() {
            renderizarTabla();
            actualizarNotificaciones();
        });
    </script>
@stop
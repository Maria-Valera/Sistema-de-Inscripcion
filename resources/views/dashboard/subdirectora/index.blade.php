@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        .dashboard-container {
            padding: 1.5rem;
            background: var(--gray-50);
            min-height: calc(100vh - 200px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--primary-dark));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .stat-card.primary::before {
            background: linear-gradient(180deg, var(--primary), var(--primary-dark));
        }

        .stat-card.success::before {
            background: linear-gradient(180deg, var(--success), #059669);
        }

        .stat-card.warning::before {
            background: linear-gradient(180deg, var(--warning), #d97706);
        }

        .stat-card.info::before {
            background: linear-gradient(180deg, var(--info), #2563eb);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .stat-icon.success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .stat-icon.warning {
            background: linear-gradient(135deg, var(--warning), #d97706);
        }

        .stat-icon.info {
            background: linear-gradient(135deg, var(--info), #2563eb);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-trend {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .stat-trend.up {
            background: var(--success-light);
            color: var(--success);
        }

        .stat-trend.down {
            background: var(--danger-light);
            color: var(--danger);
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.25rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
            color: inherit;
        }

        .action-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin: 0 auto 0.75rem;
        }

        .action-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
        }

        .activity-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-left: 3px solid var(--gray-200);
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }

        .activity-item:hover {
            background: var(--gray-50);
            border-left-color: var(--primary);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1.25rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: 20%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        .welcome-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .welcome-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .anio-escolar-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-xl);
            text-align: center;
        }

        .anio-escolar-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .anio-escolar-card p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .notifications-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            max-height: 300px;
            display: flex;
            flex-direction: column;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--gray-100);
            flex-shrink: 0;
        }

        .notifications-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }

        .notification-badge {
            background: var(--danger);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        #notifications-container {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        #notifications-container::-webkit-scrollbar {
            width: 6px;
        }

        #notifications-container::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 3px;
        }

        #notifications-container::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 3px;
        }

        #notifications-container::-webkit-scrollbar-thumb:hover {
            background: var(--gray-400);
        }

        .notification-item {
            display: flex;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--gray-50);
            border-radius: var(--radius);
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
            align-items: center;
        }

        .notification-item:hover {
            background: var(--gray-100);
            transform: translateX(3px);
        }

        .notification-icon {
            width: 32px;
            height: 32px;
            background: var(--danger-light);
            color: var(--danger);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.875rem;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.15rem;
        }

        .notification-details {
            font-size: 0.75rem;
            color: var(--gray-500);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notification-time {
            font-size: 0.7rem;
            color: var(--gray-400);
            margin-top: 0.15rem;
        }

        .notification-action {
            flex-shrink: 0;
        }

        .btn-generate-inasistencia {
            background: var(--danger);
            color: white;
            border: none;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-generate-inasistencia:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        .access-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .access-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .access-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .access-card.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .access-card.disabled:hover {
            transform: none;
            box-shadow: var(--shadow-md);
        }

        .access-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .access-icon.docente {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .access-icon.inasistencia {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
        }

        .access-icon.horario {
            background: linear-gradient(135deg, var(--warning), #d97706);
            color: white;
        }

        .access-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .access-description {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        @media (max-width: 768px) {

            .stats-grid,
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .access-grid {
                grid-template-columns: 1fr;
            }

            .welcome-title {
                font-size: 1.5rem;
            }
        }
    </style>
@stop

@section('title', 'Dashboard - Sistema de Inscripción')

@section('content_header')
@stop

@section('content')
    <div class="dashboard-container">
        <div class="welcome-banner">
            <div class="welcome-content">
                <h1 class="welcome-title">
                    ¡Bienvenido, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="welcome-subtitle">
                    Sistema de Gestión Escolar - {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                </p>
                <h3>Año Escolar Activo</h3>
                <p>{{ $anioEscolarActivo ?? 'No definido' }}</p>
            </div>
        </div>

        <!-- Contenedor de Año Escolar -->


        <!-- Notificaciones de Inasistencias -->
        <div class="notifications-card">
            <div class="notifications-header">
                <h3>Notificaciones de Inasistencias</h3>
                <span class="notification-badge" id="notification-count">0</span>
            </div>
            <div id="notifications-container">
                <!-- Las notificaciones se cargarán con JavaScript -->
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="access-grid">
            <a href="{{ url('admin/docente') }}" class="access-card">
                <div class="access-icon docente">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h4 class="access-title">Listado de Docentes</h4>
                <p class="access-description">Ver y gestionar el personal docente</p>
            </a>

            <div class="access-card">
                <a href="{{ route('admin.inasistencia.index') }}" class="access-card">
                <div class="access-icon inasistencia">
                    <i class="fas fa-user-times"></i>
                </div>
                <h4 class="access-title">Generar Inasistencias</h4>
                <p class="access-description">Registrar inasistencias de alumnos</p>
                </a>
            </div>

            <a href="{{ route('admin.horario.index') }}" class="access-card">
                <div class="access-icon horario">
                    <i class="fas fa-clock"></i>
                </div>
                <h4 class="access-title">Módulo Horario</h4>
                <p class="access-description">Gestionar horarios</p>
            </a>
        </div>

    </div>
@stop

@section('js')
    <script>
        console.log("Dashboard cargado correctamente");
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Datos de prueba para notificaciones de inasistencias de docentes
        const notificationsData = [
            {
                id: 1,
                title: 'Llegada tarde - Docente Carlos Méndez',
                details: 'Llegada 8:15 AM - Falta (7:00 AM - 8:30 AM) - Matemáticas - 5to Grado A',
                time: 'Hace 2 horas'
            },
            {
                id: 2,
                title: 'Inasistencia - Docente Ana Rodríguez',
                details: 'Falta (9:00 AM - 10:30 AM) - Lenguaje - 4to Grado B',
                time: 'Hace 4 horas'
            },
            {
                id: 3,
                title: 'Llegada tarde - Docente Luis García',
                details: 'Llegada 10:30 AM - Falta (10:30 AM - 12:00 PM) - Ciencias - 5to Grado A',
                time: 'Hace 1 día'
            },
            {
                id: 4,
                title: 'Inasistencia - Docente María López',
                details: 'Falta (12:00 PM - 1:30 PM) - Historia - 3er Grado C',
                time: 'Hace 2 días'
            },
            {
                id: 5,
                title: 'Llegada tarde - Docente José Martínez',
                details: 'Llegada 2:20 PM - Falta (1:30 PM - 3:00 PM) - Inglés - 2do Grado A',
                time: 'Hace 3 días'
            },
            {
                id: 6,
                title: 'Inasistencia - Docente Pedro Sánchez',
                details: 'Falta (8:30 AM - 9:00 AM) - Geografía - 1er Grado B',
                time: 'Hace 4 días'
            },
            {
                id: 7,
                title: 'Llegada tarde - Docente Laura Torres',
                details: 'Llegada 9:45 AM - Falta (9:00 AM - 10:30 AM) - Educación Física - 5to Grado C',
                time: 'Hace 5 días'
            }
        ];

        // Función para cargar notificaciones
        function loadNotifications() {
            const container = document.getElementById('notifications-container');
            const countBadge = document.getElementById('notification-count');
            
            // Actualizar contador
            countBadge.textContent = notificationsData.length;
            
            // Generar HTML para cada notificación
            container.innerHTML = notificationsData.map(notification => `
                <div class="notification-item">
                    <div class="notification-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-details">${notification.details}</div>
                        <div class="notification-time">${notification.time}</div>
                    </div>
                    <div class="notification-action">
                        <button class="btn-generate-inasistencia" onclick="generateInasistencia('${notification.details}')">
                            Generar
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Función para generar inasistencia
        function generateInasistencia(details) {
            alert('Generando inasistencia para: ' + details);
        }

        // Cargar notificaciones cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
        });
    </script>
@stop

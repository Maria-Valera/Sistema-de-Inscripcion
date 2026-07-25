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

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-number.small {
            font-size: 1.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* ===== Card modern (mismo lenguaje visual que el dashboard admin) ===== */
        .card-modern {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header-modern {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--gray-100, #f1f5f9);
        }

        .card-header-modern .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .card-header-modern .header-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .card-header-modern h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }

        .card-header-modern p {
            font-size: 0.85rem;
            color: var(--gray-500);
            margin: 0.15rem 0 0;
        }

        .card-body-modern {
            padding: 1.5rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-card {
            background: white;
            border: 1px solid var(--gray-100, #f1f5f9);
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
            border-color: var(--primary-light);
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

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
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
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .welcome-title {
                font-size: 1.5rem;
            }
        }
    </style>
@stop

@section('title', 'Dashboard Docente - Sistema de Inscripción')

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
                    Panel del Docente - {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                </p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-number">{{ $totalGrados }}</div>
                        <div class="stat-label">Grados Activos</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-school"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-number">{{ $totalAreasFormacion }}</div>
                        <div class="stat-label">Áreas de Formación</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-number small">{{ $anioEscolarActivo }}</div>
                        <div class="stat-label">Año Escolar Activo</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        @php
            // TEMPORAL: mientras no tengas un usuario docente real para probar,
            // usamos un id de prueba para que la tarjeta siempre se vea.
            // Cuando tengas un usuario docente real, esto vuelve a calcularse solo.
            $docenteId = Auth::user()->persona->docente->id ?? 1;
        @endphp

        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <h3>Accesos Rápidos</h3>
                        <p>Funciones principales</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="quick-actions">


                    <a href="{{ route('admin.docente.reportePDF', $docenteId) }}" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <p class="action-title">Ficha de Docente</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        console.log("Dashboard Docente cargado correctamente");
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop

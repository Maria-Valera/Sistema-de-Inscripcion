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

            .stats-grid,
            .charts-grid {
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
            </div>
        </div>

    </div>
@stop

@section('js')
    <script>
        console.log("Dashboard cargado correctamente");
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop

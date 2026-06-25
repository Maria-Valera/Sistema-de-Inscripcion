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

        <!-- Sección de Acciones Rápidas -->
        <div class="quick-actions-section">
            <div class="quick-actions-grid">
                <button class="quick-action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Justificación de Inasistencia</span>
                </button>
                <button class="quick-action-btn">
                    <i class="fas fa-file-alt"></i>
                    <span>Generar Acta</span>
                </button>
                <button class="quick-action-btn">
                    <i class="fas fa-search"></i>
                    <span>Buscar Docente</span>
                </button>
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

        .quick-actions-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
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
    </style>
@endsection

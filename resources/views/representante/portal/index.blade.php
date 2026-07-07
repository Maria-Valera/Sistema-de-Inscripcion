@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        .portal-container {
            padding: 1.5rem;
            background: var(--gray-50);
            min-height: calc(100vh - 200px);
        }

        .portal-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .portal-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .portal-content {
            position: relative;
            z-index: 1;
        }

        .portal-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .portal-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .feature-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 2px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-light);
            text-decoration: none;
            color: inherit;
        }

        .feature-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .feature-icon-wrapper.prosecucion {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .feature-icon-wrapper.carnet {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
        }

        .feature-desc {
            font-size: 0.95rem;
            color: var(--gray-500);
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .feature-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .feature-btn.prosecucion {
            background: var(--primary);
            color: white;
        }

        .feature-btn.prosecucion:hover {
            background: var(--primary-dark);
        }

        .feature-btn.carnet {
            background: var(--success);
            color: white;
        }

        .feature-btn.carnet:hover {
            background: #059669;
        }
    </style>
@stop

@section('title', 'Portal del Representante')

@section('content_header')
    <div class="content-header-modern" style="padding: 1rem 2rem 0; margin-bottom: -1rem; background: transparent; box-shadow: none;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0; font-size: 0.85rem;">
                <li class="breadcrumb-item active" aria-current="page" style="color: var(--gray-700); font-weight: 700;">
                    <i class="fas fa-home me-1"></i> Inicio
                </li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
<div class="portal-container">
    <div class="portal-header">
        <div class="portal-content">
            <h1 class="portal-title">¡Bienvenido al Portal del Representante! 👋</h1>
            <p class="portal-subtitle">Gestión simplificada para la inscripción y credenciales de sus representados</p>
        </div>
    </div>

    <div class="features-grid">
        <!-- Tarjeta Inscripción por Prosecución -->
        <a href="{{ route('portal-representante.prosecucion.index') }}" class="feature-card">
            <div class="feature-icon-wrapper prosecucion">
                <i class="fas fa-sync-alt"></i>
            </div>
            <h3 class="feature-title">Inscripción por Prosecución</h3>
            <p class="feature-desc">
                Realice el proceso de inscripción y reinscripción para el año escolar activo de sus representados que ya forman parte de la institución.
            </p>
            <span class="feature-btn prosecucion">
                Acceder <i class="fas fa-arrow-right"></i>
            </span>
        </a>

        <!-- Tarjeta Carnet Estudiantil -->
        <a href="{{ route('portal-representante.carnet.index') }}" class="feature-card">
            <div class="feature-icon-wrapper carnet">
                <i class="fas fa-id-card"></i>
            </div>
            <h3 class="feature-title">Carnet Estudiantil</h3>
            <p class="feature-desc">
                Consulte, visualice y descargue el carnet de identificación escolar oficial para cada uno de sus hijos o representados.
            </p>
            <span class="feature-btn carnet">
                Acceder <i class="fas fa-arrow-right"></i>
            </span>
        </a>
    </div>
</div>
@stop

@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        .carnet-dashboard {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
            align-items: start;
        }

        .student-selector-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .student-option {
            padding: 1rem;
            border-radius: var(--radius);
            border: 2px solid var(--gray-200);
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .student-option:hover {
            border-color: var(--primary-light);
            background: var(--gray-50);
        }

        .student-option.active {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .student-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .student-info {
            flex-grow: 1;
        }

        .student-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.2rem;
        }

        .student-meta {
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        .carnet-preview-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .carnet-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 2.5rem;
            justify-content: center;
        }

        /* DISEÑO DE TARJETA CARNET */
        .badge-card {
            width: 320px;
            height: 480px;
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            background: white;
            overflow: hidden;
            border: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            position: relative;
            font-family: 'Nunito', sans-serif;
        }

        .badge-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1.25rem 1rem;
            text-align: center;
            position: relative;
        }

        .badge-header h4 {
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
        }

        .badge-header p {
            font-size: 0.65rem;
            margin: 0.25rem 0 0;
            opacity: 0.8;
            font-weight: 600;
        }

        .badge-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
            position: relative;
        }

        .badge-photo-wrapper {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid var(--primary-light);
            overflow: hidden;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            box-shadow: var(--shadow-md);
        }

        .badge-photo-placeholder {
            font-size: 3.5rem;
            color: var(--gray-300);
        }

        .badge-student-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--gray-900);
            text-align: center;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .badge-student-id {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary);
            background: var(--primary-light);
            padding: 0.25rem 1rem;
            border-radius: 20px;
            margin-bottom: 1.25rem;
        }

        .badge-info-grid {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
            border-top: 1px solid var(--gray-200);
            padding-top: 1rem;
        }

        .badge-info-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
        }

        .badge-info-label {
            color: var(--gray-500);
            font-weight: 600;
        }

        .badge-info-value {
            color: var(--gray-900);
            font-weight: 700;
        }

        .badge-footer {
            height: 12px;
            background: linear-gradient(90deg, var(--primary), var(--pink));
        }

        /* REVERSO DEL CARNET */
        .badge-back-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 2rem 1.5rem;
            height: 100%;
            text-align: center;
        }

        .badge-back-rules {
            font-size: 0.7rem;
            color: var(--gray-500);
            line-height: 1.4;
            border-bottom: 1px solid var(--gray-200);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .badge-qr-mock {
            width: 90px;
            height: 90px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cpath d='M0 0h30v10H10v20H0V0zm70 0h30v30H90V10H70V0zM0 70h10v20h20v10H0V70zm90 20H70v10h30V70H90v20zM30 30h40v40H30V30zm10 10v20h20V40H40z' fill='%23111827'/%3E%3C/svg%3E") no-repeat center;
            background-size: contain;
            margin-bottom: 1rem;
        }

        .badge-barcode-mock {
            width: 180px;
            height: 40px;
            background: linear-gradient(90deg, 
                #000 0%, #000 3%, transparent 3%, transparent 5%, 
                #000 5%, #000 12%, transparent 12%, transparent 14%, 
                #000 14%, #000 17%, transparent 17%, transparent 22%, 
                #000 22%, #000 24%, transparent 24%, transparent 28%,
                #000 28%, #000 35%, transparent 35%, transparent 37%,
                #000 37%, #000 40%, transparent 40%, transparent 45%,
                #000 45%, #000 52%, transparent 52%, transparent 55%,
                #000 55%, #000 58%, transparent 58%, transparent 63%,
                #000 63%, #000 70%, transparent 70%, transparent 72%,
                #000 72%, #000 75%, transparent 75%, transparent 80%,
                #000 80%, #000 85%, transparent 85%, transparent 90%,
                #000 90%, #000 100%);
            margin-bottom: 1.5rem;
        }

        .badge-signature-wrapper {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .badge-signature-line {
            width: 150px;
            border-top: 1px solid var(--gray-500);
            margin-bottom: 0.25rem;
        }

        .badge-signature-text {
            font-size: 0.65rem;
            color: var(--gray-500);
            font-weight: 700;
            text-transform: uppercase;
        }

        @media (max-width: 991px) {
            .carnet-dashboard {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('title', 'Carnet Estudiantil')

@section('content_header')
    <div class="content-header-modern">
        {{-- Breadcrumbs --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0; font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" style="color: var(--primary); text-decoration: none;"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('portal-representante.index') }}" style="color: var(--primary); text-decoration: none;">Portal del Representante</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="color: var(--gray-700); font-weight: 700;">Mi Carnet Estudiantil</li>
            </ol>
        </nav>

        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper" style="background: linear-gradient(135deg, var(--success), #059669)">
                    <i class="fas fa-id-card"></i>
                </div>
                <div>
                    <h1 class="title-main">Carnet Estudiantil</h1>
                    <p class="title-subtitle">Consulte y genere la credencial escolar de su representado</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal-representante.index') }}" class="btn-create" style="background: var(--gray-500);">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Menú</span>
                </a>
                <a href="#" id="btn-imprimir-carnet" target="_blank" class="btn-create" style="background: var(--success);">
                    <i class="fas fa-print"></i>
                    <span>Imprimir Carnet</span>
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="main-container">
    <div class="carnet-dashboard">
        <!-- Columna Selector -->
        <div class="student-selector-card">
            <h4 class="mb-3 text-dark font-weight-bold" style="font-size: 1.1rem;"><i class="fas fa-users text-primary me-2"></i> Representados</h4>
            <p class="text-muted mb-4" style="font-size: 0.85rem;">Seleccione un representado de la lista para visualizar su carnet escolar.</p>
            
            <div class="students-list">
                @foreach ($representados as $index => $rep)
                    <div class="student-option {{ $index == 0 ? 'active' : '' }}" 
                         data-id="{{ $rep['id'] }}"
                         data-nombre="{{ $rep['nombre'] }}"
                         data-cedula="{{ $rep['cedula'] }}"
                         data-grado="{{ $rep['grado'] }}"
                         data-seccion="{{ $rep['seccion'] }}"
                         data-anio="{{ $rep['anio'] }}"
                         onclick="seleccionarRepresentado(this)">
                        <div class="student-avatar">
                            {{ substr($rep['nombre'], 0, 1) }}
                        </div>
                        <div class="student-info">
                            <div class="student-name">{{ $rep['nombre'] }}</div>
                            <div class="student-meta">{{ $rep['cedula'] }} • {{ $rep['grado'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Columna Visualización -->
        <div class="carnet-preview-container">
            <h4 class="text-dark font-weight-bold mb-4" style="font-size: 1.1rem; border-bottom: 2px solid var(--primary-light); padding-bottom: 0.5rem; width: 100%;">
                <i class="fas fa-eye text-primary me-2"></i> Vista Previa Digital
            </h4>
            
            <div class="carnet-cards">
                <!-- ANVERSO -->
                <div class="badge-card">
                    <div class="badge-header">
                        <h4>República Bolivariana de Venezuela</h4>
                        <p>U.E. Colegio Bolivariano de Gestión Escolar</p>
                    </div>
                    <div class="badge-body">
                        <div class="badge-photo-wrapper">
                            <!-- Foto simulada con silueta -->
                            <div class="badge-photo-placeholder">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <h3 class="badge-student-name" id="card-nombre">José Gregorio Hernández</h3>
                        <span class="badge-student-id" id="card-cedula">V-25.111.222</span>
                        
                        <div class="badge-info-grid">
                            <div class="badge-info-item">
                                <span class="badge-info-label">Nivel / Año</span>
                                <span class="badge-info-value" id="card-grado">4to Año</span>
                            </div>
                            <div class="badge-info-item">
                                <span class="badge-info-label">Sección</span>
                                <span class="badge-info-value" id="card-seccion">Sección A</span>
                            </div>
                            <div class="badge-info-item">
                                <span class="badge-info-label">Año Escolar</span>
                                <span class="badge-info-value" id="card-anio">2025-2026</span>
                            </div>
                        </div>
                    </div>
                    <div class="badge-footer"></div>
                </div>

                <!-- REVERSO -->
                <div class="badge-card">
                    <div class="badge-header">
                        <h4>INFORMACIÓN ADICIONAL</h4>
                        <p>Condiciones y Datos de Emergencia</p>
                    </div>
                    <div class="badge-back-body">
                        <div class="badge-back-rules">
                            Este documento es personal e intransferible. Identifica al portador como estudiante regular de la institución. En caso de emergencia, favor notificar a la dirección del plantel o comunicarse con su representante legal.
                        </div>
                        <div class="badge-qr-mock"></div>
                        <div class="badge-barcode-mock"></div>
                        <div class="badge-signature-wrapper">
                            <div class="badge-signature-line"></div>
                            <span class="badge-signature-text">Firma Autorizada</span>
                        </div>
                    </div>
                    <div class="badge-footer"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    function seleccionarRepresentado(element) {
        // Remover clase activo de todos
        document.querySelectorAll('.student-option').forEach(opt => opt.classList.remove('active'));
        
        // Agregar clase activo al seleccionado
        element.classList.add('active');
        
        // Obtener datos
        const nombre = element.getAttribute('data-nombre');
        const cedula = element.getAttribute('data-cedula');
        const grado = element.getAttribute('data-grado');
        const seccion = element.getAttribute('data-seccion');
        const anio = element.getAttribute('data-anio');
        
        // Actualizar vista previa
        document.getElementById('card-nombre').textContent = nombre;
        document.getElementById('card-cedula').textContent = cedula;
        document.getElementById('card-grado').textContent = grado;
        document.getElementById('card-seccion').textContent = seccion;
        document.getElementById('card-anio').textContent = anio;
        
        // Actualizar botón imprimir
        actualizarBotonImprimir(nombre, cedula, grado, seccion, anio);
    }

    function actualizarBotonImprimir(nombre, cedula, grado, seccion, anio) {
        const btn = document.getElementById('btn-imprimir-carnet');
        const baseUrl = "{{ route('portal-representante.carnet.imprimir') }}";
        
        // Generar URL con parámetros
        const queryParams = new URLSearchParams({
            nombre: nombre,
            cedula: cedula,
            grado: grado,
            seccion: seccion,
            anio: anio
        }).toString();
        
        btn.href = `${baseUrl}?${queryParams}`;
    }

    // Inicializar botón con la primera opción de la lista al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const primeraOpcion = document.querySelector('.student-option');
        if (primeraOpcion) {
            seleccionarRepresentado(primeraOpcion);
        }
    });
</script>
@stop

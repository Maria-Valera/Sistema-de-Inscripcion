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
                                    <button class="btn-action btn-view" title="Ver" data-bs-toggle="modal" data-bs-target="#viewModalHorario" onclick="cargarHorarioEnModal({{ $horario['grado_id'] }}, {{ $horario['seccion_id'] }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.horario.create', ['grado_id' => $horario['grado_id'], 'seccion_id' => $horario['seccion_id']]) }}" class="btn-action btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-action btn-pdf" title="Generar PDF" onclick="generarPDF({{ $horario['grado_id'] }}, {{ $horario['seccion_id'] }})">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    <button class="btn-action btn-delete" title="Eliminar" data-bs-toggle="modal" data-bs-target="#confirmarEliminar{{ $horario['id'] }}">
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

        <!-- Incluir modales -->
        @include('admin.horario.modales.showModal')
        @foreach($horariosAsignados as $horario)
            @include('admin.horario.modales.inactivarModal', ['horario' => $horario])
        @endforeach
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
    </script>

@endsection

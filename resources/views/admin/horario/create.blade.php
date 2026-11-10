@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/horario-styles.css') }}">
    <style>
        /* Estilos específicos adicionales para create.blade.php */
        /* Los estilos principales están en horario-styles.css */

        /* Contenedor principal de calendario y panel lateral */
        #vistaCalendario {
            display: flex;
            flex-wrap: wrap;
        }

        #vistaCalendario > [class*="col-"] {
            min-width: 0; /* Previene que los flex items crezcan más allá de su ancho de columna */
        }

        .main-content {
            width: 100%;
            min-width: 0;
        }

        .sidebar-docentes {
            width: 100% !important;
            max-width: 100%;
            position: sticky;
            top: 1rem;
            max-height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
            border-radius: 0.375rem;
            overflow: hidden;
            background: #ffffff;
        }

        .sidebar-docentes .docentes-list {
            flex: 1;
            overflow-y: auto;
            max-height: calc(100vh - 190px);
            padding: 0.75rem;
        }

        /* Simetría estricta de la cuadrícula de horarios */
        .horario-matrix-wrapper {
            overflow-x: auto;
            width: 100%;
            max-width: 100%;
        }

        .horario-matrix {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            margin-bottom: 0 !important;
        }

        .horario-matrix thead th.time-header,
        .horario-matrix tbody td.time-cell {
            width: 85px !important;
            min-width: 80px !important;
            max-width: 90px !important;
            box-sizing: border-box !important;
            text-align: center;
            font-size: 0.8rem;
            padding: 0.35rem 0.2rem !important;
        }

        .horario-matrix thead th:not(.time-header),
        .horario-matrix tbody td.drop-zone {
            width: calc((100% - 85px) / 5) !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
        }

        .drop-zone {
            min-height: 95px !important;
            height: 105px !important;
            padding: 0.35rem !important;
            vertical-align: top !important;
            box-sizing: border-box !important;
        }

        .drop-zone-inner {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            gap: 0.35rem !important;
            align-content: flex-start !important;
            justify-content: flex-start !important;
            height: 100% !important;
            max-height: 96px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-width: thin;
            padding-right: 2px;
        }

        /* Píldoras compactas alineadas a la izquierda sin estirarse a 100% */
        .drop-zone .assigned-docente {
            flex: 0 0 auto !important;
            width: auto !important;
            min-width: 80px;
            max-width: 100% !important;
            box-sizing: border-box;
            align-self: flex-start !important;
            padding: 0.3rem 0.5rem !important;
            margin: 0 !important;
            background: rgba(79, 70, 229, 0.15) !important; /* Púrpura/índigo translúcido suave coordinado */
            color: #1e1b4b !important;                       /* Texto oscuro para contraste */
            border: 1px solid rgba(79, 70, 229, 0.3) !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 1px 3px rgba(79, 70, 229, 0.08) !important;
            transition: background-color 0.15s ease, border-color 0.15s ease !important;
        }

        .drop-zone .assigned-docente:hover {
            background: rgba(79, 70, 229, 0.24) !important;
            border-color: rgba(79, 70, 229, 0.45) !important;
        }

        .drop-zone .assigned-docente .adm-mini {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.35rem;
            padding: 0;
            font-size: 0.72rem;
            white-space: nowrap;
            overflow: hidden;
            width: 100%;
        }

        .drop-zone .assigned-docente .adm-mini .adm-mini-nombre {
            font-weight: 600;
            color: #1e1b4b !important;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 85px;
            white-space: nowrap;
        }

        .drop-zone .assigned-docente .adm-mini .adm-mini-seccion {
            color: #3730a3 !important;
            opacity: 0.95;
            flex-shrink: 0;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }

        .drop-zone .assigned-docente .adm-mini .btn-lock-toggle.text-warning {
            color: #b45309 !important;
        }

        .drop-zone .assigned-docente .adm-mini .btn-lock-toggle.text-muted {
            color: #4338ca !important;
        }

        .drop-zone .assigned-docente .adm-full {
            background: rgba(79, 70, 229, 0.16) !important; /* Mismo color translúcido que los bloques */
            backdrop-filter: blur(8px) !important;           /* Efecto cristal elegante */
            -webkit-backdrop-filter: blur(8px) !important;
            color: #1e1b4b !important;
            border: 1px solid rgba(79, 70, 229, 0.35) !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
        }

        .drop-zone .assigned-docente .adm-full .docente-nombre {
            color: #1e1b4b !important;
            font-weight: 700 !important;
        }

        .drop-zone .assigned-docente .adm-full .docente-cedula {
            color: #4338ca !important;
            font-weight: 500 !important;
        }

        .drop-zone .assigned-docente .adm-full .docente-materia {
            color: #3730a3 !important;
            font-weight: 600 !important;
        }

        .drop-zone .assigned-docente .adm-full .pildora-selector {
            width: 100%;
            background: #ffffff !important; /* Selectores blancos para destacar dentro de la tarjeta translúcida */
            color: #1e293b !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 50px;
            padding: 0.35rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            text-align-last: center;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .drop-zone .assigned-docente .adm-full .pildora-selector:focus {
            border-color: #4f46e5 !important;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .drop-zone .assigned-docente .adm-full .pildora-selector:disabled {
            background: #f8fafc !important;
            color: #64748b !important;
            border-color: #e2e8f0 !important;
            opacity: 1 !important;
            cursor: default;
        }

        .drop-zone .assigned-docente .adm-full .btn-eliminar-docente {
            background: #fee2e2 !important;
            color: #dc2626 !important;
            border: 1px solid #fca5a5 !important;
        }

        .drop-zone .assigned-docente .adm-full .btn-eliminar-docente:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            border-color: #dc2626 !important;
        }

        .drop-zone .assigned-docente .adm-full .btn-lock-toggle {
            background: transparent;
            padding: 2px 5px;
            border-radius: 4px;
        }

        .drop-zone .assigned-docente .adm-full .btn-lock-toggle:hover {
            background: rgba(79, 70, 229, 0.15);
        }

        .drop-zone .assigned-docente .adm-full .btn-lock-toggle.text-warning {
            color: #b45309 !important;
        }

        .drop-zone .assigned-docente .adm-full .btn-lock-toggle.text-muted {
            color: #4338ca !important;
        }

        .drop-zone .assigned-docente .adm-full .btn-lock-toggle.text-muted:hover {
            color: #1e1b4b !important;
        }

        .drop-zone .assigned-docente .pildora-selector {
            width: 100%;
            background: white;
            color: #212529;
            border: none;
            border-radius: 50px;
            padding: 0.25rem 0.5625rem;
            font-size: 0.7rem;
            font-weight: 500;
            text-align: center;
            text-align-last: center;
            cursor: pointer;
        }

        .drop-zone .assigned-docente .pildora-selector:disabled {
            opacity: 0.85;
            cursor: default;
        }

        /* Estilos de Diagnóstico de Dificultad (M7 / P8) */
        .diagnostico-cuellos-botella {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-left: 4px solid #0284c7;
            border-radius: 0.5rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .diagnostico-cuellos-botella .diagnostico-header {
            margin-bottom: 0.75rem;
        }

        .diagnostico-aclaratoria {
            font-size: 0.8125rem;
            color: #0369a1;
            margin: 0.35rem 0 0 0;
            line-height: 1.4;
        }

        .diagnostico-listas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-top: 0.75rem;
        }

        .diagnostico-subtitulo {
            font-size: 0.8125rem;
            color: #0f172a;
            display: block;
            margin-bottom: 0.375rem;
        }

        .diagnostico-items {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .diagnostico-items li {
            font-size: 0.8125rem;
            color: #334155;
            background: white;
            padding: 0.4rem 0.65rem;
            border-radius: 0.375rem;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .diagnostico-badge {
            font-size: 0.75rem;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
            padding: 0.15rem 0.45rem;
            border-radius: 0.25rem;
            margin-left: 0.5rem;
            white-space: nowrap;
        }

        /* Estilos específicos adicionales para modo selección */
        .btn-modo-seleccion {
            background: var(--horario-secondary);
            color: white;
            border: none;
            padding: 0.625rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.15s ease-in-out;
        }

        .btn-modo-seleccion.activo {
            background: var(--horario-primary);
        }

        .barra-seleccion-flotante {
            position: fixed;
            bottom: 1.25rem;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            padding: 0.75rem 1.25rem;
            display: none;
            align-items: center;
            gap: 0.9375rem;
            z-index: 9995;
            border: 1px solid var(--gray-200);
        }

        .barra-seleccion-flotante.visible {
            display: flex;
        }

        .btn-eliminar-seleccionados {
            background: var(--horario-danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
        }

        .btn-cancelar-seleccion {
            background: var(--gray-200);
            color: var(--gray-700);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
        }

        /* Modal de detalles de conflicto */
        .modal-conflicto-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .modal-conflicto-overlay.visible {
            display: flex;
        }

        .modal-conflicto {
            background: white;
            border-radius: 0.5rem;
            max-width: 31.25rem;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-conflicto-header {
            background: var(--horario-danger);
            color: white;
            padding: 1.25rem;
            border-radius: 0.5rem 0.5rem 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-conflicto-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-conflicto-cerrar {
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

        .modal-conflicto-cerrar:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .modal-conflicto-body {
            padding: 1.25rem;
        }

        .modal-conflicto-info {
            margin-bottom: 1.25rem;
        }

        .modal-conflicto-info strong {
            display: block;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .modal-conflicto-info span {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .modal-conflicto-motivo {
            background: #f8d7da;
            border-left: 4px solid var(--horario-danger);
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            border-radius: 0 0.375rem 0.375rem 0;
        }

        .modal-conflicto-motivo strong {
            color: #721c24;
            display: block;
            margin-bottom: 0.375rem;
        }

        .modal-conflicto-motivo p {
            color: var(--gray-600);
            margin: 0;
            font-size: 0.875rem;
        }

        .modal-conflicto-sugerencias {
            margin-bottom: 1.25rem;
        }

        .modal-conflicto-sugerencias h4 {
            color: var(--gray-900);
            margin: 0 0 0.625rem 0;
            font-size: 0.875rem;
        }

        .modal-conflicto-sugerencias ul {
            margin: 0;
            padding-left: 1.25rem;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .modal-conflicto-sugerencias li {
            margin-bottom: 0.375rem;
        }

        .modal-conflicto-acciones {
            display: flex;
            gap: 0.625rem;
            justify-content: flex-end;
            padding-top: 1.25rem;
            border-top: 1px solid var(--gray-200);
        }

        .btn-modal-primario {
            background: var(--horario-info);
            color: white;
            border: none;
            padding: 0.625rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.15s ease-in-out;
        }

        .btn-modal-primario:hover {
            background: #0b5ed7;
        }

        .btn-modal-secundario {
            background: var(--gray-200);
            color: var(--gray-700);
            border: none;
            padding: 0.625rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.15s ease-in-out;
        }

        .btn-modal-secundario:hover {
            background: var(--gray-300);
        }

        /* Mejoras al panel de conflictos */
        .conflicto-item-detalles {
            margin-top: 0.625rem;
            padding-top: 0.625rem;
            border-top: 1px solid #f5c6cb;
        }

        .conflicto-tipo {
            display: inline-block;
            background: #f5c6cb;
            color: #721c24;
            padding: 0.125rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.6875rem;
            font-weight: 600;
            margin-bottom: 0.375rem;
        }

        .conflicto-motivo-texto {
            color: var(--gray-600);
            font-size: 0.8125rem;
            margin-bottom: 0.5rem;
        }

        .conflicto-sugerencias-texto {
            color: var(--gray-500);
            font-size: 0.75rem;
        }

        .conflicto-sugerencias-texto ul {
            margin: 0.25rem 0 0 0;
            padding-left: 1rem;
        }

        .btn-ver-detalles-conflicto {
            background: #721c24;
            color: white;
            border: none;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
            transition: background 0.15s ease-in-out;
        }

        .btn-ver-detalles-conflicto:hover {
            background: #5f141d;
        }

        /* Toasts Flotantes Persistentes estilo AdminLTE / Bootstrap 5 */
        #toast-container.toast-container {
            position: fixed !important;
            top: 1rem !important;
            right: 1rem !important;
            z-index: 1090 !important;
            width: auto !important;
            min-width: 320px !important;
            max-width: 380px !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: 85vh !important;
            padding: 0 !important;
            background: transparent !important;
            background-color: transparent !important;
            pointer-events: none !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 0.5rem !important;
        }

        #toast-container .toast {
            position: relative !important;
            pointer-events: auto !important;
            width: 100% !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            flex: 0 0 auto !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
            opacity: 1 !important;
            display: block !important;
            margin: 0 0 0.5rem 0 !important;
            animation: toastEntrada 0.25s ease-out;
        }

        @keyframes toastEntrada {
            from { transform: translateX(110%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        #toast-container .toast .toast-header {
            display: flex !important;
            align-items: center !important;
            padding: 0.5rem 0.75rem !important;
            height: auto !important;
            min-height: 0 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
        }

        #toast-container .toast .toast-header .close,
        #toast-container .toast .toast-header .btn-close {
            margin-left: auto !important;
            background: transparent !important;
            border: none !important;
            font-size: 1.2rem !important;
            line-height: 1 !important;
            opacity: 0.85 !important;
            cursor: pointer !important;
            padding: 0 !important;
            transition: opacity 0.15s ease !important;
            outline: none !important;
        }

        #toast-container .toast .toast-header .close:hover,
        #toast-container .toast .toast-header .btn-close:hover {
            opacity: 1 !important;
        }

        #toast-container .toast .toast-body {
            padding: 0.75rem 1rem !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #212529 !important;
            font-size: 0.875rem !important;
            line-height: 1.45 !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            overflow: visible !important;
        }

        /* Variantes de color en el header */
        #toast-container .toast-header.bg-success {
            background-color: #28a745 !important;
            color: #ffffff !important;
        }

        #toast-container .toast-header.bg-danger {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }

        #toast-container .toast-header.bg-warning {
            background-color: #ffc107 !important;
            color: #1f2d3d !important;
        }

        #toast-container .toast-header.bg-info {
            background-color: #17a2b8 !important;
            color: #ffffff !important;
        }

    </style>
@stop

@section('title', $modoEdicion ? 'Editar Horario' : 'Crear Horario')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div>
                    <h1 class="title-main">{{ $modoEdicion ? 'Editar Horario' : 'Crear Horario' }}</h1>
                    <p class="title-subtitle">{{ $modoEdicion ? 'Modificación de horarios académicos' : 'Asignación de horarios académicos' }}</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn-generar-horario" id="btnGenerarHorario">
                    <span class="spinner"></span>
                    <span class="btn-text">
                        <i class="fas fa-magic"></i>
                        {{ $modoEdicion ? 'Regenerar Horario' : 'Generar Horario' }}
                    </span>
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Contenedor de Notificaciones Flotantes (Toasts) -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;"></div>

    <div class="main-container">
        <!-- Banner informativo de validación de entrada exitosa -->
        <div class="alert alert-success d-flex align-items-center gap-3 p-3 mb-4 rounded-3 shadow-sm border-0" style="background-color: #ecfdf5; border-left: 5px solid #10b981 !important;">
            <i class="fas fa-check-circle fs-4 text-success"></i>
            <div>
                <strong class="d-block text-success" style="font-size: 0.95rem;">Configuración validada correctamente. Puedes proceder a generar el horario.</strong>
                <span class="text-muted small">El marco horario y la carga académica cumplen con los requisitos mínimos de viabilidad.</span>
            </div>
        </div>

        <!-- Parámetros de edición (ocultos) -->
        <input type="hidden" id="editGradoId" value="{{ $editGradoId ?? '' }}">
        <input type="hidden" id="editSeccionId" value="{{ $editSeccionId ?? '' }}">
        <input type="hidden" id="modoEdicion" value="{{ $modoEdicion ? 'true' : 'false' }}">


        <!-- Selección de nivel académico y área de formación -->
        <div class="selection-section" id="seccionNivel">
            <h4 class="mb-3">
                <i class="fas fa-graduation-cap" style="color: var(--primary);"></i>
                Paso 1: Seleccionar Nivel Académico y Área de Formación
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
                            <option value="{{ $grado->id }}" {{ $editGradoId == $grado->id ? 'selected' : '' }}>
                                {{ $grado->numero_grado }}° Grado
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="fas fa-book" style="color: var(--primary);"></i>
                        Área de Formación
                    </label>
                    <select class="form-select form-control-modern" id="areaFormacion" disabled>
                        <option value="">Seleccione nivel académico primero</option>
                    </select>
                </div>
                <button class="btn-generar-horario" id="btnContinuar" disabled>
                    <i class="fas fa-arrow-right"></i>
                    Continuar
                </button>
            </div>
        </div>

        <!-- Ocultar Paso 1 en modo edición -->
        <style>
            @if(isset($modo_edicion) && $modo_edicion)
                #seccionNivel {
                    display: none !important;
                }
                #vistaCalendario {
                    display: flex !important;
                }
            @endif
        </style>

        <!-- Vista del calendario (oculta inicialmente) -->
        <div class="row" id="vistaCalendario" style="display: none;">
            <!-- Cuadrícula -->
            <div class="col-12 col-lg-8 col-xl-9 mb-4">
                <div class="main-content">
                    <!-- Header de contexto -->
                    <div class="context-header" id="contextHeader">
                        <div class="context-info">
                            <span class="context-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span id="contextNivel">Nivel Académico: No seleccionado</span>
                            </span>
                            <span class="context-separator">|</span>
                            <span class="context-item">
                                <i class="fas fa-book"></i>
                                <span id="contextArea">Área de Formación: No seleccionada</span>
                            </span>
                        </div>
                        <button class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-2" id="btnVolverPaso1">
                            <i class="fas fa-arrow-left"></i>
                            Cambiar selección
                        </button>
                    </div>

                    <!-- Filtros de refinamiento -->
                    <div class="selection-section">
                        <div class="selection-row">
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
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fas fa-door-open" style="color: var(--primary);"></i>
                                    Aula
                                </label>
                                <select class="form-select form-control-modern" id="aula">
                                    <option value="">Todas las aulas</option>
                                    @foreach($aulas as $aula)
                                        <option value="{{ is_array($aula) ? $aula['id_aula'] : $aula->id_aula }}"
                                                data-seccion="{{ is_array($aula) ? $aula['seccion_id'] : null }}"
                                                data-tipo="{{ is_array($aula) ? $aula['tipo_aula'] : $aula->tipo_aula }}">
                                            {{ is_array($aula) ? $aula['nombre_aula'] : $aula->nombre_aula }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Matriz de Horarios -->
                    <div class="horario-matrix-wrapper">
                        <table class="table table-bordered horario-matrix">
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
            </div>

            <!-- Panel lateral -->
            <div class="col-12 col-lg-4 col-xl-3">
                <div class="card shadow-sm border-0 sidebar-docentes">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>
                        <h5 class="card-title mb-0 font-weight-bold" style="font-size: 0.95rem;">Docentes Disponibles</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="docentes-list" id="docentesList">
                            <div class="empty-state">
                                <i class="fas fa-user-tie"></i>
                                <p>Seleccione un nivel académico para ver los docentes disponibles</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor de notificaciones -->
        <div class="notifications-container" id="notificationsContainer"></div>

        <!-- Contenedor de notificaciones de conflictos persistente -->
        <div id="notificaciones-conflictos" class="notificaciones-conflictos"></div>

        <!-- Panel de conflictos -->
        <div class="conflictos-panel" id="conflictosPanel">
            <div class="conflictos-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h5>Conflictos Detectados</h5>
            </div>
            <div class="conflictos-body" id="conflictosBody">
                <!-- Los conflictos se insertarán aquí dinámicamente -->
            </div>
        </div>

        <!-- Modal de detalles de conflicto -->
        <div class="modal-conflicto-overlay" id="modalConflicto">
            <div class="modal-conflicto">
                <div class="modal-conflicto-header">
                    <h3>Detalles del Conflicto</h3>
                    <button class="modal-conflicto-cerrar" id="btnCerrarModalConflicto">×</button>
                </div>
                <div class="modal-conflicto-body" id="modalConflictoBody">
                    <!-- Contenido dinámico -->
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.horario.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
            <button class="btn-save" id="btnGuardarHorario">
                <i class="fas fa-save"></i>
                Guardar Horario
            </button>
            <button class="btn-modo-seleccion" id="btnModoSeleccion">
                <i class="fas fa-check-square"></i>
                Seleccionar múltiples
            </button>
        </div>
    </div>

    <!-- Overlay de carga reutilizable -->
    <div id="overlay-carga" class="overlay-carga oculto">
        <div class="spinner"></div>
        <p id="overlay-carga-mensaje">Cargando...</p>
    </div>

    <!-- Barra de selección flotante -->
    <div class="barra-seleccion-flotante" id="barraSeleccionFlotante">
        <span id="contadorSeleccionados">0 seleccionados</span>
        <button class="btn-eliminar-seleccionados" id="btnEliminarSeleccionados">
            <i class="fas fa-trash"></i> Eliminar seleccionados
        </button>
        <button class="btn-cancelar-seleccion" id="btnCancelarSeleccion">Cancelar</button>
    </div>

    <!-- Modal de Validación Previa (M4) -->
    <div class="modal fade" id="modalValidacionHorario" tabindex="-1" aria-labelledby="modalValidacionHorarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalValidacionHorarioLabel">
                        <i class="fas fa-clipboard-check text-primary me-2"></i>
                        Diagnóstico y Validación Previa de Horarios
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Disclaimer estricto -->
                    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center rounded-3 border-0 shadow-sm" style="font-size: 0.875rem; background-color: #e0f2fe; color: #0369a1;">
                        <i class="fas fa-info-circle me-2 fs-5 text-info"></i>
                        <div>
                            <em>Un validador sin errores NO garantiza que el horario se genere perfecto, solo que los datos son lógicamente posibles.</em>
                        </div>
                    </div>

                    <!-- Resumen de estado de validación -->
                    <div id="validacionEstadoAlerta" class="mb-3"></div>

                    <!-- Sección Errores (Rojo) -->
                    <div id="seccionErrores" class="mb-4 d-none">
                        <div class="d-flex align-items-center text-danger mb-2">
                            <i class="fas fa-times-circle me-2 fs-5"></i>
                            <h6 class="mb-0 fw-bold">Errores Críticos (<span id="countErrores">0</span>) - Bloquean la generación</h6>
                        </div>
                        <div id="listaErrores" class="list-group"></div>
                    </div>

                    <!-- Sección Avisos (Amarillo) -->
                    <div id="seccionAvisos" class="mb-4 d-none">
                        <div class="d-flex align-items-center text-warning mb-2" style="color: #b45309 !important;">
                            <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                            <h6 class="mb-0 fw-bold">Avisos y Advertencias (<span id="countAvisos">0</span>)</h6>
                        </div>
                        <div id="listaAvisos" class="list-group"></div>
                    </div>

                    <!-- Sección Info (Azul) -->
                    <div id="seccionInfo" class="mb-4 d-none">
                        <div class="d-flex align-items-center text-primary mb-2">
                            <i class="fas fa-info-circle me-2 fs-5"></i>
                            <h6 class="mb-0 fw-bold">Información de Carga y Contexto (<span id="countInfo">0</span>)</h6>
                        </div>
                        <div id="listaInfo" class="list-group"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nivelSelect = document.getElementById('nivelAcademico');
            const areaFormacionSelect = document.getElementById('areaFormacion');
            const btnContinuar = document.getElementById('btnContinuar');
            const seccionNivel = document.getElementById('seccionNivel');
            const vistaCalendario = document.getElementById('vistaCalendario');
            const docentesList = document.getElementById('docentesList');
            const contextHeader = document.getElementById('contextHeader');
            const contextNivel = document.getElementById('contextNivel');
            const contextArea = document.getElementById('contextArea');
            const btnVolverPaso1 = document.getElementById('btnVolverPaso1');

            // Detectar modo de edición
            const editGradoId = document.getElementById('editGradoId').value;
            const editSeccionId = document.getElementById('editSeccionId').value;
            const modoEdicion = document.getElementById('modoEdicion').value === 'true';

            console.log('Modo edición detectado:', { modoEdicion, editGradoId, editSeccionId });

            // Si está en modo edición, ocultar Paso 1 y cargar datos directamente
            if (modoEdicion && editGradoId) {
                console.log('Iniciando modo edición...');

                // Ocultar Paso 1 inmediatamente
                seccionNivel.style.display = 'none';

                // Mostrar Paso 2 inmediatamente
                vistaCalendario.style.display = 'flex';

                // Pre-cargar el grado
                nivelSelect.value = editGradoId;

                // Esperar un poco antes de disparar el evento change para cargar áreas
                setTimeout(() => {
                    console.log('Disparando evento change para cargar áreas...');
                    nivelSelect.dispatchEvent(new Event('change'));

                    // Esperar a que se carguen las áreas y analizar asignaciones existentes
                    setTimeout(() => {
                        console.log('Cargando áreas...');
                        console.log('Opciones de área:', areaFormacionSelect.options.length);

                        // Cargar asignaciones existentes para identificar el área correcta
                        fetch(`/api/horario/asignaciones?grado_id=${editGradoId}&seccion_id=${editSeccionId}`)
                            .then(response => response.json())
                            .then(asignaciones => {
                                console.log('Asignaciones existentes:', asignaciones);

                                if (asignaciones && asignaciones.length > 0) {
                                    // Obtener áreas únicas de las asignaciones existentes
                                    const areasExistentes = [...new Set(asignaciones.map(a => a.materia_id))];
                                    console.log('Áreas existentes en asignaciones:', areasExistentes);

                                    if (areasExistentes.length > 0) {
                                        // Seleccionar la primera área que tiene asignaciones
                                        const areaSeleccionar = areasExistentes[0];
                                        console.log('Área a seleccionar:', areaSeleccionar);

                                        // Buscar y seleccionar el área en el select
                                        for (let i = 0; i < areaFormacionSelect.options.length; i++) {
                                            if (areaFormacionSelect.options[i].value == areaSeleccionar) {
                                                areaFormacionSelect.selectedIndex = i;
                                                areaFormacionSelect.disabled = false;
                                                btnContinuar.disabled = false;
                                                console.log('Área seleccionada en índice:', i);
                                                break;
                                            }
                                        }

                                        // Disparar evento change para aplicar filtro
                                        areaFormacionSelect.dispatchEvent(new Event('change'));
                                    } else {
                                        console.warn('No se encontraron áreas específicas en las asignaciones. Mostrando todas las áreas.');
                                        // Dejar el select vacío para mostrar todas las áreas
                                    }
                                } else {
                                    console.warn('No hay asignaciones existentes. Mostrando todas las áreas.');
                                    // Si no hay asignaciones, dejar el select vacío
                                }

                                // Actualizar contexto
                                const gradoSeleccionado = nivelSelect.options[nivelSelect.selectedIndex]?.text || '';
                                const areaSeleccionada = areaFormacionSelect.selectedIndex > 0 
                                    ? areaFormacionSelect.options[areaFormacionSelect.selectedIndex]?.text 
                                    : 'Todas las áreas';
                                contextNivel.textContent = `Grado: ${gradoSeleccionado}`;
                                contextArea.textContent = `Área: ${areaSeleccionada}`;

                                // Cargar datos existentes del horario con filtros correctos
                                setTimeout(() => {
                                    console.log('Cargando datos API con filtros...');
                                    cargarDatosAPI();

                                    // Cargar docentes filtrados por nivel, sección y área
                                    setTimeout(() => {
                                        console.log('Cargando docentes con filtros...');
                                        cargarDocentesAPI(editGradoId);
                                    }, 1000);
                                }, 500);
                            })
                            .catch(error => {
                                console.error('Error al cargar asignaciones existentes:', error);
                                // En caso de error, proceder con todas las áreas
                                console.log('Error cargando asignaciones, usando todas las áreas.');
                            });
                    }, 1000);
                }, 300);
            }

            // Datos de prueba para docentes
            const docentesPorNivel = {
                '1': [
                    { id: 1, nombre: 'María García', area: 'Ciencias' },
                    { id: 2, nombre: 'Juan Pérez', area: 'Humanidades' },
                    { id: 3, nombre: 'Ana López', area: 'Técnica' },
                    { id: 4, nombre: 'Carlos Ruiz', area: 'Artística' },
                ],
                '2': [
                    { id: 5, nombre: 'Laura Martínez', area: 'Ciencias' },
                    { id: 6, nombre: 'Pedro Sánchez', area: 'Humanidades' },
                    { id: 7, nombre: 'Sofía Rodríguez', area: 'Técnica' },
                ],
                '3': [
                    { id: 8, nombre: 'Diego Fernández', area: 'Ciencias' },
                    { id: 9, nombre: 'Elena Gómez', area: 'Humanidades' },
                    { id: 10, nombre: 'Miguel Torres', area: 'Técnica' },
                    { id: 11, nombre: 'Lucía Jiménez', area: 'Artística' },
                ]
            };

            let draggedDocente = null;
            let horarioAsignaciones = {};
            let docentesCache = {};
            let dragAndDropInicializado = false;
            const notificationsContainer = document.getElementById('notificationsContainer');

            // Funciones reutilizables para el overlay de carga
            function mostrarPantallaCarga(mensaje = 'Cargando...') {
                document.getElementById('overlay-carga-mensaje').textContent = mensaje;
                document.getElementById('overlay-carga').classList.remove('oculto');
            }

            function ocultarPantallaCarga() {
                document.getElementById('overlay-carga').classList.add('oculto');
            }

            // Funciones para notificaciones de conflictos persistente
            function mostrarNotificacionConflicto(mensaje, idUnico) {
                const contenedor = document.getElementById('notificaciones-conflictos');

                // evitar duplicados si ya existe una notificación con el mismo idUnico
                if (document.getElementById('conflicto-' + idUnico)) return;

                const div = document.createElement('div');
                div.id = 'conflicto-' + idUnico;
                div.className = 'notificacion-conflicto';
                div.innerHTML = `
                    <span class="notificacion-conflicto-mensaje">${mensaje}</span>
                    <button class="notificacion-conflicto-cerrar" aria-label="Cerrar">×</button>
                `;
                div.querySelector('.notificacion-conflicto-cerrar').addEventListener('click', () => {
                    div.remove();
                });
                contenedor.appendChild(div);
            }

            function resolverNotificacionConflicto(idUnico) {
                const el = document.getElementById('conflicto-' + idUnico);
                if (el) el.remove();
            }

            // Función para marcar docentes con conflicto
            function marcarDocentesConConflicto(conflictos) {
                // Primero quitar la clase 'tiene-conflicto' de todas las tarjetas
                document.querySelectorAll('.docente-card').forEach(card => {
                    card.classList.remove('tiene-conflicto');
                });

                // Extraer los docente_id únicos del array de conflictos
                const docentesConConflicto = new Set();
                if (conflictos && conflictos.length > 0) {
                    conflictos.forEach(conflicto => {
                        if (conflicto.docente_id) {
                            docentesConConflicto.add(conflicto.docente_id);
                        }
                    });
                }

                // Para cada docente_id, buscar la tarjeta correspondiente y agregar la clase
                docentesConConflicto.forEach(docenteId => {
                    const tarjeta = document.querySelector(`.docente-card[data-id="${docenteId}"]`);
                    if (tarjeta) {
                        tarjeta.classList.add('tiene-conflicto');
                    }
                });
            }

            // Función para poblar el panel de conflictos con información detallada y diagnóstico de cuellos de botella
            function poblarPanelConflictos(conflictos, diagnostico = null) {
                const conflictosBody = document.getElementById('conflictosBody');
                conflictosBody.innerHTML = '';

                if (!conflictos || conflictos.length === 0) {
                    conflictosBody.innerHTML = '<p style="color: #6b7280; text-align: center; padding: 20px;">No hay conflictos detectados</p>';
                    // Limpiar marcas de conflicto en la grilla
                    document.querySelectorAll('.drop-zone.tiene-conflicto').forEach(dz => {
                        dz.classList.remove('tiene-conflicto');
                    });
                    return;
                }

                // Renderizar panel de diagnóstico (M7 / P8) si está presente
                if (diagnostico && ((diagnostico.docentes && diagnostico.docentes.length > 0) || (diagnostico.secciones && diagnostico.secciones.length > 0))) {
                    const docentesHTML = (diagnostico.docentes || []).map(d => `
                        <li>
                            <span><strong>Docente:</strong> ${escapeValidacionHtml(d.nombre)}</span>
                            <span class="diagnostico-badge">${d.rechazos} rechazos (${d.porcentaje}% de las evaluaciones)</span>
                        </li>
                    `).join('') || '<li style="color: #94a3b8; font-style: italic;">Sin rechazos significativos</li>';

                    const seccionesHTML = (diagnostico.secciones || []).map(s => `
                        <li>
                            <span><strong>Sección:</strong> ${escapeValidacionHtml(s.nombre)}</span>
                            <span class="diagnostico-badge">${s.rechazos} rechazos (${s.porcentaje}% de las evaluaciones)</span>
                        </li>
                    `).join('') || '<li style="color: #94a3b8; font-style: italic;">Sin rechazos significativos</li>';

                    const diagDiv = document.createElement('div');
                    diagDiv.className = 'diagnostico-cuellos-botella';
                    diagDiv.innerHTML = `
                        <div class="diagnostico-header">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-chart-line text-primary"></i>
                                <h6 class="mb-0 fw-bold" style="color: #0369a1;">Diagnóstico de Dificultad (Cuellos de Botella)</h6>
                            </div>
                            <p class="diagnostico-aclaratoria">
                                <i class="fas fa-info-circle me-1"></i>
                                <em>Información orientativa para el coordinador:</em> Estos datos señalan los docentes y secciones que generaron más rechazos durante la asignación automática. Úselos para saber qué ajustar (ej. liberar carga docente o flexibilizar disponibilidades horarias).
                            </p>
                        </div>
                        <div class="diagnostico-listas">
                            <div class="diagnostico-col">
                                <strong class="diagnostico-subtitulo"><i class="fas fa-user-clock me-1 text-primary"></i> Docentes con más rechazos:</strong>
                                <ul class="diagnostico-items">
                                    ${docentesHTML}
                                </ul>
                            </div>
                            <div class="diagnostico-col">
                                <strong class="diagnostico-subtitulo"><i class="fas fa-users-slash me-1 text-primary"></i> Secciones con más rechazos:</strong>
                                <ul class="diagnostico-items">
                                    ${seccionesHTML}
                                </ul>
                            </div>
                        </div>
                    `;
                    conflictosBody.appendChild(diagDiv);
                }

                conflictos.forEach((conflicto, index) => {
                    const div = document.createElement('div');
                    div.className = 'conflicto-item';
                    div.innerHTML = `
                        <div class="conflicto-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="conflicto-info">
                            <strong>${conflicto.docente_nombre || 'Docente desconocido'}</strong>
                            <p>${conflicto.materia_nombre || 'Materia desconocida'} - Sección ${conflicto.seccion_nombre || 'Sección desconocida'}</p>
                            <p>${conflicto.bloques_pendientes || 0} bloques pendientes</p>
                            <div class="conflicto-item-detalles">
                                <span class="conflicto-tipo">${conflicto.tipo_conflicto || 'Tipo desconocido'}</span>
                                <p class="conflicto-motivo-texto">${conflicto.motivo || 'Motivo no especificado'}</p>
                                <div class="conflicto-sugerencias-texto">
                                    <strong>Sugerencias:</strong>
                                    <ul>
                                        ${(conflicto.sugerencias || []).map(s => `<li>${s}</li>`).join('')}
                                    </ul>
                                </div>
                            </div>
                            <button class="btn-ver-detalles-conflicto" onclick="mostrarDetallesConflicto(${index})">
                                Ver detalles completos
                            </button>
                        </div>
                    `;
                    conflictosBody.appendChild(div);
                });

                // Guardar conflictos en una variable global para acceso desde el modal
                window.conflictosActuales = conflictos;
                
                // Marcar celdas con conflictos en la grilla
                marcarCeldasConConflictos(conflictos);
            }

            // Función para marcar celdas con conflictos en la grilla
            function marcarCeldasConConflictos(conflictos) {
                // Primero limpiar marcas anteriores
                document.querySelectorAll('.drop-zone.tiene-conflicto').forEach(dz => {
                    dz.classList.remove('tiene-conflicto');
                    dz.removeAttribute('data-conflicto-info');
                });

                // Mapeo de nombres de días a minúsculas
                const mapeoDias = {
                    'Lunes': 'lunes',
                    'Martes': 'martes',
                    'Miércoles': 'miércoles',
                    'Jueves': 'jueves',
                    'Viernes': 'viernes'
                };

                conflictos.forEach(conflicto => {
                    if (conflicto.dias_bloques_fallidos && Array.isArray(conflicto.dias_bloques_fallidos)) {
                        conflicto.dias_bloques_fallidos.forEach(espacio => {
                            const diaLower = mapeoDias[espacio.dia] || espacio.dia.toLowerCase();
                            const bloque = espacio.bloque;
                            
                            const dropZone = document.querySelector(`.drop-zone[data-day="${diaLower}"][data-block="${bloque}"]`);
                            if (dropZone) {
                                dropZone.classList.add('tiene-conflicto');
                                // Guardar información del conflicto para tooltip
                                const conflictoInfo = {
                                    docente: conflicto.docente_nombre,
                                    materia: conflicto.materia_nombre,
                                    seccion: conflicto.seccion_nombre,
                                    motivo: espacio.razones.join(', ')
                                };
                                dropZone.setAttribute('data-conflicto-info', JSON.stringify(conflictoInfo));
                            }
                        });
                    }
                });

                // Agregar event listeners para tooltips en celdas con conflicto
                agregarTooltipsConflictos();
            }

            // Función para agregar tooltips a celdas con conflicto
            function agregarTooltipsConflictos() {
                // Eliminar tooltips existentes
                document.querySelectorAll('.conflicto-tooltip').forEach(t => t.remove());

                document.querySelectorAll('.drop-zone.tiene-conflicto').forEach(dropZone => {
                    dropZone.addEventListener('mouseenter', function(e) {
                        const conflictoInfo = JSON.parse(this.getAttribute('data-conflicto-info'));
                        if (!conflictoInfo) return;

                        const tooltip = document.createElement('div');
                        tooltip.className = 'conflicto-tooltip';
                        tooltip.innerHTML = `
                            <div class="tooltip-titulo">⚠️ Conflicto de horario</div>
                            <div class="tooltip-motivo">${conflictoInfo.motivo}</div>
                            <div class="tooltip-sugerencias">
                                <div>Docente: ${conflictoInfo.docente}</div>
                                <div>Materia: ${conflictoInfo.materia}</div>
                                <div>Sección: ${conflictoInfo.seccion}</div>
                            </div>
                        `;
                        
                        document.body.appendChild(tooltip);
                        
                        const rect = this.getBoundingClientRect();
                        tooltip.style.left = rect.left + 'px';
                        tooltip.style.top = (rect.bottom + 5) + 'px';
                        
                        // Ajustar si se sale de la pantalla
                        const tooltipRect = tooltip.getBoundingClientRect();
                        if (tooltipRect.right > window.innerWidth) {
                            tooltip.style.left = (rect.right - tooltipRect.width) + 'px';
                        }
                        
                        requestAnimationFrame(() => {
                            tooltip.classList.add('visible');
                        });

                        this._tooltip = tooltip;
                    });

                    dropZone.addEventListener('mouseleave', function() {
                        if (this._tooltip) {
                            this._tooltip.classList.remove('visible');
                            setTimeout(() => {
                                if (this._tooltip) {
                                    this._tooltip.remove();
                                    this._tooltip = null;
                                }
                            }, 200);
                        }
                    });
                });
            }

            // Función para mostrar detalles de conflicto en modal
            window.mostrarDetallesConflicto = function(index) {
                const conflicto = window.conflictosActuales[index];
                if (!conflicto) return;

                const modalBody = document.getElementById('modalConflictoBody');
                
                let diasBloquesHTML = '';
                if (conflicto.dias_bloques_fallidos && Array.isArray(conflicto.dias_bloques_fallidos)) {
                    diasBloquesHTML = `
                        <div class="modal-conflicto-info">
                            <strong>Espacios fallidos:</strong>
                            <ul style="margin: 4px 0 0 0; padding-left: 20px; color: #6b7280; font-size: 0.875rem;">
                                ${conflicto.dias_bloques_fallidos.map(espacio => 
                                    `<li>${espacio.dia} - Bloque ${espacio.bloque}: ${espacio.razones.join(', ')}</li>`
                                ).join('')}
                            </ul>
                        </div>
                    `;
                }
                
                modalBody.innerHTML = `
                    <div class="modal-conflicto-info">
                        <strong>Docente:</strong>
                        <span>${conflicto.docente_nombre || 'N/A'}</span>
                    </div>
                    <div class="modal-conflicto-info">
                        <strong>Materia:</strong>
                        <span>${conflicto.materia_nombre || 'N/A'}</span>
                    </div>
                    <div class="modal-conflicto-info">
                        <strong>Sección:</strong>
                        <span>${conflicto.seccion_nombre || 'N/A'}</span>
                    </div>
                    <div class="modal-conflicto-info">
                        <strong>Bloques pendientes:</strong>
                        <span>${conflicto.bloques_pendientes || 0}</span>
                    </div>
                    ${diasBloquesHTML}
                    <div class="modal-conflicto-motivo">
                        <strong>Tipo de conflicto:</strong>
                        <p>${conflicto.tipo_conflicto || 'Desconocido'}</p>
                        <strong>Motivo:</strong>
                        <p>${conflicto.motivo || 'No especificado'}</p>
                    </div>
                    <div class="modal-conflicto-sugerencias">
                        <h4>Sugerencias de solución:</h4>
                        <ul>
                            ${(conflicto.sugerencias || []).map(s => `<li>${s}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="modal-conflicto-acciones">
                        <button class="btn-modal-secundario" onclick="cerrarModalConflicto()">Cerrar</button>
                        <button class="btn-modal-primario" onclick="resaltarDocenteConflicto(${conflicto.docente_id})">
                            Ver en lista de docentes
                        </button>
                        <button class="btn-modal-primario" onclick="resaltarHuecosDisponibles(${conflicto.docente_id})">
                            Ver huecos disponibles
                        </button>
                        <button class="btn-modal-primario" onclick="buscarAlternativasIntercambio(${index})">
                            Buscar alternativas
                        </button>
                    </div>
                `;

                document.getElementById('modalConflicto').classList.add('visible');
            };

            // Función para cerrar modal de conflicto
            window.cerrarModalConflicto = function() {
                document.getElementById('modalConflicto').classList.remove('visible');
            };

            // Función para resaltar docente con conflicto en la sidebar
            window.resaltarDocenteConflicto = function(docenteId) {
                cerrarModalConflicto();
                const tarjeta = document.querySelector(`.docente-card[data-id="${docenteId}"]`);
                if (tarjeta) {
                    tarjeta.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    tarjeta.style.animation = 'pulse 0.5s ease 3';
                    setTimeout(() => {
                        tarjeta.style.animation = '';
                    }, 1500);
                }
            };

            // Función para resaltar huecos disponibles para un docente
            window.resaltarHuecosDisponibles = function(docenteId) {
                // Limpiar resaltados anteriores
                document.querySelectorAll('.drop-zone.hueco-disponible').forEach(dz => {
                    dz.classList.remove('hueco-disponible');
                });

                // Buscar todas las celdas vacías
                document.querySelectorAll('.drop-zone').forEach(dropZone => {
                    const assignedDocente = dropZone.querySelector('.assigned-docente');
                    if (!assignedDocente) {
                        dropZone.classList.add('hueco-disponible');
                    }
                });

                mostrarNotificacion('Los huecos disponibles están resaltados en verde', 'info');
            };

            // Función para buscar alternativas de intercambio
            window.buscarAlternativasIntercambio = function(conflictoIndex) {
                const conflicto = window.conflictosActuales[conflictoIndex];
                if (!conflicto) return;

                const docenteId = conflicto.docente_id;
                const materiaId = conflicto.materia_id;
                const seccionId = conflicto.seccion_id;

                // Buscar asignaciones existentes del mismo docente en otras celdas
                const asignacionesDocente = horarioData.filter(h => h.docente_id == docenteId);
                
                // Buscar asignaciones de otros docentes en la misma materia/sección
                const asignacionesMismaMateria = horarioData.filter(h => 
                    h.materia_id == materiaId && h.docente_id != docenteId
                );

                mostrarNotificacion(`Encontradas ${asignacionesDocente.length} asignaciones del docente y ${asignacionesMismaMateria.length} de la misma materia`, 'info');
            };

            // Event listener para cerrar modal con botón X
            document.getElementById('btnCerrarModalConflicto').addEventListener('click', cerrarModalConflicto);

            // Event listener para cerrar modal al hacer clic fuera
            document.getElementById('modalConflicto').addEventListener('click', function(e) {
                if (e.target === this) {
                    cerrarModalConflicto();
                }
            });

            // Inicializar drag and drop una sola vez
            inicializarDragAndDrop();
            
            // ============================================================
            // Popup flotante para tarjetas de docente (hover)
            // ============================================================
            let tarjetaConPopup = null;
            let timeoutCerrarPopup = null;

            function posicionarPopup(tarjeta) {
                const popup = tarjeta.querySelector('.adm-full');
                if (!popup) return;

                const rectTarjeta = tarjeta.getBoundingClientRect();
                const rectPopup = popup.getBoundingClientRect();

                // Por defecto: a la derecha de la tarjeta
                let left = rectTarjeta.right + 8;
                let top = rectTarjeta.top;

                // Si se sale por la derecha, ponerlo a la izquierda
                if (left + rectPopup.width > window.innerWidth - 10) {
                    left = rectTarjeta.left - rectPopup.width - 8;
                }
                // Si tampoco cabe por la izquierda, pegarlo al borde
                if (left < 10) left = 10;

                // Si se sale por abajo, subirlo
                if (top + rectPopup.height > window.innerHeight - 10) {
                    top = window.innerHeight - rectPopup.height - 10;
                }
                if (top < 10) top = 10;

                popup.style.left = left + 'px';
                popup.style.top = top + 'px';
            }

            function abrirPopup(tarjeta) {
                if (tarjetaConPopup === tarjeta) return;

                // Cerrar el anterior
                if (tarjetaConPopup) {
                    tarjetaConPopup.classList.remove('popup-activo');
                }

                clearTimeout(timeoutCerrarPopup);
                tarjetaConPopup = tarjeta;
                tarjeta.classList.add('popup-activo');

                // Forzar reflow para que el popup ya tenga tamaño real
                void tarjeta.offsetHeight;
                posicionarPopup(tarjeta);
            }

            function cerrarPopup() {
                clearTimeout(timeoutCerrarPopup);
                timeoutCerrarPopup = setTimeout(() => {
                    if (tarjetaConPopup) {
                        tarjetaConPopup.classList.remove('popup-activo');
                        tarjetaConPopup = null;
                    }
                }, 180); // margen para que el mouse pueda llegar al popup
            }

            // Mostrar al entrar a la tarjeta
            document.addEventListener('mouseover', function(e) {
                const tarjeta = e.target.closest('.assigned-docente');
                if (!tarjeta) return;

                // Si entramos a un hijo del popup, no reabrir
                if (e.target.closest('.adm-full')) {
                    clearTimeout(timeoutCerrarPopup);
                    return;
                }

                abrirPopup(tarjeta);
            });

            // Cerrar al salir (con retardo, para permitir moverse al popup)
            document.addEventListener('mouseout', function(e) {
                const tarjeta = e.target.closest('.assigned-docente');
                if (!tarjeta || tarjeta !== tarjetaConPopup) return;

                // Si el mouse se va al popup mismo, no cerrar
                const related = e.relatedTarget;
                if (related && related.closest('.adm-full')) return;

                // Si todavía está dentro de la tarjeta, no cerrar
                if (related && tarjeta.contains(related)) return;

                cerrarPopup();
            });

            // Reposicionar si el usuario hace scroll con el popup abierto
            window.addEventListener('scroll', () => {
                if (tarjetaConPopup) posicionarPopup(tarjetaConPopup);
            }, true);

            // Abrir y mantener abierto también al hacer clic en la tarjeta del docente asignado
            document.addEventListener('click', function(e) {
                if (typeof modoSeleccionActivo !== 'undefined' && modoSeleccionActivo) return;
                
                const tarjeta = e.target.closest('.assigned-docente');
                if (tarjeta) {
                    if (!e.target.closest('.adm-full')) {
                        abrirPopup(tarjeta);
                    }
                } else if (!e.target.closest('.adm-full')) {
                    if (tarjetaConPopup) {
                        tarjetaConPopup.classList.remove('popup-activo');
                        tarjetaConPopup = null;
                    }
                }
            });
            // Función para validar ambos campos del Paso 1
            function validarPaso1() {
                const nivelSeleccionado = nivelSelect.value;
                const areaSeleccionada = areaFormacionSelect.value;
                
                if (nivelSeleccionado && areaSeleccionada) {
                    btnContinuar.disabled = false;
                } else {
                    btnContinuar.disabled = true;
                }
            }

            // Filtrar áreas de formación por nivel académico
            nivelSelect.addEventListener('change', async function() {
                const nivelId = this.value;
                
                if (nivelId) {
                    // Habilitar select de área de formación
                    areaFormacionSelect.disabled = false;
                    areaFormacionSelect.innerHTML = '<option value="">Cargando áreas...</option>';
                    
                    try {
                        const response = await fetch(`/api/areas-formacion/grado?grado_id=${nivelId}`);
                        const areas = await response.json();
                        
                        areaFormacionSelect.innerHTML = '<option value="">Seleccione un área de formación</option>';
                        
                        areas.forEach(area => {
                            const option = document.createElement('option');
                            option.value = area.id;
                            option.textContent = area.nombre;
                            areaFormacionSelect.appendChild(option);
                        });
                    } catch (error) {
                        console.error('Error al cargar áreas de formación:', error);
                        areaFormacionSelect.innerHTML = '<option value="">Error al cargar áreas</option>';
                        mostrarNotificacion('Error al cargar áreas de formación', 'error');
                    }
                } else {
                    // Deshabilitar y resetear
                    areaFormacionSelect.disabled = true;
                    areaFormacionSelect.innerHTML = '<option value="">Seleccione nivel académico primero</option>';
                }
                
                validarPaso1();
            });

            // Validar cuando cambia el área de formación
            areaFormacionSelect.addEventListener('change', validarPaso1);

            // Función para actualizar header de contexto
            function actualizarContexto() {
                const nivelTexto = nivelSelect.options[nivelSelect.selectedIndex]?.text || 'No seleccionado';
                const areaTexto = areaFormacionSelect.options[areaFormacionSelect.selectedIndex]?.text || 'No seleccionada';
                
                contextNivel.textContent = `Nivel Académico: ${nivelTexto}`;
                contextArea.textContent = `Área de Formación: ${areaTexto}`;
            }

            // Volver al Paso 1
            btnVolverPaso1.addEventListener('click', function() {
                horarioAsignaciones = {};
                modoSeleccionActivo = false;
                asignacionesSeleccionadas.clear();
                actualizarBarraFlotante();
                btnModoSeleccion.classList.remove('activo');
                document.querySelectorAll('.drop-zone').forEach(dz => {
                    dz.classList.remove('modo-seleccion', 'seleccionado');
                });
                vistaCalendario.style.display = 'none';
                seccionNivel.style.display = 'block';
                actualizarContexto();
            });

            // Almacenar información de contexto para las asignaciones
            let asignacionesContexto = {};

            // Variables para almacenar datos de la cuadrícula
            @php
                $diasJson = isset($diasCases) ? collect($diasCases)->map(function ($d) {
                    $id = $d instanceof \App\Enums\Dia ? $d->value : (is_object($d) && isset($d->value) ? $d->value : (int) $d);
                    $nombre = $d instanceof \App\Enums\Dia ? $d->label() : (is_object($d) && method_exists($d, 'label') ? $d->label() : (is_object($d) ? ($d->nombre ?? $d->nombre_dia ?? (string) $id) : (string) $id));
                    return [
                        'id' => (int) $id,
                        'dia' => (int) $id,
                        'nombre' => (string) $nombre,
                        'nombre_dia' => (string) $nombre,
                    ];
                })->values()->all() : [];

                $bloquesJson = [];
                if (isset($numeroBloques)) {
                    for ($b = 1; $b <= (int) $numeroBloques; $b++) {
                        $info = isset($bloquesCalculados) ? ($bloquesCalculados[$b] ?? ['inicio' => '07:00', 'fin' => '07:45']) : ['inicio' => '07:00', 'fin' => '07:45'];
                        $bloquesJson[] = [
                            'id' => $b,
                            'bloque' => $b,
                            'hora_inicio' => is_array($info) ? ($info['inicio'] ?? '07:00') : '07:00',
                            'hora_fin' => is_array($info) ? ($info['fin'] ?? '07:45') : '07:45',
                        ];
                    }
                }

                $reservadosJson = isset($bloquesReservados) ? collect($bloquesReservados)->map(function ($r) {
                    $diaVal = $r->dia instanceof \App\Enums\Dia ? $r->dia->value : (is_object($r->dia) && isset($r->dia->value) ? $r->dia->value : (int) $r->dia);
                    return [
                        'dia' => (int) $diaVal,
                        'bloque' => (int) $r->bloque,
                    ];
                })->values()->all() : [];
            @endphp
            let diasSemana = {!! json_encode($diasJson) !!};
            let bloquesHorario = {!! json_encode($bloquesJson) !!};
            let bloquesReservados = {!! json_encode($reservadosJson) !!};
            let seccionesGlobales = {!! json_encode($todasLasSecciones ?? \App\Models\Seccion::where('status', true)->get(['id', 'nombre', 'grado_id'])) !!};
            let horarioData = [];
            let anioEscolarActivo = null;
            let draggedAsignacion = null;

            // Función para cargar datos de la API
            async function cargarDatosAPI() {
                try {
                    mostrarPantallaCarga('Cargando horario...');
                    
                    const nivelSeleccionado = nivelSelect.value;
                    const areaFormacionSeleccionado = areaFormacionSelect?.value || null;
                    
                    const params = new URLSearchParams();
                    if (nivelSeleccionado) {
                        params.append('grado_id', nivelSeleccionado);
                    }
                    if (areaFormacionSeleccionado) {
                        params.append('area_formacion_id', areaFormacionSeleccionado);
                    }

                    const [diasResponse, bloquesResponse, horarioResponse, anioResponse] = await Promise.all([
                        fetch('/api/dias-semana'),
                        fetch('/api/bloques-horario'),
                        fetch(`/api/horario/asignaciones?${params.toString()}`),
                        fetch('/api/anio-escolar/activo')
                    ]);

                    const diasApi = await diasResponse.json().catch(() => null);
                    if (Array.isArray(diasApi) && diasApi.length > 0) {
                        diasSemana = diasApi;
                    }

                    const bloquesApi = await bloquesResponse.json().catch(() => null);
                    if (Array.isArray(bloquesApi) && bloquesApi.length > 0) {
                        bloquesHorario = bloquesApi;
                    }

                    horarioData = await horarioResponse.json();
                    
                    if (anioResponse.ok) {
                        anioEscolarActivo = await anioResponse.json();
                        console.log('Año escolar activo:', anioEscolarActivo);
                    } else {
                        console.warn('No hay año escolar activo');
                    }

                    console.log('Días de semana:', diasSemana);
                    console.log('Bloques horario:', bloquesHorario);
                    console.log('Horario data:', horarioData);
                    console.log('Nivel seleccionado:', nivelSeleccionado);
                    console.log('Área de formación seleccionada:', areaFormacionSeleccionado);

                    renderizarCalendario();
                } catch (error) {
                    console.error('Error al cargar datos de la API:', error);
                    mostrarNotificacion('Error al cargar datos del horario', 'error');
                } finally {
                    ocultarPantallaCarga();
                }
            }

            // Función para cargar docentes desde la API
            async function cargarDocentesAPI(nivel) {
                try {
                    const areaFormacionId = areaFormacionSelect.value;
                    const seccionId = document.getElementById('seccion').value;

                    const params = new URLSearchParams();
                    if (nivel) params.append('grado_id', nivel);
                    if (areaFormacionId) params.append('area_formacion_id', areaFormacionId);
                    if (seccionId) params.append('seccion_id', seccionId);

                    const url = `/api/docente?${params.toString()}`;
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error(`HTTP error ${response.status}`);
                    }
                    const docentes = await response.json();

                    console.log('Docentes cargados:', docentes);

                    renderizarDocentes(Array.isArray(docentes) ? docentes : []);
                } catch (error) {
                    console.error('Error al cargar docentes:', error);
                    renderizarDocentes([]);
                }
            }

            // Función para renderizar docentes en el sidebar
            function renderizarDocentes(docentes) {
                if (!Array.isArray(docentes)) {
                    docentes = [];
                }

                // Popular cache de docentes para uso posterior
                docentes.forEach(d => { if (d && d.id) docentesCache[d.id] = d; });

                if (docentes.length === 0) {
                    docentesList.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-user-tie"></i>
                            <p>Sin datos</p>
                            <small class="text-muted">No se encontraron docentes disponibles</small>
                        </div>
                    `;
                    return;
                }

                // Obtener el área de formación seleccionada para filtrar la visualización
                const areaFormacionId = areaFormacionSelect.value;
                const seccionId = document.getElementById('seccion').value;

                docentesList.innerHTML = docentes.map(docente => {
                    const nombre = docente.nombre_completo || docente.nombre || 'Sin nombre';
                    const horasAcademicas = docente.horas_academicas || 0;

                    // Filtrar áreas de formación según el filtro seleccionado
                    let areasAMostrar = docente.areas || [];
                    if (areaFormacionId) {
                        const areaNombre = areaFormacionSelect.options[areaFormacionSelect.selectedIndex]?.text?.trim();
                        if (areaNombre) {
                            const filtradas = areasAMostrar.filter(area => {
                                return area?.trim().toLowerCase() === areaNombre.toLowerCase();
                            });
                            if (filtradas.length > 0) {
                                areasAMostrar = filtradas;
                            }
                        }
                    }

                    const areaTexto = areasAMostrar.length > 0 ? areasAMostrar.join(', ') : 'Sin área asignada';

                    // Filtrar secciones según el filtro seleccionado
                    let seccionesAMostrar = docente.secciones || [];
                    if (seccionId) {
                        const filtradasSec = seccionesAMostrar.filter(seccion => seccion.id == seccionId);
                        if (filtradasSec.length > 0) {
                            seccionesAMostrar = filtradasSec;
                        }
                    }

                    const seccionTexto = seccionesAMostrar.length > 0 
                        ? seccionesAMostrar.map(s => s.nombre).join(', ') 
                        : (docente.secciones && docente.secciones.length > 0 
                            ? docente.secciones.map(s => s.nombre).join(', ') 
                            : 'Sin sección asignada');

                    return `
                        <div class="docente-card" draggable="true" data-id="${docente.id}" data-nombre="${nombre}" data-area="${areaTexto}" data-cedula="${docente.cedula || 'N/A'}" data-horas="${horasAcademicas}">
                            <div class="docente-name">${nombre}</div>
                            <div class="docente-area">
                                <i class="fas fa-book"></i>
                                ${areaTexto}
                            </div>
                            <div class="docente-area">
                                <i class="fas fa-users"></i>
                                ${seccionTexto}
                            </div>
                            <div class="docente-horas">
                                <i class="fas fa-clock"></i>
                                ${horasAcademicas} horas académicas
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // Función para renderizar el calendario dinámicamente
            function renderizarCalendario() {
                const thead = document.querySelector('.horario-matrix thead tr');
                const tbody = document.querySelector('.horario-matrix tbody');

                // Verificar que los elementos existan
                if (!thead || !tbody) {
                    console.error('No se encontraron elementos de la tabla del calendario');
                    return;
                }

                // Obtener filtros seleccionados
                const seccionId = document.getElementById('seccion')?.value || '';
                const areaFormacionId = areaFormacionSelect?.value || '';

                console.log('Renderizando calendario con filtros:', { seccionId, areaFormacionId });

                // Filtrar horarioData por sección y área de formación
                let horarioFiltrado = horarioData;

                if (seccionId) {
                    horarioFiltrado = horarioFiltrado.filter(h => {
                        const secId = h.seccion_id || h.cargaAcademica?.seccion?.id;
                        return secId == seccionId;
                    });
                }

                if (areaFormacionId) {
                    horarioFiltrado = horarioFiltrado.filter(h => {
                        const matId = h.materia_id || h.cargaAcademica?.materia?.id;
                        return matId == areaFormacionId;
                    });
                }

                console.log('Horarios filtrados:', horarioFiltrado.length);

                // Renderizar columnas (días de la semana)
                thead.innerHTML = '<th class="time-header">Hora / Bloque</th>';
                diasSemana.forEach(dia => {
                    const th = document.createElement('th');
                    th.textContent = dia.nombre_dia || dia.nombre;
                    thead.appendChild(th);
                });

                // Renderizar filas (bloques horarios) y celdas
                tbody.innerHTML = '';
                bloquesHorario.forEach((bloque, index) => {
                    const tr = document.createElement('tr');
                    const numBloque = Number(bloque.bloque || bloque.id || (index + 1));

                    // Celda de tiempo
                    const timeCell = document.createElement('td');
                    timeCell.className = 'time-cell';
                    timeCell.innerHTML = `
                        <strong>Bloque ${numBloque}</strong>
                        <br>
                        <small>${bloque.hora_inicio || '--:--'} - ${bloque.hora_fin || '--:--'}</small>
                    `;
                    tr.appendChild(timeCell);

                    // Celdas de días
                    diasSemana.forEach(dia => {
                        const td = document.createElement('td');
                        const numDia = Number(dia.dia || dia.id);
                        const diaNombre = dia.nombre_dia || dia.nombre;
                        td.dataset.day = numDia;
                        td.dataset.dayName = diaNombre.toLowerCase();
                        td.dataset.block = numBloque;

                        // Verificar si es celda reservada institucionalmente
                        const esReservada = Array.isArray(bloquesReservados) && bloquesReservados.some(r => Number(r.dia) === numDia && Number(r.bloque) === numBloque);

                        if (esReservada) {
                            td.className = 'drop-zone celda-reservada text-center align-middle';
                            td.style.backgroundColor = '#f1f5f9';
                            td.style.cursor = 'not-allowed';
                            td.innerHTML = '<span class="badge bg-secondary" style="background-color: #94a3b8 !important; font-size: 0.8rem; padding: 0.35rem 0.65rem;"><i class="fas fa-ban me-1"></i>Reservado</span>';
                            tr.appendChild(td);
                            return;
                        }

                        td.className = 'drop-zone';

                        const inner = document.createElement('div');
                        inner.className = 'drop-zone-inner';

                        // Buscar todas las asignaciones para este día y bloque (usando datos filtrados)
                        const asignacionesDeCelda = horarioFiltrado.filter(h => {
                            const asigDia = Number(h.dia);
                            const asigBloque = Number(h.bloque);
                            const asigDiaNombre = (h.dia_nombre || '').toLowerCase();
                            const diaCoincide = asigDia === numDia || asigDiaNombre === diaNombre.toLowerCase();
                            return diaCoincide && asigBloque === numBloque;
                        });

                        // Verificar si hay asignación local (drag and drop)
                        const key = `${numDia}-${numBloque}`;
                        const asignacionLocal = horarioAsignaciones[key];

                        // Combinar asignaciones persistidas y locales en la celda
                        let htmlTarjetas = '';
                        if (asignacionesDeCelda.length > 0) {
                            htmlTarjetas += asignacionesDeCelda.map(horarioAsignado => renderizarTarjetaPersistida(horarioAsignado)).join('');
                        }
                        if (Array.isArray(asignacionLocal) && asignacionLocal.length > 0) {
                            htmlTarjetas += asignacionLocal.map((a, idx) => renderizarAsignacionLocalHTML(key, a, idx)).join('');
                        }
                        inner.innerHTML = htmlTarjetas;

                        td.appendChild(inner);
                        tr.appendChild(td);
                    });

                    tbody.appendChild(tr);
                });

                console.log('Total drop-zones creadas:', document.querySelectorAll('.drop-zone').length);
                console.log('Calendario renderizado con', tbody.children.length, 'filas');
            }

            // Función helper para obtener la letra o nombre de la sección de manera robusta
            function obtenerLetraSeccion(asignacion) {
                if (!asignacion) return 'Sin Sección';

                // 1. Relación anidada de carga académica
                let nombre = asignacion.cargaAcademica?.seccion?.nombre;

                // 2. Objeto sección directo
                if (!nombre || nombre === '?' || nombre === 'N/A') {
                    nombre = asignacion.seccion?.nombre;
                }

                // 3. Propiedad seccion_nombre en la raíz
                if (!nombre || nombre === '?' || nombre === 'N/A') {
                    nombre = asignacion.seccion_nombre;
                }

                // 4. Búsqueda por ID en asignacion.secciones, seccionesGlobales o en el DOM
                const secId = asignacion.seccionId || asignacion.seccion_id || asignacion.cargaAcademica?.seccion_id || asignacion.cargaAcademica?.seccion?.id;
                if ((!nombre || nombre === '?' || nombre === 'N/A') && secId) {
                    if (Array.isArray(asignacion.secciones)) {
                        const sec = asignacion.secciones.find(s => String(s.id) === String(secId));
                        if (sec?.nombre) {
                            nombre = sec.nombre;
                        }
                    }
                    if ((!nombre || nombre === '?' || nombre === 'N/A') && typeof seccionesGlobales !== 'undefined' && Array.isArray(seccionesGlobales)) {
                        const secG = seccionesGlobales.find(s => String(s.id) === String(secId));
                        if (secG?.nombre) {
                            nombre = secG.nombre;
                        }
                    }
                    if (!nombre || nombre === '?' || nombre === 'N/A') {
                        const optDom = document.querySelector(`#seccion option[value="${secId}"]`);
                        if (optDom && optDom.textContent.trim()) {
                            nombre = optDom.textContent.trim();
                        }
                    }
                }

                // 5. Si es asignación local y el docente tiene lista de secciones
                if ((!nombre || nombre === '?' || nombre === 'N/A') && Array.isArray(asignacion.secciones) && asignacion.secciones.length > 0) {
                    nombre = asignacion.secciones[0]?.nombre;
                }

                // 6. Si aún no tiene y hay seccionesGlobales para el grado actual
                if ((!nombre || nombre === '?' || nombre === 'N/A') && typeof seccionesGlobales !== 'undefined' && Array.isArray(seccionesGlobales)) {
                    const gradoActual = document.getElementById('nivelAcademico')?.value;
                    const secGrado = seccionesGlobales.find(s => !gradoActual || String(s.grado_id) === String(gradoActual));
                    if (secGrado?.nombre) {
                        nombre = secGrado.nombre;
                    }
                }

                if (!nombre || nombre === '?' || nombre === 'N/A' || String(nombre).trim() === '') {
                    return 'Sin Sección';
                }

                let limpio = String(nombre).trim();
                if (/^secci[oó]n\s+([a-z0-9]+)$/i.test(limpio)) {
                    limpio = limpio.replace(/^secci[oó]n\s+/i, '').toUpperCase();
                }

                return limpio;
            }

            // Función helper para formatear la sección en la píldora compacta (ej. "(A)", "(B)", etc.)
            function formatearPildoraSeccion(asignacion) {
                const letra = obtenerLetraSeccion(asignacion);
                if (letra === 'Sin Sección') return 'Sin Sección';
                if (letra.startsWith('(') && letra.endsWith(')')) return letra;
                return `(${letra})`;
            }

            function renderizarAsignacionLocalHTML(key, asignacion, index) {
                const uid = `${key}-${index}`;

                let seccionHtml = '';
                let listaSecciones = (asignacion.secciones && asignacion.secciones.length > 0) 
                    ? asignacion.secciones 
                    : [];

                if (listaSecciones.length === 0 && typeof seccionesGlobales !== 'undefined' && Array.isArray(seccionesGlobales)) {
                    const gradoId = document.getElementById('nivelAcademico')?.value;
                    listaSecciones = seccionesGlobales.filter(s => !gradoId || String(s.grado_id) === String(gradoId));
                }

                if (listaSecciones && listaSecciones.length > 0) {
                    let opciones = '<option value="">Seleccione sección</option>';
                    listaSecciones.forEach(s => {
                        const isSelected = (String(asignacion.seccionId) === String(s.id) || String(asignacion.seccion_id) === String(s.id));
                        opciones += `<option value="${s.id}" ${isSelected ? 'selected' : ''}>${s.nombre}</option>`;
                    });
                    seccionHtml = `
                        <select class="pildora-selector" id="seccion-local-${uid}"
                                data-key="${key}" data-index="${index}"
                                onchange="cambiarSeccionAsignacion(this)">
                            ${opciones}
                        </select>`;
                } else {
                    seccionHtml = `<span class="docente-cedula">El docente no tiene secciones asignadas</span>`;
                }

                const materiasEspecializadas = ['Arte y Patrimonio','Ciencias Naturales','Biología','Física','Química'];
                const esEspecializada = materiasEspecializadas.some(m => (asignacion.area || '').includes(m));

                let aulaHtml = '';
                const aulaSelect = document.getElementById('aula');
                if (aulaSelect) {
                    let opciones = '<option value="">Seleccione aula</option>';
                    if (esEspecializada) {
                        for (let i = 0; i < aulaSelect.options.length; i++) {
                            const o = aulaSelect.options[i];
                            if (o.value !== '' && (o.dataset.tipo || '') !== 'Aula Regular') {
                                const sel = asignacion.aulaId == o.value ? 'selected' : '';
                                opciones += `<option value="${o.value}" ${sel}>${o.text}</option>`;
                            }
                        }
                    } else {
                        const fija = obtenerAulaRegularDeSeccion(asignacion.seccionId);
                        if (fija) {
                            opciones += `<option value="${fija.id}" selected>${fija.nombre}</option>`;
                            asignacion.aulaId = fija.id;
                        } else {
                            opciones += `<option value="">Sin aula regular asignada</option>`;
                        }
                    }
                    aulaHtml = `
                        <select class="pildora-selector" id="aula-local-${uid}"
                                data-key="${key}" data-index="${index}"
                                onchange="asignarAulaLocal(this)" ${!esEspecializada ? 'disabled' : ''}>
                            ${opciones}
                        </select>`;
                }

                const esFijadaLocal = !!asignacion.fijada;
                const lockIconLocalHtml = `
                    <button type="button" class="btn-lock-toggle ${esFijadaLocal ? 'text-warning' : 'text-muted'}" 
                            title="${esFijadaLocal ? 'Clase fijada (clic para desbloquear)' : 'Clase no fijada (clic para fijar)'}" 
                            onclick="toggleFijarLocal('${key}', ${index}, event)">
                        <i class="fas ${esFijadaLocal ? 'fa-lock' : 'fa-lock-open'}"></i>
                    </button>
                `;
                const draggableAttrLocal = esFijadaLocal ? 'draggable="false"' : 'draggable="true"';
                const fijadaClsLocal = esFijadaLocal ? ' clase-fijada' : '';

                return `
                    <div class="assigned-docente${fijadaClsLocal}" ${draggableAttrLocal} data-fijada="${esFijadaLocal}">
                        <div class="adm-mini">
                            <span class="adm-mini-nombre">${asignacion.nombre}</span>
                            <span class="adm-mini-seccion">${formatearPildoraSeccion(asignacion)} ${lockIconLocalHtml}</span>
                        </div>
                        <div class="adm-full">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="docente-nombre">${asignacion.nombre}</span>
                                ${lockIconLocalHtml}
                            </div>
                            <span class="docente-cedula">${asignacion.cedula || 'N/A'}</span>
                            <span class="docente-materia">${asignacion.area}</span>
                            ${seccionHtml}
                            ${aulaHtml}
                            <div class="aula-error-message"></div>
                            ${!esFijadaLocal ? `<button class="btn-eliminar-docente" onclick="eliminarAsignacion('${key}', ${index}, event)">×</button>` : ''}
                        </div>
                    </div>`;
            }

            // Función para renderizar una tarjeta de asignación persistida
            function renderizarTarjetaPersistida(horarioAsignado) {
                const docenteNombre = horarioAsignado.cargaAcademica?.docente?.nombre || horarioAsignado.docente_nombre || 'N/A';
                const docenteCedula = horarioAsignado.docente_cedula || 'N/A';
                const materiaNombre = horarioAsignado.cargaAcademica?.materia?.nombre || horarioAsignado.materia_nombre || 'N/A';
                const seccionLetra = obtenerLetraSeccion(horarioAsignado);
                const seccionPildora = formatearPildoraSeccion(horarioAsignado);
                const seccionId = horarioAsignado.cargaAcademica?.seccion?.id || horarioAsignado.seccion_id;
                const asignacionId = horarioAsignado.id;
                const aulaId = horarioAsignado.aula_id;
                const aulaNombre = horarioAsignado.aula_nombre || 'N/A';
                const aulaTipo = horarioAsignado.aula_tipo || 'Aula Regular';
                const esFijada = !!horarioAsignado.fijada;

                // Determinar si la materia es especializada (basado en el nombre)
                const materiasEspecializadas = ['Arte y Patrimonio', 'Ciencias Naturales', 'Biología', 'Física', 'Química'];
                const esEspecializada = materiasEspecializadas.some(m => materiaNombre.includes(m));

                // Construir opciones de aula filtrando por tipo de aula
                const aulaSelectGlobal = document.getElementById('aula');
                let aulaOptionsPersistida = '';
                if (aulaSelectGlobal) {
                    if (esEspecializada) {
                        for (let i = 0; i < aulaSelectGlobal.options.length; i++) {
                            const option = aulaSelectGlobal.options[i];
                            if (option.value !== '' && (option.dataset.tipo || '') !== 'Aula Regular') {
                                const selected = option.value == aulaId ? 'selected' : '';
                                aulaOptionsPersistida += `<option value="${option.value}" ${selected}>${option.text}</option>`;
                            }
                        }
                    } else {
                        // Materia regular: solo el aula fija de la sección, no todas las regulares
                        const aulaFija = obtenerAulaRegularDeSeccion(seccionId);
                        if (aulaFija) {
                            aulaOptionsPersistida += `<option value="${aulaFija.id}" selected>${aulaFija.nombre}</option>`;
                        } else {
                            aulaOptionsPersistida += `<option value="">Sin aula regular asignada a esta sección</option>`;
                        }
                    }
                }

                const lockIconHtml = `
                    <button type="button" class="btn-lock-toggle ${esFijada ? 'text-warning' : 'text-muted'}" 
                            title="${esFijada ? 'Clase fijada (clic para desbloquear)' : 'Clase no fijada (clic para fijar)'}" 
                            onclick="toggleFijarAsignacion(${asignacionId}, event)">
                        <i class="fas ${esFijada ? 'fa-lock' : 'fa-lock-open'}"></i>
                    </button>
                `;
                const draggableAttr = esFijada ? 'draggable="false"' : 'draggable="true"';
                const fijadaCls = esFijada ? ' clase-fijada' : '';

                return `
                    <div class="assigned-docente${fijadaCls}" ${draggableAttr} data-asignacion-id="${asignacionId}" data-fijada="${esFijada}">
                        <div class="adm-mini">
                            <span class="adm-mini-nombre">${docenteNombre}</span>
                            <span class="adm-mini-seccion">${seccionPildora} ${lockIconHtml}</span>
                        </div>
                        <div class="adm-full">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="docente-nombre">${docenteNombre}</span>
                                ${lockIconHtml}
                            </div>
                            <span class="docente-cedula">${docenteCedula}</span>
                            <span class="docente-materia">${materiaNombre}</span>
                            <select class="pildora-selector" disabled>
                                <option>${seccionLetra === 'Sin Sección' ? 'Sin Sección' : `Sección ${seccionLetra}`}</option>
                            </select>
                            <select class="pildora-selector" data-asignacion-id="${asignacionId}" data-aula-actual="${aulaId}" onchange="actualizarAula(this)" ${!esEspecializada || esFijada ? 'disabled' : ''}>
                                ${aulaOptionsPersistida}
                            </select>
                            <div class="aula-error-message"></div>
                            ${!esFijada ? `<button class="btn-eliminar-docente" onclick="eliminarAsignacionPersistida(${asignacionId}, event)">×</button>` : ''}
                        </div>
                    </div>
                `;
            }

            // Alternar estado de fijación en asignación guardada
            async function toggleFijarAsignacion(asignacionId, event) {
                if (event) {
                    event.stopPropagation();
                    event.preventDefault();
                }

                try {
                    const response = await fetch(`/api/horario/${asignacionId}/fijar`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        mostrarNotificacion(data.error || 'Error al cambiar estado de fijación', 'error');
                        return;
                    }

                    const idx = horarioData.findIndex(h => h.id == asignacionId);
                    if (idx !== -1) {
                        horarioData[idx].fijada = data.fijada;
                    }

                    mostrarNotificacion(data.mensaje || (data.fijada ? 'Clase fijada exitosamente' : 'Clase desbloqueada exitosamente'), 'success');
                    renderizarCalendario();
                } catch (error) {
                    console.error('Error al fijar/desfijar asignación:', error);
                    mostrarNotificacion('Error de conexión con el servidor', 'error');
                }
            }
            window.toggleFijarAsignacion = toggleFijarAsignacion;

            // Alternar estado de fijación en asignación local no guardada
            function toggleFijarLocal(key, index, event) {
                if (event) {
                    event.stopPropagation();
                    event.preventDefault();
                }
                const arr = horarioAsignaciones[key];
                if (Array.isArray(arr) && arr[index]) {
                    arr[index].fijada = !arr[index].fijada;
                    renderizarCalendario();
                    mostrarNotificacion(arr[index].fijada ? 'Clase fijada localmente' : 'Clase desbloqueada localmente', 'info');
                }
            }
            window.toggleFijarLocal = toggleFijarLocal;

            // Función para manejar el cambio de sección en una asignación
            function cambiarSeccionAsignacion(selectElement) {
                const key = selectElement.dataset.key;
                const index = parseInt(selectElement.dataset.index, 10);
                const arr = horarioAsignaciones[key];
                if (!Array.isArray(arr) || !arr[index]) return;

                const nuevaSecId = selectElement.value;
                let nombreSec = selectElement.options[selectElement.selectedIndex]?.text?.trim() || '';

                if ((!nombreSec || nombreSec.includes('Seleccione')) && typeof seccionesGlobales !== 'undefined' && Array.isArray(seccionesGlobales)) {
                    const encontrada = seccionesGlobales.find(s => String(s.id) === String(nuevaSecId));
                    if (encontrada) nombreSec = encontrada.nombre;
                }

                arr[index].seccionId = nuevaSecId;
                arr[index].seccion_id = nuevaSecId;
                arr[index].seccion_nombre = (nombreSec && !nombreSec.includes('Seleccione')) ? nombreSec : '';
                arr[index].seccion = nuevaSecId ? { id: nuevaSecId, nombre: arr[index].seccion_nombre } : null;

                const aulaRegular = obtenerAulaRegularDeSeccion(nuevaSecId);
                if (aulaRegular) arr[index].aulaId = aulaRegular.id;

                renderizarCalendario();
            }

            // Función para mostrar modal de confirmación
            function mostrarModalConfirmacion(mensajeHtml, onConfirmar) {
                // Crear overlay
                const overlay = document.createElement('div');
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                `;

                // Crear modal
                const modal = document.createElement('div');
                modal.style.cssText = `
                    background: white;
                    border-radius: 12px;
                    padding: 24px;
                    max-width: 500px;
                    width: 90%;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
                `;

                modal.innerHTML = `
                    <h3 style="margin: 0 0 16px 0; font-size: 18px; color: var(--gray-900);">Confirmar intercambio</h3>
                    <div style="margin-bottom: 20px; color: var(--gray-700); line-height: 1.5;">${mensajeHtml}</div>
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button id="btnCancelar" style="
                            padding: 10px 20px;
                            border: 1px solid var(--gray-300);
                            background: white;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 14px;
                            color: var(--gray-700);
                        ">Cancelar</button>
                        <button id="btnConfirmar" style="
                            padding: 10px 20px;
                            border: none;
                            background: var(--primary);
                            color: white;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 14px;
                            font-weight: 600;
                        ">Confirmar intercambio</button>
                    </div>
                `;

                overlay.appendChild(modal);
                document.body.appendChild(overlay);

                // Event listeners
                document.getElementById('btnCancelar').addEventListener('click', () => {
                    document.body.removeChild(overlay);
                });

                document.getElementById('btnConfirmar').addEventListener('click', () => {
                    document.body.removeChild(overlay);
                    onConfirmar();
                });

                // Cerrar al hacer clic fuera del modal
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) {
                        document.body.removeChild(overlay);
                    }
                });
            }

            // Función auxiliar para obtener aula regular de una sección
            function obtenerAulaRegularDeSeccion(seccionId) {
                if (!seccionId) return null;
                const aulaSelect = document.getElementById('aula');
                for (let i = 0; i < aulaSelect.options.length; i++) {
                    const option = aulaSelect.options[i];
                    if (option.dataset.tipo === 'Aula Regular' && option.dataset.seccion == seccionId) {
                        return { id: option.value, nombre: option.text };
                    }
                }
                return null;
            }

            // Función para mostrar notificaciones flotantes (Toasts) estándar de Bootstrap 5 / AdminLTE
            function mostrarToast(mensaje, tipo = 'error', titulo = null, persistente = true) {
                const container = document.getElementById('toast-container');
                if (!container) return;

                let icon = 'fa-exclamation-circle';
                let defaultTitulo = 'Error de Asignación';
                let headerBg = 'bg-danger text-white';
                let btnCloseClass = 'btn-close btn-close-white text-white';

                if (tipo === 'error' || tipo === 'danger') {
                    icon = 'fa-exclamation-circle';
                    defaultTitulo = 'Error de Asignación';
                    headerBg = 'bg-danger text-white';
                    btnCloseClass = 'btn-close btn-close-white text-white';
                } else if (tipo === 'warning') {
                    icon = 'fa-exclamation-triangle';
                    defaultTitulo = 'Advertencia';
                    headerBg = 'bg-warning text-dark';
                    btnCloseClass = 'btn-close text-dark';
                } else if (tipo === 'success') {
                    icon = 'fa-check-circle';
                    defaultTitulo = 'Operación Exitosa';
                    headerBg = 'bg-success text-white';
                    btnCloseClass = 'btn-close btn-close-white text-white';
                } else if (tipo === 'info') {
                    icon = 'fa-info-circle';
                    defaultTitulo = 'Información';
                    headerBg = 'bg-info text-white';
                    btnCloseClass = 'btn-close btn-close-white text-white';
                }

                // Elemento toast estándar Bootstrap 5: SIN clases de color en el contenedor raíz
                const toast = document.createElement('div');
                toast.className = 'toast shadow-sm border-0';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');

                toast.innerHTML = `
                    <div class="toast-header ${headerBg}">
                        <i class="fas ${icon} me-2 mr-2"></i>
                        <strong class="me-auto mr-auto">${titulo || defaultTitulo}</strong>
                        <small class="me-2 mr-2 ${tipo === 'warning' ? 'text-muted' : 'text-white-50'}">Ahora</small>
                        <button type="button" class="${btnCloseClass} ms-auto ml-auto close border-0 bg-transparent" data-bs-dismiss="toast" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body">
                        ${mensaje}
                    </div>
                `;

                // Botón de cierre "X" (elimina el toast)
                const btnCerrar = toast.querySelector('button');
                if (btnCerrar) {
                    btnCerrar.addEventListener('click', () => {
                        toast.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateX(110%)';
                        setTimeout(() => toast.remove(), 200);
                    });
                }

                container.appendChild(toast);

                // Errores y advertencias no se auto-eliminan (persisten hasta clic en "X").
                // Solo si no es persistente (ej. info o éxito), se auto-oculta tras 6s
                if (!persistente) {
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            toast.style.opacity = '0';
                            toast.style.transform = 'translateX(110%)';
                            setTimeout(() => toast.remove(), 300);
                        }
                    }, 6000);
                }
            }

            // Función centralizada para mostrar notificaciones mediante Toasts persistentes
            function mostrarNotificacion(mensaje, tipo = 'info', persistente = null) {
                // Por requerimiento: errores y advertencias de arrastre, solapamiento y guardado
                // son persistentes (NO desaparecen automáticamente hasta que el usuario hace clic en la "X").
                const esPersistente = persistente !== null ? persistente : (tipo === 'error' || tipo === 'warning');
                mostrarToast(mensaje, tipo, null, esPersistente);
            }

            // Continuar a la vista del calendario
            btnContinuar.addEventListener('click', async function() {
                const nivel = nivelSelect.value;
                const area = areaFormacionSelect.value;
                
                if (!nivel || !area) {
                    mostrarNotificacion('Por favor, seleccione tanto el nivel académico como el área de formación', 'warning');
                    return;
                }

                // Actualizar header de contexto
                actualizarContexto();

                // Mostrar vista del calendario pero deshabilitar interacción durante carga
                seccionNivel.style.display = 'none';
                vistaCalendario.style.display = 'flex';
                vistaCalendario.style.pointerEvents = 'none';
                vistaCalendario.style.opacity = '0.5';

                try {
                    // Cargar datos en paralelo y esperar a que terminen
                    await Promise.all([
                        cargarDatosAPI(),
                        cargarDocentesAPI(nivel)
                    ]);
                    filtrarAulasPorSeccion();
                } catch (error) {
                    console.error('Error al cargar datos:', error);
                    mostrarNotificacion('Error al cargar datos del calendario', 'error');
                } finally {
                    // Restaurar interacción
                    vistaCalendario.style.pointerEvents = '';
                    vistaCalendario.style.opacity = '';
                }
            });

            // Cambiar nivel académico y recargar grilla
            nivelSelect.addEventListener('change', function() {
                if (vistaCalendario.style.display !== 'none') {
                    // Limpiar asignaciones locales
                    horarioAsignaciones = {};
                    modoSeleccionActivo = false;
                    asignacionesSeleccionadas.clear();
                    actualizarBarraFlotante();
                    btnModoSeleccion.classList.remove('activo');
                    document.querySelectorAll('.drop-zone').forEach(dz => {
                        dz.classList.remove('modo-seleccion', 'seleccionado');
                    });
                    // Recargar datos con el nuevo filtro
                    cargarDatosAPI();
                }
            });

            // Cargar docentes según nivel
            function cargarDocentes(nivel) {
                const docentes = docentesPorNivel[nivel] || [];
                
                if (docentes.length === 0) {
                    docentesList.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-user-tie"></i>
                            <p>Sin datos</p>
                            <small class="text-muted">No hay docentes disponibles para este nivel</small>
                        </div>
                    `;
                    return;
                }

                docentesList.innerHTML = docentes.map(docente => `
                    <div class="docente-card" draggable="true" data-id="${docente.id}" data-nombre="${docente.nombre}" data-area="${docente.area}">
                        <div class="docente-name">${docente.nombre}</div>
                        <div class="docente-area">
                            <i class="fas fa-book"></i>
                            ${docente.area}
                        </div>
                    </div>
                `).join('');
            }

            // Filtrar docentes por sección
            const seccionSelect = document.getElementById('seccion');
            seccionSelect.addEventListener('change', function() {
                horarioAsignaciones = {};
                modoSeleccionActivo = false;
                asignacionesSeleccionadas.clear();
                actualizarBarraFlotante();
                btnModoSeleccion.classList.remove('activo');
                document.querySelectorAll('.drop-zone').forEach(dz => {
                    dz.classList.remove('modo-seleccion', 'seleccionado');
                });
                const nivel = nivelSelect.value;
                cargarDocentesAPI(nivel);
                filtrarAulasPorSeccion();
                // Recargar datos de asignaciones con el nuevo filtro
                cargarDatosAPI();
            });

            // Función para filtrar aulas por sección
            function filtrarAulasPorSeccion() {
                const seccionId = seccionSelect.value;
                const aulaSelect = document.getElementById('aula');
                const opciones = aulaSelect.querySelectorAll('option');

                opciones.forEach(opcion => {
                    if (opcion.value === '') {
                        // Mantener siempre la opción "Todas las aulas"
                        opcion.style.display = 'block';
                        return;
                    }

                    const tipoAula = opcion.dataset.tipo;
                    const seccionAsignada = opcion.dataset.seccion;

                    if (tipoAula === 'Aula Regular') {
                        // Aulas regulares: solo mostrar si está asignada a la sección seleccionada
                        if (seccionId && seccionAsignada === seccionId) {
                            opcion.style.display = 'block';
                        } else {
                            // Si no hay sección seleccionada o no coincide, ocultar aulas regulares
                            opcion.style.display = 'none';
                        }
                    } else {
                        // Aulas no regulares: siempre mostrar
                        opcion.style.display = 'block';
                    }
                });

                // Resetear selección si la opción seleccionada ya no es visible
                if (aulaSelect.value !== '') {
                    const opcionSeleccionada = aulaSelect.options[aulaSelect.selectedIndex];
                    if (opcionSeleccionada.style.display === 'none') {
                        aulaSelect.value = '';
                    }
                }
            }

            // Inicializar Drag and Drop
            function inicializarDragAndDrop() {
                // Salvaguarda para evitar inicialización múltiple
                if (dragAndDropInicializado) {
                    return;
                }

                // Eventos para los docentes (draggable)
                document.addEventListener('dragstart', function(e) {
                    const docenteCard = e.target.closest('.docente-card');
                    const assignedDocente = e.target.closest('.assigned-docente');
                    
                    if (docenteCard) {
                        // Arrastrando un docente nuevo desde la lista lateral
                        draggedDocente = {
                            id: docenteCard.dataset.id,
                            nombre: docenteCard.dataset.nombre,
                            area: docenteCard.dataset.area,
                            cedula: docenteCard.dataset.cedula || 'N/A',
                            areas: docentesCache[docenteCard.dataset.id]?.areas || [],
                            secciones: docentesCache[docenteCard.dataset.id]?.secciones || []
                        };
                        draggedAsignacion = null;
                        docenteCard.classList.add('dragging');
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', docenteCard.dataset.id);
                    } else if (assignedDocente) {
                        // Si la asignación está fijada (candado), no permitir arrastrar
                        if (assignedDocente.dataset.fijada === 'true' || assignedDocente.getAttribute('data-fijada') === 'true') {
                            e.preventDefault();
                            mostrarNotificacion('Esta clase está fijada y no se puede mover', 'warning');
                            return;
                        }

                        // Arrastrando una asignación existente
                        const dropZoneOrigen = assignedDocente.closest('.drop-zone');
                        draggedAsignacion = {
                            localKey: `${dropZoneOrigen.dataset.day}-${dropZoneOrigen.dataset.block}`,
                            asignacionId: assignedDocente.dataset.asignacionId || null
                        };
                        draggedDocente = null;
                        assignedDocente.classList.add('dragging');
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', 'asignacion-existente');
                    }
                });

                document.addEventListener('dragend', function(e) {
                    if (e.target.classList.contains('docente-card') || e.target.closest('.assigned-docente')) {
                        const draggingElement = e.target.classList.contains('docente-card') ? e.target : e.target.closest('.assigned-docente');
                        if (draggingElement) {
                            draggingElement.classList.remove('dragging');
                        }
                        draggedDocente = null;
                        draggedAsignacion = null;
                    }
                });

                // Eventos para las zonas de drop
                document.addEventListener('dragover', function(e) {
                    const dropZone = e.target.closest('.drop-zone');
                    if (dropZone && !dropZone.classList.contains('celda-reservada')) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        dropZone.classList.add('drag-over');
                    }
                });

                document.addEventListener('dragleave', function(e) {
                    const dropZone = e.target.closest('.drop-zone');
                    if (dropZone) {
                        dropZone.classList.remove('drag-over');
                    }
                });

                document.addEventListener('drop', function(e) {
                    const dropZone = e.target.closest('.drop-zone');
                    
                    if (!dropZone) return;

                    // Si la celda está reservada, no permitir soltar
                    if (dropZone.classList.contains('celda-reservada')) {
                        e.preventDefault();
                        mostrarNotificacion('El bloque seleccionado está reservado institucionalmente', 'warning');
                        return;
                    }
                    
                    e.preventDefault();
                    dropZone.classList.remove('drag-over');

                    // Si es una asignación existente, usar la lógica de mover
                    if (draggedAsignacion) {
                        moverAsignacion(draggedAsignacion, dropZone);
                        draggedAsignacion = null;
                        return;
                    }

                    // Si es un docente nuevo
                    if (!draggedDocente) {
                        return;
                    }

                    const day = parseInt(dropZone.dataset.day);
                    const block = parseInt(dropZone.dataset.block);
                    const key = `${day}-${block}`;

                    const areaFormacionId = document.getElementById('areaFormacion')?.value;
                    const seccionFiltro = document.getElementById('seccion')?.value || '';

                    if (!areaFormacionId) {
                        mostrarNotificacion('Por favor, seleccione un área de formación antes de asignar', 'warning');
                        return;
                    }

                    // Modo Sección Específica: verificar si la sección ya tiene clase en este bloque
                    if (seccionFiltro !== '') {
                        const tieneClase = horarioData.some(h => 
                            Number(h.dia) === day && 
                            Number(h.bloque) === block && 
                            String(h.seccion_id || h.cargaAcademica?.seccion_id) === String(seccionFiltro)
                        ) || (horarioAsignaciones[key] && horarioAsignaciones[key].some(a => String(a.seccionId) === String(seccionFiltro)));

                        if (tieneClase) {
                            mostrarNotificacion('Esta sección ya tiene una clase en este bloque', 'error');
                            return;
                        }
                    }

                    // Inicializar array si no existe
                    if (!Array.isArray(horarioAsignaciones[key])) {
                        horarioAsignaciones[key] = [];
                    }

                    // Evitar duplicar el mismo docente en el mismo bloque
                    const yaExisteDocente = horarioAsignaciones[key].some(a => String(a.id) === String(draggedDocente.id))
                        || horarioData.some(h => Number(h.dia) === day && Number(h.bloque) === block && String(h.docente_id || h.cargaAcademica?.docente_id) === String(draggedDocente.id));

                    if (yaExisteDocente) {
                        mostrarNotificacion('El docente ya tiene una clase en este bloque', 'error');
                        return;
                    }

                    const seccionSelectEl = document.getElementById('seccion');
                    let defaultSeccionId = '';
                    let defaultSeccionNombre = '';

                    if (seccionFiltro !== '') {
                        defaultSeccionId = String(seccionFiltro);
                        defaultSeccionNombre = seccionSelectEl ? seccionSelectEl.options[seccionSelectEl.selectedIndex]?.text?.trim() : '';
                    } else if (draggedDocente.secciones && draggedDocente.secciones.length > 0) {
                        defaultSeccionId = String(draggedDocente.secciones[0].id);
                        defaultSeccionNombre = draggedDocente.secciones[0].nombre;
                    } else if (typeof seccionesGlobales !== 'undefined' && Array.isArray(seccionesGlobales)) {
                        const gradoId = document.getElementById('nivelAcademico')?.value;
                        const primeraSec = seccionesGlobales.find(s => !gradoId || String(s.grado_id) === String(gradoId));
                        if (primeraSec) {
                            defaultSeccionId = String(primeraSec.id);
                            defaultSeccionNombre = primeraSec.nombre;
                        }
                    }

                    if (!defaultSeccionNombre && defaultSeccionId && typeof seccionesGlobales !== 'undefined' && Array.isArray(seccionesGlobales)) {
                        const secObj = seccionesGlobales.find(s => String(s.id) === String(defaultSeccionId));
                        if (secObj) defaultSeccionNombre = secObj.nombre;
                    }

                    horarioAsignaciones[key].push({
                        ...draggedDocente,
                        areaFormacionId: String(areaFormacionId),
                        seccionId: defaultSeccionId,
                        seccion_id: defaultSeccionId,
                        seccion_nombre: defaultSeccionNombre,
                        seccion: defaultSeccionId ? { id: defaultSeccionId, nombre: defaultSeccionNombre } : null,
                        aulaId: ''
                    });

                    renderizarCalendario();
                    mostrarNotificacion(`Docente ${draggedDocente.nombre} asignado. Seleccione sección y aula.`, 'success');
                });

                // Marcar como inicializado
                dragAndDropInicializado = true;
            }

            // Función para mover asignación existente
            async function moverAsignacion(origen, dropZoneDestino) {
                if (dropZoneDestino.classList.contains('celda-reservada')) {
                    mostrarNotificacion('El bloque seleccionado está reservado institucionalmente', 'warning');
                    return;
                }

                const diaNum = parseInt(dropZoneDestino.dataset.day);
                const bloqueNum = parseInt(dropZoneDestino.dataset.block);
                const destKey = `${diaNum}-${bloqueNum}`;

                // Si se soltó en el mismo lugar, no hacer nada
                if (destKey === origen.localKey) {
                    return;
                }

                // Identificar información de la asignación origen
                let origenData = null;
                let origenSeccionId = null;
                let origenDocenteId = null;
                let origenSeccionNombre = '';

                if (origen.asignacionId) {
                    origenData = horarioData.find(h => h.id == origen.asignacionId);
                    if (origenData?.fijada) {
                        mostrarNotificacion('Esta clase está fijada y no se puede mover', 'warning');
                        return;
                    }
                    origenSeccionId = origenData?.seccion_id || origenData?.cargaAcademica?.seccion_id || origenData?.cargaAcademica?.seccion?.id;
                    origenDocenteId = origenData?.docente_id || origenData?.cargaAcademica?.docente_id;
                    origenSeccionNombre = origenData?.seccion_nombre || origenData?.cargaAcademica?.seccion?.nombre || '';
                } else {
                    const origenArr = Array.isArray(horarioAsignaciones[origen.localKey])
                        ? horarioAsignaciones[origen.localKey]
                        : (horarioAsignaciones[origen.localKey] ? [horarioAsignaciones[origen.localKey]] : []);
                    if (origenArr.some(a => a.fijada)) {
                        mostrarNotificacion('Esta clase está fijada y no se puede mover', 'warning');
                        return;
                    }
                    const primera = origenArr[0];
                    origenSeccionId = primera?.seccionId;
                    origenDocenteId = primera?.id;
                    origenSeccionNombre = primera?.secciones?.find(s => s.id == origenSeccionId)?.nombre || '';
                }

                // Filtro de sección activo en la interfaz
                const seccionFiltro = document.getElementById('seccion')?.value || '';

                // Asignaciones presentes en la celda destino (excluyendo la que se está moviendo)
                const persistidasEnDestino = horarioData.filter(h => 
                    Number(h.dia) === diaNum && 
                    Number(h.bloque) === bloqueNum && 
                    h.id != origen.asignacionId
                );

                const localesEnDestino = Array.isArray(horarioAsignaciones[destKey])
                    ? horarioAsignaciones[destKey]
                    : (horarioAsignaciones[destKey] ? [horarioAsignaciones[destKey]] : []);

                // 3. MODO "Sección Específica":
                if (seccionFiltro !== '') {
                    // Si el usuario está viendo una sección específica y ya hay una clase en esa celda
                    const tieneClaseEnCelda = persistidasEnDestino.some(h => {
                        const sId = h.seccion_id || h.cargaAcademica?.seccion_id || h.cargaAcademica?.seccion?.id;
                        return String(sId) === String(seccionFiltro);
                    }) || localesEnDestino.some(a => String(a.seccionId) === String(seccionFiltro));

                    if (tieneClaseEnCelda || dropZoneDestino.querySelector('.assigned-docente')) {
                        mostrarNotificacion('Esta sección ya tiene una clase en este bloque', 'error');
                        return;
                    }
                }

                // 4. MODO "Todas las secciones":
                if (seccionFiltro === '') {
                    // Validar si la misma sección ya tiene clase en este bloque
                    if (origenSeccionId) {
                        const seccionOcupada = persistidasEnDestino.some(h => {
                            const sId = h.seccion_id || h.cargaAcademica?.seccion_id || h.cargaAcademica?.seccion?.id;
                            return String(sId) === String(origenSeccionId);
                        }) || localesEnDestino.some(a => String(a.seccionId) === String(origenSeccionId));

                        if (seccionOcupada) {
                            mostrarNotificacion(`Esta sección ya tiene una clase en este bloque`, 'error');
                            return;
                        }
                    }

                    // Validar si el mismo docente ya tiene clase en este bloque
                    if (origenDocenteId) {
                        const docenteOcupado = persistidasEnDestino.some(h => {
                            const dId = h.docente_id || h.cargaAcademica?.docente_id;
                            return String(dId) === String(origenDocenteId);
                        }) || localesEnDestino.some(a => String(a.id) === String(origenDocenteId));

                        if (docenteOcupado) {
                            mostrarNotificacion('El docente ya tiene una clase en este bloque', 'error');
                            return;
                        }
                    }
                }

                // Procesar movimiento:
                if (origen.asignacionId) {
                    const diaObj = diasSemana.find(d => Number(d.id || d.dia) === diaNum);

                    try {
                        const response = await fetch(`/api/horario/${origen.asignacionId}/posicion`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify({
                                dia: diaNum,
                                bloque: bloqueNum
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            mostrarNotificacion(data.error || data.message || 'Error al mover asignación', 'error');
                            return;
                        }

                        const asignacionIndex = horarioData.findIndex(h => h.id == origen.asignacionId);
                        if (asignacionIndex !== -1) {
                            horarioData[asignacionIndex].dia = diaNum;
                            horarioData[asignacionIndex].dia_nombre = diaObj?.nombre_dia || diaObj?.nombre || 'Día ' + diaNum;
                            horarioData[asignacionIndex].bloque = bloqueNum;
                            horarioData[asignacionIndex].bloque_nombre = 'Bloque ' + bloqueNum;
                        }

                        mostrarNotificacion('Asignación movida exitosamente', 'success');
                        renderizarCalendario();
                    } catch (error) {
                        console.error('Error al mover asignación:', error);
                        mostrarNotificacion('Error de conexión al servidor', 'error');
                    }
                } else {
                    const origenArr = Array.isArray(horarioAsignaciones[origen.localKey])
                        ? horarioAsignaciones[origen.localKey]
                        : (horarioAsignaciones[origen.localKey] ? [horarioAsignaciones[origen.localKey]] : []);

                    if (!Array.isArray(horarioAsignaciones[destKey])) {
                        horarioAsignaciones[destKey] = [];
                    }

                    // Agregar (append) al array de destino
                    horarioAsignaciones[destKey].push(...origenArr);
                    delete horarioAsignaciones[origen.localKey];
                    renderizarCalendario();
                    mostrarNotificacion('Asignación movida localmente', 'success');
                }
            }

            // Función para asignar aula local
            window.asignarAulaLocal = function(selectElement) {
                const key = selectElement.dataset.key;
                const index = parseInt(selectElement.dataset.index, 10);
                const arr = horarioAsignaciones[key];
                if (Array.isArray(arr) && arr[index]) {
                    arr[index].aulaId = selectElement.value;
                    mostrarNotificacion('Aula asignada correctamente', 'success');
                }
            };

            // Función para asignar sección local
            window.asignarSeccionLocal = function(selectElement) {
                const key = selectElement.dataset.key;
                const index = parseInt(selectElement.dataset.index || 0, 10);
                const arr = horarioAsignaciones[key];
                const target = Array.isArray(arr) ? arr[index] : arr;
                if (target) {
                    const secId = selectElement.value;
                    let secNom = selectElement.options[selectElement.selectedIndex]?.text?.trim() || '';
                    if ((!secNom || secNom.includes('Seleccione')) && typeof seccionesGlobales !== 'undefined') {
                        const secObj = seccionesGlobales.find(s => String(s.id) === String(secId));
                        if (secObj) secNom = secObj.nombre;
                    }
                    target.seccionId = secId;
                    target.seccion_id = secId;
                    target.seccion_nombre = (secNom && !secNom.includes('Seleccione')) ? secNom : '';
                    target.seccion = secId ? { id: secId, nombre: target.seccion_nombre } : null;
                    renderizarCalendario();
                    mostrarNotificacion('Sección asignada correctamente', 'success');
                }
            };

            // Eliminar asignación
            window.eliminarAsignacion = function(key, index, event) {
                if (event) event.stopPropagation();
                const arr = horarioAsignaciones[key];
                if (!Array.isArray(arr)) return;

                arr.splice(index, 1);
                if (arr.length === 0) delete horarioAsignaciones[key];

                renderizarCalendario();
                mostrarNotificacion('Asignación eliminada correctamente', 'success');
            };

            // Guardar horario
            document.getElementById('btnGuardarHorario').addEventListener('click', async function() {
                const asignacionesLocalesKeys = Object.keys(horarioAsignaciones);
                const asignacionesExistentes = horarioData && horarioData.length > 0;

                // Verificar si hay asignaciones
                if (asignacionesLocalesKeys.length === 0 && !asignacionesExistentes) {
                    mostrarNotificacion('No hay asignaciones en el horario', 'warning');
                    return;
                }

                // Si solo hay asignaciones existentes y ninguna local pendiente, redirigir directamente
                if (asignacionesLocalesKeys.length === 0 && asignacionesExistentes) {
                    mostrarNotificacion('El horario ya está generado y guardado.', 'success');
                    window.location.href = "{{ route('admin.horario.index') }}";
                    return;
                }

                // Construir array completo para persistir
                const asignacionesParaGuardar = [];

                // 1. Conservar asignaciones existentes que no hayan sido sobreescritas localmente
                if (asignacionesExistentes) {
                    horarioData.forEach(h => {
                        const hDia = Number(h.dia);
                        const hBloque = Number(h.bloque);
                        const celdaKey = `${hDia}-${hBloque}`;
                        if (!horarioAsignaciones[celdaKey] || horarioAsignaciones[celdaKey].length === 0) {
                            asignacionesParaGuardar.push({
                                carga_academica_id: h.carga_academica_id || h.cargaAcademica?.id || null,
                                docente_id: h.docente_id || h.cargaAcademica?.docente_id,
                                materia_id: h.materia_id || h.cargaAcademica?.area_formacion_id,
                                seccion_id: h.seccion_id || h.cargaAcademica?.seccion_id,
                                aula_id: parseInt(h.aula_id || 0),
                                dia: hDia,
                                bloque: hBloque,
                                fijada: !!h.fijada
                            });
                        }
                    });
                }

                // 2. Agregar asignaciones locales
                asignacionesLocalesKeys.forEach(key => {
                    const arr = horarioAsignaciones[key];
                    const items = Array.isArray(arr) ? arr : (arr ? [arr] : []);
                    if (items.length === 0) return;

                    const [diaStr, bloqueStr] = key.split('-');
                    const numDia = parseInt(diaStr);
                    const numBloque = parseInt(bloqueStr);

                    items.forEach(asignacion => {
                        let aulaIdFinal = asignacion.aulaId;
                        if (!aulaIdFinal && asignacion.seccionId) {
                            const aulaRegular = obtenerAulaRegularDeSeccion(asignacion.seccionId);
                            if (aulaRegular) {
                                aulaIdFinal = aulaRegular.id;
                            }
                        }

                        asignacionesParaGuardar.push({
                            carga_academica_id: asignacion.carga_academica_id || null,
                            docente_id: parseInt(asignacion.id),
                            materia_id: parseInt(asignacion.areaFormacionId),
                            seccion_id: parseInt(asignacion.seccionId),
                            aula_id: parseInt(aulaIdFinal || 0),
                            dia: numDia,
                            bloque: numBloque,
                            fijada: false
                        });
                    });
                });

                // Mostrar indicador de carga
                const btn = this;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                btn.disabled = true;
                mostrarPantallaCarga('Guardando...');

                try {
                    const response = await fetch('/api/horario/guardar-asignaciones', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            asignaciones: asignacionesParaGuardar,
                            anio_escolar_id: anioEscolarActivo ? anioEscolarActivo.id : null
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        mostrarNotificacion(data.error || data.message || 'Error al guardar horario', 'error');
                        return;
                    }

                    if (data.success) {
                        mostrarNotificacion(data.mensaje, 'success');
                        window.location.href = "{{ route('admin.horario.index') }}";
                    } else {
                        mostrarNotificacion(data.mensaje, 'warning');
                        console.error('Errores al guardar:', data.errores);
                    }

                } catch (error) {
                    console.error('Error al guardar horario:', error);
                    mostrarNotificacion('Error de conexión al servidor', 'error');
                } finally {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    ocultarPantallaCarga();
                }
            });

            // Función para actualizar aula de una asignación
            window.actualizarAula = async function(selectElement) {
                const asignacionId = selectElement.dataset.asignacionId;
                const nuevaAulaId = selectElement.value;
                const errorContainer = selectElement.parentElement.querySelector('.aula-error-message');

                if (!nuevaAulaId) {
                    errorContainer.textContent = 'Seleccione un aula';
                    errorContainer.classList.add('visible');
                    return;
                }

                mostrarPantallaCarga('Guardando...');

                try {
                    const response = await fetch(`/api/horario/${asignacionId}/aula`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ aula_id: nuevaAulaId })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const errMsg = data.error || data.message || 'Error al actualizar el aula';
                        errorContainer.textContent = errMsg;
                        errorContainer.classList.add('visible');
                        mostrarNotificacion(errMsg, 'error');
                        return;
                    }

                    // Success: actualizar visualmente
                    errorContainer.classList.remove('visible');
                    mostrarNotificacion('Aula actualizada correctamente', 'success');

                } catch (error) {
                    console.error('Error al actualizar aula:', error);
                    errorContainer.textContent = 'Error de conexión al servidor';
                    errorContainer.classList.add('visible');
                    mostrarNotificacion('Error de conexión al servidor', 'error');
                } finally {
                    ocultarPantallaCarga();
                }
            };

            // ===== LÓGICA DE VALIDACIÓN PREVIA (M4 / P6.5) =====
            const btnGenerar = document.getElementById('btnGenerarHorario');
            const modalValidacionEl = document.getElementById('modalValidacionHorario');
            let modalValidacionObj = null;

            function obtenerModalValidacion() {
                if (!modalValidacionObj && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    modalValidacionObj = bootstrap.Modal.getInstance(modalValidacionEl) || new bootstrap.Modal(modalValidacionEl);
                }
                return modalValidacionObj;
            }

            function renderizarResultadoValidacion(data) {
                const errores = data.errores || [];
                const avisos = data.avisos || [];
                const info = data.info || [];

                const alertaEstado = document.getElementById('validacionEstadoAlerta');
                const seccionErrores = document.getElementById('seccionErrores');
                const listaErrores = document.getElementById('listaErrores');
                const countErrores = document.getElementById('countErrores');

                const seccionAvisos = document.getElementById('seccionAvisos');
                const listaAvisos = document.getElementById('listaAvisos');
                const countAvisos = document.getElementById('countAvisos');

                const seccionInfo = document.getElementById('seccionInfo');
                const listaInfo = document.getElementById('listaInfo');
                const countInfo = document.getElementById('countInfo');

                listaErrores.innerHTML = '';
                listaAvisos.innerHTML = '';
                listaInfo.innerHTML = '';

                // 1. Render Errores
                countErrores.textContent = errores.length;
                if (errores.length > 0) {
                    seccionErrores.classList.remove('d-none');
                    errores.forEach(err => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item list-group-item-danger border-start border-danger border-4 mb-2 rounded shadow-sm';
                        item.innerHTML = `
                            <div class="fw-bold text-danger d-flex align-items-center">
                                <i class="fas fa-times-circle me-2"></i> ${escapeValidacionHtml(err.mensaje)}
                            </div>
                            <div class="small text-muted mt-1 ps-4">${escapeValidacionHtml(err.detalle)}</div>
                        `;
                        listaErrores.appendChild(item);
                    });
                } else {
                    seccionErrores.classList.add('d-none');
                }

                // 2. Render Avisos
                countAvisos.textContent = avisos.length;
                if (avisos.length > 0) {
                    seccionAvisos.classList.remove('d-none');
                    avisos.forEach(av => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item list-group-item-warning border-start border-warning border-4 mb-2 rounded shadow-sm';
                        item.innerHTML = `
                            <div class="fw-bold text-dark d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i> ${escapeValidacionHtml(av.mensaje)}
                            </div>
                            <div class="small text-muted mt-1 ps-4">${escapeValidacionHtml(av.detalle)}</div>
                        `;
                        listaAvisos.appendChild(item);
                    });
                } else {
                    seccionAvisos.classList.add('d-none');
                }

                // 3. Render Info
                countInfo.textContent = info.length;
                if (info.length > 0) {
                    seccionInfo.classList.remove('d-none');
                    info.forEach(inf => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item list-group-item-info border-start border-info border-4 mb-2 rounded shadow-sm';
                        item.innerHTML = `
                            <div class="fw-bold text-primary d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i> ${escapeValidacionHtml(inf.mensaje)}
                            </div>
                            <div class="small text-secondary mt-1 ps-4">${escapeValidacionHtml(inf.detalle)}</div>
                        `;
                        listaInfo.appendChild(item);
                    });
                } else {
                    seccionInfo.classList.add('d-none');
                }

                // 4. Mensaje general de estado
                if (errores.length > 0) {
                    alertaEstado.innerHTML = `
                        <div class="alert alert-danger d-flex align-items-center mb-0 shadow-sm border-0">
                            <i class="fas fa-ban me-3 fs-3"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">Se detectaron ${errores.length} inconsistencia(s) crítica(s)</h6>
                                <p class="mb-0 small">El proceso de generación automática ha sido bloqueado para evitar colisiones irresolubles. Debe corregir estos errores en la configuración y carga académica.</p>
                            </div>
                        </div>
                    `;
                } else {
                    alertaEstado.innerHTML = `
                        <div class="alert alert-success d-flex align-items-center mb-0 shadow-sm border-0">
                            <i class="fas fa-check-circle me-3 fs-3"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">Validación lógicamente viable</h6>
                                <p class="mb-0 small">No se detectaron errores críticos que impidan el armado del horario.</p>
                            </div>
                        </div>
                    `;
                }

                const modal = obtenerModalValidacion();
                if (modal) {
                    modal.show();
                } else if (typeof $ !== 'undefined') {
                    $('#modalValidacionHorario').modal('show');
                }
            }

            function escapeValidacionHtml(str) {
                if (!str) return '';
                const p = document.createElement('p');
                p.textContent = str;
                return p.innerHTML;
            }

            // Función para generar horario automáticamente con validación como barrera de entrada
            document.getElementById('btnGenerarHorario').addEventListener('click', async function() {
                const btn = this;
                
                if (!anioEscolarActivo) {
                    mostrarNotificacion('No hay año escolar activo. Configure uno antes de generar horarios.', 'error');
                    return;
                }

                // Mostrar indicador de carga
                btn.classList.add('loading');
                btn.disabled = true;
                mostrarPantallaCarga('Validando viabilidad de la carga académica...');

                try {
                    // Obtener filtros seleccionados del Paso 1
                    const nivelSelect = document.getElementById('nivelAcademico');
                    const areaFormacionSelect = document.getElementById('areaFormacion');
                    
                    const gradoId = nivelSelect?.value || null;
                    const areaFormacionId = areaFormacionSelect?.value || null;

                    // 1. Ejecutar validación previa
                    const valResponse = await fetch('/api/horario/validar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            anio_escolar_id: anioEscolarActivo.id,
                            grado_id: gradoId
                        })
                    });

                    const valData = await valResponse.json();

                    if (!valResponse.ok) {
                        mostrarNotificacion(valData.error || valData.message || 'Error al validar viabilidad del horario', 'error');
                        return;
                    }

                    // Si se detectan errores críticos, abrir modal y detener el proceso por completo
                    if (valData.errores && valData.errores.length > 0) {
                        renderizarResultadoValidacion(valData);
                        mostrarNotificacion('Generación cancelada: Se detectaron inconsistencias críticas en la carga académica.', 'error');
                        return;
                    }

                    // 2. Si no hay errores críticos, proceder con la generación
                    mostrarPantallaCarga('Generando horario, esto puede tardar unos segundos...');

                    const response = await fetch('/api/horario/generar', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ 
                            anio_escolar_id: anioEscolarActivo.id,
                            total_dias: 5,
                            total_bloques: 8,
                            grado_id: gradoId,
                            area_formacion_id: areaFormacionId
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        mostrarNotificacion(data.error || data.message || 'Error al generar horario', 'error');
                        return;
                    }

                    // Recargar datos del horario para mostrar las asignaciones generadas
                    await cargarDatosAPI();
                    
                    mostrarNotificacion(`Horario generado con ${data.asignaciones.length} asignaciones`, 'success');

                    // Marcar docentes con conflicto en la sidebar
                    marcarDocentesConConflicto(data.conflictos);

                    // Poblar panel de conflictos con información detallada y diagnóstico de cuellos de botella
                    poblarPanelConflictos(data.conflictos, data.diagnostico);

                    // Mostrar panel de conflictos si existen
                    if (data.conflictos && data.conflictos.length > 0) {
                        document.getElementById('conflictosPanel').classList.add('visible');
                        mostrarNotificacion(`Se detectaron ${data.conflictos.length} conflictos que requieren atención manual`, 'warning');
                    } else {
                        document.getElementById('conflictosPanel').classList.remove('visible');
                    }

                } catch (error) {
                    console.error('Error al generar horario:', error);
                    mostrarNotificacion('Error de conexión al servidor', 'error');
                } finally {
                    // Restaurar botón
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    ocultarPantallaCarga();
                }
            });

            // Función para mostrar conflictos (usando notificaciones persistentes)
            function mostrarConflictos(conflictos) {
                // Limpiar conflictos anteriores
                document.querySelectorAll('[id^="conflicto-"]').forEach(el => el.remove());
                
                // Mostrar cada conflicto como notificación persistente
                conflictos.forEach((conflicto, index) => {
                    const mensaje = `${conflicto.docente_nombre || 'Docente desconocido'} - ${conflicto.materia_nombre || 'Materia desconocida'}: Sección ${conflicto.seccion_nombre || 'Sección desconocida'}, ${conflicto.bloques_pendientes || 'No especificado'} bloques pendientes`;
                    mostrarNotificacionConflicto(mensaje, 'conflicto-' + index + '-' + Date.now());
                });
            }

            // Función para ocultar conflictos
            function ocultarConflictos() {
                document.querySelectorAll('[id^="conflicto-"]').forEach(el => el.remove());
            }

            // Variables para modo de selección
            let modoSeleccionActivo = false;
            let asignacionesSeleccionadas = new Set();

            const btnModoSeleccion = document.getElementById('btnModoSeleccion');
            const barraSeleccionFlotante = document.getElementById('barraSeleccionFlotante');
            const contadorSeleccionados = document.getElementById('contadorSeleccionados');
            const btnEliminarSeleccionados = document.getElementById('btnEliminarSeleccionados');
            const btnCancelarSeleccion = document.getElementById('btnCancelarSeleccion');

            // Eliminar una asignación persistida individualmente
            window.eliminarAsignacionPersistida = async function(asignacionId, event) {
                if (event) event.stopPropagation();

                mostrarPantallaCarga('Eliminando...');
                try {
                    const response = await fetch(`/api/horario/${asignacionId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        mostrarNotificacion(data.error || data.message || 'Error al eliminar asignación', 'error');
                        return;
                    }

                    horarioData = horarioData.filter(h => h.id != asignacionId);
                    renderizarCalendario();
                    mostrarNotificacion('Asignación eliminada correctamente', 'success');
                } catch (error) {
                    console.error('Error al eliminar asignación:', error);
                    mostrarNotificacion('Error de conexión al servidor', 'error');
                } finally {
                    ocultarPantallaCarga();
                }
            };

            // Alternar modo selección
            btnModoSeleccion.addEventListener('click', function() {
                modoSeleccionActivo = !modoSeleccionActivo;
                asignacionesSeleccionadas.clear();
                actualizarBarraFlotante();
                btnModoSeleccion.classList.toggle('activo', modoSeleccionActivo);

                document.querySelectorAll('.drop-zone').forEach(dz => {
                    dz.classList.toggle('modo-seleccion', modoSeleccionActivo);
                    dz.classList.remove('seleccionado');
                });
            });

            btnCancelarSeleccion.addEventListener('click', function() {
                modoSeleccionActivo = false;
                asignacionesSeleccionadas.clear();
                actualizarBarraFlotante();
                btnModoSeleccion.classList.remove('activo');
                document.querySelectorAll('.drop-zone').forEach(dz => {
                    dz.classList.remove('modo-seleccion', 'seleccionado');
                });
            });

            // Click en celda durante modo selección (delegación de eventos)
            document.addEventListener('click', function(e) {
                if (!modoSeleccionActivo) return;

                const dropZone = e.target.closest('.drop-zone');
                if (!dropZone) return;

                const assignedDocente = dropZone.querySelector('.assigned-docente[data-asignacion-id]');
                if (!assignedDocente) return; // solo permitir seleccionar celdas con asignación persistida

                const asignacionId = assignedDocente.dataset.asignacionId;

                if (asignacionesSeleccionadas.has(asignacionId)) {
                    asignacionesSeleccionadas.delete(asignacionId);
                    dropZone.classList.remove('seleccionado');
                } else {
                    asignacionesSeleccionadas.add(asignacionId);
                    dropZone.classList.add('seleccionado');
                }

                actualizarBarraFlotante();
            });

            function actualizarBarraFlotante() {
                const cantidad = asignacionesSeleccionadas.size;
                contadorSeleccionados.textContent = `${cantidad} seleccionado${cantidad !== 1 ? 's' : ''}`;
                barraSeleccionFlotante.classList.toggle('visible', modoSeleccionActivo && cantidad > 0);
            }

            // Eliminar en lote
            btnEliminarSeleccionados.addEventListener('click', async function() {
                if (asignacionesSeleccionadas.size === 0) return;

                const ids = Array.from(asignacionesSeleccionadas);

                mostrarPantallaCarga('Eliminando asignaciones...');
                try {
                    const response = await fetch('/api/horario/eliminar-multiples', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ ids })
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        mostrarNotificacion(data.error || data.message || 'Error al eliminar asignaciones', 'error');
                        return;
                    }

                    horarioData = horarioData.filter(h => !ids.includes(String(h.id)));
                    asignacionesSeleccionadas.clear();
                    modoSeleccionActivo = false;
                    btnModoSeleccion.classList.remove('activo');
                    actualizarBarraFlotante();
                    renderizarCalendario();
                    document.querySelectorAll('.drop-zone').forEach(dz => dz.classList.remove('modo-seleccion', 'seleccionado'));

                    mostrarNotificacion(data.mensaje, 'success');
                } catch (error) {
                    console.error('Error al eliminar asignaciones:', error);
                    mostrarNotificacion('Error de conexión al servidor', 'error');
                } finally {
                    ocultarPantallaCarga();
                }
            });
        });
    </script>
@endsection

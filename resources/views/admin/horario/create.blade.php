@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <style>
        .horario-container {
            display: flex;
            gap: 20px;
            height: auto;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-docentes {
            width: 280px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            background: var(--primary);
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header i {
            font-size: 18px;
        }

        .sidebar-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 14px;
        }

        .docentes-list {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }

        .docente-card {
            background: var(--gray-50);
            border-radius: var(--radius);
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            cursor: grab;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            user-select: none;
        }

        .docente-card:hover {
            background: var(--gray-100);
            border-color: var(--primary);
            transform: translateX(3px);
        }

        .docente-card.dragging {
            opacity: 0.5;
            cursor: grabbing;
        }

        .docente-card.selected {
            border-color: var(--primary);
            background: var(--primary-light);
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

        .docente-horas {
            font-size: 0.625rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
            padding-top: 0.25rem;
            border-top: 1px solid var(--gray-200);
        }

        .docente-horas i {
            color: var(--warning);
            font-size: 0.5625rem;
        }

        .selection-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .selection-row {
            display: flex;
            gap: 20px;
            align-items: flex-end;
        }

        .form-group-modern {
            flex: 1;
        }

       
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

        .drop-zone:hover {
            background: var(--gray-100);
            border-color: var(--primary);
        }

        .drop-zone.drag-over {
            background: var(--primary-light);
            border-color: var(--primary);
            border-style: solid;
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

        .drop-zone .assigned-docente .docente-cedula {
            font-size: 0.6875rem;
            opacity: 0.75;
        }

        .drop-zone .assigned-docente .docente-materia {
            font-size: 0.7rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }

        .drop-zone .assigned-docente .pildora-selector {
            width: 100%;
            background: white;
            color: #1f2937;
            border: none;
            border-radius: 999px;
            padding: 0.4rem 0.9rem;
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

        .drop-zone.ocupado-por-otro {
            background: #2c3e50;
            border-color: #1a252f;
            cursor: not-allowed;
        }

        .drop-zone.tiene-conflicto {
            border: 2px dashed #ef4444;
            background: rgba(239, 68, 68, 0.05);
            position: relative;
        }

        .drop-zone.tiene-conflicto::after {
            content: '⚠️';
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 0.75rem;
            z-index: 10;
        }

        .drop-zone.tiene-conflicto:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .drop-zone.hueco-disponible {
            border: 2px dashed #10b981;
            background: rgba(16, 185, 129, 0.05);
        }

        .drop-zone.hueco-disponible::after {
            content: '✓';
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 0.75rem;
            color: #10b981;
            z-index: 10;
        }

        .conflicto-tooltip {
            position: absolute;
            background: #1f2937;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            max-width: 250px;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .conflicto-tooltip.visible {
            opacity: 1;
        }

        .conflicto-tooltip .tooltip-titulo {
            font-weight: 600;
            margin-bottom: 4px;
            color: #fca5a5;
        }

        .conflicto-tooltip .tooltip-motivo {
            color: #d1d5db;
            margin-bottom: 6px;
        }

        .conflicto-tooltip .tooltip-sugerencias {
            font-size: 0.6875rem;
            color: #9ca3af;
        }

        .conflicto-tooltip .tooltip-sugerencias li {
            margin-bottom: 2px;
        }

        .btn-eliminar-docente {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.2s ease;
        }

        .btn-eliminar-docente:hover {
            background: var(--danger);
            transform: scale(1.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            opacity: 0.9;
        }

        .btn-save {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-save:hover {
            opacity: 0.9;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .notifications-container {
            margin-top: 1.5625rem;
        }

        .notification {
            padding: 0.9375rem;
            border-radius: var(--radius);
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .notification:last-child {
            margin-bottom: 0;
        }

        .notification.error {
            background: var(--danger-light);
            border-color: var(--danger);
        }

        .notification.error i {
            color: var(--danger);
            font-size: 1.25rem;
        }

        .notification.error strong {
            color: var(--danger);
        }

        .notification.success {
            background: var(--success-light);
            border-color: var(--success);
        }

        .notification.success i {
            color: var(--success);
            font-size: 1.25rem;
        }

        .notification.success strong {
            color: var(--success);
        }

        .notification.warning {
            background: var(--warning-light);
            border-color: var(--warning);
        }

        .notification.warning i {
            color: var(--warning);
            font-size: 1.25rem;
        }

        .notification.warning strong {
            color: var(--warning);
        }

        .aula-selector-container {
            margin-top: 0.5rem;
        }

        .aula-selector {
            width: 100%;
            padding: 0.25rem 0.5rem;
            font-size: 0.6875rem;
            border: 1px solid var(--gray-300);
            border-radius: 4px;
            background: white;
            color: var(--gray-700);
        }

        .aula-selector:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }

        .aula-error-message {
            font-size: 0.625rem;
            color: var(--danger);
            margin-top: 0.25rem;
            display: none;
        }

        .aula-error-message.visible {
            display: block;
        }

        .btn-generar-horario {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .btn-generar-horario:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-generar-horario:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-generar-horario .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .btn-generar-horario.loading .spinner {
            display: inline-block;
        }

        .btn-generar-horario.loading .btn-text {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .conflictos-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            display: none;
        }

        .conflictos-panel.visible {
            display: block;
        }

        .conflictos-header {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .conflictos-header i {
            font-size: 20px;
        }

        .conflictos-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .conflictos-body {
            padding: 20px;
        }

        .conflicto-item {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .conflicto-item:last-child {
            margin-bottom: 0;
        }

        .conflicto-icon {
            width: 40px;
            height: 40px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .conflicto-info {
            flex: 1;
        }

        .conflicto-info strong {
            display: block;
            color: #991b1b;
            margin-bottom: 4px;
        }

        .conflicto-info p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .conflicto-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Overlay de carga */
        .overlay-carga {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            flex-direction: column;
            gap: 20px;
        }

        .overlay-carga.oculto {
            display: none;
        }

        .overlay-carga .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .overlay-carga p {
            color: white;
            font-size: 16px;
            margin: 0;
            font-weight: 500;
        }

        /* Estilos para aula texto */
        .aula-texto {
            background: #f3f4f6;
            color: #374151;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .aula-texto.aula-sin-asignar {
            background: #fef3c7;
            color: #92400e;
        }

        .aula-container {
            margin-top: 8px;
        }

        /* Notificaciones de conflictos persistente */
        .notificaciones-conflictos {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9990;
            width: 400px;
            max-width: 90vw;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notificacion-conflicto {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .notificacion-conflicto-mensaje {
            flex: 1;
            color: #991b1b;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notificacion-conflicto-mensaje::before {
            content: '⚠️';
            font-size: 16px;
        }

        .notificacion-conflicto-cerrar {
            background: none;
            border: none;
            color: #991b1b;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .notificacion-conflicto-cerrar:hover {
            background: #fee2e2;
        }

        .conflicto-badge {
            background: #fecaca;
            color: #991b1b;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .context-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .context-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        .context-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .context-item i {
            font-size: 16px;
            opacity: 0.9;
        }

        .context-separator {
            opacity: 0.5;
            font-size: 0.875rem;
        }

        .btn-back-mini {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 500;
        }

        .btn-back-mini:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        .docente-card.tiene-conflicto {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .docente-card.tiene-conflicto .docente-name {
            color: #991b1b;
        }

        .drop-zone.modo-seleccion {
            cursor: pointer;
        }

        .drop-zone.seleccionado {
            outline: 3px solid #007bff;
            outline-offset: -3px;
        }

        .btn-modo-seleccion {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-modo-seleccion.activo {
            background: #007bff;
        }

        .barra-seleccion-flotante {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            padding: 12px 20px;
            display: none;
            align-items: center;
            gap: 15px;
            z-index: 9995;
        }

        .barra-seleccion-flotante.visible {
            display: flex;
        }

        .btn-eliminar-seleccionados {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-cancelar-seleccion {
            background: #e5e7eb;
            color: #374151;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
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
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-conflicto-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
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
            padding: 20px;
        }

        .modal-conflicto-info {
            margin-bottom: 20px;
        }

        .modal-conflicto-info strong {
            display: block;
            color: #374151;
            margin-bottom: 4px;
            font-size: 0.875rem;
        }

        .modal-conflicto-info span {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .modal-conflicto-motivo {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 0 6px 6px 0;
        }

        .modal-conflicto-motivo strong {
            color: #991b1b;
            display: block;
            margin-bottom: 6px;
        }

        .modal-conflicto-motivo p {
            color: #6b7280;
            margin: 0;
            font-size: 0.875rem;
        }

        .modal-conflicto-sugerencias {
            margin-bottom: 20px;
        }

        .modal-conflicto-sugerencias h4 {
            color: #374151;
            margin: 0 0 10px 0;
            font-size: 0.875rem;
        }

        .modal-conflicto-sugerencias ul {
            margin: 0;
            padding-left: 20px;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .modal-conflicto-sugerencias li {
            margin-bottom: 6px;
        }

        .modal-conflicto-acciones {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-modal-primario {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s ease;
        }

        .btn-modal-primario:hover {
            background: #2563eb;
        }

        .btn-modal-secundario {
            background: #e5e7eb;
            color: #374151;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s ease;
        }

        .btn-modal-secundario:hover {
            background: #d1d5db;
        }

        /* Mejoras al panel de conflictos */
        .conflicto-item-detalles {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #fecaca;
        }

        .conflicto-tipo {
            display: inline-block;
            background: #fecaca;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.6875rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .conflicto-motivo-texto {
            color: #6b7280;
            font-size: 0.8125rem;
            margin-bottom: 8px;
        }

        .conflicto-sugerencias-texto {
            color: #9ca3af;
            font-size: 0.75rem;
        }

        .conflicto-sugerencias-texto ul {
            margin: 4px 0 0 0;
            padding-left: 16px;
        }

        .btn-ver-detalles-conflicto {
            background: #991b1b;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
            transition: background 0.2s ease;
        }

        .btn-ver-detalles-conflicto:hover {
            background: #7f1d1d;
        }

    </style>
@stop

@section('title', 'Crear Horario')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div>
                    <h1 class="title-main">Crear Horario</h1>
                    <p class="title-subtitle">Asignación de horarios académicos</p>
                </div>
            </div>
             <button class="btn-generar-horario" id="btnGenerarHorario">
                <span class="spinner"></span>
                <span class="btn-text">
                    <i class="fas fa-magic"></i>
                    Generar Horario
                </span>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        

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
                            <option value="{{ $grado->id }}">
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
                <button class="btn-filter" id="btnContinuar" disabled>
                    <i class="fas fa-arrow-right"></i>
                    Continuar
                </button>
            </div>
        </div>

        <!-- Vista del calendario (oculta inicialmente) -->
        <div class="horario-container" id="vistaCalendario" style="display: none;">
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
                    <button class="btn-back-mini" id="btnVolverPaso1">
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

            <!-- Sidebar de docentes -->
            <div class="sidebar-docentes">
                <div class="sidebar-header">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h5>Docentes Disponibles</h5>
                </div>
                <div class="docentes-list" id="docentesList">
                    <div class="empty-state">
                        <i class="fas fa-user-tie"></i>
                        <p>Seleccione un nivel académico para ver los docentes disponibles</p>
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

            // Función para poblar el panel de conflictos con información detallada
            function poblarPanelConflictos(conflictos) {
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

            // Variables para almacenar datos de la API
            let diasSemana = [];
            let bloquesHorario = [];
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

                    diasSemana = await diasResponse.json();
                    bloquesHorario = await bloquesResponse.json();
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
                    const docentes = await response.json();

                    console.log('Docentes cargados:', docentes);

                    renderizarDocentes(docentes);
                } catch (error) {
                    console.error('Error al cargar docentes:', error);
                    mostrarNotificacion('Error al cargar docentes', 'error');
                }
            }

            // Función para renderizar docentes en el sidebar
            function renderizarDocentes(docentes) {
                // Popular cache de docentes para uso posterior
                docentes.forEach(d => { docentesCache[d.id] = d; });

                if (docentes.length === 0) {
                    docentesList.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-user-tie"></i>
                            <p>No hay docentes disponibles</p>
                        </div>
                    `;
                    return;
                }

                // Obtener el área de formación seleccionada para filtrar la visualización
                const areaFormacionId = areaFormacionSelect.value;
                const seccionId = document.getElementById('seccion').value;

                docentesList.innerHTML = docentes.map(docente => {
                    const nombre = docente.nombre_completo || 'Sin nombre';
                    const horasAcademicas = docente.horas_academicas || 0;

                    // Filtrar áreas de formación según el filtro seleccionado
                    let areasAMostrar = docente.areas || [];
                    if (areaFormacionId) {
                        // Si hay un filtro de área, mostrar solo esa área si el docente la tiene
                        areasAMostrar = areasAMostrar.filter(area => {
                            // Necesitamos comparar con el nombre del área seleccionado
                            const areaNombre = areaFormacionSelect.options[areaFormacionSelect.selectedIndex]?.text;
                            return area === areaNombre;
                        });
                    }

                    const areaTexto = areasAMostrar.length > 0 ? areasAMostrar.join(', ') : 'Sin área asignada';

                    // Filtrar secciones según el filtro seleccionado
                    let seccionesAMostrar = docente.secciones || [];
                    if (seccionId) {
                        seccionesAMostrar = seccionesAMostrar.filter(seccion => seccion.id == seccionId);
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
                        return h.seccion_id == seccionId;
                    });
                }

                if (areaFormacionId) {
                    horarioFiltrado = horarioFiltrado.filter(h => {
                        return h.materia_id == areaFormacionId;
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

                    // Celda de tiempo
                    const timeCell = document.createElement('td');
                    timeCell.className = 'time-cell';
                    timeCell.innerHTML = `
                        <strong>Bloque ${index + 1}</strong>
                        <br>
                        <small>${bloque.hora_inicio} - ${bloque.hora_fin}</small>
                    `;
                    tr.appendChild(timeCell);

                    // Celdas de días
                    diasSemana.forEach(dia => {
                        const td = document.createElement('td');
                        td.className = 'drop-zone';
                        const diaNombre = dia.nombre_dia || dia.nombre;
                        td.dataset.day = diaNombre.toLowerCase();
                        td.dataset.block = index + 1;

                        // Buscar si hay horario asignado para este día y bloque (usando datos filtrados)
                        const horarioAsignado = horarioFiltrado.find(h => {
                            const apiDiaNombre = h.dia_nombre;
                            const apiBloqueId = h.bloque_id;
                            return apiDiaNombre && apiDiaNombre.toLowerCase() === diaNombre.toLowerCase() && apiBloqueId === bloque.id;
                        });

                        // Verificar si hay asignación local (drag and drop)
                        const key = `${diaNombre.toLowerCase()}-${index + 1}`;
                        const asignacionLocal = horarioAsignaciones[key];

                        // Priorizar asignación local sobre datos de API
                        if (asignacionLocal) {
                            td.innerHTML = renderizarAsignacionLocalHTML(key, asignacionLocal);
                        } else if (horarioAsignado) {
                            const docenteNombre = horarioAsignado.docente_nombre || 'N/A';
                            const docenteCedula = horarioAsignado.docente_cedula || 'N/A';
                            const materiaNombre = horarioAsignado.materia_nombre || 'N/A';
                            const seccionNombre = horarioAsignado.seccion_nombre || 'N/A';
                            const asignacionId = horarioAsignado.id;
                            const aulaId = horarioAsignado.aula_id;
                            const aulaNombre = horarioAsignado.aula_nombre || 'N/A';
                            const aulaTipo = horarioAsignado.aula_tipo || 'Aula Regular';

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
                                    const aulaFija = obtenerAulaRegularDeSeccion(horarioAsignado.seccion_id);
                                    if (aulaFija) {
                                        aulaOptionsPersistida += `<option value="${aulaFija.id}" selected>${aulaFija.nombre}</option>`;
                                    } else {
                                        aulaOptionsPersistida += `<option value="">Sin aula regular asignada a esta sección</option>`;
                                    }
                                }
                            }

                            td.innerHTML = `
                                <div class="assigned-docente" draggable="true" data-asignacion-id="${asignacionId}">
                                    <span class="docente-nombre">${docenteNombre}</span>
                                    <span class="docente-cedula">${docenteCedula}</span>
                                    <span class="docente-materia">${materiaNombre}</span>
                                    <select class="pildora-selector" disabled>
                                        <option>${seccionNombre}</option>
                                    </select>
                                    <select class="pildora-selector" data-asignacion-id="${asignacionId}" data-aula-actual="${aulaId}" onchange="actualizarAula(this)" ${!esEspecializada ? 'disabled' : ''}>
                                        ${aulaOptionsPersistida}
                                    </select>
                                    <div class="aula-error-message"></div>
                                    <button class="btn-eliminar-docente" onclick="eliminarAsignacionPersistida(${asignacionId}, event)">×</button>
                                </div>
                            `;
                        } else {
                            // Celda vacía pero visible para permitir drag and drop
                            td.innerHTML = '';
                        }

                        tr.appendChild(td);
                    });

                    tbody.appendChild(tr);
                });

                console.log('Total drop-zones creadas:', document.querySelectorAll('.drop-zone').length);

                console.log('Calendario renderizado con', tbody.children.length, 'filas');
            }

            // Función para renderizar HTML de asignación local (unificada)
            function renderizarAsignacionLocalHTML(key, asignacion) {
                // Crear selector de sección usando solo las secciones del docente
                let seccionHtml = '';
                if (asignacion.secciones && asignacion.secciones.length > 0) {
                    let seccionOptions = '<option value="">Seleccione sección</option>';
                    asignacion.secciones.forEach(seccion => {
                        const selected = asignacion.seccionId == seccion.id ? 'selected' : '';
                        seccionOptions += `<option value="${seccion.id}" ${selected}>${seccion.nombre}</option>`;
                    });
                    seccionHtml = `
                        <select class="pildora-selector" id="seccion-local-${key}" data-key="${key}" onchange="cambiarSeccionAsignacion(this)">
                            ${seccionOptions}
                        </select>
                    `;
                } else {
                    seccionHtml = `<span class="docente-cedula">El docente no tiene secciones asignadas</span>`;
                }

                // Determinar si la materia es especializada (basado en el nombre)
                const materiasEspecializadas = ['Arte y Patrimonio', 'Ciencias Naturales', 'Biología', 'Física', 'Química'];
                const esEspecializada = materiasEspecializadas.some(m => asignacion.area.includes(m));

                // Filtrar aulas según si la materia es especializada
                let aulaHtml = '';
                const aulaSelect = document.getElementById('aula');
                if (aulaSelect) {
                    let aulaOptions = '<option value="">Seleccione aula</option>';
                    if (esEspecializada) {
                        for (let i = 0; i < aulaSelect.options.length; i++) {
                            const option = aulaSelect.options[i];
                            if (option.value !== '' && (option.dataset.tipo || '') !== 'Aula Regular') {
                                const selected = asignacion.aulaId == option.value ? 'selected' : '';
                                aulaOptions += `<option value="${option.value}" ${selected}>${option.text}</option>`;
                            }
                        }
                    } else {
                        // Materia regular: solo el aula fija de la sección, no todas las regulares
                        const aulaFija = obtenerAulaRegularDeSeccion(asignacion.seccionId);
                        if (aulaFija) {
                            aulaOptions += `<option value="${aulaFija.id}" selected>${aulaFija.nombre}</option>`;
                            // Auto-asignar de una vez ya que es la única opción válida
                            asignacion.aulaId = aulaFija.id;
                        } else {
                            aulaOptions += `<option value="">Sin aula regular asignada a esta sección</option>`;
                        }
                    }
                    aulaHtml = `
                        <select class="pildora-selector" id="aula-local-${key}" data-key="${key}" onchange="asignarAulaLocal(this)" ${!esEspecializada ? 'disabled' : ''}>
                            ${aulaOptions}
                        </select>
                    `;
                }

                return `
                    <div class="assigned-docente" draggable="true">
                        <span class="docente-nombre">${asignacion.nombre}</span>
                        <span class="docente-cedula">${asignacion.cedula || 'N/A'}</span>
                        <span class="docente-materia">${asignacion.area}</span>
                        ${seccionHtml}
                        ${aulaHtml}
                        <div class="aula-error-message"></div>
                        <button class="btn-eliminar-docente" onclick="eliminarAsignacion('${key}', event)">×</button>
                    </div>
                `;
            }

            // Función para manejar el cambio de sección en una asignación
            function cambiarSeccionAsignacion(selectElement) {
                const key = selectElement.dataset.key;
                const nuevaSeccionId = selectElement.value;
                const asignacion = horarioAsignaciones[key];
                
                if (!asignacion) return;

                // Actualizar el seccion_id en memoria
                asignacion.seccionId = nuevaSeccionId;

                // Buscar y asignar automáticamente el aula regular de la sección
                const aulaRegular = obtenerAulaRegularDeSeccion(nuevaSeccionId);
                if (aulaRegular) {
                    asignacion.aulaId = aulaRegular.id;
                    
                    // Actualizar visualmente el selector de aula
                    const aulaSelect = document.getElementById(`aula-local-${key}`);
                    if (aulaSelect) {
                        aulaSelect.value = aulaRegular.id;
                    }
                }
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

            // Función para mostrar notificaciones
            function mostrarNotificacion(mensaje, tipo = 'info') {
                const icon = tipo === 'error' ? 'fa-exclamation-circle' : 
                            tipo === 'success' ? 'fa-check-circle' : 
                            tipo === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
                
                const notification = document.createElement('div');
                notification.className = `notification ${tipo}`;
                notification.innerHTML = `
                    <i class="fas ${icon}"></i>
                    <div>
                        <strong>${mensaje}</strong>
                    </div>
                `;
                
                notificationsContainer.appendChild(notification);
                
                // Auto eliminar después de 5 segundos
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
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
                            <p>No hay docentes disponibles para este nivel</p>
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

                    if (tipoAula === 'regular') {
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
                    if (dropZone && !dropZone.classList.contains('ocupado-por-otro')) {
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
                    
                    e.preventDefault();
                    dropZone.classList.remove('drag-over');

                    // Si es una asignación existente, usar la lógica de mover
                    if (draggedAsignacion) {
                        moverAsignacion(draggedAsignacion, dropZone);
                        draggedAsignacion = null;
                        return;
                    }

                    // Si es un docente nuevo, usar la lógica existente
                    if (!draggedDocente || dropZone.classList.contains('ocupado-por-otro')) {
                        return;
                    }

                    const day = dropZone.dataset.day;
                    const block = dropZone.dataset.block;
                    const key = `${day}-${block}`;

                    // Verificar si ya hay un docente asignado
                    if (horarioAsignaciones[key]) {
                        mostrarNotificacion('Esta celda ya tiene un docente asignado. Elimine la asignación actual antes de agregar otra.', 'error');
                        return;
                    }

                    // Obtener el área de formación y sección actuales para guardar contexto
                    const areaFormacionId = document.getElementById('areaFormacion').value;
                    const seccionId = document.getElementById('seccion').value;

                    console.log('Guardando asignación con contexto:', {
                        key,
                        areaFormacionId,
                        seccionId,
                        draggedDocente
                    });

                    // Validar que se haya seleccionado un área de formación
                    if (!areaFormacionId) {
                        mostrarNotificacion('Por favor, seleccione un área de formación antes de asignar', 'warning');
                        return;
                    }

                    // Asignar docente con contexto
                    horarioAsignaciones[key] = {
                        ...draggedDocente,
                        areaFormacionId: String(areaFormacionId),
                        seccionId: seccionId ? String(seccionId) : '',
                        aulaId: ''
                    };

                    // Actualizar visualmente con selectores usando función unificada
                    dropZone.innerHTML = renderizarAsignacionLocalHTML(key, horarioAsignaciones[key]);

                    mostrarNotificacion(`Docente ${draggedDocente.nombre} asignado correctamente. Seleccione sección y aula.`, 'success');
                });

                // Marcar como inicializado
                dragAndDropInicializado = true;
            }

            // Función para mover asignación existente
            async function moverAsignacion(origen, dropZoneDestino) {
                const destKey = `${dropZoneDestino.dataset.day}-${dropZoneDestino.dataset.block}`;
                
                // Si se soltó en el mismo lugar, no hacer nada
                if (destKey === origen.localKey) {
                    return;
                }

                // Verificar si la celda destino está ocupada
                const destinoOcupado = horarioAsignaciones[destKey] || dropZoneDestino.querySelector('.assigned-docente');
                
                if (destinoOcupado) {
                    // Obtener información de la asignación destino
                    const destinoAsignacion = dropZoneDestino.querySelector('.assigned-docente');
                    const destinoAsignacionId = destinoAsignacion?.dataset.asignacionId;
                    
                    // Si ambas son asignaciones persistidas, ofrecer intercambio
                    if (origen.asignacionId && destinoAsignacionId) {
                        // Obtener información de ambas asignaciones desde horarioData
                        const origenData = horarioData.find(h => h.id == origen.asignacionId);
                        const destinoData = horarioData.find(h => h.id == destinoAsignacionId);
                        
                        if (origenData && destinoData) {
                            const origenDia = diasSemana.find(d => d.id == origenData.dia_id);
                            const destinoDia = diasSemana.find(d => d.id == destinoData.dia_id);
                            
                            const mensaje = `
                                <strong>[${origenDia?.nombre_dia || origenData.dia_nombre} - Bloque ${origenData.bloque_id}]:</strong><br>
                                Docente: ${origenData.docente_nombre}<br>
                                Sección: ${origenData.seccion_nombre}<br>
                                Aula: ${origenData.aula_nombre}<br><br>
                                <strong>⇄</strong><br><br>
                                <strong>[${destinoDia?.nombre_dia || destinoData.dia_nombre} - Bloque ${destinoData.bloque_id}]:</strong><br>
                                Docente: ${destinoData.docente_nombre}<br>
                                Sección: ${destinoData.seccion_nombre}<br>
                                Aula: ${destinoData.aula_nombre}
                            `;
                            
                            mostrarModalConfirmacion(mensaje, async () => {
                                try {
                                    const response = await fetch('/api/horario/intercambiar', {
                                        method: 'PUT',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                                        },
                                        body: JSON.stringify({
                                            horario_id_1: origen.asignacionId,
                                            horario_id_2: destinoAsignacionId
                                        })
                                    });
                                    
                                    const data = await response.json();
                                    
                                    if (!response.ok) {
                                        if (data.error === 'conflicto' && data.detalles) {
                                            const mensajes = data.detalles.map(d => d.mensaje).join('<br>');
                                            mostrarNotificacionConflicto('Conflicto al intercambiar: ' + mensajes, 'swap-' + Date.now());
                                        } else {
                                            mostrarNotificacion(data.error || 'Error al intercambiar asignaciones', 'error');
                                        }
                                        return;
                                    }
                                    
                                    // Actualizar horarioData en memoria con los datos actualizados
                                    const idx1 = horarioData.findIndex(h => h.id == data.asignacion1.id);
                                    const idx2 = horarioData.findIndex(h => h.id == data.asignacion2.id);
                                    
                                    if (idx1 !== -1) {
                                        horarioData[idx1] = {
                                            id: data.asignacion1.id,
                                            docente_id: data.asignacion1.docente_id,
                                            docente_nombre: data.asignacion1.docente?.persona 
                                                ? data.asignacion1.docente.persona.primer_nombre + ' ' + data.asignacion1.docente.persona.primer_apellido 
                                                : 'N/A',
                                            materia_id: data.asignacion1.materia_id,
                                            materia_nombre: data.asignacion1.materia?.nombre_area_formacion || 'N/A',
                                            seccion_id: data.asignacion1.seccion_id,
                                            seccion_nombre: data.asignacion1.seccion?.nombre || 'N/A',
                                            grado_id: data.asignacion1.seccion?.grado_id || null,
                                            grado_nombre: data.asignacion1.seccion?.grado 
                                                ? data.asignacion1.seccion.grado.numero_grado + '° Grado' 
                                                : 'N/A',
                                            aula_id: data.asignacion1.aula_id,
                                            aula_nombre: data.asignacion1.aula?.nombre_aula || 'N/A',
                                            aula_tipo: data.asignacion1.aula?.tipo_aula || 'N/A',
                                            dia_id: data.asignacion1.dia_id,
                                            dia_nombre: data.asignacion1.dia?.nombre_dia || data.asignacion1.dia_id,
                                            bloque_id: data.asignacion1.bloque_id,
                                            bloque_nombre: 'Bloque ' + data.asignacion1.bloque_id
                                        };
                                    }
                                    
                                    if (idx2 !== -1) {
                                        horarioData[idx2] = {
                                            id: data.asignacion2.id,
                                            docente_id: data.asignacion2.docente_id,
                                            docente_nombre: data.asignacion2.docente?.persona 
                                                ? data.asignacion2.docente.persona.primer_nombre + ' ' + data.asignacion2.docente.persona.primer_apellido 
                                                : 'N/A',
                                            materia_id: data.asignacion2.materia_id,
                                            materia_nombre: data.asignacion2.materia?.nombre_area_formacion || 'N/A',
                                            seccion_id: data.asignacion2.seccion_id,
                                            seccion_nombre: data.asignacion2.seccion?.nombre || 'N/A',
                                            grado_id: data.asignacion2.seccion?.grado_id || null,
                                            grado_nombre: data.asignacion2.seccion?.grado 
                                                ? data.asignacion2.seccion.grado.numero_grado + '° Grado' 
                                                : 'N/A',
                                            aula_id: data.asignacion2.aula_id,
                                            aula_nombre: data.asignacion2.aula?.nombre_aula || 'N/A',
                                            aula_tipo: data.asignacion2.aula?.tipo_aula || 'N/A',
                                            dia_id: data.asignacion2.dia_id,
                                            dia_nombre: data.asignacion2.dia?.nombre_dia || data.asignacion2.dia_id,
                                            bloque_id: data.asignacion2.bloque_id,
                                            bloque_nombre: 'Bloque ' + data.asignacion2.bloque_id
                                        };
                                    }
                                    
                                    mostrarNotificacion('Asignaciones intercambiadas exitosamente', 'success');
                                    renderizarCalendario();
                                } catch (error) {
                                    console.error('Error al intercambiar asignaciones:', error);
                                    mostrarNotificacion('Error de conexión al servidor', 'error');
                                }
                            });
                            return;
                        }
                    }
                    
                    // Si una es local y la otra persistida, no permitir intercambio
                    if ((origen.asignacionId && !destinoAsignacionId) || (!origen.asignacionId && destinoAsignacionId)) {
                        mostrarNotificacion('No se puede intercambiar una asignación guardada con una que aún no se ha guardado', 'warning');
                        return;
                    }
                    
                    // Si ambas son locales, intercambiar en memoria sin confirmación
                    if (!origen.asignacionId && !destinoAsignacionId) {
                        horarioAsignaciones[destKey] = horarioAsignaciones[origen.localKey];
                        delete horarioAsignaciones[origen.localKey];
                        renderizarCalendario();
                        mostrarNotificacion('Asignaciones intercambiadas localmente', 'success');
                        return;
                    }
                    
                    mostrarNotificacion('La celda destino ya está ocupada', 'error');
                    return;
                }

                if (origen.asignacionId) {
                    // Asignación persistida en BD: actualizar en backend
                    const diaNombre = dropZoneDestino.dataset.day;
                    const dia = diasSemana.find(d => 
                        (d.nombre_dia || d.nombre).toLowerCase() === diaNombre.toLowerCase()
                    );

                    if (!dia) {
                        mostrarNotificacion('Error: No se encontró el día correspondiente', 'error');
                        return;
                    }

                    const bloqueIndex = parseInt(dropZoneDestino.dataset.block) - 1;
                    const bloque = bloquesHorario[bloqueIndex];

                    if (!bloque) {
                        mostrarNotificacion('Error: No se encontró el bloque correspondiente', 'error');
                        return;
                    }

                    try {
                        const response = await fetch(`/api/horario/${origen.asignacionId}/posicion`, {
                            method: 'PUT',
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify({ 
                                dia_id: dia.id,
                                bloque_id: bloque.id
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            mostrarNotificacion(data.error || 'Error al mover asignación', 'error');
                            return;
                        }

                        // Actualizar horarioData en memoria en lugar de recargar todo
                        const asignacionIndex = horarioData.findIndex(h => h.id == origen.asignacionId);
                        if (asignacionIndex !== -1) {
                            horarioData[asignacionIndex].dia_id = dia.id;
                            horarioData[asignacionIndex].dia_nombre = dia.nombre_dia || dia.nombre;
                            horarioData[asignacionIndex].bloque_id = bloque.id;
                        }

                        mostrarNotificacion('Asignación movida exitosamente', 'success');
                        renderizarCalendario();
                    } catch (error) {
                        console.error('Error al mover asignación:', error);
                        mostrarNotificacion('Error de conexión al servidor', 'error');
                    }
                } else {
                    // Asignación solo local: mover en memoria y refrescar
                    horarioAsignaciones[destKey] = horarioAsignaciones[origen.localKey];
                    delete horarioAsignaciones[origen.localKey];
                    renderizarCalendario();
                    mostrarNotificacion('Asignación movida localmente', 'success');
                }
            }

            // Función para asignar aula local
            window.asignarAulaLocal = function(selectElement) {
                const key = selectElement.dataset.key;
                if (horarioAsignaciones[key]) {
                    horarioAsignaciones[key].aulaId = selectElement.value;
                    mostrarNotificacion('Aula asignada correctamente', 'success');
                }
            };

            // Función para asignar sección local
            window.asignarSeccionLocal = function(selectElement) {
                const key = selectElement.dataset.key;
                if (horarioAsignaciones[key]) {
                    horarioAsignaciones[key].seccionId = selectElement.value;
                    // Re-renderizar la celda inmediatamente para aplicar bloqueo de aula si corresponde
                    const dropZone = selectElement.closest('.drop-zone');
                    if (dropZone) {
                        dropZone.innerHTML = renderizarAsignacionLocalHTML(key, horarioAsignaciones[key]);
                    }
                    mostrarNotificacion('Sección asignada correctamente', 'success');
                }
            };

            // Eliminar asignación
            window.eliminarAsignacion = function(key, event) {
                if (event) {
                    event.stopPropagation();
                }
                
                delete horarioAsignaciones[key];
                const dropZone = document.querySelector(`.drop-zone[data-day="${key.split('-')[0]}"][data-block="${key.split('-')[1]}"]`);
                if (dropZone) {
                    dropZone.innerHTML = '';
                }
                
                mostrarNotificacion('Asignación eliminada correctamente', 'success');
            };

            // Guardar horario
            document.getElementById('btnGuardarHorario').addEventListener('click', async function() {
                const asignacionesLocales = Object.keys(horarioAsignaciones);
                const asignacionesExistentes = horarioData && horarioData.length > 0;

                // Verificar si hay asignaciones locales (drag & drop) o asignaciones existentes (generadas automáticamente)
                if (asignacionesLocales.length === 0 && !asignacionesExistentes) {
                    mostrarNotificacion('No hay asignaciones en el horario', 'warning');
                    return;
                }

                // Si solo hay asignaciones existentes (generadas automáticamente), redirigir directamente
                if (asignacionesLocales.length === 0 && asignacionesExistentes) {
                    mostrarNotificacion('El horario ya fue generado y guardado automáticamente', 'success');
                    window.location.href = "{{ route('admin.horario.index') }}";
                    return;
                }

                // Convertir asignaciones locales al formato que espera el backend
                const asignacionesParaGuardar = asignacionesLocales.map(key => {
                    const asignacion = horarioAsignaciones[key];
                    const [diaNombre, bloqueId] = key.split('-');
                    
                    // Encontrar el ID del día correspondiente al nombre
                    const dia = diasSemana.find(d => 
                        (d.nombre_dia || d.nombre).toLowerCase() === diaNombre.toLowerCase()
                    );
                    
                    // Si no hay aulaId asignada, usar el aula regular fija de la sección si existe
                    let aulaIdFinal = asignacion.aulaId;
                    if (!aulaIdFinal && asignacion.seccionId) {
                        const aulaRegular = obtenerAulaRegularDeSeccion(asignacion.seccionId);
                        if (aulaRegular) {
                            aulaIdFinal = aulaRegular.id;
                        }
                    }
                    
                    return {
                        docente_id: parseInt(asignacion.id),
                        materia_id: parseInt(asignacion.areaFormacionId),
                        seccion_id: parseInt(asignacion.seccionId),
                        aula_id: parseInt(aulaIdFinal || 0),
                        dia_id: dia ? dia.id : 1,
                        bloque_id: parseInt(bloqueId)
                    };
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ 
                            asignaciones: asignacionesParaGuardar,
                            anio_escolar_id: anioEscolarActivo ? anioEscolarActivo.id : null
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        mostrarNotificacion(data.error || 'Error al guardar horario', 'error');
                        return;
                    }

                    if (data.success) {
                        mostrarNotificacion(data.mensaje, 'success');
                        // Redirigir inmediatamente al inicio del módulo
                        window.location.href = "{{ route('admin.horario.index') }}";
                    } else {
                        mostrarNotificacion(data.mensaje, 'warning');
                        console.error('Errores al guardar:', data.errores);
                    }

                } catch (error) {
                    console.error('Error al guardar horario:', error);
                    mostrarNotificacion('Error de conexión al servidor', 'error');
                } finally {
                    // Restaurar botón
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
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ aula_id: nuevaAulaId })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        errorContainer.textContent = data.error || 'Error al actualizar el aula';
                        errorContainer.classList.add('visible');
                        mostrarNotificacion(data.error || 'Error al actualizar el aula', 'error');
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

            // Función para generar horario automáticamente
            document.getElementById('btnGenerarHorario').addEventListener('click', async function() {
                const btn = this;
                
                if (!anioEscolarActivo) {
                    mostrarNotificacion('No hay año escolar activo. Configure uno antes de generar horarios.', 'error');
                    return;
                }

                // Mostrar indicador de carga
                btn.classList.add('loading');
                btn.disabled = true;
                mostrarPantallaCarga('Generando horario, esto puede tardar unos segundos...');

                try {
                    // Obtener filtros seleccionados del Paso 1
                    const nivelSelect = document.getElementById('nivelAcademico');
                    const areaFormacionSelect = document.getElementById('areaFormacion');
                    
                    const gradoId = nivelSelect?.value || null;
                    const areaFormacionId = areaFormacionSelect?.value || null;

                    const response = await fetch('/api/horario/generar', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
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
                        mostrarNotificacion(data.error || 'Error al generar horario', 'error');
                        return;
                    }

                    // Recargar datos del horario para mostrar las asignaciones generadas
                    await cargarDatosAPI();
                    
                    mostrarNotificacion(`Horario generado con ${data.asignaciones.length} asignaciones`, 'success');

                    // Marcar docentes con conflicto en la sidebar
                    marcarDocentesConConflicto(data.conflictos);

                    // Poblar panel de conflictos con información detallada
                    poblarPanelConflictos(data.conflictos);

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
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        mostrarNotificacion(data.error || 'Error al eliminar asignación', 'error');
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ ids })
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        mostrarNotificacion(data.error || 'Error al eliminar asignaciones', 'error');
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

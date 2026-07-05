<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Carnet Estudiantil - {{ $estudiante['nombre'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .no-print-area {
            background-color: white;
            padding: 15px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            width: 100%;
            max-width: 680px;
            box-sizing: border-box;
        }

        .btn-imprimir {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
            transition: all 0.2s ease;
        }

        .btn-imprimir:hover {
            background: #4338ca;
        }

        .no-print-info {
            font-size: 13px;
            color: #6b7280;
            flex-grow: 1;
        }

        /* DISEÑO DE TARJETAS PARA IMPRESIÓN */
        .print-container {
            display: flex;
            gap: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .badge-card {
            width: 320px;
            height: 480px;
            border-radius: 16px;
            background: white;
            border: 2px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .badge-header {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: white;
            padding: 20px 15px;
            text-align: center;
        }

        .badge-header h4 {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
        }

        .badge-header p {
            font-size: 10px;
            margin: 4px 0 0;
            opacity: 0.8;
            font-weight: 600;
        }

        .badge-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px;
        }

        .badge-photo-wrapper {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #eef2ff;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            color: #d1d5db;
        }

        .badge-photo-placeholder svg {
            width: 60px;
            height: 60px;
            fill: currentColor;
        }

        .badge-student-name {
            font-size: 18px;
            font-weight: 800;
            color: #111827;
            text-align: center;
            margin: 0 0 8px 0;
            line-height: 1.2;
        }

        .badge-student-id {
            font-size: 15px;
            font-weight: 700;
            color: #4f46e5;
            background: #eef2ff;
            padding: 4px 16px;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .badge-info-grid {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        .badge-info-item {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        .badge-info-label {
            color: #6b7280;
            font-weight: 600;
        }

        .badge-info-value {
            color: #111827;
            font-weight: 700;
        }

        .badge-footer {
            height: 12px;
            background: linear-gradient(90deg, #4f46e5, #ec4899);
        }

        /* REVERSO */
        .badge-back-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 30px 24px;
            height: 100%;
            text-align: center;
            box-sizing: border-box;
        }

        .badge-back-rules {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.5;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .badge-qr-mock {
            width: 90px;
            height: 90px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cpath d='M0 0h30v10H10v20H0V0zm70 0h30v30H90V10H70V0zM0 70h10v20h20v10H0V70zm90 20H70v10h30V70H90v20zM30 30h40v40H30V30zm10 10v20h20V40H40z' fill='%23111827'/%3E%3C/svg%3E") no-repeat center;
            background-size: contain;
            margin-bottom: 15px;
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
            margin-bottom: 20px;
        }

        .badge-signature-wrapper {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .badge-signature-line {
            width: 150px;
            border-top: 1px solid #6b7280;
            margin-bottom: 4px;
        }

        .badge-signature-text {
            font-size: 9px;
            color: #6b7280;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* REGLAS DE IMPRESIÓN */
        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .badge-card {
                box-shadow: none;
                border: 1px solid #9ca3af;
            }
        }
    </style>
</head>
<body>

    <!-- Area de Herramientas (No imprimible) -->
    <div class="no-print-area no-print">
        <div class="no-print-info">
            <strong>Impresión del Carnet Estudiantil</strong><br>
            Asegúrese de configurar los márgenes como "Ninguno" o "Mínimos" y activar la opción de "Gráficos de fondo" en su navegador antes de proceder.
        </div>
        <button class="btn-imprimir" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 2px;">
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0-2 2h1v3a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-3h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Imprimir Carnet
        </button>
    </div>

    <!-- Contenedor de Carnets -->
    <div class="print-container">
        <!-- ANVERSO -->
        <div class="badge-card">
            <div class="badge-header">
                <h4>República Bolivariana de Venezuela</h4>
                <p>U.E. Colegio Bolivariano de Gestión Escolar</p>
            </div>
            <div class="badge-body">
                <div class="badge-photo-wrapper">
                    <div class="badge-photo-placeholder">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="badge-student-name">{{ $estudiante['nombre'] }}</h3>
                <span class="badge-student-id">{{ $estudiante['cedula'] }}</span>
                
                <div class="badge-info-grid">
                    <div class="badge-info-item">
                        <span class="badge-info-label">Nivel / Año</span>
                        <span class="badge-info-value">{{ $estudiante['grado'] }}</span>
                    </div>
                    <div class="badge-info-item">
                        <span class="badge-info-label">Sección</span>
                        <span class="badge-info-value">{{ $estudiante['seccion'] }}</span>
                    </div>
                    <div class="badge-info-item">
                        <span class="badge-info-label">Año Escolar</span>
                        <span class="badge-info-value">{{ $estudiante['anio'] }}</span>
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

</body>
</html>

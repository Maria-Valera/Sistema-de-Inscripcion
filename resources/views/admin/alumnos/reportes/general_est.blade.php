<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte General del Estudiante</title>
    <style>
        @page {
            margin: 0.8cm 1cm 2cm 1cm;
            size: portrait;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 8pt;
            line-height: 1.2;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
        }

        /* CINTILLO SUPERIOR */
        .cintillo {
            background-color: #1589e1;
            padding: 3px 10px;
            margin-bottom: 5px;
        }

        .cintillo table {
            border: none;
        }

        .cintillo td {
            border: none;
            padding: 2px;
            color: #fff;
            vertical-align: middle;
        }

        .cintillo img {
            max-height: 35px;
            max-width: 80px;
            object-fit: contain;
        }

        .cintillo-text {
            font-size: 7pt;
            text-align: right;
        }

        /* ENCABEZADO INSTITUCIÓN */
        .institution-header {
            border: 2px solid #1589e1;
            padding: 8px;
            text-align: center;
            margin-bottom: 8px;
            background-color: #f8f9fa;
        }

        .institution-header h1 {
            color: #1589e1;
            margin: 0 0 3px 0;
            font-size: 14pt;
            font-weight: bold;
        }

        .institution-subtitle {
            font-size: 9pt;
            color: #666;
            margin: 0;
            font-style: italic;
        }

        /* ENCABEZADO REPORTE */
        .header {
            text-align: center;
            margin: 0 0 8px 0;
            padding: 6px 10px;
            background: #e9ecef;
            color: #333;
            border: 1px solid #ced4da;
            border-radius: 3px;
        }

        .header h1 {
            color: #333;
            margin: 0 0 3px 0;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header h2 {
            color: #333;
            margin: 4px 0;
            font-size: 10pt;
            font-weight: 600;
        }

        .header h3 {
            color: #333;
            margin: 4px 0;
            font-size: 9pt;
            font-weight: 500;
        }

        .header p {
            margin: 0;
            font-size: 7.5pt;
            font-style: italic;
            opacity: 0.95;
        }

        h2 {
            color: #1589e1;
            font-size: 10pt;
            margin: 15px 0 8px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #ced4da;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            position: relative;
        }

        h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 60px;
            height: 2px;
            background: #1589e1;
            border-radius: 2px;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
            background: white;
            padding: 12px 15px;
            border-radius: 6px;
            border: 1px solid #ced4da;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 0 0;
            page-break-inside: avoid;
            page-break-before: avoid;
            background: white;
        }

        th, td {
            padding: 4px 5px;
            text-align: left;
            border: 1px solid #000000;
            vertical-align: middle;
            word-break: break-word;
        }

        th {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.3px;
            text-align: center;
        }

        tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        tr:last-child {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background-color: #f0f7ff;
        }

        tr:hover {
            background-color: #e3f2fd;
        }

        .foto-perfil {
            width: 110px;
            height: 140px;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            margin-left: 20px;
            float: right;
            border-radius: 4px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .text-muted {
            color: #666;
            font-size: 8pt;
        }

        /* Estilo para filas específicas */
        td:first-child {
            font-weight: 600;
            color: #1589e1;
            width: 25%;
        }

        /* Estilo para el logo wrapper original */
        .logo-wrapper {
            display: flex;
            align-items: center;
        }

        /* ESTILOS PARA IMPRESIÓN */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                page-break-inside: auto;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            .section {
                box-shadow: none;
            }

            .institution-header {
                box-shadow: none;
            }

            tr:hover {
                background-color: inherit;
            }
        }
    </style>
</head>
<body class="clearfix">

    <div class="container">
        <!-- CINTILLO SUPERIOR -->
        <table width="100%" class="cintillo cintillo-color encabezado">
            <tr style="color:#eee;">
                <!-- IZQUIERDA -->
                <td width="33%" class="text-left">
                    <img src="{{ public_path('img/logo-ven.webp') }}" alt="Logo" width="90" height="30">
                </td>

                <!-- CENTRO -->
                <td width="34%" class="text-center" style="text-align: center">
                    <strong class="titulo-principal" style="font-size:14px;">
                        LICEO GRAL. JUAN GUILLERMO IRIBARREN
                    </strong><br>
                    PORTUGUESA – ARAURE
                </td>

                <!-- DERECHA -->
                <td width="33%" class="text-right" style="font-size:9px;">
                    Ministerio del Poder Popular<br>
                    para la <b>Educación</b>
                </td>
            </tr>
        </table>

        <!-- ENCABEZADO REPORTE -->
        <div class="header">
            <h1>FICHA DE REPORTES PARA ESTUDIANTE REGISTRADOS</h1>
            <h2>ESTUDIANTE {{ $alumno->persona->primer_nombre ?? 'N/A' }} {{ $alumno->persona->primer_apellido ?? 'N/A' }}</h2>
            <h3>CÉDULA: {{ $alumno->persona->tipoDocumento->nombre ?? 'N/A' }}-{{ $alumno->persona->numero_documento ?? 'N/A' }}</h3>
        </div>
        
        <h2>1.- DATOS PERSONALES</h2>
        <div class="section">
            <table class="form-table">
                <tr>
                    <td>Nombres</td>
                    <td>{{ $alumno->persona->primer_nombre ?? 'N/A' }}                        
                        {{ $alumno->persona->segundo_nombre ?? '' }}
                        {{ $alumno->persona->tercer_nombre ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td>Apellidos</td>
                    <td>{{ $alumno->persona->primer_apellido ?? 'N/A' }}                        
                        {{ $alumno->persona->segundo_apellido ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td>Número de cédula</td>
                    <td>{{ $alumno->persona->tipoDocumento->nombre ?? 'N/A' }}-{{ $alumno->persona->numero_documento ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Fecha de nacimiento</td>
                    <td>{{ \Carbon\Carbon::parse($alumno->persona->fecha_nacimiento)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                     <td>Edad</td>
                    <td>{{ \Carbon\Carbon::parse($alumno->persona->fecha_nacimiento)->age }}</td>
                </tr>
                @if(!auth()->user()?->esSoloDocente())
                <tr>
                    <td>Lugar de Nacimiento</td>
                    <td>
                        <strong>País:</strong> {{ $alumno->persona->localidad->estadoThroughMunicipio->pais->nameES ?? 'N/A' }} |
                        <strong>Estado:</strong> {{ $alumno->persona->localidad->estadoThroughMunicipio->nombre_estado ?? 'N/A' }} |
                        <strong>Municipio:</strong> {{ $alumno->persona->localidad->municipio->nombre_municipio ?? 'N/A' }} |
                        <strong>Localidad:</strong> {{ $alumno->persona->localidad->nombre_localidad ?? 'N/A' }}
                    </td>
                </tr>
                @endif
                <tr>
                    <td>Género</td>
                    <td>{{ $alumno->persona->genero->genero ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        
        <h2>2.- INFORMACIÓN DE SALUD</h2>
        <div class="section">
            <table class="form-table">
                <tr>
                    <td>Discapacidad</td>
                    <td>
                        @if ($alumno->discapacidades && $alumno->discapacidades->count() > 0)
                            {{ $alumno->discapacidades->pluck('nombre_discapacidad')->implode(', ') }}
                        @else
                            Ninguna registrada
                        @endif
                    </td>
                </tr>
                @if(!auth()->user()?->esSoloDocente())
                <tr>
                    <td>Etnia</td>
                    <td>{{ $alumno->etniaIndigena->nombre ?? 'No pertenece a ninguna etnia indígena' }}</td>
                </tr>
                <tr>
                    <td>Orden de nacimiento</td>
                    <td>{{ $alumno->ordenNacimiento->orden_nacimiento ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Lateralidad</td>
                    <td>{{ $alumno->lateralidad->lateralidad ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Peso</td>
                    <td>{{ $alumno->peso ?? 'N/A' }} kg</td>
                </tr>
                <tr>
                    <td>Estatura</td>
                    <td>{{ $alumno->estatura ?? 'N/A' }} m</td>
                </tr>
                <tr>
                    <td>Talla camisa</td>
                    <td>{{ $alumno->talla_camisa_nombre ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Talla pantalón</td>
                    <td>{{ $alumno->talla_pantalon_nombre ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Talla de zapatos</td>
                    <td>{{ $alumno->talla_zapato ?? 'N/A' }}</td>
                </tr>
                @endif
            </table>
        </div>

        <p class="footer-info">
            Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}
        </p>
    </div>  
        <script type="text/php">
            if (isset($pdf)) {
                $pdf->page_text(40, 800, "Generado por: {{ Auth::user()->name ?? 'Sistema' }} - {{ date('d/m/Y H:i:s') }}", null, 7, array(90, 90, 90));
                $pdf->page_text(700, 800, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 8, array(0, 0, 0));
            }
        </script>  
</body>
</html>